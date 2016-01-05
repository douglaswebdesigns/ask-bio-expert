<?php

abstract class CMA_BaseController
{
	
    const TITLE_SEPARATOR = '&gt;';
    const MESSAGE_SUCCESS = 'success';
    const MESSAGE_ERROR = 'error';
    
    const ADMIN_SETTINGS = 'CMA_admin_settings';
    const ADMIN_ABOUT = 'CMA_admin_about';
    const ADMIN_ADDONS = 'CMA_addons';
    const ADMIN_USER_GUIDE = 'CMA_user_guide';
    const ADMIN_LOGS = 'CMA_logs';
    const ADMIN_IMPORT = 'CMA_import';
    const ADMIN_UNMARK_SPAM = 'CMA_unmark_spam';
    const ADMIN_PRIVATE_ANSWER = 'CMA_private_answer';
    const ADMIN_SETTINGS_SEARCH_USERS = 'CMA_settings_search_users';
    const ADMIN_SETTINGS_USER_GET_ITEM = 'CMA_settings_search_users_get_item';
    const ADMIN_BP_NOTIFY = 'CMA_BP_notify';
    
    const FAKE_POST_TYPE = 'page';
    const FAKE_POST_META_KEY = 'cma_empty_dummy_page';
    
    const OPTION_TITLES = 'CMA_panel_titles';
    
    const CUSTOM_IMPORT = 0;

    /**
     * Reference to the original main query.
     *
     * @var WP_Query
     */
    public static $query;

    protected static $populatedData = array();
    
    protected static $_titles = array();
    protected static $_fired = false;
    protected static $_pages = array();
    protected static $_params = array();
    protected static $_errors = array();
    protected static $_customPostTypes = array();

    public static function init()
    {
        add_action('init', array(get_class(), 'registerPages'), 2);
    }

    protected static function _addAdminPages()
    {
    	
    	if (is_admin() AND isset($_GET['page']) AND $_GET['page'] == self::ADMIN_SETTINGS) {
    		add_action('init', array(__CLASS__, 'processClearDatabase'), 998);
    		add_action('init', array(__CLASS__, 'processSettings'), 999);
    	}
    	
    	if (isset($_GET['page'])) {
    		if ($_GET['page'] == self::ADMIN_LOGS AND !empty($_GET['action']) AND CMA_LogsController::isActionBeforeRender($_GET['action'])) {
	    		CMA_LogsController::init();
    		}
    		else if ($_GET['page'] == self::ADMIN_SETTINGS_SEARCH_USERS) {
    			self::settingsSearchUsers();
    		}
    		else if ($_GET['page'] == self::ADMIN_SETTINGS_USER_GET_ITEM) {
    			self::settingsSearchUsersGetItem();
    		}
    		else if ($_GET['page'] == self::ADMIN_IMPORT AND self::_isPost() AND !empty($_POST['step'])) {
    			self::processCustomImport();
    		}
    		else if ($_GET['page'] == self::ADMIN_UNMARK_SPAM) {
    			self::processUnmarkSpam();
    		}
    		else if ($_GET['page'] == self::ADMIN_PRIVATE_ANSWER) {
    			self::processPrivateAnswer();
    		}
    		else if ($_GET['page'] == self::ADMIN_BP_NOTIFY) {
    			CMA_BuddyPress::notifyAllUsers();
    		}
    	}
        add_action('CMA_custom_post_type_nav', array(get_class(), 'addCustomPostTypeNav'), 1, 1);
        add_action('CMA_custom_taxonomy_nav', array(get_class(), 'addCustomTaxonomyNav'), 1, 1);
        if (current_user_can('manage_options')) {
        	add_action('admin_menu', array(get_class(), 'registerAdminPages'), 15);
        }
    }
    
    
    public static function getContributorUrl($user) {
    	if (is_scalar($user)) $user = get_userdata($user);
    	if ($customUrl = CMA_Settings::getOption(CMA_Settings::OPTION_CONTRIBUTOR_CUSTOM_URL)) {
    		return str_replace(array('%s', '%d'), array($user->user_nicename, $user->ID), $customUrl);
    	} else {
    		return CMA_BaseController::getUrl('contributor', $user->user_nicename);
    	}
    }
    
    protected static function processCustomImport() {
    	
    	if (!self::CUSTOM_IMPORT) exit;
    	
    	if ($_POST['step'] == 'clear') {
    		global $wpdb;
    		$wpdb->query("CREATE TEMPORARY TABLE temp_imported_posts (ID bigint(20))");
			$wpdb->query("INSERT INTO temp_imported_posts SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = 'cma_import_old_id'");
			$wpdb->query("DELETE FROM $wpdb->postmeta WHERE post_id IN (SELECT ID FROM temp_imported_posts)");
    		$wpdb->query("DELETE FROM $wpdb->posts WHERE ID IN (SELECT ID FROM temp_imported_posts)");
    		$wpdb->query("CREATE TEMPORARY TABLE temp_imported_comments (ID bigint(20))");
    		$wpdb->query("INSERT INTO temp_imported_comments SELECT DISTINCT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'cma_import_old_id'");
    		$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_id IN (SELECT ID FROM temp_imported_comments)");
    		$wpdb->query("DELETE FROM $wpdb->commentmeta WHERE comment_id IN (SELECT ID FROM temp_imported_comments)");
    		$wpdb->query("CREATE TEMPORARY TABLE temp_imported_users (ID bigint(20))");
			$wpdb->query("INSERT INTO temp_imported_users SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key = 'cma_import_old_id'");
			$wpdb->query("DELETE FROM $wpdb->usermeta WHERE user_id IN (SELECT ID FROM temp_imported_users)");
    		$wpdb->query("DELETE FROM $wpdb->users WHERE ID IN (SELECT ID FROM temp_imported_users)");
    		echo '<strong style="color:#009900;">Answers has been cleared</strong>';
    		exit;
    	}
    	
    	if (empty($_POST['database']) OR empty($_POST['host']) OR empty($_POST['user']) OR empty($_POST['pass'])) {
    		die('Enter database access credentials.');
    	}
    	
    	require_once CMA_PATH . '/lib/helpers/import/OsqaImport.php';
    	try {
    		$import = new CMA_OsqaImport($_POST['host'], $_POST['database'], $_POST['user'], $_POST['pass']);
    	} catch (PDOException $e) {
    		echo '<strong style="color:#990000">Failed to connect with database.</strong>';
    		exit;
    	}
    	switch ($_POST['step']) {
    		case 'database':
    			echo '<div>Database connection is OK</div>';
    			break;
    		case 'users':
    			$import->importUsers();
    			break;
    		case 'questions':
    			$import->importQuestions();
    			break;
    		case 'answers':
    			$import->importAnswers();
    			break;
    		case 'comments':
    			$import->importComments();
    			break;
    		case 'passwords':
    			$import->sendUsersPasswords();
    			break;
    			
    	}
    	exit;
    }
    

    public static function initSessions()
    {
        if (!session_id() AND !headers_sent()) session_start();
        add_action('wp_logout', array(get_class(), 'endSessions'));
        add_action('wp_login', array(get_class(), 'endSessions'));
    }


