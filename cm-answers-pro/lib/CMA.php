<?php

class CMA {
	
    const TEXT_DOMAIN = 'cm-answers-pro';
    const OPTION_VERSION = 'cma_version';

    static $version;
    protected static $isLicenseOk;

    public static function init($pluginFilePath)
    {
    	
        register_activation_hook($pluginFilePath, array(__CLASS__, 'install'));
        register_uninstall_hook($pluginFilePath, array(__CLASS__, 'uninstall'));

        add_action('init', array('CMA_Update', 'run'), 0);
        add_action( 'widgets_init', array('CMA_AnswerController', 'registerSidebars') );

        // Check licensing API before controller init
        $licensingApi = new CMA_Cminds_Licensing_API('CM Answers Pro', CMA_Thread::ADMIN_MENU, 'CM Answers Pro', CMA_PLUGIN_FILE,
        	array('release-notes' => 'http://answers.cminds.com/release-notes/'), '', array('CM Answers Pro'));
        self::$isLicenseOk = $licensingApi->isLicenseOk();
        
        CMA_Labels::bootstrap();
        CMA_Thread::init();
        add_action('plugins_loaded', array('CMA_BuddyPress', 'bootstrap'));
		
        add_action('init', array('CMA_BaseController', 'bootstrap'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enable_scripts'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enable_admin_scripts'));
        add_action('wp_head', array(__CLASS__, 'add_base_url'));

        add_filter('bp_blogs_record_comment_post_types', array(get_class(), 'bp_record_my_custom_post_type_comments'));
        
        add_filter('plugin_action_links_' . CMA_PLUGINNAME, array(__CLASS__, 'cma_settings_link'));
        add_filter('cm_micropayments_integrations', function($a = array()) {
        	if (!is_array($a)) $a = array();
        	$a[] = 'CM Answers Pro';
        	return $a;
        });
        
		add_action('plugins_loaded', array(__CLASS__, 'cm_lang_init'));
        
    }
    
	static function cm_lang_init() {
	  load_plugin_textdomain( 'cm-answers-pro', false, dirname( plugin_basename( CMA_PLUGIN_FILE ) ) . '/lang' );
	  load_plugin_textdomain( 'cm-answers-pro-backend', false, dirname( plugin_basename( CMA_PLUGIN_FILE ) ) . '/lang' );
	}
    
    
    public static function isLicenseOk() {
    	return self::$isLicenseOk;
    }
    
    
    public static function getPostingUserId() {
    	if (is_user_logged_in()) {
    		return get_current_user_id();
    	} else {
    		return apply_filters('cma_anonymous_user_id', array());
    	}
    }
    
    
    /**
     * Get CMA index permalink.
     * 
     * @return string
     */
    public static function permalink() {
    	return get_post_type_archive_link(CMA_Thread::POST_TYPE);
    }
    

    /**
     * Add settings link on plugin page
     * @param type $links
     * @return type
     */
    public static function cma_settings_link($links)
    {
        $links['delete cma_tables'] = '<a href="' . esc_attr(admin_url('admin.php?page=CMA_admin_settings&flush_cma_db=1&nonce='
        	. wp_create_nonce(CMA_BaseController::ADMIN_SETTINGS)))
        	. '" class="delete">' . __('Clear database', 'cm-answers-pro') . '</a>';
        return $links;
    }

    public static function install($networkwide)
    {
        global $wpdb;

        flush_rewrite_rules();

        if( function_exists('is_multisite') && is_multisite() )
        {
// check if it is a network activation - if so, run the activation function for each blog id
            if( $networkwide )
            {
                $old_blog = $wpdb->blogid;
// Get all blog ids
                $blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM {$wpdb->blogs}"));
                foreach($blogids as $blog_id)
                {
                    switch_to_blog($blog_id);
                    self::install_blog();
                }
                switch_to_blog($old_blog);
                return;
            }
        }
        self::install_blog();
    }

    public static function uninstall()
    {
        flush_rewrite_rules();
    }

    public static function install_blog()
    {

    }

    public static function add_base_url()
    {
    }

    public static function enable_scripts()
    {
        wp_enqueue_script('jquery');
    }

