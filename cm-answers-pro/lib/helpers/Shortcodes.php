<?php
require_once CMA_PATH . '/lib/helpers/Widgets/QuestionsWidget.php';
class CMA_Shortcodes
{
	
	
	const CUSTOM_QUESTIONS_INDEX_PAGE_META_KEY = '_cma_custom_index_page';
	const CUSTOM_QUESTIONS_INDEX_PAGE_META_VALUE = '1';

    public static function init()
    {
    	
    	if (!CMA::isLicenseOk()) return;
    	
        add_action('init', array(__CLASS__, 'add_rewrite_endpoint'));

        add_shortcode('cma-my-questions', array(__CLASS__, 'shortcode_my_questions'));
        add_shortcode('cma-my-answers', array(__CLASS__, 'shortcode_my_answers'));
        add_shortcode('cma-answers', array(__CLASS__, 'shortcode_answers'));
        add_shortcode('cma-questions', array(__CLASS__, 'shortcode_questions'));
        add_shortcode('cma-comments', array(__CLASS__, 'shortcode_comments'));
        add_shortcode('cma-categories', array(__CLASS__, 'shortcode_categories'));
        add_shortcode('cma-categories-list', array(__CLASS__, 'shortcode_categories_list'));
        add_shortcode('cma-index', array(__CLASS__, 'shortcode_index'));

//         add_action('wp_enqueue_scripts', array(__CLASS__,'addStyles'));
        
    }

