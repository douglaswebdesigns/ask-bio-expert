<?php

class CMA_AnswerController extends CMA_BaseController {

    const PARAM_EDIT_QUESTION_ID = 'editQuestionId';
    const PARAM_EDIT_ANSWER_ID = 'editAnswerId';
    const PARAM_RESOLVE_QUESTION_ID = 'resolveQuestionId';
    
    const NONCE_FOLLOW = 'cma_follow';
    
    

    public static function initialize()
    {
    	
    	add_filter('manage_edit-' . CMA_Thread::POST_TYPE . '_columns', array(get_class(), 'registerAdminColumns'));
    	add_filter('manage_edit-' . CMA_Thread::POST_TYPE . '_sortable_columns', array(get_class(), 'registerAdminSortableColumns'));
    	add_filter('manage_' . CMA_Thread::POST_TYPE . '_posts_custom_column', array(get_class(), 'adminColumnDisplay'), 10, 2);
    	do_action('CMA_custom_post_type_nav', CMA_Thread::POST_TYPE);
    	add_filter('CMA_admin_settings', array(get_class(), 'addAdminSettings'));
    	add_action('pre_get_posts', array(get_class(), 'fixPostType'), 1, 1);
    	add_action('pre_get_posts', array(get_class(), 'registerCustomOrder'), 9999, 1);
    	add_action('pre_get_posts', array(get_class(), 'registerTagFilter'), 9999, 1);
    	add_action('pre_get_posts', array(get_class(), 'registerCustomFilter'), 9999, 1);
    	add_action('pre_get_posts', array(get_class(), 'registerPageCount'), 9999, 1);
    	add_action('pre_get_posts', array(get_class(), 'registerAsHomepage'), 99, 1);
    	add_action('pre_get_posts', array(get_class(), 'checkIfDisabled'), 98, 1);
//     	add_action('pre_get_posts', array(get_class(), 'registerAdminCustomOrder'), 98, 1);
    	add_filter('the_posts', array(__CLASS__, 'thePostsFilter'));
    	add_action('cma_index_header_after', array(__CLASS__, 'indexHeaderAfter'));
    	add_filter('posts_where', array(get_class(), 'registerCommentsFiltering'), 1, 1);
    	add_action('added_term_relationship', array(__CLASS__, 'addedTermRelationship'), 10, 2);
    	add_filter('cma_thread_meta_keys', array('CMA_Thread', 'getMetaKeys'), 1, 1);
    	add_filter('cma_answer_meta_keys', array('CMA_Answer', 'getMetaKeys'), 1, 1);
    	add_filter('cma_comment_meta_keys', array('CMA_Comment', 'getMetaKeys'), 1, 1);
    	add_filter('cma_restore_widget_options', array(__CLASS__, 'restoreWidgetOptions'));
    	add_action('admin_notices', array(__CLASS__, 'printAdminWarnings'));
    	add_filter('cma_video_helper_process_content', array('CMA_VideoHelper', 'processContent'));
    	
    	if (!CMA::isLicenseOk()) return;
        add_filter('posts_search', array(get_class(), 'alterSearchQuery'), 9999, 2);
        add_action('template_redirect', array(get_class(), 'processQueryVars'), 1);
        add_filter('template_include', array(get_class(), 'overrideTemplate'), PHP_INT_MAX);
        add_action('parse_query', array(get_class(), 'processStatusChange'));
        add_filter('wp_nav_menu_items', array(get_class(), 'addMenuItem'), 1, 1);
       	add_action('wp_enqueue_scripts', array(__CLASS__, 'addDisclaimer'));
       	add_filter('cma_is_thread_visible', array(__CLASS__, 'isThreadVisible'), 10, 3);
       	add_filter('cma_is_answer_visible', array(__CLASS__, 'isAnswerVisible'), 10, 3);
       	add_filter('cma_is_category_visible', array(__CLASS__, 'isCategoryVisible'), 10, 3);
       	add_filter('cma_email_headers', array(__CLASS__, 'registerEmailContentType'));
       	add_filter('cma_email_body', array(__CLASS__, 'filterEmailBody'));
       	add_action('cma_thread_set_best_answer', array(__CLASS__, 'bestAnswerNotification'), 10, 1);
        /* add_action('pre_get_posts', array(get_class(), 'registerStickyPosts'), 1, 1); */

       	add_action('transition_post_status', array(get_class(), 'processPostStatusChange'), 10, 3);
       	add_action('before_delete_post', array(get_class(), 'processBeforePostDelete'));
        add_action('wp_set_comment_status', array(get_class(), 'processAnwserStatusChange'), 999, 2);
        add_action('delete_comment', array(get_class(), 'processBeforeCommentDelete'));
        add_action('CMA_display_question_form_upload', array(get_class(), 'displayQuestionFormUpload'), 98, 1);
        add_action('CMA_display_answer_form_upload', array(get_class(), 'displayAnswerFormUpload'), 98, 1);
        add_action('CMA_form_tags', array(__CLASS__, 'displayFormTags'));
        add_action('CMA_breadcrumbs', array(get_class(), 'displayBreadcrumbs'), 98, 1);
        add_action('CMA_questions_table_before', array('CMA_Ads', 'display'));
        add_action('CMA_questions_table_after', array('CMA_Ads', 'display'));
        add_action('CMA_thread_before', array('CMA_Ads', 'display'));
        add_action('CMA_thread_answers_before', array('CMA_Ads', 'display'));
        add_action('CMA_thread_answers_after', array('CMA_Ads', 'display'));
        add_action('CMA_thread_answers_form_after', array('CMA_Ads', 'display'));
        add_action('cma_question_snippet', array(__CLASS__, 'showQuestionSnippet'), 10, 2);
        add_action('wp_head', array(__CLASS__, 'wp_head_inline_styles'));
        add_action('wp_head', array(__CLASS__, 'wp_head_noindex'));
        add_filter('the_permalink', array(__CLASS__, 'filterThePermalink'));
        self::initThreadPage();
        
        // AJAX actions
        self::addAjaxHandler('cma_vote', array(get_class(), 'processVote'));
        self::addAjaxHandler('cma_tag_autocomplete', array(get_class(), 'processTagAutocomplete'));

        
        if( is_admin() )
        {
            add_filter('post_row_actions', array(get_class(), 'pageRowActions'), 10, 2);
        }

        if (CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_LOGIN_WIDGET)) {
        	add_action('CMA_login_form', array(get_class(), 'showLoginForm'));
        }
                
		add_shortcode('cma-followed', array(__CLASS__, 'shortcodeFollowed'));