    public static function endSessions()
    {
    	
//         self::initSessions();
        if( session_id() AND !headers_sent())
        {
//             session_regenerate_id(true);
//             session_destroy();
//             unset($_SESSION);
//             self::initSessions();
        }
    }

    public static function initialize()
    {
    	add_action('cma_flash_messages', array(get_class(), 'showFlashMessages'));
    	add_filter('cma_admin_nav', array(__CLASS__, 'getAdminNav'));
    }
    
    
    static function showFlashMessages() {
    	echo self::_loadView('answer/meta/flash-messages', array('messages' => CMA_FlashMessage::pop()));
    }
    

    public static function registerPages()
    {
    	if (CMA::isLicenseOk()) {
	        add_action('generate_rewrite_rules', array(get_class(), 'registerRewriteRules'));
	        add_filter('query_vars', array(get_class(), 'registerQueryVars'));
	        add_filter('wp_title', array(get_class(), '_showPageTitle'), 1, 3);
	        add_action('pre_get_posts', array(get_class(), 'setQueryVars'), 1, 1);
	        add_filter('the_posts', array(get_class(), 'editQuery'), 10, 2);
	        add_filter('the_content', array(get_class(), 'showPageContent'), 10, 1);
    	}
    }

    public static function setQueryVars($query)
    {
        if( $query->is_home )
        {
            foreach(self::$_pages as $page)
            {
                if( isset($query->query_vars[$page['query_var']]) && $query->query_vars[$page['query_var']] == 1 )
                {
                    $query->is_home = false;
                }
            }
        }
    }