    public static function add_rewrite_endpoint()
    {
        add_rewrite_endpoint(
                CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_PERMALINK)
                , EP_PERMALINK | EP_PAGES
        );
    }

	public static function shortcode_answers($atts)
    {
    	if (empty($atts['author'])) return;
        $limit = (isset($atts['limit'])) ? $atts['limit'] : 5;
        $atts['pagination'] = (isset($atts['pagination'])) ? $atts['pagination'] : 1;
//         $answersCount = CMA_Answer::countForUser($atts['author'], $approved = true, $onlyVisible = true);
        $answers = CMA_Answer::getByUser($atts['author'], $approved = true, $limit, $page = 1, $onlyVisible = true);
        $totalPages = ceil(CMA_Answer::countForUser($atts['author'], $approved = true, $onlyVisible = true)/$limit);
        
        $authorSlug = '';
       	$user = get_user_by(is_numeric($atts['author']) ? 'id' : 'slug', $atts['author']);
        if (!empty($user)) $authorSlug = $user->user_nicename;
        
        $public = false;
        $currentPage = 1;
        $ajax = ((!isset($atts['ajax']) OR $atts['ajax']) ? true : false);
        
        return CMA_BaseController::_loadView('answer/widget/answers-list',
        	compact('answers', 'atts', 'public', 'authorSlug', 'currentPage', 'totalPages', 'limit', 'ajax'));
    }
    
    public static function shortcode_my_answers($atts)
    {
        if(!is_user_logged_in())
        {
            return '';
        }

        $atts['author'] = get_current_user_id();
		return self::shortcode_answers($atts);
    }

    public static function shortcode_my_questions($atts, $widget = false)
    {
        if(!is_user_logged_in())
        {
            return '';
        }

        if(!is_array($atts))
        {
            $atts = array();
        }

        $atts['limit']          = (isset($atts['limit'])) ? $atts['limit'] : 5;
        $atts['form']          = (isset($atts['form'])) ? $atts['form'] : 0;
        $atts['pagination']    = (isset($atts['pagination'])) ? $atts['pagination'] : 1;
        $atts['author']         = get_current_user_id();
        $atts['user_questions'] = true;
        $atts['statusinfo']     = true;

        return self::general_shortcode($atts, $widget);
    }
    
    
    public static function shortcode_comments($atts) {
    	global $cmaQuestionLinkQuery;
    	$cmaQuestionLinkQuery = array('post_id' => $atts['post_id']);
    	$result = self::general_shortcode($atts, false);
    	$cmaQuestionLinkQuery = null;
    	return $result;
    }
    
    
    public static function shortcode_index($atts, $widget = false) {
    	if (CMA_Thread::canBeViewed()) {
    		CMA_AnswerController::addDisclaimer($force = true);
	    	if (!isset($atts['navbar'])) $atts['navbar'] = 1;
	    	if (!isset($atts['form'])) $atts['form'] = 1;
	    	if (!isset($atts['pagination'])) $atts['pagination'] = 1;
// 	    	$atts['displaycategories'] = 1;
	    	if (empty($atts['limit'])) $atts['limit'] = CMA_Settings::getOption(CMA_Settings::OPTION_ITEMS_PER_PAGE);
	    	return self::general_shortcode($atts, $widget);
    	}
    	else if (!$widget) {
    		$output = '<ul class="errors"><li>'. CMA_Labels::getLocalized('no_permissions') .'</li></ul>
                <a href="javascript:history.back(-1)">'. CMA_Labels::getLocalized('back_to_previous_page') .'</a><br />';
    		$output .= CMA_BaseController::_loadView('answer/widget/login', compact('widget'));
            return $output;
    	}
    }

    public static function shortcode_questions($atts, $widget = false)
    {
        return self::general_shortcode($atts, $widget);
    }
    
    
    public static function shortcode_categories($atts, $widget = false) {
    	CMA_BaseController::loadScripts();
    	$atts = is_array($atts) ? $atts : array();
    	$atts = CMA_Thread::sanitize_array($atts, array(
        	'questions' => array('bool', true),
    		'unanswered' => array('bool', true),
    		'answers' => array('bool', true),
    		'follow' => array('bool', true),
    		'header' => array('bool', true),
    		'activity' => array('bool', true),
    		'parent' => array('*', ''),
    	));
    	$displayOptions = $atts;
    	$categories = CMA_Category::getCategoriesTreeArray();
    	$shortcode = !$widget;
    	$parentCategoryId = (empty($atts['parent']) ? 0 : $atts['parent']);
    	if (!is_numeric($parentCategoryId)) {
    		if ($term = get_term_by('slug', $parentCategoryId, CMA_Category::TAXONOMY)) {
    			$parentCategoryId = $term->term_id;
    		} else {
    			$parentCategoryId = 0;
    		}
    	}
    	$checkPermissions = true;
    	return CMA_BaseController::_loadView('answer/widget/categories',
    			array_merge($atts, compact('displayOptions', 'categories', 'widget', 'shortcode', 'parentCategoryId', 'checkPermissions')));
    	
    }
    
    
    public static function shortcode_categories_list($atts) {
    	CMA_BaseController::loadScripts();
    	$atts = is_array($atts) ? $atts : array();
    	$categories = CMA_Category::getCategoriesTreeArray();
    	$parentCategoryId = 0;
    	return CMA_BaseController::_loadView('answer/widget/categories-list',
    			array_merge($atts, compact('categories', 'parentCategoryId')));
    }
    

    public static function general_shortcode($atts, $widget = true)
    {
    	
        $atts = is_array($atts) ? $atts : array();
        $displayOptionsDefaults = CMA_Settings::getDisplayOptionsDefaults();
        
        $atts = CMA_Thread::sanitize_array($atts, array(
			'limit' => array('int', 5),
            'cat' => array('*', null),
        	'tag' => array('*', null),
            'author' => array('string', null),
        	'contributor' => array('string', null),
        	'answered' => array('bool', null),
        	'resolved' => array('bool', null),
            'sort' => array('string', CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_ORDER_BY)),
            'order' => array('string', 'desc'),
            'tiny' => array('bool', false),
            'form' => array('bool', $displayOptionsDefaults['form']),
            'displaycategories' => array('bool', (bool)$displayOptionsDefaults['categories']),
        	'resolvedprefix' => array('bool', $displayOptionsDefaults['resolvedPrefix']),
        	'icons' => array('bool', $displayOptionsDefaults['icons']),
            'pagination' => array('bool', $displayOptionsDefaults['pagination']),
            'hidequestions' => array('bool', $displayOptionsDefaults['hideQuestions']),
            'search' => array('bool', $displayOptionsDefaults['search']),
            'votes' => array('bool', $displayOptionsDefaults['votes']),
            'views' => array('bool', $displayOptionsDefaults['views']),
            'answers' => array('bool', $displayOptionsDefaults['answers']),
            'updated' => array('bool', $displayOptionsDefaults['updated']),
            'authorinfo' => array('bool', $displayOptionsDefaults['authorinfo']),
            'statusinfo' => array('bool', $displayOptionsDefaults['statusinfo']),
        	'tags' => array('bool', $displayOptionsDefaults['tags']),
            'wrapperclass' => array('string', $displayOptionsDefaults['wrapperclass']),
        	'navbar' => array('bool', false),
        	'sortbar' => array('bool', false),
        	'ajax' => array('bool', true),
        	'showid' => array('bool', false),
        	'dateposted' => array('bool', false),
        	'showcontent' => array('bool', false),
        	'formontop' => array('bool', $displayOptionsDefaults['formontop']),
        	'subtree' => array('bool', $displayOptionsDefaults['subtree']),
        ));
        
        if($atts['tiny']) $atts['pagination'] = false;
		
        $search = esc_attr(CMA_AnswerController::$query->get('search'));
        $paged  = esc_attr(CMA_AnswerController::$query->get('paged'));

        $questionsArgs = array(
            'post_type' => CMA_Thread::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => $atts['limit'],
            'paged' => $paged,
            'orderby' => $atts['sort'],
            'order' => $atts['order'],
            'fields' => 'ids',
            'widget' => true,
            'tag' => empty($atts['tag']) ? (isset($_GET["cmatag"]) ? $_GET["cmatag"] : '') : $atts['tag'],
            'search' => $search
        );
        
        if (!is_null($atts['resolved'])) {
        	$questionsArgs['meta_query'] = array(array(
        		'key' => CMA_Thread::$_meta['resolved'],
        		'value' => intval($atts['resolved']),
        	));
        }

        if(!empty($atts['user_questions']))
        {
            $questionsArgs['user_questions'] = $atts['user_questions'];
        }

        if(!empty($atts['author']))
        {
        	if (!is_numeric($atts['author'])) {
        		if ($user = get_user_by('slug', $atts['author'])) {
        			$atts['author'] = $user->ID;
        		} else {
        			$atts['author'] = null;
        		}
        	}
            $questionsArgs['author'] = $atts['author'];
        }
        
    	if(!empty($atts['contributor']) AND !is_numeric($atts['contributor'])) {
        	if ($user = get_user_by('slug', $atts['contributor'])) {
        		$atts['contributor'] = $user->ID;
        	} else {
        		$atts['contributor'] = null;
        	}
        }
        

        $category = null;
        if(!empty($atts['cat']))
        { // there may be multiple categories separated by commas
        	if (!is_array($atts['cat'])) $categories = explode(',', $atts['cat']);
        	else $categories = $atts['cat'];
        	$categories = array_filter($categories);
        	$categoriesSlugs = array();
        	foreach ($categories as $i => $cat) {
        		if (!is_scalar($cat)) continue;
	        	if (preg_match('/^[0-9]+$/', $cat)) {
	        		$category = get_term($cat, CMA_Category::TAXONOMY);
	        		$categoriesSlugs[] = $category->slug;
	        		$catId = $cat;
	        	}
	        	else if ($category = get_term_by('slug', trim($cat), CMA_Category::TAXONOMY)) {
        			$catId = $category->term_id;
        			$categoriesSlugs[] = $category->slug;
	        	} else {
	        		$catId = false;
	        	}
	        	if ($catId) {
		        	if (empty($questionsArgs['tax_query'][0])) {
			        	$questionsArgs['tax_query'][0] = array(
			        		'taxonomy' => CMA_Category::TAXONOMY,
			        		'field' => 'term_id',
			        		'terms' => array($catId),
			        	);
		        	} else {
		        		$questionsArgs['tax_query'][0]['terms'][] = $catId;
		        	}
	        	}
        	}
        	$atts['cat'] = implode(',', $categoriesSlugs);
        }
        
        $customWhereCallback = function($val) use ($atts) {
        	global $wpdb;
        	if (!is_null($atts['answered'])) {
        		$val .= CMA_AnswerController::registerCommentsFiltering($val, ($atts['answered'] ? 'ans' : 'unans'));
        	}
        	if (!empty($atts['contributor'])) {
        		$val .= $wpdb->prepare(" AND (post_author = %d OR ID IN (
        			SELECT wc.comment_post_ID FROM $wpdb->comments wc
        				WHERE wc.user_id = %d
        				AND wc.comment_approved = 1
        			))", $atts['contributor'], $atts['contributor']);
        	}
        	$val .= " AND $wpdb->posts.ID IS NOT NULL";
        	return $val;
        };
        $questionsArgs = apply_filters('cma_questions_shortcode_query_args', $questionsArgs, $atts);
        add_filter('posts_where_request', $customWhereCallback);
        add_filter('posts_where_request', array('CMA_AnswerController', 'categoryAccessFilter'));
        $q           = CMA_Thread::customOrder(new WP_Query(), $atts['sort']);
        foreach ($questionsArgs as $key => $val) {
        	$q->set($key, $val);
        }
        $questions   = array_map(array('CMA_Thread', 'getInstance'), $q->get_posts());
     	
        $maxNumPages = $atts['maxNumPages'] = $q->max_num_pages;
        $paged       = $q->query_vars['paged'];
        remove_filter('posts_where_request', $customWhereCallback);
        remove_filter('posts_where_request', array('CMA_AnswerController', 'categoryAccessFilter'));
        
        $displayOptions = array(
            'hideQuestions' => $atts['hidequestions'],
            'tags' => !$atts['tiny'],
            'pagination' => !$atts['tiny'] && $atts['pagination'],
            'form' => $atts['form'],
            'categories' => $atts['displaycategories'],
            'search' => $atts['search'],
            'votes' => $atts['votes'],
            'views' => $atts['views'],
            'answers' => $atts['answers'],
            'updated' => $atts['updated'],
            'authorinfo' => $atts['authorinfo'],
        	'tags' => $atts['tags'],
            'statusinfo' => $atts['statusinfo'],
            'wrapperclass' => $atts['wrapperclass'],
        	'navbar' => $atts['navbar'],
        	'sortbar' => $atts['sortbar'],
        	'formontop' => $atts['formontop'],
        	'resolvedPrefix' => $atts['resolvedprefix'],
        	'icons' => $atts['icons'],
        	'showid' => $atts['showid'],
        	'dateposted' => $atts['dateposted'],
        	'showcontent' => $atts['showcontent'],
        	'subtree' => $atts['subtree'],
        );
        $checkPermissions = true;
        $widget = true;
        
        $category = CMA_Category::getInstance($category);
        $options = array_merge($atts, compact('displayOptions', 'catId', 'maxNumPages', 'paged', 'widget', 'search', 'checkPermissions'));
        $options['checkPermissions'] = false;
        $options = apply_filters('cma_questions_shortcode_widget_options', $options);
        $widgetCacheId = $options['widgetCacheId'] = CMA_AnswerController::saveWidgetOptions($options);
        $options['questions'] = $questions;
        
        CMA_BaseController::loadScripts();
        
        $result = CMA_BaseController::_loadView('answer/widget/questions', $options);
        if ($atts['ajax']) $result = '<div class="cma-widget-ajax" data-widget-cache-id="'. $widgetCacheId .'">'. $result .'</div>';
        return $result;
    }
    
    
    /**
     * Get custom Questions Index page.
     */
	public static function getCustomQuestionsIndexPage($publish = true) {
		$posts = get_pages(array(
			'meta_key' => self::CUSTOM_QUESTIONS_INDEX_PAGE_META_KEY,
			'meta_value' => self::CUSTOM_QUESTIONS_INDEX_PAGE_META_VALUE,
			'post_status' => $publish ? 'publish' : 'publish,private,draft,trash',
		));
		return reset($posts);
	}
	
	
	/**
	 * Initialize custom Questions Index AJAX page.
	 */
	public static function initCustomQuestionsIndexPage() {
		if (!self::getCustomQuestionsIndexPage($publish = true) AND CMA_Settings::getOption(CMA_Settings::OPTION_CREATE_AJAX_PAGE)) {
			$permalink = CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_PERMALINK) . '-ajax';
    		$post = array(
    			'post_title' => CMA_Labels::getLocalized('index_page_title'),
    			'post_name' => $permalink,
    			'post_content' => '[cma-index]',
    			'post_author' => get_current_user_id(),
    			'post_status' => 'publish',
    			'post_type' => 'page',
    			'comment_status' => 'closed',
    			'ping_status' => 'closed',
    		);
    		$result = wp_insert_post($post);
    		if (is_numeric($result)) {
    			add_post_meta($result, self::CUSTOM_QUESTIONS_INDEX_PAGE_META_KEY, self::CUSTOM_QUESTIONS_INDEX_PAGE_META_VALUE);
    		}
    	}
	}
	
	/**
	 * Get a permalink to the custom Questions Index page.
	 * 
	 * @return string
	 */
	public static function getCustomQuestionsIndexPermalink($publish = true) {
		if ($page = self::getCustomQuestionsIndexPage($publish)) {
			return get_permalink($page->ID);
		}
	}


}