        // Function runs only once on script install or update
        self::postsStickyReset();
    }
    
    
    
    static function printAdminWarnings() {
    	if (!current_user_can('manage_options')) return;
    	if (PHP_VERSION == '5.4.43') {
    		printf('<div class="error"><p>%s</p></div>',
    			'Your PHP version 5.4.43 contains a serious bug related to the anonymous functions. ' .
    			'For this reason the CM Answers plugin won\'t work properly.<br />Please ask your hosting provider to change the PHP version for your website.');
    	}
    	
    	// Check memory limit
    	$memoryLimit = ini_get('memory_limit');
    	$memoryLimitUnit = preg_replace('/[0-9]/', '', $memoryLimit);
    	$memoryLimitNumber = preg_replace('/[^0-9]/', '', $memoryLimit);
    	if ('G' == strtoupper($memoryLimitUnit)) $memoryLimitNumber *= 1024;
    	if ($memoryLimitNumber < 256) {
    		printf('<div class="error"><p>%s</p></div>',
    			sprintf('We are sorry, but the CM Answers plugin requires at least 256 MB memory, but your php.ini memory_limit is set to %s. ' .
    					'Please contact with your hosting provider and ask to increase the memory limit.', $memoryLimit)
    		);
    	}
    	
    }
    
    
    static function addDisclaimer($force = false) {
    	if( CMA_Thread::isDisclaimerEnabled() ) {
	    	if( $force OR (self::$query->get('post_type') == CMA_Thread::POST_TYPE || self::$query->is_tax(CMA_Category::TAXONOMY) )) {
		        wp_register_script('cma_disclaimer', CMA_URL . '/views/resources/disclaimer.js');
		        wp_localize_script('cma_disclaimer', 'cma_disclaimer_opts', array(
		            'content'    => CMA_Thread::getDisclaimerContent(),
		            'acceptText' => CMA_Thread::getDisclaimerContentAccept(),
		            'rejectText' => CMA_Thread::getDisclaimerContentReject()));
		        wp_enqueue_script('cma_disclaimer');
	    	}
    	}
    }
    
    
    static protected function initThreadPage() {
        add_filter('the_content', array(__CLASS__, 'filterQuestionContent'), PHP_INT_MAX-5);
        add_filter('the_content', array(__CLASS__, 'filterQuestionContentBody'), 5);
        add_filter('the_content', array(__CLASS__, 'filterQuestionContentShortcodes'), 4);
        add_filter('comments_template', array(__CLASS__, 'commentsTemplate'));
        add_filter('edit_post_link', array(__CLASS__, 'editPostLink'));
        add_action('get_template_part_cma', array(__CLASS__, 'getTemplatePart'), 10, 2);
        
        add_filter('the_content', array(__CLASS__, 'filterIndexContent'), PHP_INT_MAX);
        add_filter('comments_clauses', array(__CLASS__, 'avoidIndexComments'), PHP_INT_MAX, 2);
        
    }
    
    
    public static function avoidIndexComments($pieces, WP_Comment_Query $query) {
    	if (is_main_query() AND get_query_var('is_cma_index')) {
    		$pieces['where'] .= ' AND 1=2 ';
    	}
    	return $pieces;
    }
    
    
    
    public static function filterIndexContent($content) {
    	if (is_main_query() AND get_query_var('is_cma_index') AND get_query_var('cma_prepared_single')) {
    		$questions = array_filter(array_map(array('CMA_Thread', 'getInstance'), self::$query->posts));
    		$displayOptions = CMA_Settings::getDisplayOptionsDefaults();
    		return self::_loadView('answer/content-archive', compact('questions', 'displayOptions'), true);
    	} else return $content;
    }
    
    
    public static function filterThePermalink($url) {
    	if (is_main_query() AND get_query_var('is_cma_index') AND get_query_var('cma_prepared_single')
    			AND $fakePost = self::getFakePost() AND $url == get_permalink($fakePost->ID)) {
    		$url = site_url() . $_SERVER['REQUEST_URI'];
    	}
    	return $url;
    }
    
    
    public static function editPostLink($link) {
    	if ((is_main_query() AND get_post_type() == CMA_Thread::POST_TYPE) OR get_query_var('cma_prepared_single')) {
    		// Don't show edit link
    		$link = '';
    	}
    	return $link;
    }
    
    
    public static function filterQuestionContent($content) {
    	if (is_main_query() AND is_single() AND get_post_type() == CMA_Thread::POST_TYPE) {
    		remove_filter('the_content', array(__CLASS__, __FUNCTION__));
    		$post = get_post();
    		$thread = CMA_Thread::getInstance($post->ID);
    		$data = self::createHTMLData($thread->getViewData());
    		$displayOptions = self::getDisplayOptions();
	    	return self::_loadView('answer/content-question', compact('thread', 'data', 'content', 'displayOptions'));
        } else return $content;
    }
    
    
    static function getDisplayOptions() {
    	$widgetOptions = self::restoreWidgetOptions();
    	if (!empty($widgetOptions) AND !empty($widgetOptions['displayOptions'])) {
    		return $widgetOptions['displayOptions'];
    	} else {
    		return CMA_Settings::getDisplayOptionsDefaults();
    	}
    }
    
    
    public static function filterQuestionContentShortcodes($content) {
    	if (is_main_query() AND is_single() AND get_post_type() == CMA_Thread::POST_TYPE) {
    		remove_filter( 'the_content', 'do_shortcode', 11 );
    		return do_shortcode(CMA_Thread::filterShortcodes($content));
    	} else return $content;
    }
    
    
    public static function filterQuestionContentBody($content) {
    	if (is_main_query() AND is_single() AND get_post_type() == CMA_Thread::POST_TYPE) {
    		remove_filter('the_content', array(__CLASS__, __FUNCTION__));
	    	$content = CMA_VideoHelper::processContent($content);
    	}
    	return $content;
    }
    
    
    
    public static function isThreadVisible($result, $threadId, $userId) {
    	if ($thread = CMA_Thread::getInstance($threadId)) {
    		if (!$thread->isVisible($userId)) {
    			$result = false;
    		}
    	} else {
    		$result = false;
    	}
    	return $result;
    }
    
    
    public static function isAnswerVisible($result, $answerId, $userId) {
    	if ($answer = CMA_Answer::getById($answerId)) {
    		if (!$answer->isVisible($userId)) {
    			$result = false;
    		}
    	} else {
    		$result = false;
    	}
    	return $result;
    }
    
    public static function isCategoryVisible($result, $categoryId, $userId) {
    	if ($category = CMA_Category::getInstance($categoryId)) {
    		if (!$category->isVisible($userId)) {
    			$result = false;
    		}
    	} else {
    		$result = false;
    	}
    	return $result;
    }
    
    
    public static function getTemplatePart($slug, $name = null) {
    	$post = get_post();
    	$thread = CMA_Thread::getInstance($post->ID);
    	switch ($name) {
    		case 'header':
    			if (self::isAjax()) {
    				echo self::_loadView('answer/meta/single-header', compact('thread'));
    			}
    			break;
    		case 'breadcrumbs':
    			do_action('CMA_breadcrumbs');
    			break;
    		case 'navbar':
    			if (CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_QUESTION_PAGE_NAV_BAR) AND empty($_GET['post_id'])) {
    				$displayOptions = self::getDisplayOptions();
    				echo self::_loadView('answer/nav/nav-bar', compact('thread', 'displayOptions'));
    			}
    			break;
    		case 'backlink':
				echo self::getBacklink();
				break;
    		case 'messages':
    			if (is_main_query()) {
    				do_action('cma_flash_messages');
    			}
    			break;
    		case 'tags':
    			echo CMA_Thread::getTags($post->ID);
    			break;
    		case 'social-icons':
    			if( CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_SOCIAL) AND empty($_GET['widgetCacheId']) ) {
    				echo self::_loadView('answer/widget/social', compact('thread'));
    			}
    			break;
    		case 'thread-ref-id':
    			if( CMA_Settings::getOption(CMA_Settings::OPTION_THREAD_DISPLAY_ID) ) {
    				echo self::_loadView('answer/meta/thread-ref-id', compact('thread'));
    			}
    			break;
    		case 'rating-question':
    			$rating = $thread->getPostRating();
    			$usersFavoriteNumber = count($thread->getUsersFavorite());
    			$favoriteTitle = $thread->isFavorite() ? CMA::__('Unmark as favorite') : CMA::__('Mark as favorite');
    			$favoriteNumberTitle = sprintf(CMA_Labels::getLocalized('favorite_for_users'), $usersFavoriteNumber);
    			echo self::_loadView('answer/meta/rating-question',
    				compact('thread', 'rating', 'usersFavoriteNumber', 'favoriteTitle', 'favoriteNumberTitle'));
    			break;
    		case 'attachments':
    			$attachments = $thread->getAttachments();
    			echo self::_loadView('answer/meta/attachments', compact('thread', 'attachments'));
    			break;
    		case 'user-related-questions':
    			if (CMA_Settings::getOption(CMA_Settings::OPTION_USER_RELATED_QUESTIONS_ENABLE)) {
	    			$questions = $thread->getUserRelatedQuestions(true);
	    			echo self::_loadView('answer/meta/user-related-questions', compact('thread', 'questions'));
    			}
    			break;
    		case 'resolve-question':
    			echo self::_loadView('answer/meta/resolve-question', compact('thread'));
    			break;
    		case 'meta-question':
    			$avatar = CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_GRAVATARS) ? get_avatar($thread->getAuthorId(), 32) : null;
    			$author = sprintf(CMA::__('Posted by %s'), $thread->getAuthorLink());
				if ( CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_USER_STATS) ) {
					$author .= ' '. self::getUserStats($thread->getAuthorId());
				}
				$displayOptions = self::getDisplayOptions();
    			echo self::_loadView('answer/meta/meta-question', compact('thread', 'avatar', 'author', 'userStats', 'displayOptions'));
    			break;
    		case 'controls-question':	
    			echo self::_loadView('answer/meta/controls-question', compact('thread'));
    			break;
    		case 'comments-question':
    			do_action('CMA_comments_question', $thread->getId());
    			break;
    		case 'answers':
    			global $answers, $answersSort;
    			$answersSort = !empty($_GET['sort']) ? $_GET['sort'] : CMA_Answer::getOrderBy();
    			if (!ctype_alnum($answersSort)) $answersSort = 'newest';
    			$answers = $thread->getAnswers($answersSort, $onlyVisible = true);
    			$data = self::createHTMLData(array(
    				'rating-enabled' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_RATING_ALLOWED)),
    				'rating-negative-allowed' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_NEGATIVE_RATING_ALLOWED)),
    				'best-answer-defined' => ($thread->getBestAnswerId() ? 1 : 0),
    				'best-answer-enabled' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_ENABLED_MARK_BEST_ANSWER)),
    				'allow-own-best-answer' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_OWN_BEST_ANSWER)),
    				'best-answer-nonce' => wp_create_nonce('cma_best_answer'),
    				'spam-nonce' => wp_create_nonce('cma_report_spam'),
    			));
    			echo self::_loadView('answer/thread-answers', compact('thread', 'data'));
    			break;
    		case 'single-answer':
    			global $answer;
    			$data = self::createHTMLData($answer->getViewData());
    			echo self::_loadView('answer/single-answer', compact('thread', 'answer', 'data'));
    			break;
    		case 'sort-answers':
    			global $answers, $answersSort;
    			if( CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_RATING_ALLOWED) AND !empty($answers) AND count($answers) > 1) {
    				
    				$desc = CMA_Settings::getOption(CMA_Settings::OPTION_ANSWER_SORTING_DESC);
					$dateClass = ($answersSort == CMA_Answer::ORDER_BY_DATE) ? 'cma-current-sort' : '';
					$dateLabel = CMA_Labels::getLocalized($desc ? 'orderby_newest' : 'orderby_oldest');
					$dateUrl = $thread->getPermalink(array('sort' => 'newest'));
					$votesClass = ($answersSort == CMA_Answer::ORDER_BY_VOTES) ? 'cma-current-sort' : '';
					$votesLabel = CMA_Labels::getLocalized($desc ? 'orderby_highest_rating' : 'orderby_lowest_rating');
					$votesUrl = $thread->getPermalink(array('sort' => 'votes'));
    				echo self::_loadView('answer/meta/sort-answers', compact('thread', 'dateClass', 'dateLabel', 'dateUrl', 'votesClass', 'votesLabel', 'votesUrl'));
    			}
    			break;
    		case 'rating-answer':
    			global $answer;
    			$rating = $answer->getRating();
    			$bestAnswerLabel = CMA_Labels::getLocalized('best_answer');
    			echo self::_loadView('answer/meta/rating-answer',
    				compact('thread', 'rating', 'bestAnswerLabel'));
    			break;
    		case 'content-answer':
    			global $answer;
    			$content = do_shortcode(CMA_Thread::filterShortcodes(CMA_VideoHelper::processContent($answer->getContent())));
    			echo self::_loadView('answer/content-answer', compact('thread', 'answer', 'content'));
    			break;
    		case 'attachments-answer':
    			global $answer;
    			$attachments = $answer->getAttachments();
    			echo self::_loadView('answer/meta/attachments', compact('thread', 'answer', 'attachments'));
    			break;
    		case 'meta-answer':
    			global $answer;
    			$avatar = CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_GRAVATARS) ? get_avatar($answer->getAuthorId(), 32) : null;
    			$author = sprintf(CMA::__('Posted by %s'), $answer->getAuthorLink());
				if ( CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_USER_STATS) ) {
					$author .= ' '. self::getUserStats($answer->getAuthorId());
				}
    			echo self::_loadView('answer/meta/meta-answer', compact('thread', 'answer', 'avatar', 'author', 'userStats'));
    			break;
    		case 'controls-answer':
    			global $answer;	
    			echo self::_loadView('answer/meta/controls-answer', compact('thread', 'answer'));
    			break;
    		case 'comments-answer':
    			global $answer;	
    			do_action('CMA_comments_answer', $answer->getThreadId(), $answer->getId());
    			break;
    		case 'form-answer':
    			if( !$thread->isResolved() || CMA_Settings::getOption(CMA_Settings::OPTION_ANSWER_AFTER_RESOLVED) ) {
    				$backlink = self::getBacklink();
    				echo CMA_BaseController::_loadView('answer/widget/answer-form', compact('post', 'thread', 'backlink'));
					do_action('CMA_thread_answers_form_after');
    			}
    			break;
    	}
    }
    
    
    public static function getBacklink() {
    	$backlinkUrl = (!empty($_GET['post_id']) AND is_numeric($_GET['post_id'])) ? get_permalink($_GET['post_id']) : CMA::getReferer();
		return sprintf('<a class="cma-backlink" href="%s"> &laquo; %s</a>', esc_attr($backlinkUrl), CMA_Labels::getLocalized('back_to_previous_page'));
    }
    
    public static function getUserStats($userId) {
    	static $users;
    	if (empty($users[$userId])) {
	    	$users[$userId] = sprintf('(%s: %d, %s: %d)',
				CMA_Labels::getLocalized('Questions'),
				CMA_Thread::getCountQuestionsByUser($userId),
				CMA_Labels::getLocalized('Answers'),
				CMA_Thread::getCountAnswersByUser($userId));
    	}
    	return $users[$userId];
    }
    
    
    public static function createHTMLData(array $data) {
    	$result = '';
    	foreach ($data as $key => $val) {
    		$result .= ' data-'. $key .'="'. esc_attr($val) .'"';
    	}
    	return $result;
    }
    
    
    public static function commentsTemplate($theme_template) {
    	if (is_main_query() AND is_single() AND get_post_type() == CMA_Thread::POST_TYPE) {
        	$name = 'answer/comments-template';
        	$path = CMA_PATH . '/views/frontend/'. $name .'.phtml';
        	return self::locateTemplate(array($name), $path);
        }
        else return $theme_template;
    }
    
    
    
    
    public static function registerSidebars() {
    	$sidebarSettings = array(
            'id'          => 'cm-answers-sidebar',
            'name'        => CMA_Settings::__('CM Answers Sidebar'),
            'description' => CMA_Settings::__('This sidebar is shown on CM Answers pages'),
        );
        $sidebarExtra = array_filter(array(
        	'before_widget' => CMA_Thread::getSidebarSettings('before_widget'),
        	'after_widget' => CMA_Thread::getSidebarSettings('after_widget'),
        	'before_title' => CMA_Thread::getSidebarSettings('before_title'),
        	'after_title' => CMA_Thread::getSidebarSettings('after_title'),
        ));
        register_sidebar(array_merge($sidebarSettings, $sidebarExtra));
    }


    /**
     * Embed CMA Custom CSS inline styles.
     */
    public static function wp_head_inline_styles() {
    	echo self::_loadView('answer/widget/inline-styles');
    }
    
    
    /**
     * Append meta noindex tag for some URLs.
     */
    public static function wp_head_noindex() {
    	
    	// Noindex for non-canonical
    	$addNoindex = false;
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_NOINDEX_NON_CANONICAL)) {
	    	if (self::$query->get('is_cma_index')) { // Index page
	    		$disallowedParamsKeys = array('sort', 'cmtag', 'widgetCacheId', 'question_type', 'search');
	    		$disallowedParams = array_intersect(array_keys($_GET), $disallowedParamsKeys);
	    		if (!empty($disallowedParams) OR $category = self::getCurrentCategory()) {
	    			$addNoindex = true;
	    		}
	    	}
	    	else if (self::$query->is_single() AND self::$query->get('post_type') == CMA_Thread::POST_TYPE ) { // Single thread
	    		$disallowedParamsKeys = array('sort', 'widgetCacheId');
	    		$disallowedParams = array_intersect(array_keys($_GET), $disallowedParamsKeys);
	    		if (!empty($disallowedParams)) {
	    			$addNoindex = true;
	    		}
	    	}
    	}
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_NOINDEX_CONTRIBUTOR) AND self::$query->get('contributor')) { // Contributor page
    		$addNoindex = true;
    	}
    	
    	// Print noindex
    	if ($addNoindex) {
    		echo '<meta name="robots" content="noindex, follow" />';
    	}
    	
    }
    
    
    public static function shortcodeFollowed() {
    	
    	if (!get_current_user_id()) return;
    	
    	if (CMA_Thread::canBeFollower()) {
    		$postsIds = CMA_FollowersEngine::getFollowed(CMA_Thread::FOLLOWERS_USER_META_PREFIX);
    	}
    	if (!empty($postsIds)) {
	    	$posts = get_posts(array(
	    			'include' => $postsIds,
	    			'post_type' => CMA_Thread::POST_TYPE,
	    			'number' => 100,
	    		));
    	} else {
    		$posts = array();
    	}
    	$threads = array();
    	foreach ($posts as $post) {
    		$threads[] = CMA_Thread::getInstance($post->ID);
    	}
    	
    	if (CMA_Category::canBeFollower()) {
    		$termsIds = CMA_FollowersEngine::getFollowed(CMA_Category::FOLLOWERS_USER_META_PREFIX);
    	}
    	if (!empty($termsIds)) {
	    	$terms = get_terms(CMA_Category::TAXONOMY, array(
	    		'include' => $termsIds,
	    		'hide_empty' => 0,
	    	));
    	} else {
    		$terms = array();
    	}
    	
    	$categories = array();
    	foreach ($terms as $term) {
    		$categories[] = CMA_Category::getInstance($term->term_id);
    	}
    	
    	return self::_loadView('answer/widget/followed', compact('threads', 'categories'));
    	
    }
    
    

    public static function pageRowActions($actions, $post)
    {

        if( $post->post_type === CMA_Thread::POST_TYPE )
        {
            $cmaPost = CMA_Thread::getInstance($post->ID);
            if( $cmaPost->isResolved() )
            {
                $actions['unresolved'] = '<a class="" href="' . esc_attr(add_query_arg(array('cma-action' => 'unresolve', 'cma-id' => $post->ID)))
                	. '" title="'. esc_attr(CMA_Settings::__('Mark as unresolved')) . '">' . CMA_Settings::__('Mark as unresolved') . '</a>';
            }
            else
            {
                $actions['resolved'] = '<a class="" href="' . esc_attr(add_query_arg(array('cma-action' => 'resolve', 'cma-id' => $post->ID)))
                	. '" title="' . esc_attr(CMA_Settings::__('Mark as resolved')) . '">' . CMA_Settings::__('Mark as resolved') . '</a>';
            }
        }

        return $actions;
    }

    public static function postsStickyReset()
    {
        global $wpdb;
        if( get_option('cma_sticky_posts_updated', 0) == 0 )
        {
            $wpdb->query("insert into {$wpdb->postmeta} (post_id, meta_key, meta_value)
                          select ID, '_sticky_post', '0' from $wpdb->posts");
            add_option('cma_sticky_posts_updated', 1);
        }
    }

    public static function alterSearchQuery($search, WP_Query $query)
    {
        if( ($query->is_main_query() AND $query->query_vars['post_type'] == CMA_Thread::POST_TYPE OR !empty($query->query_vars['cma_category']))
        	&& !$query->is_single && !$query->is_404 && !$query->is_author && isset($query->query['search']) )
        {
            global $wpdb;
            global $wp_version;
            $search_term = $query->query['search'];
            if( !empty($search_term) )
            {
                $search = '';
                $query->is_search = true;
                // added slashes screw with quote grouping when done early, so done later
                $search_term = stripslashes($search_term);
                preg_match_all('/".*?("|$)|((?<=[\r\n\t ",+])|^)[^\r\n\t ",+]+/', $search_term, $matches);

                if( version_compare($wp_version, '3.7', '<') )
                {
                    $terms = array_map('_search_terms_tidy', $matches[0]);
                }
                else
                {
                    $terms = array_map(function($t)
                    {
                        return trim($t, "\"'\n\r ");
                    }, $matches[0]);
                }

                $n = '%';
                $searchand = ' AND ';
                foreach((array) $terms as $term)
                {
                	$search .= $wpdb->prepare(" AND ($wpdb->posts.post_title LIKE %s OR $wpdb->posts.post_content LIKE %s)", "%$term%", "%$term%");
                }
                add_filter('get_search_query', function($q) use ($search_term) { return $search_term; }, 99, 1);
                remove_filter('posts_request', 'relevanssi_prevent_default_request');
                remove_filter('the_posts', 'relevanssi_query');
            }
        }
        return $search;
    }

    public static function addMenuItem($items)
    {
        if( CMA_Settings::getOption(CMA_Settings::OPTION_ADD_ANSWERS_MENU) )
        {
            $link = self::_loadView('answer/meta/menu-item', array(
            	'checkPermissions' => true,
            	'widget' => true,
            	'listUrl' => CMA::permalink(),
            ));
            return $items . $link;
        }
        return $items;
    }

    public static function showLoginForm($options)
    {
        echo self::_loadView('answer/widget/login', $options);
    }
    
    
    
    public static function processBeforePostDelete($postId) {
    	if ($thread = CMA_Thread::getInstance($postId)) {
    		$thread->clearCache($hard = true);
    	}
    }
    
    
    public static function processPostStatusChange($newStatus, $oldStatus, $post) {
    	if ($post->post_type == CMA_Thread::POST_TYPE AND $thread = CMA_Thread::getInstance($post->ID)) {
    		if ($newStatus != 'publish') {
    			// When question has been removed - purge the W3TC page cache
    			$thread->clearCache($hard = true);
    		}
    	}
    }
    

    public static function processStatusChange()
    {
        if( is_admin() && get_query_var('post_type') == CMA_Thread::POST_TYPE && isset($_REQUEST['cma-action']) )
        {
        	
        	$cretateNotice = function($msg) {
        		add_action('admin_notices', function($q) use ($msg) {
                	printf('<div class="updated"><p>%s</p></div>', $msg);
                });
        	};
        	
            switch($_REQUEST['cma-action'])
            {
                case 'approve':
                    $id = $_REQUEST['cma-id'];
                    if( is_numeric($id) )
                    {
                        $thread = CMA_Thread::getInstance($id);
                        $thread->approve();
                        $thread->notifyAboutNewQuestion();
                        $cretateNotice(sprintf(CMA_Settings::__('Question "%s" has been succesfully approved.'),
                        		esc_html($thread->getTitle())));
                    }
                    break;
                case 'trash':
                    $id = $_REQUEST['cma-id'];
                    if( is_numeric($id) )
                    {
                        $thread = CMA_Thread::getInstance($id);
                        $thread->trash();
                        $cretateNotice(sprintf(CMA_Settings::__('Question "%s" has been succesfully moved to trash.'),
                        		esc_html($thread->getTitle())));
                    }
                    break;
                case 'resolve':
                    $id = $_REQUEST['cma-id'];
                    if( is_numeric($id) )
                    {
                        $thread = CMA_Thread::getInstance($id);
                        $thread->setResolved(true);
                        $cretateNotice(sprintf(CMA_Settings::__('Question "%s" has been succesfully marked as resolved.'),
                        		esc_html($thread->getTitle())));
                    }
                    break;
                case 'unresolve':
                    $id = $_REQUEST['cma-id'];
                    if( is_numeric($id) )
                    {
                        $thread = CMA_Thread::getInstance($id);
                        $thread->setResolved(false);
                        $cretateNotice(sprintf(CMA_Settings::__('Question "%s" has been succesfully marked as unresolved.'),
                        		esc_html($thread->getTitle())));
                    }
                    break;
            }
        }
    }
    
    
    public static function processBeforeCommentDelete($commentId) {
    	if ($answer = CMA_Answer::getById($commentId) AND $thread = $answer->getThread()) {
    		$thread->clearCache($hard = true);
    	}
    }
    

    public static function processAnwserStatusChange($answerId, $status)
    {
        /*
         * Get the comment, author, thread
         */
        $answer = get_comment($answerId);

        /*
         * Comment not found
         */
        if( !$answer)
        {
            return;
        }
        
        // Comment is not a CMA answer nor comment
        if ($answer->comment_type != CMA_Answer::COMMENT_TYPE AND $answer->comment_type != CMA_Comment::COMMENT_TYPE) {
        	return;
        }

        $thread = CMA_Thread::getInstance($answer->comment_post_ID);
        /*
         * Fix in case $thread isn't found
         */
        if( is_object($thread) )
        {
        	$thread->updateThreadMetadata(array('answerId' => $answerId));
        	
        	if ($status == 'approve') { // Send notifications
        		$thread->setUpdated();
	        	if ($answer->comment_type == CMA_Answer::COMMENT_TYPE) {
	        		$thread->notifyAboutNewAnswer($answerId);
	        	}
	        	else if ($comment = CMA_Comment::getById($answerId)) {
	        		$comment->sendNotifications();
	        	}
        	} else {
        		$thread->clearCache();
        	}
    	}
    }
    
    
    public static function fixPostType(WP_Query $query) {
    	if ($query->get('cma_category') AND $query->get('post_type') != CMA_Thread::POST_TYPE) {
    		$query->set('post_type', CMA_Thread::POST_TYPE);
    	}
    }
    

    public static function registerCustomOrder(WP_Query $query)
    {
    	if (is_admin()) return;
        if ($query->get('post_type') == CMA_Thread::POST_TYPE AND $query->is_main_query()
        	&& (!isset($query->query_vars['widget']) || $query->query_vars['widget'] !== true)
        	&& !$query->is_single && !$query->is_404 && !$query->is_author )
        {
        	if (isset($_GET['sort'])) {
	            if( !$query->get('widget') && !$query->get('user_questions') )
	            {
	                $query = CMA_Thread::customOrder($query, $_GET['sort']);
	                $query->is_top = true;
	            }
        	}
        	else if ($widgetOptions = self::restoreWidgetOptions()) {
	    		if (!empty($widgetOptions['sort'])) {
	    			$query = CMA_Thread::customOrder($query, $widgetOptions['sort']);
	    			$query->is_top = true;
	    		}
	    		if (!empty($widgetOptions['order'])) {
	    			$query->set('order', $widgetOptions['order']);
	    		}
	        } else {
	        	$query = CMA_Thread::customOrder($query, CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_ORDER_BY));
	        }
        }
        
    }

    public static function registerTagFilter(WP_Query $query)
    {
        if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CMA_Thread::POST_TYPE
        	&& (!isset($query->query_vars['widget']) || $query->query_vars['widget'] !== true)
        	&& !$query->is_single && !$query->is_404 && !$query->is_author && isset($_GET['cmatag']) )
        {
            if( !$query->get('widget') && !$query->get('user_questions') )
            {
                $query = CMA_Thread::tagFilter($query, $_GET['cmatag']);
            }
        }
        
    }
    
    
    public static function registerCustomFilter(WP_Query $query) {
    	if( !is_admin() AND isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CMA_Thread::POST_TYPE ) {
    		
    		if ((!isset($query->query_vars['widget']) || $query->query_vars['widget'] !== true) && !$query->is_single && !$query->is_404 && !$query->is_author) {
    			
    			if ($widgetOptions = self::restoreWidgetOptions()) {
    				if (!empty($widgetOptions['author'])) {
    					$query->set('author', $widgetOptions['author']);
    				}
    				if (isset($widgetOptions['resolved']) AND !is_null($widgetOptions['resolved'])) {
    					$metaQuery = $query->get('meta_query'); 
    					$metaQuery[] = array(
			        		'key' => CMA_Thread::$_meta['resolved'],
			        		'value' => intval($widgetOptions['resolved']),
			        	);
    					$query->set('meta_query', $metaQuery);
    				}
    			}
    			else if (CMA_Thread::showOnlyOwnQuestions()) {
		    		$userId = get_current_user_id();
		    		if (empty($userId)) $userId = 99999999;
		    		$query->set('author', $userId);
    			}
	    		
    		}
    		
    		add_filter('posts_where_request', array(__CLASS__, 'categoryAccessFilter'));
    		
    	}
    	
    }
    
    
    public static function thePostsFilter($posts) {
    	remove_filter('posts_where_request', array(__CLASS__, 'categoryAccessFilter'));
    	return $posts;
    }
    
    
    public static function categoryAccessFilter($val) {
    	
    	// Don't add a subquery if no category restricted (query time optimization):
    	if (!CMA_Category::isAnyCategoryResticted()) return $val;
    	
    	$val .= ' AND (ID IN ('. CMA_Thread::getCategoryAccessFilterSubquery() .')
    					OR ID NOT IN ('. CMA_Thread::getCategorizedThreadIdsSubquery() .')
    					OR post_author = '. intval(get_current_user_id()) .'
    				)';
    	return $val;
    	
    }
    

    public static function registerPageCount($query)
    {
    	if (is_admin()) return;
        if( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CMA_Thread::POST_TYPE
        	&& (!isset($query->query_vars['widget']) || $query->query_vars['widget'] !== true)
        	&& !$query->is_single && !$query->is_404 && !$query->is_author )
        {
            if( !$query->get('widget') && !isset($_GET["pagination"]) && !$query->get('user_questions') )
            {
                $query->set('posts_per_page', CMA_Settings::getOption(CMA_Settings::OPTION_ITEMS_PER_PAGE));
            }
        }
        
        if ($widgetOptions = self::restoreWidgetOptions() AND !empty($widgetOptions['limit'])) {
        	$query->set('posts_per_page', $widgetOptions['limit']);
        }
        
    }
    
    
    
    public static function restoreWidgetOptions() {
    	if (isset($_GET['widgetCacheId'])) {
    		$options = get_transient('cma_widget_'. $_GET['widgetCacheId']);
    		if (!empty($options)) return $options;
    	}
    }
    
    
    public static function saveWidgetOptions($options) {
    	$key = md5(microtime() . rand());
    	set_transient('cma_widget_'. $key, $options, 3600*24);
    	return $key;
    }
    

    public static function registerAsHomepage(WP_Query $query)
    {
        /* @var $query WP_Query */
        if( $query->is_main_query() && $query->is_home() && CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_AS_HOMEPAGE)
        	&& empty($query->query_vars['post_type']) && !$query->is_page )
        {
            $query->set('post_type', CMA_Thread::POST_TYPE);
            $query->is_archive = true;
            $query->is_page = false;
            $query->is_post_type_archive = true;
            $query->is_home = true;
            $query->set('cma_homepage', 1);
        }
    }

    public static function checkIfDisabled($query)
    {
        /* @var $query WP_Query */
        if( $query->is_main_query() && CMA_Settings::getOption(CMA_Settings::OPTION_ANSWER_PAGE_DISABLED) && !$query->is_single() && !CMA_AJAX && !self::_isPost()
        	&& isset($query->query_vars['post_type']) AND $query->query_vars['post_type'] == CMA_Thread::POST_TYPE )
        {
            $query->is_404 = true;
        }
    }
    


    public static function registerCommentsFiltering($sql, $questionType = null)
    {
    	if ($widgetOptions = self::restoreWidgetOptions() AND !is_null($widgetOptions['answered'])) {
    		$questionType = ($widgetOptions['answered'] ? 'ans' : 'unans');
    	}
    	else if (empty($questionType) AND !empty($_GET['question_type'])) {
    		$questionType = $_GET['question_type'];
    	}
        if( !empty($questionType) )
        {
            global $wpdb;
            
            switch ($questionType) {
            	case 'ans':
            		$expr = ' > 0 ';
            		break;
            	case 'unans':
            		$expr = ' = 0 ';
            		break;
            	default:
            		return $sql;
            }
            
            $sql .= $wpdb->prepare(" AND (SELECT COUNT(*) FROM {$wpdb->comments} cma_comments_filtering
            	WHERE cma_comments_filtering.comment_type = %s
            	AND cma_comments_filtering.comment_post_ID = {$wpdb->posts}.ID
            	AND cma_comments_filtering.comment_approved = 1) $expr ",
            CMA_Answer::COMMENT_TYPE);
            
        }
        return $sql;
    }

    public static function overrideTemplate($template)
    {
    	global $wp_query;
    	
//     	var_dump($wp_query->request);exit;
    	
        if( get_query_var('post_type') == CMA_Thread::POST_TYPE || is_tax(CMA_Category::TAXONOMY) )
        {
        	
        	// Avoid to display 404 when trying to load to big page number
        	if (is_404() AND self::$query->get('paged') > 1) {
        		wp_redirect(preg_replace('#/page/[0-9]+/#', '/', $_SERVER['REQUEST_URI']));
        		exit;
        	}
        	
            if (!CMA_Thread::canBeViewed()) {
            	$template = self::prepareSinglePage(
            		$title = CMA_Labels::getLocalized('index_page_title'),
            		$content = self::_loadView('answer/meta/access-denied', array(), true),
            		true
            	);
            }
            else if( is_404() ) {
                // leave default 404 template
                $template = get_404_template();
            }
            else if( is_single() )
            {
            	
            	global $post;

                if (!empty($_GET[self::PARAM_EDIT_QUESTION_ID])) {
                	$template = self::prepareSinglePage($post->post_title, self::_processEditQuestionView(), true);
                }
                else if (!empty($_GET[self::PARAM_EDIT_ANSWER_ID])) {
                	$template = self::prepareSinglePage($post->post_title, self::_processEditAnswerView(), true);
                }
                else { // Thread page
                	
                	wp_enqueue_script('cma-toast', CMA_URL . '/views/resources/toast/js/jquery.toastmessage.js', array('jquery'), false, true);
                	wp_enqueue_style('cma-toast-css', CMA_URL . '/views/resources/toast/resources/css/jquery.toastmessage.css', array(), false);
                	
                	if( CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_SOCIAL) )
                	{
                		wp_enqueue_script('cma-twitter', 'https://platform.twitter.com/widgets.js', array(), false, true);
                		wp_enqueue_script('cma-linkedin', 'https://platform.linkedin.com/in.js', array(), false, true);
                	}
                	
                	if (self::isAjax()) {
                		$template = self::locateTemplate(array('answer/ajax'), $template);
                	}
					else if ($name = CMA_Settings::getThreadPageTemplate()) {
	                	$template = locate_template(array($name, 'page.php', 'single.php'), false, false);
	                	add_filter('body_class', array(__CLASS__, 'pageBodyClass'), 20);
					} else {
						$template = self::locateTemplate(array('answer/single'), $template);
					}
                }

            }
            else // Index page
            {

            	if (self::isAjax()) {
            		self::prepareSinglePage(
            			$title = self::getIndexTitle(),
            			$content = '',
            			$newQuery = true
            		);
            		$template = self::locateTemplate(array('answer/ajax'), $template);
            	}
            	else if ($name = CMA_Settings::getIndexPageTemplate()) {
	            	self::prepareSinglePage(
	            		$title = self::getIndexTitle(),
	            		$content = '',
	            		$newQuery = true
	            	);
	            	$template = locate_template(array($name, 'page.php', 'single.php'), false, false);
	            	add_filter('body_class', array(__CLASS__, 'pageBodyClass'), 20);
            	} else {
            		$template = self::locateTemplate(array('answer/index'), $template);
            	}
            	$wp_query->set('is_cma_index', 1);
            	self::$query->set('is_cma_index', 1);
            	
            }
            add_filter('body_class', array(get_class(), 'adjustBodyClass'), 20, 2);
            self::loadScripts();
            
        }
        else if (get_query_var('cma_answer_answers')) {
        	self::loadScripts();
        }
        
        return $template;
    }
    
    
    
    
    static function pageBodyClass($classes) {
    	if (self::$query->is_single()) {
    		$template = CMA_Settings::getThreadPageTemplate();
    	} else {
    		$template = CMA_Settings::getIndexPageTemplate();
    	}
    	$classes[] = 'page';
        $classes[] = 'page-template';
		$classes[] = 'page-template-' . sanitize_html_class( str_replace( '.', '-', $template ) );
        if (stripos($template, 'full-width') !== false) {
        	$classes[] = 'full-width';
        }
        return $classes;
    }


    protected static function _processEditQuestionView() {
    	global $answer, $post, $cmaPageContent, $populatedData;
    	remove_filter('the_content', 'wptexturize');
    	$thread = CMA_Thread::getInstance($post->ID);
    	if ($thread->canEditQuestion()) {
	    	$_SESSION['CMA_populate'] = array(
	    		'thread_id' => $post->ID,
    			'thread_title' => $post->post_title,
    			'thread_content' => $post->post_content,
	    		'thread_category' => $thread->getCategoryId(),
    			'thread_tags' => $thread->getTagsString(),
    		);
    	} else {
    		self::addMessage(self::MESSAGE_ERROR, CMA_Labels::getLocalized('Cannot edit this question.'));
    		wp_safe_redirect(CMA::getReferer());
    		exit;
    	}
    	$edit = true;
    	$cmaPageContent = CMA_BaseController::_loadView('answer/widget/question-form', compact('post', 'thread', 'edit', 'populatedData'));
    	return $cmaPageContent;
    }

    
    protected static function _processEditAnswerView() {
    	global $answer, $post, $cmaPageContent, $populatedData;
    	remove_filter('the_content', 'wptexturize');
    	$thread = CMA_Thread::getInstance($post->ID);
    	$answerId = $_GET[self::PARAM_EDIT_ANSWER_ID];
    	$answer = CMA_Answer::getById($answerId, get_current_user_id());
    	if ($thread AND $answer AND $answer->canEdit()) {
    		$populatedData['content'] = $answer->getContent();
    	} else {
    		self::addMessage(self::MESSAGE_ERROR, CMA_Labels::getLocalized('Cannot edit this answer.'));
    		wp_safe_redirect(CMA::getReferer());
    		exit;
    	}
    	$edit = true;
    	$cmaPageContent = CMA_BaseController::_loadView('answer/widget/answer-form', compact('post', 'thread', 'edit', 'populatedData'));
    	return $cmaPageContent;
    }

    /* protected static function _processViews()
    {
        global $wp_query;
        $post = $wp_query->post;
        $thread = CMA_Thread::getInstance($post->ID);
    } */

    protected static function _processEditQuestion() {

    	$error = false;
    	
    	if (isset($_GET['editQuestionId']) AND is_numeric($_GET['editQuestionId']) AND $thread = CMA_Thread::getInstance($_GET['editQuestionId'])
    		AND $thread->canEditQuestion()) {
    		
	    	try {
	    		
		    	if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_question')) {
					throw new Exception(CMA::__('Invalid nonce.'));
				}
	    		
	    		// Update content
	    		$thread->updateQuestionContent(get_current_user_id(), $_POST['thread_title'], $_POST['thread_content']);
	    		
	    		// Update tags
	    		$tags = (isset($_POST['thread_tags']) ? $_POST['thread_tags'] : null);
	    		wp_set_post_tags($thread->getId(), $tags);
	    		
	    	} catch (Exception $e) {
	    		$error = $e;
	    	}
	    	
    	} else {
    		$error = CMA::__('You cannot edit this question.');
    	}
    	
    	if ($error) {
    		if (self::isAjax()) {
    			
    			if (is_object($error) AND $error instanceof Exception) {
    				$array = @unserialize($error->getMessage());
    				if (!is_array($array)) $error = $error->getMessage();
    				else $error = $array;
    			}
    			
    			if (!is_array($error)) {
    				$error = array($error);
    			}
    			
    			header('Content-type: application/json');
    			echo json_encode(array('success' => 0, 'messages' => $error));
    			exit;
    			
    		} else {
    			self::addMessage(self::MESSAGE_ERROR, $error);
    		}
    	} else {
    		$msg = CMA::__('Question has been saved.');
    		if (self::isAjax()) {
    			header('Content-type: application/json');
    			echo json_encode(array('success' => 1, 'messages' => array($msg), 'redirect' => $thread->getPermalink()));
    			exit;
    		} else {
    			self::addMessage(self::MESSAGE_SUCCESS, $msg);
    			wp_safe_redirect($thread->getPermalink());
    			exit;
    		}
    	}

    }
    
    

    protected static function _processEditAnswer($answerId) {

    	$post = self::$query->post;
    	$thread = CMA_Thread::getInstance($post->ID);
    	$answer = CMA_Answer::getById($answerId, get_current_user_id());
    	if ($thread AND $answer AND $answer->canEdit()) {
	
	    	$error = false;
	    	$messages = array();
	
	    	try {
		    	if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_answer')) {
					throw new Exception(CMA::__('Invalid nonce.'));
				}
	    		$answer->updateContent($_POST['content'], get_current_user_id());
	    		self::addMessage(self::MESSAGE_SUCCESS, CMA_Labels::getLocalized('Answer has been updated.'));
		    	wp_safe_redirect($thread->getPermalink() .'#answer-'. $answerId);
		    	exit;
	    	} catch (Exception $e) {
	    		self::addMessage(self::MESSAGE_ERROR, $e);
	    		$error = true;
	    		wp_safe_redirect($answer->getEditURL());
	    		exit;
		    }
		    
    	} else {
    		self::addMessage(self::MESSAGE_ERROR, CMA_Labels::getLocalized('Cannot edit this answer.'));
    		$error = true;
    		wp_safe_redirect($post->ID ? get_permalink($post->ID) : CMA::permalink());
    		exit;
    	}

    }

    protected static function _processAddAnswerToThread()
    {
        $post = self::$query->post;
        $thread = CMA_Thread::getInstance($post->ID);
        $content = (isset($_POST['content']) ? stripslashes($_POST['content']) : '');
        $author_id = CMA::getPostingUserId();
        $error = false;
        $messages = array();
        try
        {
	        if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_answer')) {
				throw new Exception(CMA::__('Invalid nonce.'));
			}
			if (!$thread->canPostAnswers($author_id)) {
				throw new Exception(CMA::__('You cannot post answers.'));
			}
            $answerId = $thread->addAnswer($content, $author_id,
            	$follow = !empty($_POST['thread_notify']),
            	$resolved = !empty($_POST['thread_resolved']),
            	$private = !empty($_POST['private'])
            );
            if ($answerId AND $thread->getStatus() == 'draft' AND current_user_can('manage_options')) {
            	$thread->approve();
            	$thread->notifyAboutNewQuestion();
            }
            
        }
        catch(Exception $e)
        {
            $messages = @unserialize($e->getMessage());
            if (!is_array($messages)) $messages = array($e->getMessage());
            $error = true;
        }
        if( $error )
        {
        	foreach((array) $messages as $message)
        	{
        		self::addMessage(self::MESSAGE_ERROR, $message);
        	}
        }
        else
        {
        	$autoApprove = CMA_Settings::getOption(CMA_Settings::OPTION_ANSWER_AUTO_APPROVE) || CMA_Thread::isAuthorAutoApproved(CMA::getPostingUserId());

        	if( $autoApprove )
        	{
        		$msg = __('Your answer has been succesfully added.', 'cm-answers-pro');
        	}
        	else
        	{
        		$msg = __('Thank you for your answer, it has been held for moderation.', 'cm-answers-pro');
        	}
        	$msg = apply_filters('cma_answer_post_msg_success', $msg);
        	self::addMessage(self::MESSAGE_SUCCESS, $msg);
        	if( !empty($_POST['cma-referrer']) ) wp_redirect($_POST['cma-referrer'], 303);
        	else wp_redirect($_SERVER['REQUEST_URI'] . ($autoApprove ? '#answer-' . $answerId : ''), 303);
        	exit;
        }
    }

    protected static function _processAddThread()
    {
        
        $title = $_POST['thread_title'];
        $content = $_POST['thread_content'];
        $tags = (isset($_POST['thread_tags']) ? $_POST['thread_tags'] : null);
        $notify = (isset($_POST['thread_notify']) ? (bool) $_POST['thread_notify'] : false);
        $thread_id = null;
        
        if (!empty($_POST['thread_subcategory'])) {
        	$cat = $_POST['thread_subcategory'];
        }
        else if (!empty($_POST['thread_category'])) {
        	$cat = $_POST['thread_category'];
        } else {
        	$cat = null;
        }
        
        $author_id = CMA::getPostingUserId();
        $error = false;
        $messages = array();
        $data = array(
            'title'     => $title,
            'content'   => $content,
            'notify'    => $notify,
            'author_id' => $author_id,
            'category'  => $cat,
            'tags'      => $tags,
        	'userRelatedQuestions' => (isset($_POST['cma_related_questions']) ? $_POST['cma_related_questions'] : array()),
        );
        try
        {
        	if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_question')) {
				throw new Exception(CMA::__('Invalid nonce.'));
			}
			if (!CMA_Thread::canPostQuestions()) {
				throw new Exception(CMA::__('You cannot post questions.'));
			}
            $thread = CMA_Thread::newThread($data);
            $thread_id = $thread->getId();
        }
        catch(Exception $e)
        {
            $messages = @unserialize($e->getMessage());
            if (!is_array($messages)) $messages = array($e->getMessage());
            $error = true;
        }
        if( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' )
        {
        	if (!$error) {
        		$heldForModeration = $thread->wasHeldForModeration();
                if( !$heldForModeration ) {
                    $messages[] = CMA_Labels::getLocalized('msg_post_question_success');
                } else {
                    $messages[] = CMA_Labels::getLocalized('msg_post_question_moderation');
                }
        	}
            header('Content-type: application/json');
            echo json_encode(array('success'   => (int) (!$error), 'thread_id' => $thread_id,
                'messages'   => $messages));
            exit;
        }
        else
        {
            if( $error )
            {
                foreach((array) $messages as $message)
                {
                    self::addMessage(self::MESSAGE_ERROR, $message);
                }
                self::_populate($_POST);
                if( !empty($_POST['cma-referrer']) ) wp_redirect($_POST['cma-referrer'], 303);
                else wp_redirect(get_post_type_archive_link(CMA_Thread::POST_TYPE), 303);
            }
            else
            {
                $heldForModeration = $thread->wasHeldForModeration();
                if( !$heldForModeration ) {
                    $msg = CMA_Labels::getLocalized('msg_post_question_success');
                } else {
                    $msg = CMA_Labels::getLocalized('msg_post_question_moderation');
                }
                self::addMessage(self::MESSAGE_SUCCESS, apply_filters('cma_question_post_msg_success', $msg));
                
                if (!empty($_POST['cma-redirect'])) {
                	if ($_POST['cma-redirect'] == '_thread') {
                		if ($heldForModeration) {
                			wp_redirect(get_post_type_archive_link(CMA_Thread::POST_TYPE), 303);
                		} else {
                			wp_redirect(get_permalink($thread_id), 303);
                		}
                	} else {
                		wp_redirect($_POST['cma-redirect'], 303);
                	}
                }
                else if( !empty($_POST['cma-referrer']) ) wp_redirect($_POST['cma-referrer'], 303);
                else wp_redirect(get_post_type_archive_link(CMA_Thread::POST_TYPE), 303);
            }

            exit;
        }
    }
    
    
    
    protected static function _processCountView() {
    	if (isset(self::$query->post) AND $thread = CMA_Thread::getInstance(self::$query->post->ID)) {
    		$thread->countView();
    	}
    	exit;
    }
    
    
    protected static function _processResolve() {
    	
    	$error = false;
    	$messages = array();
    	
    	$post = self::$query->post;
    	$thread = CMA_Thread::getInstance($post->ID);
    	
    	try {
    		
    		if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_resolve')) {
				throw new Exception(CMA::__('Invalid nonce.'));
			}
    		
    		$thread->resolve();
    		$msg = CMA_Labels::getLocalized('thread_resolved_success');
    		self::addMessage(self::MESSAGE_SUCCESS, apply_filters('cma_question_resolved_msg_success', $msg, $thread));
    		wp_safe_redirect(CMA::getReferer());
    		exit;
    	} catch (Exception $e) {
    		self::addMessage(self::MESSAGE_ERROR, $e);
    		$error = true;
    	}
    }
    
    
    protected static function _processFollow() {
    	
    	$result = array('success' => 0, 'message' => CMA::__('An error occurred.'));
    	
    	if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_follow')) {
    		$result['message'] = CMA::__('Invalid nonce.');
    		
    	} // Follow thread
    	else if (!empty(self::$query->post) AND $thread = CMA_Thread::getInstance(self::$query->post->ID) AND empty($_POST['categoryId'])) {
    		$followersEngine = $thread->getFollowersEngine();
    		if ($thread->canSubscribe()) {
    			if ($followersEngine->isFollower()) {
    				$followersEngine->removeFollower();
    				$result = array('success' => 1, 'message' => CMA_Labels::getLocalized('unfollow_success'), 'isFollower' => false);
    			} else {
    				$followersEngine->addFollower();
    				$result = array('success' => 1, 'message' => CMA_Labels::getLocalized('follow_success'), 'isFollower' => true);
    			}
    		}
    		
    	} // Follow category
    	else if (CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_CATEGORY_FOLLOWING)
    			AND !empty($_POST['categoryId']) AND $category = CMA_Category::getInstance($_POST['categoryId'])) {
    		$followersEngine = $category->getFollowersEngine();
    		if (CMA_FollowersEngine::canBeFollower()) {
    			if ($followersEngine->isFollower()) {
    				$followersEngine->removeFollower();
    				$result = array('success' => 1, 'message' => CMA_Labels::getLocalized('unfollow_category_success'), 'isFollower' => false);
    			} else {
    				$followersEngine->addFollower();
    				$result = array('success' => 1, 'message' => CMA_Labels::getLocalized('follow_category_success'), 'isFollower' => true);
    			}
    		}
    	}
    	
    	header('Content-type: application/json');
    	echo json_encode($result);
    	exit;
    	 
    }
    
    
    
    protected static function _processLoadSubcategories() {
    	$categoryId = self::_getParam('cma-category-id');
    	$result = array();
    	if (!CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_POST_ONLY_SUBCATEGORIES)) {
    		$result[] = array('id' => 0, 'name' => CMA_Labels::getLocalized('all_subcategories'), 'url' => '');
    	}
    	if (!empty($categoryId)) {
    		$categories = CMA_Category::getSubcategories($categoryId);
    		foreach ($categories as $category_id => $name) {
    			$result[] = array(
    				'id' => $category_id,
    				'name' => $name,
    				'url' => get_term_link($category_id, CMA_Category::TAXONOMY)
    			);
    		}
    	}
    	
    	header('Content-type: application/json');
    	echo json_encode($result);
    	exit;
    	
    }
    
    
    public static function processVote() {
    	$response = array(
    		'success' => 0,
    		'message' => CMA::__('There was an error while processing your vote.')
    	);
    	
    	if( !is_user_logged_in() AND !CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_GUESTS_VOTING)) {
			$response['message'] = CMA::__('You have to be logged-in.');
		}
        else if (!empty($_POST['threadId']) AND $thread = CMA_Thread::getInstance($_POST['threadId'])) {
        	if (!empty($_POST['answerId']) AND $answer = CMA_Answer::getById($_POST['answerId'])) {
        		
        		// Voting for answer
        		if( $answer->getThreadId() == $_POST['threadId'] AND $answer->isVotingAllowed(CMA::getPostingUserId()) ) {
	        		if (!empty($_POST['value']) AND $_POST['value'] == 'up') $answer->voteUp();
	        		else $answer->voteDown();
	        		$response['rating'] = $answer->getRating();
	        		$response['message'] = CMA::__('Thank you for voting!');
	        		$response['success'] = 1;
        		} else {
        			$response['message'] = CMA::__('You cannot vote.');
        		}
        		
        	} else {
        		
        		// Voting for thread
        		if( $thread->isVotingAllowed(CMA::getPostingUserId()) ) {
        			if (!empty($_POST['value']) AND $_POST['value'] == 'up') $thread->voteUp();
	        		else $thread->voteDown();
	        		$response['rating'] = $thread->getPostRating();
	        		$response['message'] = CMA::__('Thank you for voting!');
	        		$response['success'] = 1;
        		} else {
        			$response['message'] = CMA::__('You cannot vote.');
        		}
        		
        	}
    	}
    	
    	header('content-type: application/json');
    	echo json_encode($response);
    	exit;
    	
    }
    
    
    public static function processDelete() {
    	
    	if (isset($_GET['backlink'])) {
    		$url = base64_decode($_GET['backlink']);
    	} else $url = null;
    	
    	if (self::$query->post AND !empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], 'cma_thread_delete')) {
    		if ($thread = CMA_Thread::getInstance(self::$query->post->ID)) {
    			
    			if (empty($url) AND $category = $thread->getCategory()) {
    				$url = $category->getPermalink();
    			}
    			
    			if ($thread->canDelete()) {
    				
    				$thread->delete();
    				self::addMessage(self::MESSAGE_SUCCESS, CMA::__('Thread has been deleted.'));
    				
    			} else {
    				self::addMessage(self::MESSAGE_ERROR, CMA::__('You cannot delete this thread.'));
    			}
    		} else {
    			self::addMessage(self::MESSAGE_ERROR, CMA::__('Thread not found.'));
    		}
    	} else {
    		self::addMessage(self::MESSAGE_ERROR, CMA::__('Invalid request.'));
    	}
    	
    	if (empty($url)) $url = CMA::permalink();
    	if (isset($_GET['widgetCacheId'])) {
    		$url = add_query_arg('widgetCacheId', urlencode($_GET['widgetCacheId']), $url);
    	}
    	wp_redirect($url);
    	exit;
    	
    }
    
    
    protected static function _processUpload() {
    	header('content-type: application/json');
    	require_once(ABSPATH . 'wp-admin/includes/image.php');
    	require_once(ABSPATH . 'wp-admin/includes/media.php');
    	$attachments = array();
    	if( CMA_Settings::areAttachmentsAllowed() AND !empty($_FILES['cma-file'])) {
    		$maxFileSize = CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_MAX_SIZE);
    		foreach ($_FILES['cma-file']['name'] as $i => $name) {
    			$attachment = array('name' => $name);
    			if ($_FILES['cma-file']['size'][$i] > $maxFileSize) {
    				$attachments[] = array_merge($attachment, array('status' => 'ERROR', 'msg' => CMA::__('File is too large.')));
    			}
    			else if (!CMA_Thread::checkAttachmentAllowed($name)) {
    				$msg = CMA::__('Filetype is not allowed. Allowed extensions:');
    				$msg .= ' '. implode(', ', CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_FILE_EXTENSIONS));
    				$attachments[] = array_merge($attachment, array('status' => 'ERROR', 'msg' => $msg));
    			} else {
	    			$newName = floor(microtime(true)*1000) . '_' . sanitize_file_name($name);
	    			$target = CMA_Attachment::getUploadPath() . $newName;
	    			if( move_uploaded_file($_FILES['cma-file']['tmp_name'][$i], $target) ) {
	    				$data = array(
	    					'guid'           => $target,
	    					'post_mime_type' => $_FILES['cma-file']['type'][$i],
	    					'post_title'     => $name,
	    					'post_content'   => '',
	    					'post_status'    => 'draft'
	    				);
	    				$attach_id = wp_insert_attachment($data, $target);
	    				$attach_data = wp_generate_attachment_metadata($attach_id, $target);
	    				wp_update_attachment_metadata($attach_id, $attach_data);
	    				$attachments[] = array_merge($attachment, array(
	    					'status' => 'OK',
	    					'id' => $attach_id,
	    				));
	    			} else {
	    				$attachments[] = array_merge($attachment, array('status' => 'ERROR'));
	    			}
    			}
    		}
    	}
    	echo json_encode($attachments);
    	exit;
    }
    
    
    protected static function _processReportSpam() {
    	if( self::$query->is_single() ) {
    		$post = self::$query->post;
    		if( !empty($post) ) {
    			
    			$response = array(
    				'success' => 0,
    				'message' => CMA::__('An error occurred.')
    			);
    			
	    		if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_report_spam')) {
		    		$response['message'] = CMA::__('Invalid nonce.');
		    		
		    	}
    			else if ( CMA_Settings::canReportSpam() ) {
    			
    				$thread = CMA_Thread::getInstance($post->ID);
    				
    				$answerId = self::_getParam('answerId');
    				
    				if ($userId = CMA::getPostingUserId()) {
    					$user = apply_filters('cma_filter_author', get_user_by('id', $userId), array('thread' => $thread));
    					$user = $user->display_name;
    				} else {
    					$user = CMA::__('Guest');
    				}
    				
    				
    				if ($answerId AND $answer = CMA_Answer::getById($answerId)) {
    					$answer->markAsSpam(true);
    					$url = $answer->getPermalink();
    					$author = $answer->getAuthorLink(true);
    					$content = CMA_Thread::lightContent($answer->getContent());
    					$datetime = $answer->getDate();
    					$trashLink = get_admin_url(null, sprintf('comment.php?c=%d&action=trashcomment', $answerId));
    					$spamLink = get_admin_url(null, sprintf('comment.php?c=%d&action=spamcomment', $answerId));
    				} else {
    					$thread->markAsSpam(true);
    					$url = get_permalink($post->ID);
    					$author = $thread->getAuthorLink(true);
    					$content = $thread->getLightContent();
    					$datetime = $post->post_date;
    					$trashLink = get_admin_url(null, sprintf('post.php?post=%d&action=trash', $post->ID));
    					$spamLink = '--';
    				}
    				
    				$replace = array(
    					'[blogname]' => get_bloginfo('name'),
    					'[url]' => $url,
    					'[title]' => strip_tags($thread->getTitle()),
    					'[author]' => strip_tags($author),
    					'[content]' => $content,
    					'[user]' => strip_tags($user),
    					'[datetime]' => $datetime,
    					'[trash]' => $trashLink,
    					'[spam]' => $spamLink,
    				);
    				$subject = strtr(CMA_Settings::getOption(CMA_Settings::OPTION_SPAM_REPORTING_EMAIL_SUBJECT), $replace);
    				$template = strtr(CMA_Settings::getOption(CMA_Settings::OPTION_SPAM_REPORTING_EMAIL_TEMPLATE), $replace);
    				
    				$emails = explode(',', CMA_Settings::getOption(CMA_Settings::OPTION_SPAM_REPORTING_EMAIL_ADDR));
    				
    				CMA_Email::send($emails, $subject, $template);
    				
    				/* $headers = array();
		            foreach($emails as $email) {
		            	$email = trim($email);
		            	if (is_email($email)) {
		            		$headers[] = ' Bcc: '. $email;
		            	}
		            }
		            
		            if (!empty($headers)) wp_mail(null, $subject, $template, $headers); */
    				
    				$response['success'] = 1;
    				$response['message'] = CMA_Labels::getLocalized('spam_report_sent');
    			
    			}
    			 
    			header('Content-type: application/json');
    			echo json_encode($response);
    			exit;
    			
    		}
    	}
    }
    
    
    protected static function _processUnmarkSpam() {
    	if( self::$query->is_single() ) {
    		$post = self::$query->post;
    		if( !empty($post) ) {
    			
    			$response = array(
    				'success' => 0,
    				'message' => CMA::__('An error occurred.')
    			);
    			
    			$thread = CMA_Thread::getInstance($post->ID);
    			
    			if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_report_spam')) {
		    		$response['message'] = CMA::__('Invalid nonce.');
    			}
    			else if ($answerId = self::_getParam('answerId') AND $answer = CMA_Answer::getById($answerId) AND $answer->canUnmarkSpam()) {
    				$answer->markAsSpam(0);
    				$response['success'] = 1;
    				$response['message'] = CMA::__('Content has been unmarked as a spam.');
    			}
    			else if ($thread AND $thread->canUnmarkSpam()) {
    				$thread->markAsSpam(0);
    				$response['success'] = 1;
    				$response['message'] = CMA::__('Content has been unmarked as a spam.');
    			}
    			
    			header('Content-type: application/json');
    			echo json_encode($response);
    			exit;
    			
    		}
    	}
    }
    
    
    
    protected static function _processMarkBestAnswer()
    {
    	 
    	if( self::$query->is_single() )
    	{
    		$post = self::$query->post;
    		if( !empty($post) )
    		{
    			
    			$response = array(
    				'success' => 0,
    				'message' => CMA::__('An error occurred.')
    			);
    			
    			if( !is_user_logged_in() ) {
    				$response['message'] = CMA::__('You have to be logged-in.');
    			}
    			else if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_best_answer')) {
					$response['message'] = CMA::__('Invalid nonce.');
    			} else {
    
    				$thread = CMA_Thread::getInstance($post->ID);
    				$answerId = self::_getParam('cma-answer-id');
    				
    				if ($answer = CMA_Answer::getById($answerId)) {
    					if ($thread->getBestAnswerId() == $answerId) {
    						if( $thread->canUnmarkBestAnswer() ) {
	    						$thread->unmarkBestAnswer();
	    						$response['message'] = CMA::__('The best answer has been unmarked.');
	    						$response['marked'] = 0;
	    						$response['success'] = 1;
    						}
    					} else {
    						if( $answer->canMarkBestAnswer() ) {
			    				$thread->setBestAnswer($answerId);
			    				$removeOther = ($_POST['remove-other'] === true || $_POST['remove-other'] === 'true');
			    				if ($removeOther AND CMA_Settings::getOption(CMA_Settings::OPTION_BEST_ANSWER_REMOVE_OTHER)) {
			    					$thread->removeNotBestAnswers();
			    				}
			    				$response['message'] = apply_filters('cma_question_mark_best_answer_msg_success', CMA::__('The best answer has been marked.'), $thread);
			    				$response['marked'] = 1;
			    				$response['success'] = 1;
    						}
    					}
    				} else {
    					$response['message'] = CMA::__('Answer not found.');
    				}
    				
    			}
    			
    			header('Content-type: application/json');
    			echo json_encode($response);
    			exit;
    			
    		}
    	}
    
    }
    
    
    protected static function _processFavorite()
    {
    
    	if( self::$query->is_single() )
    	{
    		$post = self::$query->post;
    		if( !empty($post) )
    		{
    			 
    			$response = array(
    					'success' => 0,
    					'message' => CMA::__('An error occurred.')
    			);
    
    			if( !is_user_logged_in() ) {
    				$response['message'] = CMA::__('You have to be logged-in.');
    			}
    			else if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_favorite_question')) {
					$response['message'] = CMA::__('Invalid nonce.');
    			} else {
    				
    				$thread = CMA_Thread::getInstance($post->ID);
    				
    				if( $thread->canMarkFavorite() ) {
    					$response['number'] = count($thread->getUsersFavorite());
    					$initialState = $thread->isFavorite();
    					$thread->setFavorite(!$initialState);
    					$response['success'] = 1;
    					if (!$initialState) {
    						$response['message'] = CMA::__('Marked as favorite.');
    						$response['title'] = CMA::__('Unmark as favorite');
    						$response['number']++;
    					} else {
    						$response['message'] = CMA::__('Unmarked as favorite.');
    						$response['title'] = CMA::__('Mark as favorite');
    						$response['number']--;
    					}
    					$response['favorite'] = !$initialState;
    				}
    
    			}
    			 
    			header('Content-type: application/json');
    			echo json_encode($response);
    			exit;
    			 
    		}
    	}
    
    }
    
    
    
    public static function getIndexTitle() {
    	ob_start();
    	$foundPosts = self::$query->found_posts;
    	if( self::$query->is_tax(CMA_Category::TAXONOMY) ) {
    		single_term_title();
    	} else {
    		post_type_archive_title();
    		if (isset($_GET['cmatag'])) {
    			$tagTerm = get_term_by('slug', $_GET['cmatag'], 'post_tag');
    			if (!empty($tagTerm)) {
    				echo ' for tag "' . esc_html($tagTerm->name) . '"';
    			}
    		}
    	}
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_TITLE_FOUND_POSTS)) {
    		echo ' (' . $foundPosts . ')';
    	}
    	return ob_get_clean();
    }
    

    public static function processQueryVars()
    {
    	
        $action = self::_getParam('cma-action');
        if( !empty($action) )
        {
            switch($action)
            {
                case 'add':
                    if( is_single() ) self::_processAddAnswerToThread();
                    else self::_processAddThread();
                    break;
                case 'upload':
                    self::_processUpload();
                    break;
                case 'report-spam':
                    self::_processReportSpam();
                    break;
                case 'follow':
                    self::_processFollow();
                    break;
                case 'unmark-spam':
                    self::_processUnmarkSpam();
                    break;
                case 'mark-best-answer':
                 	self::_processMarkBestAnswer();
                  	break;
                case 'tags-autocomplete':
                	self::_processTagsAutocomplete();
                	break;
                case 'favorite':
                	self::_processFavorite();
                	break;
                case 'load-subcategories':
                    self::_processLoadSubcategories();
                    break;
                case 'display-private-question-form':
                    self::displayPrivateQuestionForm();
                    break;
                case 'private-question-send':
                    self::_processPrivateQuestionSend();
                    break;
                case 'widget':
                	self::_displayWidget();
                	break;
                case 'resolve':
                	self::_processResolve();
                	break;
                case 'delete':
                	self::processDelete();
                	break;
                case 'count-view':
                	self::_processCountView();
                	break;
                case 'edit':
                	if (!empty($_POST[self::PARAM_EDIT_ANSWER_ID])) {
                		self::_processEditAnswer($_POST[self::PARAM_EDIT_ANSWER_ID]);
                	} else {
                   		self::_processEditQuestion();
                	}
                   	break;
            }
        }
    }
    
    
    protected static function _displayWidget() {
    	if ($widgetOptions = self::restoreWidgetOptions()) {
    		echo CMA_Shortcodes::general_shortcode($widgetOptions, true);
    		exit;
    	}
    }
    

    public static function adjustBodyClass($wp_classes, $extra_classes)
    {
        foreach($wp_classes as $key => $value)
        {
            if( $value == 'singular' ) unset($wp_classes[$key]);
        }

        if( in_array('cma_thread', $wp_classes) && (!CMA_Thread::isSidebarEnabled() || !is_active_sidebar('cm-answers-sidebar') ) )
        {
//            $extra_classes[] = 'full-width';
        }
        return array_merge($wp_classes, (array) $extra_classes);
    }

    public static function registerAdminColumns($columns)
    {
    	$updatedSortUrl = add_query_arg(array('orderby' => 'updated', 'order' => ((isset($_GET['order']) AND $_GET['order'] == 'asc') ? 'desc' : 'asc')), $_SERVER['REQUEST_URI']);
    	$columns['modified'] = 'Updated';
        $columns['author'] = CMA_Settings::__('Author');
        $columns['views'] = CMA_Labels::getLocalized('views_col');
        $columns['status'] = CMA_Labels::getLocalized('status_col');
        $columns['comments'] = CMA_Labels::getLocalized('Answers');
        return $columns;
    }
    
    
    public static function registerAdminSortableColumns($columns) {
    	$columns['modified'] = 'modified';
    	return $columns;
    }
    
    

    public static function adminColumnDisplay($columnName, $id)
    {
        $thread = CMA_Thread::getInstance($id);
        if( !$thread ) return;
        switch($columnName)
        {
            case 'author':
                echo $thread->getAuthor()->display_name;
                break;
            case 'views':
                echo $thread->getViews();
                break;
            case 'modified':
            	if ($comment = $thread->getLastComment()) {
            		echo $comment->getDate();
            	} else {
            		echo Date('Y-m-d H:i:s', $thread->getUnixUpdated());
            	}
            	
            	break;
            case 'status':
                echo $thread->getStatus();
                if( strtolower($thread->getStatus()) == strtolower(__('pending', 'cm-answers-pro')) )
                {
                    ?>
                    <a href="<?php
                    echo esc_attr(add_query_arg(array(
						'cma-action' => 'approve',
                        'cma-id'     => $id
					)));
                    ?>">(Approve)</a>
                    <?php
                }
                break;
        }
    }
    
    

    public static function addAdminSettings($params = array())
    {
    	
        $params['DisclaimerContent'] = CMA_Thread::getDisclaimerContent();
        $params['DisclaimerContentAccept'] = CMA_Thread::getDisclaimerContentAccept();
        $params['DisclaimerContentReject'] = CMA_Thread::getDisclaimerContentReject();
        $params['DisclaimerApproved'] = CMA_Thread::isDisclaimerEnabled();
        $params['sidebarBeforeWidget'] = CMA_Thread::getSidebarSettings('before_widget');
        $params['sidebarAfterWidget'] = CMA_Thread::getSidebarSettings('after_widget');
        $params['sidebarBeforeTitle'] = CMA_Thread::getSidebarSettings('before_title');
        $params['sidebarAfterTitle'] = CMA_Thread::getSidebarSettings('after_title');
        
        $params['sidebarEnable'] = CMA_Thread::isSidebarEnabled();
        $params['sidebarContributorEnable'] = CMA_Thread::isSidebarContributorEnabled();
        $params['sidebarMaxWidth'] = CMA_Thread::getSidebarMaxWidth();

        $params['referralEnable'] = CMA_Thread::isReferralEnabled();
        $params['affiliateCode'] = CMA_Thread::getAffiliateCode();

        $params['customCSS'] = CMA_Thread::getCustomCss();

        $params['spamFilter'] = CMA_Thread::getSpamFilter();

        return $params;
    }
    


    public static function showPagination($arguments = array(), $base = null)
    {
        
        if (empty($base)) $base = get_post_type_archive_link(CMA_Thread::POST_TYPE);
        if (strpos($base, '?') !== false) {
			$base = str_replace('?', 'page/%#%/?', $base);
		} else {
			$base .= 'page/%#%/';
		}

        $params = array(
            'maxNumPages' => isset($arguments['maxNumPages']) ? $arguments['maxNumPages'] : self::$query->max_num_pages,
            'paged'       => isset($arguments['paged']) ? $arguments['paged'] : self::$query->get('paged'),
			'base' => $base,
        );
		unset($arguments['maxNumPages']);
		$params['add_args'] = array_filter($arguments);

// 		var_dump($params);
        $pagination = CMA_BaseController::_loadView('answer/widget/pagination', $params);
        return $pagination;
    }
    
    
    public static function displayQuestionFormUpload() {
		if (CMA_Thread::areQuestionAttachmentsAllowed()) {
			echo self::_loadView('answer/meta/form-upload');
		}
	}
	
	
	public static function displayAnswerFormUpload() {
		if (CMA_Answer::areAnswerAttachmentsAllowed()) {
			echo self::_loadView('answer/meta/form-upload');
		}
	}
    
    
    public static function writeCategoriesTableBody($categories, $parentCategoryId = 0, $depth = 0) {
		if (!empty($categories[$parentCategoryId]) AND is_array($categories[$parentCategoryId])) {
			foreach ($categories[$parentCategoryId] as $category) {
				$categoryId = $category->term_id;
				echo self::_loadView('answer/meta/categories-row', compact('category', 'depth'));
				if (!empty($categories[$categoryId])) {
					self::writeCategoriesTableBody($categories, $categoryId, $depth+1);
				}
			}
		}
	}
	
	
	public static function indexHeaderAfter() {
		$content = CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_HEADER_AFTER_TEXT);
		if (strlen($content)) {
			echo self::_loadView('answer/meta/index_header_after', compact('content'));
		}
	}
	
	
	
	public static function displayFormTags($tags) {
		if (is_array($tags)) $tags = implode(',', $tags);
		echo self::_loadView('answer/meta/form-tags', compact('tags'));
	}
	
	
	protected static function _processTagsAutocomplete() {
		$result = array();
		$search = trim(self::_getParam('cma-tag'));
		if (strlen($search)) {
			$tags = get_tags(array(
				'orderby' => 'count',
				'order' => 'DESC',
				'number' => 10,
				'search' => $search,
			));
		}
		header('content-type: application/json');
		echo json_encode($result);
		exit;
	}
	
	
	
	public static function displayPrivateQuestionForm() {
		if ($userId = self::_getParam('user') AND $user = get_userdata($userId)) {
			echo self::_loadView('answer/meta/private-question-form', compact('user'));
		}
		exit;
	}
	
	
	protected static function _processPrivateQuestionSend() {
		header('content-type: application/json');
		try {
			$nonce = self::_getParam('nonce');
			if (!CMA_Settings::getOption(CMA_Settings::OPTION_PRIVATE_QUESTIONS_ENABLED)) {
				throw new Exception(serialize(array('global' => CMA::__('Private questions are disabled.'))));
			}
			else if (empty($nonce) OR !wp_verify_nonce($nonce, 'private_question')) {
				throw new Exception(serialize(array('global' => CMA::__('Invalid nonce.'))));
			}
			else if (CMA_PrivateQuestion::send(get_current_user_id(), self::_getParam('user'), self::_getParam('title'), self::_getParam('question'))) {
				echo json_encode(array('success' => 1, 'msg' => CMA_Labels::getLocalized('private_question_sent_success')));
			} else {
				throw new Exception(serialize(array('email' => CMA::__('Cannot send email. Please try again.'))));
			}
		} catch (Exception $e) {
			echo json_encode(array('success' => 0, 'msg' => CMA::__('An error occured.'), 'errors' => unserialize($e->getMessage())));
		}
		exit;
	}
	
	
	public static function displayBreadcrumbs($threadId = null, $basedOnSettings = true) {

		if ($basedOnSettings AND !CMA_Settings::getOption(CMA_Settings::OPTION_BREADCRUMBS_ENABLED)) {
			return;
		}

		global $post;
		$queriedObject = self::$query->get_queried_object();
		
		$indexLink = sprintf('<a href="%s">%s</a>',
			esc_attr(get_post_type_archive_link(CMA_Thread::POST_TYPE)),
			esc_html(CMA_Labels::getLocalized('index_page_title'))
		);
		$categoryLink = null;
		$paretnCategoryLink = null;
		$threadLink = null;
		if (empty($threadId) AND self::$query->is_single() AND !empty($post) AND $post->post_type = CMA_Thread::POST_TYPE) {
			$threadId = $post->ID;
		}
		
		if (!empty($threadId) AND $thread = CMA_Thread::getInstance($threadId)) {
			if ($category = $thread->getCategory()) {
				$categoryLink = $category->getLink();
				if ($parentCategory = $category->getParentInstance()) {
					$parentCategoryLink = $parentCategory->getLink();
				}
			}
			$threadLink = sprintf('<a href="%s">%s</a>',
				esc_attr(!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $thread->getPermalink()),
				esc_html($thread->getTitle())
			);
		} else {
			if (isset($queriedObject->term_id) AND $category = get_term($queriedObject->term_id, CMA_Category::TAXONOMY)) {
				$categoryLink = sprintf('<a href="%s">%s</a>',
					esc_attr(get_term_link($category->term_id, CMA_Category::TAXONOMY)),
					esc_html($category->name)
				);
				if (!empty($category->parent) AND $parent = get_term($category->parent, CMA_Category::TAXONOMY)) {
					$parentCategoryLink = sprintf('<a href="%s">%s</a>',
						esc_attr(get_term_link($parent->term_id, CMA_Category::TAXONOMY)),
						esc_html($parent->name)
					);
				}
			}
		}
		if (!empty($categoryLink) OR !empty($threadLink)) {
			if (empty($threadLink)) $categoryLink = '<span>'. strip_tags($categoryLink) .'</span>';
			else $threadLink = '<span>'. strip_tags($threadLink) .'</span>';
			echo self::_loadView('answer/nav/breadcrumbs', compact('indexLink', 'categoryLink', 'parentCategoryLink', 'threadLink'));
		}
	}
	
	
	
	public static function getCurrentCategory() {
		$queriedObject = self::$query->get_queried_object();
		if ($queriedObject AND isset($queriedObject->term_id) AND $category = get_term($queriedObject->term_id, CMA_Category::TAXONOMY)) {
			return $category;
		}
	}
	
	
	public static function answersTitle() {
		if ($authorSlug = self::_getParam('author')) {
			if ($user = get_user_by('slug', $authorSlug)) {
				return esc_html($user->display_name) . ' - '. CMA_Labels::getLocalized('Answers');
			}
		}
	}
	
	public static function answersHeader() {
		add_filter('cma_edit_query_post', array(__CLASS__, 'answers_edit_query_post'));
		set_query_var('cma_answer_answers', '1');
	}
	
	
	public static function answers_edit_query_post($post) {
		if ($author = self::_getParam('author')) {
			$post->post_name = trailingslashit($post->post_name) . 'author/' . urlencode($author);
		}
		return $post;
	}

	
	public static function answersAction() {
		
		$limit = intval(self::_getParam('limit'));
		if (empty($limit)) $limit = 5;
		$currentPage = max(1, intval(self::_getParam('page')));
		$totalPages = 1;
		$answers = array();
		$authorSlug = '';
		$ajax = false;
		
		if ($authorSlug = self::_getParam('author')) {
			if ($user = get_user_by('slug', $authorSlug)) {
				$authorSlug = $user->user_nicename;
				$answers = CMA_Answer::getByUser($user->ID, $approved = true, $limit, $currentPage, $onlyVisible = true);
				$totalPages = ceil(CMA_Answer::countForUser($user->ID, $approved = true, $limit, $currentPage, $onlyVisible = true)/$limit);
			}
		}
		$public = false;
		return array(
			'content' => self::_loadView('answer/widget/answers-list', compact('answers', 'currentPage', 'totalPages', 'authorSlug', 'limit', 'ajax', 'public'))
		);
	}
	
	
	public static function addedTermRelationship($object_id, $tt_id) {
		global $wpdb;
		// If CMA category associated with CMA Thread, remove other categories
		if (CMA_Category::TAXONOMY == $wpdb->get_var($wpdb->prepare("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %d", $tt_id))) {
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id <> %d", $object_id, $tt_id));
		}
	}
	
	
	static function processTagAutocomplete() {

		if ( ! isset( $_GET['tax'] ) ) {
			wp_die( 0 );
		}
		
		$taxonomy = sanitize_key( $_GET['tax'] );
		$tax = get_taxonomy( $taxonomy );
		if ( ! $tax ) {
			wp_die( 0 );
		}

		$s = wp_unslash( $_GET['q'] );

		$comma = _x( ',', 'tag delimiter' );
		if ( ',' !== $comma )
			$s = str_replace( $comma, ',', $s );
		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[count( $s ) - 1];
		}
		$s = trim( $s );
		
		$term_search_min_chars = (int) apply_filters( 'term_search_min_chars', 2, $tax, $s );

		/*
		 * Require $term_search_min_chars chars for matching (default: 2)
		* ensure it's a non-negative, non-zero integer.
		*/
		if ( ( $term_search_min_chars == 0 ) || ( strlen( $s ) < $term_search_min_chars ) ){
			wp_die();
		}
		
		$results = get_terms( $taxonomy, array( 'name__like' => $s, 'fields' => 'names', 'hide_empty' => false ) );
		
		echo join( $results, "\n" );
		wp_die();
	}
	
	
	static function registerEmailContentType($headers) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_EMAIL_USE_HTML)) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}
		return $headers;
	}
	
	
	static function filterEmailBody($body) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_EMAIL_USE_HTML) AND CMA_Settings::getOption(CMA_Settings::OPTION_EMAIL_HTML_NL2BR)) {
			$body = nl2br($body);
		}
		return $body;
	}
	
	
	
	static function showQuestionSnippet($thread, $displayOptions) {
		echo self::_loadView('answer/meta/question-snippet', compact('thread', 'displayOptions'));
	}
	
	
	
	static function bestAnswerNotification(CMA_Thread $thread) {
		global $wpdb;
		$answer = $thread->getBestAnswer();
		$receivers = array();
		$receiversOption = CMA_Settings::getOption(CMA_Settings::OPTION_NOTIF_BEST_ANSWER_RECEIVERS);
		if (in_array(CMA_Settings::NOTIF_QUESTION_AUTHOR, $receiversOption)) {
			$receivers[] = $thread->getAuthorEmail();
		}
		if (in_array(CMA_Settings::NOTIF_ANSWER_AUTHOR, $receiversOption)) {
			$receivers[] = $answer->getAuthorEmail();
		}
		if (in_array(CMA_Settings::NOTIF_FOLLOWERS, $receiversOption)) {
			$receivers = array_merge($receivers, $thread->getFollowersEmails());
		}
		if (in_array(CMA_Settings::NOTIF_CONTRIBUTORS, $receiversOption)) {
			$receivers = array_merge($receivers, $thread->getContributorsEmails());
			$receivers[] = $thread->getAuthorEmail();
		}
		$receivers = array_filter(array_unique($receivers));
		if( !empty($receivers) ) {
			$message = CMA_Settings::getOption(CMA_Settings::OPTION_NOTIF_BEST_ANSWER_CONTENT);
			$title = CMA_Settings::getOption(CMA_Settings::OPTION_NOTIF_BEST_ANSWER_TITLE);
			$replace = array(
				'[blogname]' => get_bloginfo('name'),
				'[question_title]' => strip_tags($thread->getTitle()),
				'[question_body]' => strip_tags($thread->getContent()),
				'[question_author]' => strip_tags($thread->getAuthorName()),
				'[answer]' => strip_tags($answer->getContent()),
				'[answer_link]' => $answer->getPermalink(),
				'[answer_author]' => strip_tags($answer->getAuthorName()),
			);
			
			CMA_Email::send($receivers, $title, $message, $replace);
			
		}
	}
	
	

}