    public static function registerRewriteRules($rules)
    {
        $newRules = array();
        $additional = array();

        foreach(self::$_pages as $page)
        {
            if( is_array($page['slug']) )
            {
                foreach($page['slug'] as $slug)
                {
                    if( $slug == 'contributor/index' )
                    {
                        $newRules['^contributor/([^/]*)/?'] = 'index.php?CMA-contributor-index=1&contributor=$matches[1]';
                    }

                    if( strpos($slug, '/') === false )
                    {
                        $additional['^' . $slug . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
                    }
                    else
                    {
                        $newRules['^' . $slug . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
                    }
                }
            }
            else $newRules['^' . $page['slug'] . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
        }

        $rules->rules = $newRules + $additional + $rules->rules;

        return $rules->rules;
    }

    public static function flush_rules()
    {
        $rules = get_option('rewrite_rules');
        foreach(self::$_pages as $page)
        {
            if(is_string($page['slug']) && !isset($rules['^' . $page['slug'] . '(?=\/|$)']) )
            {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
                return;
            }
        }
    }

    public static function registerQueryVars($query_vars)
    {
        self::flush_rules();
        foreach(self::$_pages as $page)
        {
            $query_vars[] = $page['query_var'];
        }
        return $query_vars;
    }

    protected static function _registerAction($query_var, $args = array())
    {
        $slug = $args['slug'];
        $contentCallback = isset($args['contentCallback']) ? $args['contentCallback'] : null;
        $headerCallback = isset($args['headerCallback']) ? $args['headerCallback'] : null;
        $title = !empty($args['title']) ? $args['title'] : ucfirst($slug);
        $titleCallback = isset($args['titleCallback']) ? $args['titleCallback'] : null;
        self::$_pages[$query_var] = array(
            'query_var'       => $query_var,
            'slug'            => $slug,
            'title'           => $title,
            'titleCallback'   => $titleCallback,
            'contentCallback' => $contentCallback,
            'headerCallback'  => $headerCallback,
            'viewPath'        => $args['viewPath'],
            'controller'      => $args['controller'],
            'action'          => $args['action']
        );
    }

    /**
     * Locate the template file, either in the current theme or the public views directory
     *
     * @static
     * @param array $possibilities
     * @param string $default
     * @return string
     */
    protected static function locateTemplate($possibilities, $default = '')
    {
    	
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_SUPPORT_THEME_DIR)) {
	        /*
	         *  check if the theme has an override for the template
	         */
	        $theme_overrides = array();
	        foreach($possibilities as $p)
	        {
	            $theme_overrides[] = 'CMA/' . $p . '.phtml';
	        }
	        if( $found = locate_template($theme_overrides, FALSE) )
	        {
	            return $found;
	        }
    	}

        /*
         *  check for it in the public directory
         */
        foreach($possibilities as $p)
        {
            if( file_exists(CMA_PATH . '/views/frontend/' . $p . '.phtml') )
            {
                return CMA_PATH . '/views/frontend/' . $p . '.phtml';
            }
        }

        /*
         *  we don't have it
         */
        return $default;
    }
    
    
    
    static function isAjax() {
    	return ( ! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]) == 'xmlhttprequest' );
    }
    

    static protected function prepareSinglePage($title, $content, $newQuery = false) {
    	global $wp, $wp_query, $wp_the_query, $post;
    	
    	// Call this filter to set the WP SEO title before the $wp_query instance will be replaced:
    	$wp_seo_title = apply_filters('wp_title', $title, '', '');
    	
    	if ($newQuery) {
    		$wp_query = new WP_Query(array('post_type' => self::FAKE_POST_TYPE, 'meta_key' => self::FAKE_POST_META_KEY, 'meta_value' => '1'));
    		if ($wp_query->post_count == 0 OR empty($wp_query->posts) OR empty($wp_query->posts[0])) {
	    		$wp_query->posts[0] = $wp_query->post = self::getFakePost();
	    		$wp_query->found_posts = $wp_query->post_count = 1;
    		}
    		$wp_query->posts[0]->post_title = $title;
    		$wp_query->posts[0]->post_content = $content;
    		$post = $wp_query->post = reset($wp_query->posts);
    		if (!empty(self::$query->query_vars['cma_homepage'])) {
    			$wp_query->is_home = true;
    			self::$query->is_home = true;
    			// Added 2015-08-21:
    			$wp_query->is_single = true;
    			$wp_query->is_singular = true;
    			$wp_query->is_page = true;
    		} else {
    			$wp_query->is_single = true;
    			$wp_query->is_singular = true;
    			$wp_query->is_page = true;
    		}
    		$wp_the_query = $wp_query;
    	}
    	
    	$wp_query->set('cma_prepared_single', 1);
    	$wp_query->set('cma_title', $title);
    	$wp_query->set('cma_page_content', $content);
    	add_filter('the_title', array(__CLASS__, 'filterTitle'));
    	remove_filter('the_content', 'wpautop');
    	return locate_template(array('page.php', 'single.php'), false, false);
    }
    
    
    
    static function filterTitle($title) {
    	global $wp_query;
    	$fakePost = self::getFakePost();
    	if (is_main_query() AND is_single() AND get_query_var('cma_prepared_single') AND $cmaTitle = get_query_var('cma_title')
    			AND $title == str_replace('-', '&#8211;', $fakePost->post_title)) {
    		$title = $cmaTitle;
    	}
    	return $title;
    }
    

    public static function _showPageTitle($title, $sep = '', $seplocation = 'right')
    {
    	if ( CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_AS_HOMEPAGE) AND !empty(self::$query->query_vars['cma_homepage']) ) {
	    	$title = get_bloginfo( 'name', 'display' );
			$site_description = get_bloginfo( 'description', 'display' );
			if ( $site_description ) {
				$title .= " $sep $site_description";
			}
    	}
        else foreach(self::$_pages as $page)
        {
            if( self::$query->get($page['query_var']) == 1 )
            {
                if( !empty($page['titleCallback']) ) $title = call_user_func($page['titleCallback']);
                else $title = self::$_titles[$page['controller'] . '-' . $page['action']] ? self::$_titles[$page['controller'] . '-' . $page['action']] : $page['title'];
                if( !empty($sep) )
                {
                    $title = str_replace(self::TITLE_SEPARATOR, $sep, $title);
                    if( $seplocation == 'right' ) $title.=' ' . $sep . ' ';
                    else $title = ' ' . $sep . ' ' . $title;
                }
                break;
            }
        }
        return $title;
    }
    
    
    public static function settingsSearchUsers() {
    	$result = array();
    	if (!empty($_GET['q']) AND strlen(trim($_GET['q'])) > 0) {
	    	$users = get_users(array('search' => trim($_GET['q']) .'*', 'number' => 10));
	    	foreach ($users as $user) {
	    		$result[] = $user->user_login;
	    	}
    	}
    	echo implode(PHP_EOL, $result);
    	exit;
    }
    
    
    public static function settingsSearchUsersGetItem() {
    	if (!current_user_can('manage_options')) exit;
    	if (!empty($_GET['q']) AND strlen(trim($_GET['q'])) > 0 AND !empty($_GET['fieldName'])) {
    		if ($user = get_user_by('login', $_GET['q'])) {
    			echo CMA_SettingsViewAbstract::renderUsersListItem($_GET['fieldName'], $user->ID, $user->user_login);
    		}
    	}
    	exit;
    }
    

    public static function editQuery($posts, WP_Query $wp_query)
    {
        if( !self::$_fired )
        {
            foreach(self::$_pages as $page)
            {
                if( $wp_query->get($page['query_var']) == 1 )
                {
                    remove_all_actions('wpseo_head');
                    if( !empty($page['headerCallback']) )
                    {
                        self::$_fired = true;
                        call_user_func($page['headerCallback']);
                    }
                    
                    /*
                     * create a fake post
                     */
                    $page_slug = (is_array($page['slug']) ? reset($page['slug']) : $page['slug']);
                    $post = new stdClass;
                    $post->post_author = 1;
                    $post->post_name = $page_slug;
                    $post->guid = get_bloginfo('wpurl') . '/' . $page_slug;
                    $post->post_title = self::_showPageTitle($page['title']);
                    /*
                     * put your custom content here
                     */
                    $post->post_content = 'Content Placeholder';
                    /*
                     * just needs to be a number - negatives are fine
                     */
                    $post->ID = -42;
                    $post->post_status = 'static';
                    $post->comment_status = 'closed';
                    $post->ping_status = 'closed';
                    $post->comment_count = 0;
                    /*
                     * dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
                     */
                    $post->post_date = current_time('mysql');
                    $post->post_date_gmt = current_time('mysql', 1);

                    $posts = NULL;
                    $posts[] = apply_filters('cma_edit_query_post', $post);

                    $wp_query->is_page = true;
                    $wp_query->is_singular = true;
                    $wp_query->is_home = false;
                    $wp_query->is_archive = false;
                    $wp_query->is_category = false;
                    unset($wp_query->query["error"]);
                    $wp_query->query_vars["error"] = "";
                    add_filter('template_include', array(get_class(), 'overrideBaseTemplate'));
                    self::$_fired = true;
                    break;
                }
            }
        }
        return $posts;
    }

    public static function overrideBaseTemplate($template)
    {
        $template = self::locateTemplate(array(
                    'page'
                        ), $template);
        return $template;
    }

    public static function showPageContent($content)
    {
        foreach(self::$_pages as $page)
        {
            if( self::$query->get($page['query_var']) == 1 )
            {
                if( !empty(self::$_errors) )
                {
                    $viewParams = call_user_func(array('CMA_ErrorController', 'errorAction'));
                    ob_start();
                    echo self::_loadView('error', $viewParams);
                    $content = ob_get_clean();
                }
                else
                {
                    $viewParams = array();
                    if( !empty($page['contentCallback']) ) $viewParams = call_user_func($page['contentCallback']);
                    ob_start();
                    do_action('cma_flash_messages');
                    $viewParams['checkPermissions'] = true;
                    echo self::_loadView($page['viewPath'], $viewParams);
                    $content = ob_get_clean();
                }
                break;
            }
        }
        return $content;
    }
    

    public static function _loadView($_name, $_params = array())
    {
        $canBeViewed = (empty($_params['checkPermissions']) OR CMA_Thread::canBeViewed());
        if( !$canBeViewed )
        {
            if( isset($_params['widget']) && $_params['widget'] ) return '';

            $path = CMA_PATH . '/views/frontend/permissions.phtml';
            $_name = 'permissions';
            $_params = array('contentOnly' => true);
        }
        else
        {
            $path = CMA_PATH . '/views/frontend/' . $_name . '.phtml';
        }
        
        $template = self::locateTemplate(array($_name), $path);
        if( !empty($_params) ) extract($_params);
        ob_start();
        require($template);
        return ob_get_clean();
    }

    protected static function _getSlug($controller, $action, $single = false)
    {
        if( $action == 'index' ) if( $single ) return $controller;
            else return array(
                    $controller . '/' . $action,
                    $controller
                );
        else return $controller . '/' . $action;
    }

    protected static function _getTitle($controller, $action, $hasBody = false)
    {
        $title = apply_filters('CMA_title_controller', ucfirst($controller)) . ' ' . self::TITLE_SEPARATOR . ' ' . apply_filters('CMA_title_action', ucfirst($action));

        if( !isset(self::$_titles[$controller . '-' . $action]) && $hasBody )
        {
            self::$_titles[$controller . '-' . $action] = $title;
        }

        return $title;
    }

    /**
     * Get query parameter which indicates which action from which controller we should call
     * @param type $controller
     * @param type $action
     * @return type
     */
    protected static function _getQueryArg($controller, $action)
    {
        return "CMA-{$controller}-{$action}";
    }

    protected static function _getViewPath($controller, $action)
    {
        return $controller . '/' . $action;
    }
    

    public static function bootstrap()
    {
    	
    	global $wp_query;
    	// Create local reference to the main query:
    	if (empty(self::$query)) {
    		self::$query = $wp_query;
    	}
    	
    	CMA_Thread::initAnonymousVotingCookie();
    	
    	self::initSessions();
    	
    	add_filter('preprocess_comment', function($commentdata) { // Resolve conflict with Responsible Shop Theme
    		$types = array(CMA_Answer::COMMENT_TYPE, CMA_Comment::COMMENT_TYPE, CMA_Thread::POST_TYPE);
    		if (!empty($commentdata['comment_type']) AND in_array($commentdata['comment_type'], $types)) {
	    		$className = 'white_label_themes';
		    	if (class_exists($className) AND isset($GLOBALS['CORE']) AND is_object($GLOBALS['CORE']) AND get_class($GLOBALS['CORE']) == $className) {
		    		remove_filter('preprocess_comment', array($GLOBALS['CORE'], '_preprocess_comment'));
		    	}
    		}
	    	return $commentdata;
    	}, 1);
    	add_filter('comment_row_actions', array(__CLASS__, 'adminCommentRowActionsFilter'), 10, 2);
    	
    	// Initialize CMA fake page
    	self::initFakePage();
    	
    	// Initialize custom Questions Index page
    	CMA_Shortcodes::initCustomQuestionsIndexPage();
    	CMA_RelatedQuestionsMetaBox::bootstrap();
        
        self::_addAdminPages();
        self::$_titles = get_option(self::OPTION_TITLES, array());
        $controllersDir = dirname(__FILE__);

        /*
         * Loop the controllers
         */
        foreach(scandir($controllersDir) as $name)
        {
            if( $name != '.' && $name != '..' && $name != basename(__FILE__) && strpos($name, 'Controller.php') !== false )
            {
                $controllerName = substr($name, 0, strpos($name, 'Controller.php'));
                $controllerClassName = CMA_PREFIX . $controllerName . 'Controller';
                $controller = strtolower($controllerName);
                include_once $controllersDir . DIRECTORY_SEPARATOR . $name;
                $controllerClassName::initialize();
                $args = array();

                /*
                 * Loop the methods in each controller
                 */
                foreach(get_class_methods($controllerClassName) as $methodName)
                {
                    if( strpos($methodName, 'Action') !== false && substr($methodName, 0, 1) != '_' )
                    {
                        $action = substr($methodName, 0, strpos($methodName, 'Action'));
                        $query_arg = self::_getQueryArg($controller, $action);
                        $newArgs = array(
                            'query_arg'       => self::_getQueryArg($controller, $action),
                            'slug'            => self::_getSlug($controller, $action),
                            'title'           => self::_getTitle($controller, $action, true),
                            'viewPath'        => self::_getViewPath($controller, $action),
                            'contentCallback' => array($controllerClassName, $methodName),
                            'controller'      => $controller,
                            'action'          => $action
                        );
                        if( !isset($args[$query_arg]) ) $args[$query_arg] = array();
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    }
                    elseif( strpos($methodName, 'Header') !== false && substr($methodName, 0, 1) != '_' )
                    {
                        $action = substr($methodName, 0, strpos($methodName, 'Header'));
                        $query_arg = self::_getQueryArg($controller, $action);
                        $newArgs = array(
                            'query_arg'      => self::_getQueryArg($controller, $action),
                            'slug'           => self::_getSlug($controller, $action),
                            'title'          => self::_getTitle($controller, $action),
                            'viewPath'       => self::_getViewPath($controller, $action),
                            'headerCallback' => array($controllerClassName, $methodName),
                            'controller'     => $controller,
                            'action'         => $action
                        );
                        if( !isset($args[$query_arg]) ) $args[$query_arg] = array();
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    }
                    elseif( strpos($methodName, 'Title') !== false && substr($methodName, 0, 1) != '_' )
                    {
                        $action = substr($methodName, 0, strpos($methodName, 'Title'));
                        $query_arg = self::_getQueryArg($controller, $action);
                        $newArgs = array(
                            'query_arg'     => self::_getQueryArg($controller, $action),
                            'slug'          => self::_getSlug($controller, $action),
                            'title'         => self::_getTitle($controller, $action),
                            'viewPath'      => self::_getViewPath($controller, $action),
                            'titleCallback' => array($controllerClassName, $methodName),
                            'controller'    => $controller,
                            'action'        => $action
                        );
                        if( !isset($args[$query_arg]) ) $args[$query_arg] = array();
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    }
                }

                foreach($args as $query_arg => $data)
                {
                    self::_registerAction($query_arg, $data);
                }
            }
        }


        if (is_admin() AND basename($_SERVER['REQUEST_URI']) == 'edit-comments.php') { // Filter answers from wp comments list
        	add_filter('comments_clauses', array(__CLASS__, 'filterWPCommentsList'));
        }
        
        self::registerPages();
        self::replaceCommentSystem();
    }
    
    
    
    protected static function initFakePage() {
    	global $wpdb;
    	$record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts p
    		JOIN $wpdb->postmeta m ON m.post_id = p.ID AND m.meta_key = %s
    		WHERE post_type = %s",
    		self::FAKE_POST_META_KEY, self::FAKE_POST_TYPE));
    	if (empty($record)) { // Post does not exists
    		$post = array(
    			'post_title' => CMA_Labels::getLocalized('index_page_title'),
    			'post_name' => self::getFakePostName(),
    			'post_content' => 'CM Answers',
    			'post_author' => get_current_user_id(),
    			'post_status' => 'future',
    			'post_date' => Date('Y-m-d H:i:s', time() + 3600*24*365*10),
    			'post_type' => self::FAKE_POST_TYPE,
    			'comment_status' => 'closed',
    			'ping_status' => 'closed',
    		);
    		if ($postId = wp_insert_post($post)) {
    			add_post_meta($postId, self::FAKE_POST_META_KEY, 1);
    		}
    	} else { // Change back to "future"
    		if ($record->post_status != 'future') {
    			$record->post_status = 'future';
    			$record->post_date = Date('Y-m-d H:i:s', time() + 3600*24*365*10);
    			wp_update_post($record);
    		}
    	}
    }
    
    
    static function getFakePost() {
    	global $wpdb;
    	$record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts p
    		JOIN $wpdb->postmeta m ON m.post_id = p.ID AND m.meta_key = %s
    		WHERE post_type = %s",
    		self::FAKE_POST_META_KEY, self::FAKE_POST_TYPE));
    	if ($record) {
    		$post = new WP_Post($record);
    		return $post;
    	}
    }
    
    
    static function getFakePostName() {
    	return CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_PERMALINK);
    }
    
    
    protected static function replaceCommentSystem() {
    	
    	$currentPostId = null;
    	$category = null;
    	add_filter('comments_array', function($comments, $postId) use (&$category) {
    		$category = CMA_Category::getPostCommentsCategory($postId);
    		if (is_main_query() AND get_post_type() != CMA_Thread::POST_TYPE AND !empty($category) AND !empty($category->term_id)) {
	    		$posts = get_posts(array('category' => $category->term_id, 'post_type' => CMA_Thread::POST_TYPE));
	    		return $posts;
    		} else {
    			return $comments;
    		}
    	}, 10, 2);
    	
    	add_filter( 'comments_template', function($theme_template) use (&$category) {
    		if (is_main_query() AND get_post_type() != CMA_Thread::POST_TYPE AND !empty($category) AND !empty($category->term_id)) {
    			return CMA_PATH . '/views/frontend/comment/comments.phtml';
    		} else {
    			return $theme_template;
    		}
    	});
    	
    	add_action('post_comment_status_meta_box-options', function($post) { // Add select box with CMA categories
    		$categories = CMA_Category::getCategoriesTree(null, 0, $onlyVisible = false);
    		$currentCategory = CMA_Category::getPostCommentsCategory($post->ID);
    		echo CMA_BaseController::_loadView('../backend/hooks/post_comment_status_meta_box', compact('categories', 'post', 'currentCategory'));
    	});
    	
    	add_action('save_post', function($postId) { // Save CMA category ID for post/page
    		if (isset($_POST['cma_category'])) {
    			if (empty($_POST['cma_category'])) {
    				wp_set_post_terms($postId, array(), CMA_Category::TAXONOMY);
    			}
    			else if ($_POST['cma_category'] == 'new') {
    				if (!empty($_POST['cma_category_name'])) {
    					$category = (object)wp_insert_term($_POST['cma_category_name'], CMA_Category::TAXONOMY);
    					clean_term_cache(array(), CMA_Category::TAXONOMY);
    				}
    			} else {
    				$category = get_term($_POST['cma_category'], CMA_Category::TAXONOMY, OBJECT);
    			}
    			if (!empty($category) AND !empty($category->term_id)) {
    				CMA_Category::setPostCommentsCategoryId($postId, $category->term_id);
    			}
    		}
    	}, 10);
    	
    	add_action('save_page', function($postId) { // Save CMA category ID for post/page
    		if (isset($_POST['cma_category'])) {
    			if (empty($_POST['cma_category'])) {
    				wp_set_post_terms($postId, array(), CMA_Category::TAXONOMY);
    			}
    			else if ($_POST['cma_category'] == 'new') {
    				if (!empty($_POST['cma_category_name'])) {
    					$category = (object)wp_insert_term($_POST['cma_category_name'], CMA_Category::TAXONOMY);
    					clean_term_cache(array(), CMA_Category::TAXONOMY);
    				}
    			} else {
    				$category = get_term($_POST['cma_category'], CMA_Category::TAXONOMY, ARRAY_A);
    			}
    			if (!empty($category) AND !empty($category->term_id)) {
    				CMA_Category::setPostCommentsCategoryId($postId, $category->term_id);
    			}
    		}
    	}, 10);
    	
    }

    protected static function _getHelper($name, $params = array())
    {
        $name = ucfirst($name);
        include_once CMA_PATH . '/lib/helpers/' . $name . '.php';
        $className = CMA_PREFIX . $name;
        return new $className($params);
    }

    protected static function _isPost()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    public static function getUrl($controller, $action, $params = array())
    {
        $paramsString = '';
        $additionalParams = array();
        if( !empty($params) )
        {
            foreach($params as $key => $value)
            {
                if( strpos($value, '/') !== false ) $additionalParams[] = urlencode($key) . '=' . urlencode($value);
                else $paramsString.='/' . urlencode($key) . '/' . urlencode($value);
            }
        }
        $url = home_url(trailingslashit(self::_getSlug($controller, $action, true))) . preg_replace('/^\//', '', $paramsString);
        if( !empty($additionalParams) )
        {
            $url.='?' . implode('&', $additionalParams);
        }
        return $url;
    }

    /**
     * Get action param (from $_GET or uri - /name/value)
     * Marcin Dudek: WHY not just use get_query_var()?
     *
     * @param string $key
     * @return string
     * @author REC
     */
    public static function _getParam($name)
    {
        if( empty(self::$_params) )
        {
            $req_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $home_path = parse_url(home_url());
            $home_path = isset($home_path['path']) ? $home_path['path'] : '';
            $home_path = trim($home_path, '/');

            $req_uri = trim($req_uri, '/');
            $req_uri = preg_replace("/^". preg_quote($home_path, "/") . "/", '', $req_uri);
            $req_uri = trim($req_uri, '/');
            $parts = explode('/', $req_uri);
            if( !empty($parts) )
            {
                $params = array();
                for($i = count($parts) - 1; $i > 0; $i-=2)
                {
                    $params[$parts[$i - 1]] = $parts[$i];
                }
                self::$_params = $params + $_REQUEST;
            }
        }
        return isset(self::$_params[$name]) ? self::$_params[$name] : '';
    }

    protected static function _addError($msg)
    {
        self::$_errors[] = $msg;
    }

    protected static function _getErrors()
    {
        $errors = self::$_errors;
        self::$_errors = array();
        return $errors;
    }
    
    
    public static function sessionSet($name, $value) {
    	if (!session_id()) session_start();
    	$_SESSION[$name] = $value;
    }
    
    
    public static function sessionGet($name) {
    	if (!session_id()) session_start();
    	return (isset($_SESSION[$name]) ? $_SESSION[$name] : NULL);
    }


    public static function addMessage($type, $msg) {
    	
    	if (is_object($msg) AND $msg instanceof Exception) {
    		$array = @unserialize($msg->getMessage());
    		if (!is_array($array)) $msg = $msg->getMessage();
    		else $msg = $array;
    	}
    	 
    	if (!is_array($msg)) {
    		$msg = array($msg);
    	}
    	
    	foreach ($msg as $m) {
    		CMA_FlashMessage::push($m, ($type == self::MESSAGE_SUCCESS ? CMA_FlashMessage::SUCCESS : CMA_FlashMessage::ERROR));
    	}
    	
    }

    
    protected static function _populate(array $data = array())
    {
        $_SESSION['CMA_populate'] = $data;
    }

    public static function getPopulatedData()
    {
        if (!empty(self::$populatedData)) {
        	return self::$populatedData;
        }
        else if( isset($_SESSION['CMA_populate']) )
        {
            self::$populatedData = (array) $_SESSION['CMA_populate'];
            unset($_SESSION['CMA_populate']);
            return self::$populatedData;
        } else {
        	return array();
        }
        
    }

    public static function _userRequired()
    {
        if( !is_user_logged_in() )
        {
            self::_addError('You have to be logged in to see this page. <a href="' . esc_attr(wp_login_url($_SERVER['REQUEST_URI'])) . '">Log in</a>');
            return false;
        }
        return true;
    }

    public static function registerAdminPages()
    {
    	global $submenu;
    	
        wp_enqueue_script('jquery');
        add_submenu_page(apply_filters('CMA_admin_parent_menu', 'options-general.php'), CMA_Settings::__('CM Answers Logs'), CMA_Settings::__('Logs'), 'manage_options', self::ADMIN_LOGS, array('CMA_LogsController', 'init'));
        add_submenu_page(apply_filters('CMA_admin_parent_menu', 'options-general.php'), CMA_Settings::__('CM Answers Settings'), CMA_Settings::__('Settings'), 'manage_options', self::ADMIN_SETTINGS, array(get_class(), 'displaySettingsPage'));
        add_submenu_page(apply_filters('CMA_admin_parent_menu', 'options-general.php'), CMA_Settings::__('About'), CMA_Settings::__('About'), 'manage_options', self::ADMIN_ABOUT, array(get_class(), 'displayAboutPage'));
        add_submenu_page(apply_filters('CMA_admin_parent_menu', 'options-general.php'), CMA_Settings::__('Add-ons'), CMA_Settings::__('Add-ons'), 'manage_options', self::ADMIN_ADDONS, array(get_class(), 'displayAboutPage'));
        if (self::CUSTOM_IMPORT) {
        	add_submenu_page(apply_filters('CMA_admin_parent_menu', 'options-general.php'), 'CM Answers Import', 'Import', 'manage_options', self::ADMIN_IMPORT, array(get_class(), 'displayImportPage'));
        }
        
        $current_user = wp_get_current_user();
        if( user_can($current_user, 'edit_posts') )
        {
            add_submenu_page(apply_filters('CMA_admin_parent_menu', 'options-general.php'), CMA_Settings::__('User Guide'), CMA_Settings::__('User Guide'), 'manage_options', self::ADMIN_USER_GUIDE, array(get_class(), 'displayAboutPage'));
        }
        
        
    }
    
    
    public static function displayImportPage() {
    	if (!self::CUSTOM_IMPORT) exit;
    	ob_start();
    	require(CMA_PATH . '/views/backend/import.phtml');
    	self::displayAdminPage(ob_get_clean());
    }
    
    
    /**
     * Exclude the CMA comments type from the default comments list in the wp-admin.
     * 
     * @param array $clauses
     * @return array
     */
    public static function filterWPCommentsList($clauses) {
    	$types = array(
    		CMA_Answer::COMMENT_TYPE => CMA_Thread::POST_TYPE,
    		CMA_Comment::COMMENT_TYPE => CMA_Thread::POST_TYPE,
    	);
    	foreach ($types as $commentType => $postType) {
    		if ((empty($postType) OR empty($obj->query_vars['post_type']) OR $obj->query_vars['post_type'] != $postType)
    				AND (empty($obj->query_vars['type']) OR $obj->query_vars['type'] != $commentType)) {
    			$clauses['where'] .= (empty($clauses['where']) ? '' : ' AND') . ' comment_type <> "'. $commentType .'"';
    		}
    	}
    	return $clauses;
    }
    

    public static function displaySettingsPage()
    {
        wp_enqueue_style('jquery-ui-tabs-css', CMA_URL . '/views/resources/jquery-ui-tabs.css');
        wp_enqueue_script('jquery-ui-tabs', false, array(), false, true);
        wp_enqueue_script('cma-suggest-user', CMA_URL . '/views/resources/js/suggest-user.js', array('suggest', 'jquery'));

        $messages = array();
        if( !empty($_POST['titles']) )
        {
            self::$_titles = array_map('stripslashes', $_POST['titles']);
            update_option(self::OPTION_TITLES, self::$_titles);
            $messages[] = CMA_Settings::__('Settings succesfully updated');
        }

    	if (!get_option('permalink_structure')) { // rewrite notice
        	$messages[] = sprintf(
        		CMA_Settings::__('Plugin pages will appear correctly if you choose non-default <a href="%s">permalink structure</a> in your Wordpress settings and enable URL rewrite rules.'),
        			esc_attr(admin_url('options-permalink.php')));
        }

        $params = apply_filters('CMA_admin_settings', array());
        extract($params);

        ob_start();
        require(CMA_PATH . '/views/backend/settings.phtml');
        self::displayAdminPage(ob_get_clean());
    }

    public static function getAdminNav()
    {
        global $submenu, $plugin_page, $pagenow;
        ob_start();
        $submenus = array();
        if( isset($submenu[apply_filters('CMA_admin_parent_menu', 'options-general.php')]) )
        {
            $thisMenu = $submenu[apply_filters('CMA_admin_parent_menu', 'options-general.php')];
            foreach($thisMenu as $item)
            {
                $slug = $item[2];
                $slugParts = explode('?', $slug);
                $name = '';
                if( count($slugParts) > 1 ) $name = $slugParts[0];
                $isCurrent = ($slug == $plugin_page || (!empty($name) && $name === $pagenow));
                $url = (strpos($item[2], '.php') !== false || preg_match('#^https?://#', $slug)) ? $slug : get_admin_url('', 'admin.php?page=' . $slug);
                $submenus[] = array(
                    'link'    => $url,
                    'title'   => $item[0],
                    'current' => $isCurrent
                );
            }
            require(CMA_PATH . '/views/backend/nav.phtml');
        }
        return ob_get_clean();
    }

    public static function displayAdminPage($content)
    {
        $nav = self::getAdminNav();
        require(CMA_PATH . '/views/backend/template.phtml');
    }

    public static function displayAboutPage()
    {
        ob_start();
        if ($_GET['page'] == self::ADMIN_ABOUT) {
        	$iframeURL = 'https://www.cminds.com/store/?showfilter=No&cat=Plugin&nitems=3';
        }
        else if ($_GET['page'] == self::ADMIN_USER_GUIDE) {
        	$iframeURL = 'https://answers.cminds.com/cm-answers-user-guide/';
        } else {
        	$iframeURL = 'https://www.cminds.com/store/?showfilter=No&amp;tags=Answer&amp;nitems=3';
        }
        require(CMA_PATH . '/views/backend/about.phtml');
        self::displayAdminPage(ob_get_clean());
    }

    public static function addCustomTaxonomyNav($taxonomy)
    {
        add_action('after-' . $taxonomy . '-table', array(get_class(), 'filterAdminNavEcho'), 10, 1);
    }

    public static function filterAdminNavEcho()
    {
        echo self::getAdminNav();
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#col-container').prepend($('#CMA_admin_nav'));
            });
        </script>
        <?php
    }

    public static function addCustomPostTypeNav($postType)
    {
        self::$_customPostTypes[] = $postType;
        add_filter('views_edit-' . $postType, array(get_class(), 'filterAdminNav'), 10, 1);
        add_action('restrict_manage_posts', array(get_class(), 'addAdminStatusFilter'));
    }

    public static function addAdminStatusFilter($postType)
    {
        global $typenow;
        if( in_array($typenow, self::$_customPostTypes) )
        {
            $status = get_query_var('post_status');
            ?><select name="post_status">
                <option value="0">Filter by status</option>
                <option value="publish"<?php if( $status == 'publish' ) echo ' selected="selected"';
            ?>>Approved</option>
                <option value="draft"<?php if( $status == 'draft' ) echo ' selected="selected"';
            ?>>Pending</option>
                <option value="trash"<?php if( $status == 'trash' ) echo ' selected="selected"';
            ?>>Trash</option>
            </select><?php
        }
    }

    public static function filterAdminNav($views = null)
    {
        global $submenu, $plugin_page, $pagenow;
        $scheme = is_ssl() ? 'https://' : 'http://';
        $adminUrl = str_replace($scheme . $_SERVER['HTTP_HOST'], '', admin_url());
        $homeUrl = home_url();
        $currentUri = str_replace($adminUrl, '', $_SERVER['REQUEST_URI']);
        $submenus = array();
        if( isset($submenu[apply_filters('CMA_admin_parent_menu', 'options-general.php')]) )
        {
            $thisMenu = $submenu[apply_filters('CMA_admin_parent_menu', 'options-general.php')];
            foreach($thisMenu as $i => $item) {
				$isTrash = strpos($currentUri, 'post_status=trash') && strpos($currentUri, 'post_type='. CMA_Thread::POST_TYPE);
	            if( $i == 1 AND strpos($currentUri, 'post_type=' . CMA_Thread::POST_TYPE) ) {
					$url = get_admin_url('', 'edit.php?post_status=trash&post_type='. CMA_Thread::POST_TYPE .'&action=-1&m=0&paged=1&action2=-1');
	                $submenus['Trash'] = '<a href="' . esc_attr($url) . '" class="' . ($isTrash ? 'current' : '') . '">'. __('Trash') .'</a>';
	            }
                $slug = $item[2];
                $isCurrent = !$isTrash ? ($slug == $plugin_page || strpos($item[2], '.php') === strpos($currentUri, '.php')) : false;
                $url = (strpos($item[2], '.php') !== false || strpos($slug, 'http://') !== false) ? $slug : get_admin_url('', 'admin.php?page=' . $slug);
                $submenus[$item[0]] = '<a href="' . esc_attr($url) . '" class="' . ($isCurrent ? 'current' : '') . '">' . $item[0] . '</a>';
            }
        }
        return $submenus;
    }
    
    
    public static function getUsersTwitterName($userId = null) {
		if (empty($userId)) $userId = get_current_user_id(); 
		$user = get_user_by('id', $userId);
		$meta = get_user_meta($userId, '_cma_uid_twitter', true);
		if (!empty($user) AND !empty($meta)) {
			if (preg_match('/^(.+)@twitter\.com$/', $user->user_email, $match)) {
				if (isset($match[1])) {
					return $match[1];
				}
			}
		}
	}
	
	
	public static function getMetaDescription() {
		$wp_query = self::$query;
		if ($wp_query->is_single() AND !empty($wp_query->post) AND isset($wp_query->post->post_title)) {
			return preg_replace('/[\s\n\r\t]+/', ' ', strip_tags($wp_query->post->post_content));
		}
		else if ($description = CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_META_DESC)) {
			return $description;
		}
		else if (!empty($wp_query->queried_object) AND isset($wp_query->queried_object->name)) {
			$result = 'Questions in the category: '. $wp_query->queried_object->name .'.';
			if (is_array($wp_query->posts)) foreach ($wp_query->posts as $post) {
				$result .= ' '. $post->post_title;
			}
			return $result;
		}
	}
	
	
	public static function get_header($name = null) {
		$wp_query = self::$query;
		
		if (!CMA_Settings::getOption(CMA_Settings::OPTION_SEO_META_REWRITE_ENABLED)) {
			get_header($name);
			return;
		}
		
		ob_start();
		get_header($name);
		$content = ob_get_clean();
		if ($desc = static::getMetaDescription()) {
			$content = self::replaceHeadTag($content,
				'#(<meta name="description" content=")([^"]+)("[ /]*>)#i',
				'<meta name="description" content="'. esc_attr($desc) .'">',
				$desc,
				$append = true
			);
		}
		if ($keywords = CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_META_KEYWORDS)) {
			$content = self::replaceHeadTag($content,
				'#(<meta name="keywords" content=")([^"]+)("[ /]*>)#i',
				'<meta name="keywords" content="'. esc_attr($keywords) .'">',
				$keywords,
				$append = true
			);
		}
		if (!$wp_query->is_single() AND $title = CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_META_TITLE)) {
			$content = self::replaceHeadTag($content,
				'#(<title>)([^<]+)(</title>)#i',
				'<title>'. esc_html($title) .'</title>',
				$title,
				$append = false
			);
		}
		
		// ---------------------------------------------------------------------------------------------------
		// Add canonical
		
		if ($wp_query->get('CMA-contributor-index')) {
			if ($user = get_user_by('slug', $wp_query->get('contributor'))) {
				$canonical = self::getContributorUrl($user);
			}
		}
		else if ($wp_query->is_post_type_archive(CMA_Thread::POST_TYPE)) {
			$obj = $wp_query->get_queried_object();
			if (isset($obj->term_id) AND $category = CMA_Category::getInstance($obj->term_id)) {
				$canonical = $category->getPermalink();
			} else {
				$canonical = CMA::permalink();
			}
		}
		else if ($wp_query->is_single() AND $wp_query->get('post_type') == CMA_Thread::POST_TYPE) {
			// Canonical is added by WP
		}
		if (!empty($canonical)) {
			$content = self::replaceHeadTag($content,
				'#(<link rel=[\'"]?canonical[\'"]? href=[\'"])([^\'"]+)([\'"][ /]*>)#i',
				'<link rel="canonical" href="'. esc_attr($canonical) .'">',
				$canonical,
				$append = false
			);
		}
		
		echo $content;
	}
	
	
	protected static function replaceHeadTag($content, $pattern, $new, $value, $append = false) {
		if (preg_match($pattern, $content)) {
			$replacement = '$1';
			if ($append) $replacement .= '$2 ';
			$replacement .= esc_attr($value) .'$3';
			return preg_replace($pattern, $replacement, $content);
		} else {
			return str_replace('</head>', $new .'</head>', $content);
		}
	}
	
	
		
	public static function adminCommentRowActionsFilter($actions, $comment) {
		if ($comment->comment_type == CMA_Answer::COMMENT_TYPE AND $answer = CMA_Answer::getById($comment->comment_ID)) {
			if ($answer->canUnmarkSpam()) {
				$url = sprintf('./?page=%s&answer_id=%d&nonce=%s&backlink=%s',
					urlencode(self::ADMIN_UNMARK_SPAM),
					$answer->getId(),
					urlencode(wp_create_nonce(self::ADMIN_UNMARK_SPAM)),
					urlencode(base64_encode($_SERVER['REQUEST_URI']))
				);
				$actions['unmark_cma_spam'] = sprintf('<a href="%s" title="%s">%s</a>',
					esc_attr($url),
					esc_attr(CMA_Settings::__('Unmark this answer as possibly spam.')),
					esc_html(CMA_Settings::__('Unmark spam'))
				);
			}
			$page = CMA_BaseController::ADMIN_PRIVATE_ANSWER;
			$url = sprintf('./?page=%s&%s=%d&nonce=%s&private=%d&backlink=%s',
				urlencode($page),
				'answer_id',
				urlencode($answer->getId()),
				urlencode(wp_create_nonce($page)),
				intval(!$answer->isPrivate()),
				urlencode(base64_encode($_SERVER['REQUEST_URI']))
			);
			$actions['private_answer'] = sprintf('<a href="%s" title="%s">%s</a>',
				esc_attr($url),
				esc_attr($answer->isPrivate() ? CMA_Settings::__('Show private answer to all users.') : CMA_Settings::__('Make answer available only for the question author.')),
				esc_html($answer->isPrivate() ? CMA_Settings::__('Unmark as private') : CMA_Settings::__('Mark as private'))
			);
		}
		
		return $actions;
	}
	
	
	protected static function processUnmarkSpam() {
		if (!empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], self::ADMIN_UNMARK_SPAM)) {
			
			// Answer
			if (!empty($_GET['answer_id'])) {
				if ($answer = CMA_Answer::getById($_GET['answer_id'])) {
					if ($answer->canUnmarkSpam()) {
						$answer->markAsSpam(0);
					} else die('Cannot unmark spam.');
				} else die('Unknown answer.');
			}
			
			// Comment
			if (!empty($_GET['comment_id'])) {
				if ($comment = CMA_Comment::getById($_GET['comment_id'])) {
					if ($comment->canUnmarkSpam()) {
						$comment->markAsSpam(0);
					} else die('Cannot unmark spam.');
				} else die('Unknown comment.');
			}
			
			if (!empty($_GET['backlink'])) {
				wp_safe_redirect(base64_decode(urldecode($_GET['backlink'])));
				exit;
			}
			
		} else die('Invalid nonce.');
	}
	
	
	protected static function processPrivateAnswer() {
		if (!empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], self::ADMIN_PRIVATE_ANSWER)) {
			
			// Answer
			if (!empty($_GET['answer_id'])) {
				if ($answer = CMA_Answer::getById($_GET['answer_id'])) {
					$answer->setPrivate(!empty($_GET['private']));
				} else die('Unknown answer.');
			}
			
			if (!empty($_GET['backlink'])) {
				wp_safe_redirect(base64_decode(urldecode($_GET['backlink'])));
				exit;
			}
			
		} else die('Invalid nonce.');
	}
	
	
	protected static function addAjaxHandler($action, $handler, $loggedUsers = true, $guests = true) {
		if ($loggedUsers) add_action('wp_ajax_'. $action, $handler);
        if ($guests) add_action('wp_ajax_nopriv_'. $action, $handler);
	}
	
	
	public static function loadScripts() {

		wp_register_style('CMA-css', CMA_URL . '/views/resources/app.css', array(), CMA::version());
		add_action('wp_head', array(__CLASS__, 'high_priority_style'), PHP_INT_MAX);
		add_action('wp_footer', array(__CLASS__, 'high_priority_style'), PHP_INT_MAX);
        wp_enqueue_style('dashicons');

		$cmaVariables = array(
    		'CMA_URL' => get_post_type_archive_link(CMA_Thread::POST_TYPE),
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'loaderBarUrl' => CMA_URL . '/views/resources/imgs/ajax-loader-bar.gif',
			'loaderUrl' => CMA_URL . '/views/resources/imgs/ajax-loader.gif',
			'navBarAutoSubmit' => CMA_Settings::getOption(CMA_Settings::OPTION_NAVBAR_AUTO_SUBMIT),
			'bestAnswerRemoveOther' => CMA_Settings::getOption(CMA_Settings::OPTION_BEST_ANSWER_REMOVE_OTHER),
			'bestAnswerRemoveOtherLabel' => CMA_Labels::getLocalized('best_answer_remove_other_confirm'),
			'confirmThreadDelete' => CMA::__('Do you really want to delete this question?'),
    	);

		wp_enqueue_script('cma-script', CMA_RESOURCE_URL . 'script.js', array('jquery', 'cma-toast', 'jquery-ui-dialog', 'suggest'), false, true);
    	wp_localize_script('cma-script', 'CMA_Variables', $cmaVariables);
    	wp_enqueue_script('jquery');

		wp_enqueue_script('cma-toast', CMA_RESOURCE_URL . 'toast/js/jquery.toastmessage.js', array('jquery'), false, true);
        wp_enqueue_style('cma-toast-css', CMA_RESOURCE_URL . 'toast/resources/css/jquery.toastmessage.css', array(), false);
    	wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style("wp-jquery-ui-dialog");
    	
    	wp_enqueue_script( 'suggest' );
    	
	}
	
	
	public static function high_priority_style() {
		if (!CMA_Settings::getOption(CMA_Settings::OPTION_DISABLE_CSS) AND !defined('CMA_CSS_PRINTED')) {
			wp_print_styles('CMA-css');
			define('CMA_CSS_PRINTED', 1);
		}
	}
	
	
	
	public static function processClearDatabase() {
		if(isset($_GET['flush_cma_db']) && $_GET['flush_cma_db'] == 1 AND !empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], self::ADMIN_SETTINGS)) {
			CMA::flushDatabase();
			wp_redirect(admin_url('plugins.php'));
			exit;
		}
	}
	

    public static function processSettings() {

			if (empty($_POST)) return;
			
	        // CSRF protection
	        if (!empty($_POST) AND (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], self::ADMIN_SETTINGS))) {
	        	die('Invalid nonce');
	        }

			CMA_Settings::processPostRequest();
        	
            CMA_Thread::setDisclaimerEnabled(isset($_POST['disclaimer_approve']) && $_POST['disclaimer_approve'] == 1);
            CMA_Thread::setDisclaimerContent(stripslashes($_POST['disclaimer_content']));
            CMA_Thread::setDisclaimerContentAccept(stripslashes($_POST['disclaimer_content_accept']));
            CMA_Thread::setDisclaimerContentReject(stripslashes($_POST['disclaimer_content_reject']));

            CMA_Thread::setSidebarSettings('before_widget', stripslashes($_POST['sidebar_before_widget']));
            CMA_Thread::setSidebarSettings('after_widget', stripslashes($_POST['sidebar_after_widget']));
            CMA_Thread::setSidebarSettings('before_title', stripslashes($_POST['sidebar_before_title']));
            CMA_Thread::setSidebarSettings('after_title', stripslashes($_POST['sidebar_after_title']));
            CMA_Thread::setSidebarEnabled(isset($_POST['sidebar_enable']) && $_POST['sidebar_enable'] == 1);
            CMA_Thread::setSidebarMaxWidth((int) $_POST['sidebar_max_width']);
            CMA_Thread::setSidebarContributorEnabled(isset($_POST['sidebar_contributor_enable']) ? $_POST['sidebar_contributor_enable'] : '1');

            // Social login
            if(isset($_POST['fb_app_id']) && isset($_POST['fb_app_secret']))
            {
                update_option('_cma_fb_app_id', trim($_POST['fb_app_id']));
                update_option('_cma_fb_app_secret', trim($_POST['fb_app_secret']));
            }
            if(isset($_POST['google_client_id']) && isset($_POST['google_client_secret']))
            {
                update_option('_cma_google_client_id', trim($_POST['google_client_id']));
                update_option('_cma_google_client_secret', trim($_POST['google_client_secret']));
            }
            if(isset($_POST['linkedin_api_key']) && isset($_POST['linkedin_secret_key']))
            {
                update_option('_cma_linkedin_api_key', trim($_POST['linkedin_api_key']));
                update_option('_cma_linkedin_secret_key', trim($_POST['linkedin_secret_key']));
            }
            if (isset($_POST['twitter_consumer_key']) && isset($_POST['twitter_consumer_secret'])) {
            	update_option('_cma_twitter_consumer_key', trim($_POST['twitter_consumer_key']));
            	update_option('_cma_twitter_consumer_secret', trim($_POST['twitter_consumer_secret']));
            }
            if (isset($_POST['live_client_id']) && isset($_POST['live_client_secret'])) {
            	update_option('_cma_live_client_id', trim($_POST['live_client_id']));
            	update_option('_cma_live_client_secret', trim($_POST['live_client_secret']));
            }

            
            // Labels
            $labels = CMA_Labels::getLabels();
            foreach ($labels as $labelKey => $label) {
				if (isset($_POST['label_'. $labelKey])) {
					CMA_Labels::setLabel($labelKey, stripslashes($_POST['label_'. $labelKey]));
				}
			}
			

            CMA_Thread::setSpamFilter(isset($_POST['spamFilter']) && $_POST['spamFilter'] == 1);

            CMA_Thread::setReferralEnabled(isset($_POST['referral_enable']) && $_POST['referral_enable'] == 1);
            if( !empty($_POST['affiliate_code']) )
            {
                CMA_Thread::setAffiliateCode(stripslashes($_POST['affiliate_code']));
            }

            if( isset($_POST['custom_css']) )
            {
                CMA_Thread::setCustomCss(stripslashes($_POST['custom_css']));
            }
            
            // Clear the permalinks
            flush_rewrite_rules(true);
            delete_option('rewrite_rules');

            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
            
	}

}