    public static function enable_admin_scripts()
    {
        wp_enqueue_script('cma-admin-script', CMA_RESOURCE_URL . 'admin_script.js', array('jquery'), false, true);
    }
    
    
    public static function getReferer() {
    	$wp_query = CMA_AnswerController::$query;
    	
    	$isEditPage = function($url) {
    		if (is_array($url)) $params = $url;
    		else parse_str(parse_url($url, PHP_URL_QUERY), $params);
    		$editParams = array(CMA_AnswerController::PARAM_EDIT_ANSWER_ID, CMA_AnswerController::PARAM_EDIT_QUESTION_ID);
    		return (count(array_intersect(array_keys($params), $editParams)) > 0);
    	};
    	$isTheSameHost = function($a, $b) {
    		return parse_url($a, PHP_URL_HOST) == parse_url($b, PHP_URL_HOST);
    	};
    	
    	$canUseReferer = (!empty($_SERVER['HTTP_REFERER'])
    			AND $isTheSameHost($_SERVER['HTTP_REFERER'], site_url())
    			AND !$isEditPage($_SERVER['HTTP_REFERER']));
    	$canUseCurrentPost = ($wp_query->is_single() AND !empty($wp_query->post) AND $wp_query->post->post_type == CMA_Thread::POST_TYPE
    			AND $isEditPage($_GET));
    	
    	if (!empty($_GET['backlink'])) { // GET backlink param
    		return base64_decode(urldecode($_GET['backlink']));
    	}
    	else if (!empty($_POST['backlink'])) { // POST backlink param
    		return $_POST['backlink'];
    	}
    	else if ($canUseReferer) { // HTTP referer
    		return $_SERVER['HTTP_REFERER'];
    	}
    	else if ($canUseCurrentPost) { // Question permalink
    		return get_permalink($wp_query->post->ID);
    	} else { // CMA index page
    		return get_post_type_archive_link(CMA_Thread::POST_TYPE);
    	}
    }
    

    /**
     * BuddyPress record custom post type comments
     * @param array $post_types
     * @return string
     */
    public static function bp_record_my_custom_post_type_comments($post_types)
    {
        $post_types[] = CMA_Thread::POST_TYPE;
        return $post_types;
    }

    /**
     * Get localized string.
     *
     * @param string $msg
     * @return string
     */
    public static function __($msg)
    {
        return __($msg, self::TEXT_DOMAIN);
    }

    /**
     * Get plugin's version.
     *
     * @return string|null
     */
    public static function version()
    {
        if( !empty(self::$version) )
        {
            return self::$version;
        }
        else
        {
            $readme = file_get_contents(CMA_PATH . '/readme.txt');
            preg_match('/Stable tag\: ([0-9\.]+)/i', $readme, $match);
            if( isset($match[1]) )
            {
            	self::$version = $match[1];
                return $match[1];
            }
        }
    }
    
    
    public static function isDebug() {
    	return (isset($_GET['cmdebug']) AND md5($_GET['cmdebug']) == 'be452c756d1c1c49fb10465b4526987a');
    }


    public static function flushDatabase()
    {
        global $wpdb;
        
        // Delete questions with answers:
        $ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", CMA_Thread::POST_TYPE));
        foreach ($ids as $id) {
        	wp_delete_post($id, true);
        }
        
        // Delete post meta
        $meta = CMA_Thread::$_meta;
    	foreach ($meta as &$m) {
    		$m = $wpdb->prepare('%s', $m . '%');
    	}
    	$sql = "DELETE FROM $wpdb->postmeta\nWHERE meta_key LIKE ". implode("\nOR meta_key LIKE ", $meta);
    	$result = $wpdb->query($sql);
        
        // Delete categories
        $categories = $wpdb->delete($wpdb->term_taxonomy,  array('taxonomy' => CMA_Category::TAXONOMY));
        
        // Delete options
        $wpdb->query('DELETE FROM '.$wpdb->options.' WHERE option_name LIKE  \'cma\\_%\'');
        
        // User meta
        $sql = $wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE meta_key IN (%s, %s) OR meta_key LIKE %s",
        	CMA_Thread::USERMETA_COUNTER_QUESTIONS, CMA_Thread::USERMETA_COUNTER_ANSWERS, 'cma\\_%');
        $result = $wpdb->query($sql);

    }
    
    
    public static function parse_php_info()
    {
    	ob_start();
    	phpinfo(INFO_MODULES);
    	$s = ob_get_clean();
    	$s = strip_tags($s, '<h2><th><td>');
    	$s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', "<info>\\1</info>", $s);
    	$s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', "<info>\\1</info>", $s);
    	$vTmp = preg_split('/(<h2>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
    	$vModules = array();
    	for($i = 1; $i < count($vTmp); $i++) {
    		if( preg_match('/<h2>([^<]+)<\/h2>/', $vTmp[$i], $vMat) ) {
    			$vName = trim($vMat[1]);
    			$vTmp2 = explode("\n", $vTmp[$i + 1]);
    			foreach($vTmp2 AS $vOne) {
    				$vPat = '<info>([^<]+)<\/info>';
    				$vPat3 = "/$vPat\s*$vPat\s*$vPat/";
    				$vPat2 = "/$vPat\s*$vPat/";
    				if( preg_match($vPat3, $vOne, $vMat) ) { // 3cols
    					$vModules[$vName][trim($vMat[1])] = array(trim($vMat[2]), trim($vMat[3]));
    				}
    				elseif( preg_match($vPat2, $vOne, $vMat) ) { // 2cols
    					$vModules[$vName][trim($vMat[1])] = trim($vMat[2]);
    				}
    			}
    		}
    	}
    	return $vModules;
   	}

}
