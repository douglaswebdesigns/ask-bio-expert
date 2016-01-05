<?php

class CMA_Thread extends CMA_PostType {
	
    /**
     * Post type name
     */
    const POST_TYPE = 'cma_thread';
    const ADMIN_MENU = 'CMA_answers_menu';
    
    const SORT_BY_NEWEST = 'newest';
    const SORT_BY_HOTTEST = 'hottest';
    const SORT_BY_VOTES = 'votes';
    const SORT_BY_VIEWS = 'views';
    
    const OPTION_DISCLAIMER_APPROVE = 'cma_disclaimer_approve';
    const OPTION_NEGATIVE_RATING_ALLOWED = 'cma_negative_rating_allowed';
    const OPTION_ENABLE_CATEGORY_FOLLOWING = 'cma_enable_category_following';
    /*
     * Access options
     */
    const OPTION_VIEW_ACCESS = 'cma_access_view';
    const DEFAULT_VIEW_ACCESS = '0';
    const OPTION_VIEW_ACCESS_ROLES = 'cma_access_view_roles';
    const DEFAULT_VIEW_ACCESS_ROLES = null;
    const DEFAULT_POST_QUESTIONS_ACCESS = '1';
    const OPTION_POST_QUESTIONS_ACCESS = 'cma_access_post_questions';
    const OPTION_POST_QUESTIONS_ACCESS_ROLES = 'cma_access_post_questions_roles';
    const DEFAULT_POST_QUESTIONS_ACCESS_ROLES = null;
    const DEFAULT_POST_ANSWERS_ACCESS = '1';
    const OPTION_POST_ANSWERS_ACCESS = 'cma_access_post_answers';
    const OPTION_POST_ANSWERS_ACCESS_ROLES = 'cma_access_post_answers_roles';
    const DEFAULT_POST_ANSWERS_ACCESS_ROLES = null;

    /*
     * Access options - end
     */
    const OPTION_TAGS_WIDGET_LIMIT = 'cma_tags_widget_limit';
    
    const OPTION_SIDEBAR_ENABLED = 'cma_sidebar_enabled';
    const OPTION_SIDEBAR_MAX_WIDTH = 'cma_sidebar_max_width';
    const OPTION_SIDEBAR_CONTRIBUTOR_ENABLED = 'cma_sidebar_contributor_enabled';
    const OPTION_SIDEBAR_SETTINGS = 'cma_sidebar_settings';
    const OPTION_VOTES_NO = 'cma_votes_no';
    const OPTION_MARKUP_BOX = 'cma_markup_box';
    const OPTION_AFFILIATE_CODE = 'cma_affiliate_code';
    const OPTION_REFERRAL_ENABLED = 'cma_referral_enabled';
    
    const OPTION_CUSTOM_CSS = 'cma_custom_css';
    const OPTION_DISCLAIMER_CONTENT = 'cma_disclaimer_content';
    const DEFAULT_DISCLAIMER_CONTENT = 'Place here your disclaimer text';
    const OPTION_DISCLAIMER_CONTENT_ACCEPT = 'cma_disclaimer_content_accept';
    const OPTION_DISCLAIMER_CONTENT_REJECT = 'cma_disclaimer_content_reject';
    const DEFAULT_DISCLAIMER_CONTENT_ACCEPT = 'Accept Terms';
    const DEFAULT_DISCLAIMER_CONTENT_REJECT = 'Reject Terms';
    const OPTION_SPAM_FILTER = 'cma_spam_filter';
    const YES = 1;
    const NO = 0;
    const VOTES_NO = 0;
    const DEFAULT_USER_COMMENT_ONLY = 0;
    const DEFAULT_USER_LOGGED_ONLY = 0;
    const DEFAULT_TAGS_SWITCH = 0;
    const FOLLOWERS_USER_META_PREFIX = 'cma_follower_thread';
	const COOKIE_ANONYMOUS_UID = 'cma_anon_uid';
	
	const USERMETA_COUNTER_QUESTIONS = '_cm_answers_questions';
	const USERMETA_COUNTER_ANSWERS = '_cm_answers_answers';
    

    /**
     * @var CMA_Thread[] singletones cache
     */
    protected static $instances = array();
    /**
     * @var array meta keys mapping
     */
    public static $_meta = array(
        'views'                  => '_views',
        'listeners'              => '_listeners',
        'resolved'               => '_resolved',
        'highestRatedAnswer'     => '_highest_rated_answer',
        'votes_answers'          => '_votes',
    	'votes_question'         => '_votes_question',
    	'votes_question_answers' => '_votes_question_answers',
        'stickyPost'             => '_sticky_post',
    	'rating'                 => '_rating',
    	'usersRated'             => '_users_rated',
    	'bestAnswer'             => '_best_answer_id',
    	'usersFavorite'         => '_users_favorite',
    	'authorIP'				=> '_author_ip',
		'authorCountryCode'		=> '_author_country_code',
    	'authorCountryName'		=> '_author_country_name',
    	'markedAsSpam'			=> '_marked_as_spam',
    	'attachment'			=> '_attachment',
    	'userRatingPositive'    => 'cma_user_rating_posivite',
    	'userRatingNegative'    => 'cma_user_rating_negative',
    	'userRatingHandicap'    => 'cma_user_rating_handicap',
    	'votesHandicap'         => 'cma_votes_handicap',
    	'voteIp'                => '_cma_vote_ip',
    	'voteUA'                => '_cma_vote_ua',
    	'voteTime'              => '_cma_vote_time',
    	'voteCookie'            => '_cma_vote_cookie',
    	'userRelatedQuestion'   => '_cma_user_related_question',
    	'categoryCustomField'	=> '_cma_category_custom_field',
    );
    

    /**
     * Initialize model
     */
    public static function init()
    {

        $post_type_args = array(
            'has_archive'  => TRUE,
//            'menu_position' => 4,
            'show_in_menu' => self::ADMIN_MENU,
            'rewrite'      => array(
                'slug'       => CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_PERMALINK),
                'with_front' => FALSE
            ),
            'supports'     => array('title', 'editor', 'author'),
            'hierarchical' => FALSE,
        	'taxonomies' => array('post_tag', CMA_Category::TAXONOMY),
        );
        
        $plural = CMA_Labels::getLocalized('index_page_title');
		if (empty($plural)) $plural = CMA_Labels::getLocalized('Questions');
        self::registerPostType(self::POST_TYPE, 'Question', $plural, $plural, $post_type_args);

        
        add_filter('CMA_admin_parent_menu', create_function('$q', 'return "' . self::ADMIN_MENU . '";'));
        add_action('admin_menu', array(get_class(), 'registerAdminMenu'));

        $taxonomy_args = array(
            'rewrite' => array(
                'slug'         => CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_PERMALINK) . '/' . CMA_Settings::getOption(CMA_Settings::OPTION_CATEGORIES_URL_PART),
                'with_front'   => TRUE,
                'show_ui'      => TRUE,
                'hierarchical' => false,
            ),
        );
        self::registerTaxonomy(CMA_Category::TAXONOMY, array(self::POST_TYPE), 'CMA Category', 'CMA Categories', $taxonomy_args);
        add_action('generate_rewrite_rules', array(get_class(), 'fixCategorySlugs'));
        require_once CMA_PATH . '/lib/helpers/Shortcodes.php';
        CMA_Shortcodes::init();
        
    }


    public static function fixCategorySlugs($wp_rewrite)
    {
    	$categoriesPart = CMA_Settings::getOption(CMA_Settings::OPTION_CATEGORIES_URL_PART);
    	$permalink = CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_PERMALINK);
        $wp_rewrite->rules = array(
            $permalink . '/'. $categoriesPart .'/([^/]+)/?$'                   => $wp_rewrite->index . '?post_type=' . self::POST_TYPE . '&' . CMA_Category::TAXONOMY . '=' . $wp_rewrite->preg_index(1),
            $permalink . '/'. $categoriesPart .'/([^/]+)/page/?([0-9]{1,})/?$' => $wp_rewrite->index . '?post_type=' . self::POST_TYPE . '&' . CMA_Category::TAXONOMY . '=' . $wp_rewrite->preg_index(1) . '&paged=' . $wp_rewrite->preg_index(2),
                ) + $wp_rewrite->rules;
    }

    /**
     * @static
     * @param int $id
     * @return CMA_Thread
     */
    public static function getInstance($id = 0)
    {
        if( !$id )
        {
            return NULL;
        }
        if (is_object($id)) $id = $id->ID;
        if( !isset(self::$instances[$id]) || !self::$instances[$id] instanceof self )
        {
        	$obj = new self($id);
        	if ($obj AND !empty($obj->post) AND $obj->post->post_type == self::POST_TYPE) {
            	self::$instances[$id] = $obj;
        	}
        }
        
        if (empty(self::$instances[$id])) return null;
        else return self::$instances[$id];
        
    }
    
    
    
    public function getFollowersEngine() {
    	return new CMA_FollowersEngine(self::FOLLOWERS_USER_META_PREFIX, $this->getId());
    }
    

    public static function registerAdminMenu()
    {
        $current_user = wp_get_current_user();

        if( user_can($current_user, 'manage_options') )
        {
            $page = add_menu_page('Questions', 'CM Answers Pro', 'edit_posts', self::ADMIN_MENU, create_function('$q', 'return;'));
            add_submenu_page(self::ADMIN_MENU, 'Answers & Comments', 'Answers  & Comments', 'edit_posts', 'edit-comments.php?post_type=' . self::POST_TYPE);
            add_submenu_page(self::ADMIN_MENU, 'Categories', 'Categories', 'manage_categories', 'edit-tags.php?taxonomy=' . CMA_Category::TAXONOMY . '&amp;post_type=' . self::POST_TYPE);
            if( isset($_GET['taxonomy']) && $_GET['taxonomy'] == CMA_Category::TAXONOMY && isset($_GET['post_type']) && $_GET['post_type'] == self::POST_TYPE )
            {
                add_filter('parent_file', create_function('$q', 'return "' . self::ADMIN_MENU . '";'), 999);
            }
//             add_submenu_page(self::ADMIN_MENU, 'Add new question', 'Add New', 'edit_posts', 'post-new.php?post_type=' . self::POST_TYPE);
        }
    }

    /**
     * Get content of answer
     * @return string
     */
    public function getContent($charscount = 0, $striptags = false)
    {
    	$content = $this->post->post_content;
//         $content = self::contentFilter($this->post->post_content, $this->getAuthorId());

        if( $striptags )
        {
            $content = strip_tags($content);
            $shortcodePattern = '#\[\w+(\s+\w+(=["\']?[^"\']+["\']?)?)*\](.+\[/\w+\])?#i';
            $content = preg_replace($shortcodePattern, '', $content); 
        }

        if( $charscount > 0 and strlen($content) > $charscount )
        {
            $content = substr($content, 0, $charscount) . "...";
        }
        return $content;
    }
    
    
    public function getLightContent() {
    	return self::lightContent($this->getContent());
    }
    
    
    public static function lightContent($content) {
    	return preg_replace('/[\s\n\r\t]+/', ' ', strip_tags($content));
    }
    

    public function isSticky()
    {
        return $this->getPostMeta(self::$_meta['stickyPost']);
    }

    /**
     * Set content of question
     * @param string $_description
     * @param bool $save Save immediately?
     * @return CMA_Thread
     */
    public function setContent($_content, $save = false)
    {
        $this->post->post_content = nl2br($_content);
        if( $save ) $this->savePost();
        return $this;
    }

    /**
     * Set status
     * @param string $_status
     * @param bool $save Save immediately?
     * @return CMA_Thread
     */
    public function setStatus($_status, $save = false)
    {
        $this->post->post_status = $_status;
        if( $save ) $this->savePost();
        return $this;
    }

    public function getStatus()
    {
        if ($this->isPublished()) return __('approved', 'cm-answers-pro');
        else return __('pending', 'cm-answers-pro');
    }
    
    
    public function isPublished() {
    	return ($this->post->post_status == 'publish');
    }
    

    public function getAttachments()
    {
    	return CMA_QuestionAttachment::selectForQuestion($this);
    }

    /**
     * Get author ID
     * @return int Author ID
     */
    public function getAuthorId()
    {
        return $this->post->post_author;
    }

    /**
     * Get author
     * @return WP_User
     */
    public function getAuthor()
    {
    	return self::getUser($this->getAuthorId(), $this);
    }
    
    
    public function getAuthorEmail() {
    	if ($user = $this->getAuthor() AND !empty($user->user_email)) {
    		return $user->user_email;
    	}
    }
    
    
    public function getAuthorName() {
    	if ($user = $this->getAuthor() AND !empty($user->display_name)) {
    		return $user->display_name;
    	}
    }
    
    
    public function getAuthorLink($simple = false) {
    	if ($user = $this->getAuthor()) {
    		return ($simple ? $user->link : $user->richLink);
    	} else {
    		return CMA::__('unknown');
    	}
    }
    
    
    protected static function createAuthorLink($user, $simple = false) {
    	if (empty($user)) return null;
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_AUTHOR_LINK_ENABLED)) {
			$url = CMA_BaseController::getContributorUrl($user);
			if( !empty($url) ) $authorLink = sprintf('<a href="%s">%s</a>', esc_attr($url), esc_html($user->display_name));
    	}
    	if (empty($authorLink)) {
    		$authorLink = '<span class="cma-author">' . esc_html($user->display_name) .'</span>';
    	}
		if (!$simple) {
			if (self::canSendPrivateQuestion($user->ID)) {
				$authorLink .= ' ' . self::createPrivateQuestionIcon($user->ID);
			}
		}
		return $authorLink;
    }
    
    
    public static function canSendPrivateQuestion($targetUserId) {
    	$userId = get_current_user_id();
    	return (CMA_Settings::getOption(CMA_Settings::OPTION_PRIVATE_QUESTIONS_ENABLED) AND $userId AND $userId != $targetUserId);
    }
    

    /**
     * Set author
     * @param int $_author
     * @param bool $save Save immediately?
     * @return CMA_Thread
     */
    public function setAuthor($_author, $save = false)
    {
        $this->post->post_author = $_author;
        if( $save ) $this->savePost();
        self::updateQA($_author);
        return $this;
    }
    
    
    
    public static function createPrivateQuestionIcon($userId) {
    	return sprintf('<a href="#" class="cma-private-question-icon" title="%s" data-user-id="%d"></a>',
    		esc_attr(CMA_Labels::getLocalized('send_private_question')),
    		intval($userId)
    	);
    }

    
    
    public static function getUser($userId, $contextObject = null) {
    	if ($userId AND $user = apply_filters('cma_filter_author', get_userdata($userId), $contextObject)) {
    		$user->link = self::createAuthorLink($user, true);
    		$user->richLink = self::createAuthorLink($user, false);
			return apply_filters('cma_get_author', $user);
    	}
    }
    

    public function getLastPoster()
    {
    	global $wpdb;
    	
    	$userId = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM $wpdb->comments
    		WHERE comment_post_ID = %d AND comment_approved = 1 ORDER BY comment_ID DESC LIMIT 1", $this->getId()));
    	if ($user = get_userdata($userId)) {
    		return self::getUser($userId, $this);
    	} else {
    		return $this->getAuthor();
    	}
    }


    public function getViews()
    {
        return (int) $this->getPostMeta(self::$_meta['views']);
    }

    public function setViews($views)
    {
        return $this->savePostMeta(array(self::$_meta['views'] => $views));
    }

    public function countView()
    {
        $increment = true;
        if( ! CMA_Settings::getOption(CMA_Settings::OPTION_INCREMENT_VIEWS) )
        {
            $currentBlockedIds = isset($_COOKIE['cma_viewed_questions']) ? maybe_unserialize($_COOKIE['cma_viewed_questions']) : array();
            if( in_array($this->getId(), $currentBlockedIds) )
            {
                $increment = false;
            }
            else
            {
                $currentBlockedIds[] = $this->getId();
                setcookie('cma_viewed_questions', serialize($currentBlockedIds), time() + (3600 * 24 * 30), null, null, null, true);
            }
        }

        if( $increment )
        {
            $views = $this->getViews();
            $this->savePostMeta(array(self::$_meta['views'] => $views + 1));
        }
        return $this;
    }

    public function getTitle($withResolved = true)
    {
        $title = '';
        if( $this->isResolved() AND $withResolved ) {
        	$title .= '[' . CMA_Labels::getLocalized('RESOLVED') . '] ';
        }
        $title .= parent::getTitle();
        return $title;
    }

    public function getVotes()
    {
        switch ( CMA_Settings::getOption(CMA_Settings::OPTION_VOTES_MODE)) {
        	case CMA_Settings::VOTES_MODE_ANSWERS_COUNT:
        		return (int) $this->getVotesAnswers();
        	case CMA_Settings::VOTES_MODE_QUESTION_COUNT:
        		return (int) $this->getVotesQuestion();
        	case CMA_Settings::VOTES_MODE_QUESTION_ANSWERS_COUNT:
        		return (int) $this->getVotesQuestionAnswers();
        	case CMA_Settings::VOTES_MODE_QUESTION_RATING:
        		return (int) $this->getPostRating();
        	default:
        		return $this->getHighestRatedAnswer();
        }
    }
    
    
    public function getVotesAnswers() {
    	return (int) $this->getPostMeta(self::$_meta['votes_answers']);
    }
    
    
    public function getVotesQuestion() {
    	return (int) $this->getPostMeta(self::$_meta['votes_question']);
    }
    
    
    public function getVotesQuestionAnswers() {
    	return (int) $this->getPostMeta(self::$_meta['votes_question_answers']);
    }
    
    
    public function getPostRating() {
    	return intval($this->getPostMeta(self::$_meta['rating']));
    }
    
    
    public function setPostRating($rating) {
    	update_post_meta($this->ID, self::$_meta['rating'], $rating);
    	return $this;
    }
    
    
    public function addAnswerVoteCount() {
    	$this->savePostMeta(array(self::$_meta['votes_answers'] => $this->getVotesAnswers() + 1));
    	$this->savePostMeta(array(self::$_meta['votes_question_answers'] => $this->getVotesQuestionAnswers() + 1));
    	$this->refreshHighestRatedAnswer();
    	return $this;
    }
    

    public function getHighestRatedAnswer()
    {
        return (int) $this->getPostMeta(self::$_meta['highestRatedAnswer']);
    }

    public function refreshHighestRatedAnswer()
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT MAX(mr.meta_value*1)
        		FROM {$wpdb->comments} c
        		JOIN {$wpdb->commentmeta} mr ON c.comment_ID=mr.comment_id AND mr.meta_key = %s
        		WHERE c.comment_post_ID='%d' AND c.comment_approved = 1",
        	CMA_Answer::META_RATING, $this->getId()
        );
        $highest = intval($wpdb->get_var($sql));
        $this->savePostMeta(array(self::$_meta['highestRatedAnswer'] => $highest));
        return $this;
    }

    public function isResolved()
    {
        return $this->getPostMeta(self::$_meta['resolved']) == 1;
    }

    public function setResolved($value = true)
    {
        $this->savePostMeta(array(self::$_meta['resolved'] => (int) $value));
        return $this;
    }
    
    /**
     * Returns user ids of the thread followers and category followers.
     * 
     * @return array
     */
    public function getAllFollowers() {
    	$result = $this->getFollowersEngine()->getFollowers();
    	if ($category = $this->getCategory()) {
    		$result = array_unique(array_merge($result, $category->getFollowersEngine()->getFollowers()));
    	}
    	return array_filter($result);
    }
    
    
    public function getRelated($limit = 5, $matchCategory = true, $matchTags = true) {
    	$tax_query = array();
    	if ($matchTags AND $tags = get_the_tags($this->getId())) {
	    	$tagsIds = array_map(function($tag) { return $tag->term_id; }, $tags);
    		$tax_query[] = array(
    			'taxonomy' => 'post_tag',
    			'field' => 'id',
    			'terms' => $tagsIds,
    			'operator' => 'IN'
    		);
    	}
    	if ($matchCategory AND $category = $this->getCategory()) {
    		$tax_query[] = array(
    			'taxonomy' => CMA_Category::TAXONOMY,
    			'field' => 'id',
    			'terms' => $category->getId(),
    			'operator' => 'IN'
    		);	
    	}
    	if (!empty($tax_query)) {
    		$tax_query['relation'] = 'OR';
    		add_filter('posts_where_request', array('CMA_AnswerController', 'categoryAccessFilter'));
    		$query = new WP_Query();
    		$query->set('tax_query', $tax_query);
    		$query->set('meta_key', self::$_meta['views']);
    		$query->set('post_type', self::POST_TYPE);
    		$query->set('limit', $limit);
    		$query->set('orderby', 'meta_value_num');
    		$query->set('order', 'DESC');
    		$query->set('post__not_in', array($this->getId()));
    		$result = $query->get_posts();
    		remove_filter('posts_where_request', array('CMA_AnswerController', 'categoryAccessFilter'));
    		return $result;
    	} else {
    		return array();
    	}
    }
    

    /**
     * Get the date when the question was first asked
     * @param string $format
     * @return type
     */
    public function getCreationDate($format = '')
    {
        if( empty($format) )
        {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }
        return date_i18n($format, strtotime($this->post->post_date));
    }

    /**
     * Returns the last comment object WP_Comment or null if no comments
     * @return CMA_Answer
     */
    public function getLastAnswer() {
    	$answers = CMA_Answer::getAnswersByThread($this->getId(), $approved = true, CMA_Answer::ORDER_BY_DATE, 'DESC', 1);
    	return end($answers);
    }

    /**
     * Get last answer or comment.
     * 
     * @param bool $onlyVisible default true
     * @return CMA_Answer|CMA_Comment
     */
    public function getLastComment($onlyVisible = true)
    {
    	global $wpdb;
//         if( empty($format) )
//         {
//             $format = get_option('date_format') . ' ' . get_option('time_format');
//         }
        
        $records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments
        	WHERE comment_post_ID = %d AND comment_approved = 1
        	ORDER BY comment_ID DESC",
        	$this->getId()
        ));
        foreach ($records as $record) {
        	if ($record->comment_type == CMA_Answer::COMMENT_TYPE) {
        		if ($answer = new CMA_Answer($record)) {
        			if (!$onlyVisible OR $answer->isVisible()) {
        				return $answer;
        			}
        		}
        	} else {
        		if ($comment = new CMA_Comment($record)) {
        			if (!$onlyVisible OR $comment->isVisible()) {
        				return $comment;
        			} 
        		}
        	}
        }
        

//         $lastAnswer = $this->getLastAnswer();
        
//         if( $lastAnswer )
//         {
//             $dateString = $lastAnswer->getDate();
//         }
//         else
//         {
//             $dateString = $this->post->post_modified;
//         }

//         return date_i18n($format, strtotime($dateString));
    }

    /**
     *
     * @param type $gmt
     * @return type
     */
    public function getUnixUpdated($gmt = false)
    {
        return get_post_modified_time('G', $gmt, $this->getPost());
    }

    /**
     *
     * @param type $gmt
     * @return type
     */
    public function getUnixDate($gmt = false)
    {
        return get_post_time('G', $gmt, $this->getPost());
    }

    public function setUpdated($date = null)
    {
        global $wpdb;

        if( empty($date) )
        {
            $date = current_time('mysql');
        }

        $this->post->post_modified = $date;
        $this->post->post_modified_gmt = $date;

        $wpdb->update($wpdb->posts, array('post_modified' => $date, 'post_modified_gmt' => get_gmt_from_date($date)), array('ID' => $this->post->ID));

        return $this;
    }

    public function getNumberOfAnswers() {
    	global $wpdb;
    	return intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments
    			WHERE comment_post_ID = %d
    				AND comment_type = %s
    				AND comment_approved = 1",
    			$this->getId(),
    			CMA_Answer::COMMENT_TYPE
    	)));
    }

    public function getAnswers($orderby = CMA_Answer::ORDER_BY_DATE, $onlyVisible = true) {
        $answers = CMA_Answer::getAnswersByThread($this->getId(), $approved = true, $orderby);
        if (!$onlyVisible) return $answers;
        else {
	        $results = array();
	        foreach ($answers as $answer) {
	        	if ($answer->isVisible()) {
	        		$results[] = $answer;
	        	}
	        }
	        return $results;
        }
    }

    public function isEditAllowed($userId)
    {
        return (user_can($userId, 'manage_options') || $this->getAuthorId() == $userId);
    }

    public static function newThread($data = array())
    {
    	
    	$userId = CMA::getPostingUserId();
    	$user = get_userdata($userId);
    	if (empty($userId) OR empty($user)) throw new Exception(CMA::__('Invalid user.'));
    	
        $title = self::titleFilter($data['title']);
        $content = self::contentFilter($data['content'], $userId);
        
        self::validateTitle($title, $editId = false, $errors);
        if( !CMA_Settings::getOption(CMA_Settings::OPTION_QUESTION_DESCRIPTION_OPTIONAL) && empty($content) )
        {
            $errors[] = __('Content cannot be empty', 'cm-answers-pro');
        }
        if (($badWord = CMA_BadWords::filterIfEnabled($content)) !== false) {
        	$errors[] = sprintf(CMA_Labels::getLocalized('msg_content_includes_bad_word'), $badWord);
        }
        if ( !empty($_FILES) AND !self::areQuestionAttachmentsAllowed() ) $errors[] = __('Upload is not allowed.', 'cm-answers-pro');
        elseif( !self::validateUploadSize() ) $errors[] = __('The file you uploaded is too big', 'cm-answers-pro');
        elseif( !self::validateUploadNames() ) $errors[] = __('The file you uploaded is not allowed', 'cm-answers-pro');
    	if( !empty($data['category']) && $data['category'] > 0 )
        {
           	if ($category = CMA_Category::getInstance($data['category'])) {
           		if (!$category->isVisible()) $errors[] = CMA::__('You have no permission to post this question.');
           	} else $errors[] = CMA::__('Choose a valid category.');
        }
        else if (CMA_Settings::getOption(CMA_Settings::OPTION_QUESTION_REQUIRE_CATEGORY)) {
           	$errors[] = CMA::__('Choose a category.');
        }

        if( !empty($errors) )
        {
            throw new Exception(serialize($errors));
        }

        if( CMA_Settings::getOption(CMA_Settings::OPTION_QUESTION_AUTO_APPROVE) || self::isAuthorAutoApproved($userId) )
        {
            $status = 'publish';
        }
        else
        {
            $status = 'draft';

            if( self::getSpamFilter() || CMA_Settings::getOption(CMA_Settings::OPTION_SIMULATE_COMMENT) )
            {
                /** Hack, simulate comment adding to trigger spam filters * */
                $commentdata = array(
                    'comment_post_ID'      => 0,
                    'comment_author'       => $user->first_name,
                    'comment_author_email' => $user->user_email,
                	'comment_author_url'   => '',
                    'comment_content'      => $title . ' ' . $content,
                    'comment_type'         => self::POST_TYPE,
                    'user_ID'              => $userId,
                	'comment_parent' 	   => 0,
                	'comment_author_IP'    => preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] ),
                	'comment_date'	   => current_time('mysql'),
                	'comment_date_gmt'	   => current_time('mysql', 1),
                	'comment_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 ) : '',
                );


                if( CMA_Settings::getOption(CMA_Settings::OPTION_SIMULATE_COMMENT) )
                { // Simulate comment to detect flood and so on.
                	if (wp_allow_comment($commentdata) == 'spam') {
                		$status = 'draft';
                	}
                }
            }
        }
        
        $postData = array(
            'post_status'  => $status,
            'post_type'    => self::POST_TYPE,
            'post_title'   => $title,
            'post_content' => $content,
            'post_name'    => urldecode(sanitize_title_with_dashes(remove_accents($title))),
            'post_author'  => $userId,
        );
        
        do_action('cma_question_post_before', $postData);
        $id = wp_insert_post($postData);
        
        if( $id instanceof WP_Error )
        {
            return $id->get_error_message();
        }
        else
        {
            $instance = self::getInstance($id);
            $instance->setUpdated()
                    ->setResolved(false)
                    ->setAuthorIP()
            		->checkGeolocation();
            if( !empty($data['notify']) AND $data['notify'] == 1 ) $instance->getFollowersEngine()->addFollower();
            $instance->savePostMeta(array(self::$_meta['views'] => 0));
            $instance->savePostMeta(array(self::$_meta['votes_answers'] => 0));
            $instance->savePostMeta(array(self::$_meta['votes_question'] => 0));
            $instance->savePostMeta(array(self::$_meta['votes_question_answers'] => 0));
            $instance->savePostMeta(array(self::$_meta['highestRatedAnswer'] => 0));
            $instance->savePostMeta(array(self::$_meta['stickyPost'] => 0));
            if (!empty($data['category'])) {
            	$r = wp_set_post_terms($id, array($data['category']), CMA_Category::TAXONOMY, true);
            }
            if( isset($data['tags']) ) {
            	$r = wp_set_post_tags($id, $data["tags"], true);
            }
            
            if (CMA_Settings::getOption(CMA_Settings::OPTION_USER_RELATED_QUESTIONS_ENABLE) AND !empty($data['userRelatedQuestions'])) {
            	$instance->setUserRelatedQuestions(CMA_UserRelatedQuestions::getIdsFromRaw($data['userRelatedQuestions']));
            }
            
            $instance->savePost();

            $attachmentsIds = CMA_QuestionAttachment::handleUpload($instance->getId());
            if( !empty($_POST['attached']) && is_array($_POST['attached']) ) {
            	$attachmentsIds = array_merge($attachmentsIds, $_POST['attached']);
            }
           	foreach ($attachmentsIds as $attachmentId) {
            	if (!empty($attachmentId)) $instance->addAttachment($attachmentId);
            }
            
        	if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_EVERYBODY_FOLLOW_ENABLED)) {
            	$instance->makeEverybodyFollowers();
            }
            
            if( $status == 'draft' ) {
                $instance->notifyModerator();
            } else {
            	self::updateQA($userId);
            	$instance->notifyAboutNewQuestion();
            }
            
            if (CMA_Settings::getOption(CMA_Settings::OPTION_LOGS_ENABLED)) {
            	CMA_QuestionPostLog::instance()->log($id);
            }
            
            do_action('cma_question_post_after', $instance, $data);
            
            return $instance;
            
        }
    }
    
    
    
    function setUserRelatedQuestions(array $related) {
    	$related = array_unique($related);
    	$current = $this->getUserRelatedQuestionsIds();
    	$toAdd = array_diff($related, $current);
    	$toRemove = array_diff($current, $related);
    	foreach ($toAdd as $id) {
    		if (get_post($id)) {
   				add_post_meta($this->getId(), self::$_meta['userRelatedQuestion'], $id, $unique = false);
    		} else {
    			$toRemove[] = $id;
    		}
    	}
    	foreach ($toRemove as $id) {
    		delete_post_meta($this->getId(), self::$_meta['userRelatedQuestion'], $id);
    	}
    	return $this;
    }
    
    
    function getUserRelatedQuestionsIds() {
    	return get_post_meta($this->getId(), self::$_meta['userRelatedQuestion'], $single = false);
    }
    
    
    function getUserRelatedQuestions($onlyVisible = true) {
    	$questions = array_filter(array_map(array('CMA_Thread', 'getInstance'), $this->getUserRelatedQuestionsIds()));
    	if ($onlyVisible) {
    		foreach ($questions as &$thread) {
    			if (!$thread->isVisible()) {
    				$thread = null;
    			}
    		}
    		$questions = array_filter($questions);
    	}
    	return $questions;
    }
    
    
    protected static function validateTitle($title, $editId, &$errors) {
    	global $wpdb;
    	
    	if( empty($title) ) $errors[] = CMA::__('Title cannot be empty');
    	if (($badWord = CMA_BadWords::filterIfEnabled($title)) !== false) {
    		$errors[] = sprintf(CMA_Labels::getLocalized('msg_content_includes_bad_word'), $badWord);
    	}
    	
    	// Duplicates
    	$duplicateMode = CMA_Settings::getOption(CMA_Settings::OPTION_DUPLICATED_QUESTIONS_MODE);
    	$sql = $wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_title = %s", $title);
    	if (!empty($editId)) $sql .= ' AND ID <> '. intval($editId);
        switch ($duplicateMode) {
        	case CMA_Settings::DUPLICATED_QUESTIONS_ANY_AUTHOR:
        		break;
        	case CMA_Settings::DUPLICATED_QUESTIONS_SAME_AUTHOR:
        		$sql .= $wpdb->prepare(" AND post_author = %d", CMA::getPostingUserId());
        		break;
        	case CMA_Settings::DUPLICATED_QUESTIONS_ALLOW:
        		$sql = null;
        		break;
        }
        if ($sql) {
        	$count = $wpdb->get_var($sql);
        	if ($count > 0) {
        		$errors[] = CMA_Labels::getLocalized('msg_post_question_uniq_error');
        	}
        }
        
    }
    
    
    protected function makeEverybodyFollowers() {
    	global $wpdb;
    	$engine = $this->getFollowersEngine();
    	$users = $wpdb->get_col("SELECT ID FROM $wpdb->users");
    	foreach ($users as $userId) {
    		$engine->addFollower($userId);
    	}
    }
    
    /**
     * Save post author country by IPGeolocation.
     * 
     * @return CMA_Thread
     */
    public function checkGeolocation() {
    	if ($apiKey = CMA_Settings::getOption(CMA_Settings::OPTION_GEOLOCIATION_API_KEY)) {
    		$service = new CMA_IPGeolocation();
    		$service->setKey($apiKey);
    		$response = $service->getCountry($this->getAuthorIP());
    		if (!empty($response['countryCode']) AND $response['countryCode'] != '-') {
    			$this->addPostMeta(array(self::$_meta['authorCountryCode'] => $response['countryCode']), true);
    		}
    		if (!empty($response['countryName']) AND $response['countryName'] != '-') {
    			$this->addPostMeta(array(self::$_meta['authorCountryName'] => $response['countryName']), true);
    		}
    	}
    	return $this;
    }
    
    
    public function isVisible($userId = null) {
    	if ($category = $this->getCategory()) {
    		if ($category->isVisible($userId)) {
	    		if (CMA_Settings::getOption(CMA_Settings::OPTION_RESTRICT_UNANSWERED_QUESTIONS_TO_EXPERTS)) {
	    			return ($this->getAnswersCount(false /* must be false, else the recursion occurs */) > 0 OR $this->isExpert());
	    		} else {
	    			return true;
	    		}
    		} else {
    			return false;
    		}
    	} else {
    		return true;
    	}
    }
    
    
    public function getAuthorIP() {
    	return $this->getPostMeta(self::$_meta['authorIP']);
    }
    
    public function setAuthorIP($ipaddr = null) {
    	if (is_null($ipaddr)) {
    		$ipaddr = $_SERVER['REMOTE_ADDR'];
    	}
    	return $this->addPostMeta(array(self::$_meta['authorIP'] => $ipaddr), true);
    }

    /**
     * Checks if the Thread has been held for moderation
     * @return boolean
     */
    public function wasHeldForModeration()
    {
        $held = $this->post->post_status === 'draft';
        return $held;
    }

    public static function getSpamFilter()
    {
        return get_option(self::OPTION_SPAM_FILTER, 1);
    }

    public static function setSpamFilter($value)
    {
        update_option(self::OPTION_SPAM_FILTER, (bool) $value);
    }

    public function notifyModerator()
    {
        $link = get_permalink($this->getId());
        $author = strip_tags($this->getAuthor()->display_name);
        $email = $this->getAuthor()->user_email;
        $ip = $this->getAuthorIP();
        $title = strip_tags($this->getTitle());
        $content = strip_tags($this->getContent());

        $approveLink = admin_url('edit.php?post_status=draft&post_type=' . self::POST_TYPE . '&cma-action=approve&cma-id=' . $this->getId());
        $trashLink = admin_url('edit.php?post_status=draft&post_type=' . self::POST_TYPE . '&cma-action=trash&cma-id=' . $this->getId());
        $pendingLink = admin_url('edit.php?post_status=draft&post_type=' . self::POST_TYPE);

        $emailTitle = '[' . get_bloginfo('name') . '] Please moderate: "' . $title . '"';
        $emailContent = "A new question has been asked and is waiting for your approval {$link}

Author : {$author}
IP     : {$ip}
E-mail : {$email}
Title  : {$title}
Content:
{$content}


Approve it: {$approveLink}
Trash it: {$trashLink}
Please visit the questions moderation panel:
{$pendingLink}
";

		$receivers = array(get_option('admin_email'));
		if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_ADMIN_NOTIFICATION_ENABLED)) {
			$receivers = array_merge($receivers, CMA_Settings::getOption(CMA_Settings::OPTION_POST_ADMIN_NOTIFICATION_EMAIL));
		}
		
		CMA_Email::send($receivers, $emailTitle, $emailContent);
		
		/* $headers = array();
		foreach($receivers as $email) {
			$email = trim($email);
			if (is_email($email)) {
				$headers[] = ' Bcc: '. $email;
			}
		}

        if (!empty($headers)) @wp_mail(null, $emailTitle, $emailContent, $headers); */
        
    }
    
    
    
    public function getCategoryFollowers() {
    	$followers = array();
    	if ($category = $this->getCategory()) {
	    	$followers = $category->getFollowersEngine()->getFollowers();
	    	if ($parent = $category->getParentInstance()) {
	    		$followers = array_unique(array_merge($followers, $parent->getFollowersEngine()->getFollowers()));
	    	}
    	}
    	return $followers;
    }
    

    public function notifyAboutNewQuestion() {
    	global $wpdb;
    	
    	$receivers = array();
    	
    	// Append newsletter followers
    	$users = CMA_ThreadNewsletter::getNewsletterFollowers(array('ID', 'user_email'));
    	foreach ($users as $user) {
    		if ($this->isVisible($user['ID'])) {
    			$receivers[] = $user['user_email'];
    		}
    	}
    	unset($users);
    		
        // Append category followers
        if (CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_CATEGORY_FOLLOWING) AND $category = $this->getCategory()) {
        	$followers = $this->getCategoryFollowers();
        	if (!empty($followers) AND is_array($followers)) foreach ($followers as $userId) {
        		if ($this->isVisible($userId)) {
	        		if ($user = get_user_by('id', $userId)) {
	        			$receivers[] = $user->user_email;
	        		}
        		}
        	}
        	unset($followers);
        }
    	
	    // Admin custom receivers' emails
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_ADMIN_NOTIFICATION_ENABLED)) {
        	$receivers = array_merge($receivers, CMA_Settings::getOption(CMA_Settings::OPTION_POST_ADMIN_NOTIFICATION_EMAIL));
    	}
    	
    	// Add experts
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_EXPERT_NOTIFICATION_ENABLED)) {
    		$receivers = array_merge($receivers, $this->getExpertsEmails());
    	}
        
    	$receivers = array_filter(array_unique($receivers));
        if( !empty($receivers) )
        {
            $title = CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFICATION_TITLE);
            $content = CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFICATION_CONTENT);
            
            $replace = array(
            	'[blogname]' => get_bloginfo('name'),
            	'[author]' => strip_tags($this->getAuthor()->display_name),
            	'[ip]' => $this->getAuthorIP(),
            	'[question_title]' => strip_tags($this->getTitle()),
            	'[question_body]' => strip_tags($this->getContent()),
            	'[question_status]' => $this->getStatus(),
            	'[question_link]' => get_permalink($this->getId()),
            	'[opt_out_url]' => CMA_ThreadNewsletter::getOptOutUrl($this, CMA_ThreadNewsletter::TYPE_NEW_THREADS),
            );
            
//             $title = str_replace('[blogname]', $blogname, $title);
//             $title = str_replace('[author]', $author, $title);
//             $title = str_replace('[question_title]', $questionTitle, $title);
//             $title = str_replace('[question_status]', $questionStatus, $title);
//             $title = str_replace('[question_link]', $questionLink, $title);
//             $content = str_replace('[blogname]', $blogname, $content);
//             $content = str_replace('[author]', $author, $content);
//             $content = str_replace('[question_title]', $questionTitle, $content);
//             $content = str_replace('[question_status]', $questionStatus, $content);
//             $content = str_replace('[question_link]', $questionLink, $content);
            
            CMA_Email::send($receivers, $title, $content, $replace);
            
            /* $headers = array();
            foreach($receivers as $email) {
            	$email = trim($email);
            	if (is_email($email)) {
            		$headers[] = ' Bcc: '. $email;
            	}
            }
            
            if (!empty($headers)) wp_mail(null, $title, $content, $headers); */
            
        }
    }
    
    
    /**
     * Returns users' IDs of experts which are associated with this thread.
     */
    public function getExperts() {
    	$experts = array();
    	if ($category = $this->getCategory()) {
    		$experts = $category->getExperts();
    		if ($parentCategory = $category->getParentInstance()) {
    			$experts = array_merge($experts, $parentCategory->getExperts());
    		}
    	}
    	return array_unique($experts);
    }
    
    
    public function getExpertsEmails() {
    	$experts = $this->getExperts();
    	$result = array();
    	foreach ($experts as $userId) {
    		if ($user = get_userdata($userId)) {
    			$result[$userId] = $user->user_email;
    		}
    	}
    	return $result;
    }
    
    
    public function isExpert($userId = null) {
    	if (empty($userId)) $userId = get_current_user_id();
    	return in_array($userId, $this->getExperts());
    }
    
    
    public function hasExperts() {
    	$experts = $this->getExperts();
    	return !empty($experts);
    }


    public function approve()
    {
        $this->setStatus('publish', true);
    }

    public function trash()
    {
        $this->setStatus('trash', true);
    }

    
    public function getParentCategory() {
    	if ($category = $this->getCategory()) {
    		return $category->getParentInstance();
    	}
    }
    
    public function getCategory() {
    	$terms = wp_get_post_terms($this->getId(), CMA_Category::TAXONOMY);
    	if ($category = reset($terms)) {
    		return new CMA_Category($category);
    	}
    }
    
    
    public function getCategoryId() {
    	if ($category = $this->getCategory()) {
    		return $category->getId();
    	}
    }
    

    public function addAnswer($content, $author_id, $follow = false, $resolved = false, $private = false)
    {
    	
        $user = get_userdata($author_id);
        if (empty($author_id) OR empty($user)) throw new Exception(CMA::__('Invalid user.'));
        
        if (!$this->isVisible()) throw new Exception(CMA::__('You have no permission to post this answer.'));
        $content = self::contentFilter($content, $author_id);

        if( empty($content) ) $errors[] = __('Content cannot be empty', 'cm-answers-pro');
        if (($badWord = CMA_BadWords::filterIfEnabled($content)) !== false) {
        	$errors[] = sprintf(CMA_Labels::getLocalized('msg_content_includes_bad_word'), $badWord);
        }
        if( !empty($errors) )
        {
            throw new Exception(serialize($errors));
        }

        $approved = (CMA_Settings::getOption(CMA_Settings::OPTION_ANSWER_AUTO_APPROVE) || self::isAuthorAutoApproved($author_id)) ? 1 : 0;

        $answer = new CMA_Answer(array(
            'comment_post_ID'      => $this->getId(),
            'comment_author'       => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_author_IP'    => $_SERVER['REMOTE_ADDR'],
            'user_id'              => $author_id,
            'comment_parent'       => 0,
            'comment_content'      => apply_filters('comment_text', str_replace(';)', ':)', $content)),
            'comment_approved'     => $approved,
            'comment_date'         => current_time('mysql'),
            'comment_type'         => CMA_Answer::COMMENT_TYPE
        ));
        
        do_action('cma_answer_post_before', $this, $answer);
        
        $answer->save();
        $answerId = $answer->getId();
        
        if (!$answerId) throw new Exception('Failed to add answer.');
        
    	$attachmentsIds = CMA_AnswerAttachment::handleUpload($this->getId());
        if( !empty($_POST['attached']) && is_array($_POST['attached']) ) {
        	$attachmentsIds = array_merge($attachmentsIds, $_POST['attached']);
        }
        foreach ($attachmentsIds as $attachmentId) {
        	$answer->addAttachment($attachmentId);
        }

        $answer->setPrivate($private);
        
        if (!$private) {
	        $this->updateThreadMetadata(array(
	            'commentId' => $answerId,
	            'authorId'  => $author_id,
	            'follow'    => $follow,
	            'resolved'  => $resolved,
	            'approved'  => $approved,
	        	'answerId' => $answerId,
	        ), $notifyUsers = !$private);
	        if ($approved) {
	        	$this->setUpdated();
	        }
        }
        if ($approved) {
        	self::updateQA($author_id);
        	$this->notifyAboutNewAnswer($answerId);
        }
        else if ( !$approved ) {
            wp_notify_moderator($answerId);
        }
        
        if (CMA_Settings::getOption(CMA_Settings::OPTION_LOGS_ENABLED)) {
        	CMA_AnswerPostLog::instance()->log($answerId);
        }
        
        do_action('cma_answer_post_after', $this, $answer);
        
        return $answerId;
    }
    
    
    function getFollowersEmails() {
    	global $wpdb;
    	$emails = array();
    	if ($followers = $this->getFollowersEngine()->getFollowers()) {
    		$followers = array_unique(array_filter(array_map('intval', $followers)));
    		$followersEmails = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users WHERE ID IN (". implode(',', $followers) .")", ARRAY_A);
    		foreach ($followersEmails as $user) {
    			if ($this->isVisible($user['ID'])) {
    				$emails[$user['ID']] = $user['user_email'];
    			}
    		}
    	}
    	return $emails;
    }
    
    
    function getContributorsEmails() {
    	global $wpdb;
    	$emails = array();
    	$contributors = $wpdb->get_results($wpdb->prepare("SELECT c.user_id, IFNULL(c.comment_author_email, u.user_email) AS email FROM $wpdb->users u
    		LEFT JOIN $wpdb->comments c ON c.user_id = u.ID
    		WHERE c.comment_post_ID = %d", $this->getId()), ARRAY_A);
    	foreach ($contributors as $user) {
    		if ($this->isVisible($user['user_id'])) {
    			$emails[$user['user_id']] = $user['email'];
    		}
    	}
    	return $emails;
    }
    

    public function notifyAboutNewAnswer($lastAnswerId)
    {
    	global $wpdb;
    	
    	$lastAnswer = CMA_Answer::getById($lastAnswerId);
    	
    	$receivers = array();
    	
    	// All users are receivers
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_ANSWER_NOTIFY_EVERYBODY_ENABLED) AND !$lastAnswer->isPrivate()) {
    		$users = $wpdb->get_results("SELECT ID, user_email FROM $wpdb->users", ARRAY_A);
    		foreach ($users as $user) {
    			if ($this->isVisible($user['ID'])) {
    				$receivers[] = $user['user_email'];
    			}
    		}
    		unset($users);
    	} else {
	    	
	    	// Users followers
	    	if (CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_THREAD_FOLLOWING)) {
		        $followers = $this->getFollowersEngine()->getFollowers();
		        if( !empty($followers) ) {
		        	foreach($followers as $user_id) {
		            	if ($user_id != $lastAnswer->getAuthorId() AND $this->isVisible($user_id)) {
			                $user = get_userdata($user_id);
			                if( !empty($user->user_email) ) {
			                	$receivers[] = $user->user_email;
			                }
		            	}
		        	}
		        }
		        unset($followers);
	    	}
	        
    	}
    	
    	$receivers = apply_filters('cma_answer_notification_emails', $receivers, $lastAnswerId);
    	
    	if ($lastAnswer->isPrivate()) {
    		if ($threadAuthor = $this->getAuthor()) {
    			$receivers = array_intersect($receivers, array($threadAuthor->user_email));
    		}
    	}
    	
	    // Admin custom receivers' emails
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_ANSWER_ADMIN_NOTIFICATION_ENABLED)) {
    		$receivers = array_merge($receivers, CMA_Settings::getOption(CMA_Settings::OPTION_POST_ADMIN_NOTIFICATION_EMAIL));
    	}
        
        $receivers = array_filter(array_unique($receivers));
        if( !empty($receivers) )
        {
            $message = CMA_Settings::getOption(CMA_Settings::OPTION_THREAD_NOTIFICATION);
            $title = CMA_Settings::getOption(CMA_Settings::OPTION_THREAD_NOTIFICATION_TITLE);
            
            $replace = array(
            	'[blogname]' => get_bloginfo('name'),
            	'[question_title]' => strip_tags($this->getTitle()),
            	'[question_body]' => strip_tags($this->getContent()),
            	'[answer]' => strip_tags($lastAnswer->getContent()),
            	'[comment_link]' => $lastAnswer->getPermalink(),
            	'[author]' => strip_tags($lastAnswer->getAuthor()->display_name),
            	'[ip]' => $lastAnswer->getAuthorIP(),
            	'[opt_out_url]' => CMA_ThreadNewsletter::getOptOutUrl($this, CMA_ThreadNewsletter::TYPE_THREAD),
            );
            
            CMA_Email::send($receivers, $title, $message, $replace);
            
            /* $headers = array();
            foreach($receivers as $email) {
            	if (is_email($email)) {
            		$headers[] = ' Bcc: '. $email;
            	}
            }
            
            wp_mail(null, $title, $message, $headers); */
            
        }
    }

    public function updateThreadMetadata($array)
    {
        $authorId = (isset($array['authorId'])) ? $array['authorId'] : null;
        $answerId = (isset($array['answerId'])) ? $array['answerId'] : null;
		
        if( $authorId && isset($array['follow']) && $array['follow'] )
        {
            $this->getFollowersEngine()->addFollower($authorId);
        }

        if( isset($array['resolved']) && $array['resolved'] )
        {
            $this->setResolved($array['resolved']);
        }

    }
    
    
    public function clearCache($hard = false) {
    	if (function_exists('w3tc_pgcache_flush_post')) {
    		w3tc_pgcache_flush_post($this->getId());
    	}
    	if ($hard AND function_exists('w3tc_pgcache_flush')) {
    		w3tc_pgcache_flush();
    	}
    }


	public function getVoters() {
    	$voters = array_merge(
    		get_post_meta($this->getId(), self::$_meta['userRatingPositive'], $single = false),
    		get_post_meta($this->getId(), self::$_meta['userRatingNegative'], $single = false)
    	);
    	if (is_array($voters) AND !empty($voters)) {
    		return array_unique($voters);
    	} else {
    		return array();
    	}
    }
    

	public function isVotingAllowed($userId) {
		if (!CMA_Settings::getOption(CMA_Settings::OPTION_CAN_VOTE_MYSELF) AND $this->getAuthorId() == $userId) {
			return false;
		} else {
			return !$this->didUserVoted($userId);
		}
	}

    public function didUserVoted($userId) {
    	if (is_user_logged_in()) {
        	return in_array($userId, $this->getVoters());
    	}
    	else if (CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_GUESTS_VOTING)) {
    		return in_array(self::getUserVotingId(), $this->getVoters());
    	} else {
			return false;
		}
    }

    public function voteUp()
    {
    	return $this->_vote($positive = true);
    }

    public function voteDown()
    {
    	return $this->_vote($positive = false);
    }
    
    
    static function initAnonymousVotingCookie() {
    	if (!is_user_logged_in() AND empty($_COOKIE[CMA_Thread::COOKIE_ANONYMOUS_UID])) {
    		// Set anonymous cookie
    		$uid = md5(microtime() . mt_rand(1, 9999999) . uniqid());
    		setcookie(CMA_Thread::COOKIE_ANONYMOUS_UID, $uid, time() + (3600 * 24 * 365 * 10), null, null, null, true);
    	}
    }
    

    protected function _vote($positive) {
    	$current = $this->getPostRating();
		$point = $positive ? 1 : -1;
		$userVotingId = self::getUserVotingId();
    	$metaId = add_post_meta($this->getId(), self::$_meta[$positive ? 'userRatingPositive' : 'userRatingNegative'], $userVotingId, $unique = false);
    	if ($metaId) {
	    	add_post_meta($this->getId(), self::$_meta['voteIp'] .'_'. $metaId, $_SERVER['REMOTE_ADDR']);
	    	add_post_meta($this->getId(), self::$_meta['voteUA'] .'_'. $metaId, $_SERVER['HTTP_USER_AGENT']);
	    	add_post_meta($this->getId(), self::$_meta['voteTime'] .'_'. $metaId, time());
	    	if (!empty($_COOKIE[self::COOKIE_ANONYMOUS_UID])) {
	    		add_post_meta($this->getId(), self::$_meta['voteCookie'] .'_'. $metaId, $_COOKIE[self::COOKIE_ANONYMOUS_UID]);
	    	}
	    	$this->updateRatingCache();
	    	
	    	if (CMA_Settings::getOption(CMA_Settings::OPTION_LOGS_ENABLED)) {
	    		CMA_QuestionVoteLog::instance()->log($this->getId(), $point);
	    	}
	    	
    	} else $point = 0;
    	return $current + $point;
    }
    
    
    /**
     * Returns user's ID if logged in. For guests returns random ID.
     */
    static function getUserVotingId() {
    	if (is_user_logged_in()) {
    		return get_current_user_id();
    	} else {
    		return $_SERVER['REMOTE_ADDR'];
    	}
    }
    
    
    
    public function updateRatingCache() {
    	global $wpdb;
    	
    	// Post rating
    	$rating = $this->getRatingHandicap() + $this->getRatingPositiveCount() - $this->getRatingNegativeCount();
    	update_post_meta($this->getId(), self::$_meta['rating'], $rating);
    	
    	// Answers votes count
    	$votes_answers = $this->getVotesHandicap() +
    		$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->commentmeta m
    			INNER JOIN $wpdb->comments c ON c.comment_ID = m.comment_id
    			WHERE c.comment_type = %s AND c.comment_post_ID = %d AND m.meta_key IN (%s, %s) AND c.comment_approved = 1",
    			CMA_Answer::COMMENT_TYPE, $this->getId(), CMA_Answer::META_USER_RATING_NEGATIVE, CMA_Answer::META_USER_RATING_POSITIVE
    		));
    	update_post_meta($this->getId(), self::$_meta['votes_answers'], $votes_answers);
    	
    	// Question votes count
    	$votersCount = count($this->getVoters());
    	$votes_question = $this->getVotesHandicap() + $votersCount;
    	update_post_meta($this->getId(), self::$_meta['votes_question'], $votes_question);
    	
    	// Question + answers votes count
    	$votes_question_answers = $votes_answers + $votersCount;
    	update_post_meta($this->getId(), self::$_meta['votes_question_answers'], $votes_question_answers);
    	
    	// Highest rated answer
    	$this->refreshHighestRatedAnswer();
    	
    }
    
    
    public function getRatingHandicap() {
    	return intval($this->getPostMeta(self::$_meta['userRatingHandicap']));
    }
    
    public function setRatingHandicap($val) {
    	$this->savePostMeta(array(self::$_meta['userRatingHandicap'] => intval($val)));
    	$this->updateRatingCache();
    	return $this;
    }
    
	public function getVotesHandicap() {
    	return intval($this->getPostMeta(self::$_meta['votesHandicap']));
    }
    
    public function setVotesHandicap($val) {
    	$this->savePostMeta(array(self::$_meta['votesHandicap'] => intval($val)));
    	$this->updateRatingCache();
    	return $this;
    }
    
    
    public function getRatingPositiveCount() {
    	return count(get_post_meta($this->getId(), self::$_meta['userRatingPositive'], $single = false));
    }
    
	public function getRatingNegativeCount() {
    	return count(get_post_meta($this->getId(), self::$_meta['userRatingNegative'], $single = false));
    }
    
    
    function canDelete($userId = null) {
    	if (empty($userId)) $userId = get_current_user_id();
    	if (!empty($userId) AND $userId == $this->getAuthorId()) {
    		// I am the author
    		if (CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_QUESTION_DELETE_NOANSWERS) AND $this->getAnswersCount($onlyVisible = false) > 0) {
    			// Disallowed to delete answered questions:
    			return false;
    		} else switch (CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_QUESTION_DELETE)) {
    			case CMA_Settings::DELETE_MODE_ANYTIME:
    				return true;
    			case CMA_Settings::DELETE_MODE_WITHIN_HOUR:
    				return (time() - strtotime($this->post->post_date) < 3600);
    			case CMA_Settings::DELETE_MODE_WITHIN_DAY:
    				return (time() - strtotime($this->post->post_date) < 3600 * 24);
    			case CMA_Settings::DELETE_MODE_DISALLOWED:
    			default:
    				return false;
    		}
    	} else {
    		return false;
    	}
    }
    
    
    function delete() {
    	return wp_delete_post($this->getId(), $force = !CMA_Settings::getOption(CMA_Settings::OPTION_QUESTION_DELETE_TRASH)) !== false;
    }
    
    
    function getAnswersCount($onlyVisible = true) {
    	return count($this->getAnswers(null, $onlyVisible));
    }
    

    /**
     *
     * @param int $date Unix timestamp
     * @return string
     */
    public static function renderDaysAgo($date, $gmt = false)
    {
        if( !is_numeric($date) )
        {
            $date = strtotime($date);
        }
        $current = current_time('timestamp', $gmt);
        $seconds_ago = floor($current - $date);

        if( $seconds_ago < 0 )
        {
            return __('some time ago', 'cm-answers-pro');
        }
        else
        {
            if( $seconds_ago < 60 )
            {
                return sprintf(_n('1 second ago', '%d seconds ago', $seconds_ago, 'cm-answers-pro'), $seconds_ago);
            }
            else
            {
                $minutes_ago = floor($seconds_ago / 60);
                if( $minutes_ago < 60 )
                {
                    return sprintf(_n('1 minute ago', '%d minutes ago', $minutes_ago, 'cm-answers-pro'), $minutes_ago);
                }
                else
                {
                    $hours_ago = floor($minutes_ago / 60);
                    if( $hours_ago < 24 )
                    {
                        return sprintf(_n('1 hour ago', '%d hours ago', $hours_ago, 'cm-answers-pro'), $hours_ago);
                    }
                    else
                    {
                        $days_ago = floor($hours_ago / 24);
                        if( $days_ago < 7 )
                        {
                            return sprintf(_n('1 day ago', '%d days ago', $days_ago, 'cm-answers-pro'), $days_ago);
                        }
                        else
                        {
                            $weeks_ago = floor($days_ago / 7);
                            if( $weeks_ago < 4 )
                            {
                                return sprintf(_n('1 week ago', '%d weeks ago', $weeks_ago, 'cm-answers-pro'), $weeks_ago);
                            }
                            else
                            {
                                $months_ago = floor($weeks_ago / 4);
                                if( $months_ago < 12 )
                                {
                                    return sprintf(_n('1 month ago', '%d months ago', $months_ago, 'cm-answers-pro'), $months_ago);
                                }
                                else
                                {
                                    $years_ago = floor($months_ago / 12);
                                    return sprintf(_n('1 year ago', '%d years ago', $years_ago, 'cm-answers-pro'), $years_ago);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function setDisclaimerEnabled($value = true)
    {
        update_option(self::OPTION_DISCLAIMER_APPROVE, (int) $value);
    }

    public static function isDisclaimerEnabled()
    {
        return (bool) get_option(self::OPTION_DISCLAIMER_APPROVE);
    }

    public static function isAuthorAutoApproved($author_id)
    {
        return in_array($author_id, CMA_Settings::getOption(CMA_Settings::OPTION_AUTO_APPROVE_AUTHORS));
    }

    public static function convertShorthandToBytes($shorthand)
    {
        if( !$shorthand || !is_string($shorthand) )
        {
            return _e('NOT SET. Typically: 32768B(32MB)');
        }

        $val = trim($shorthand);
        $last = mb_strtolower(mb_substr($val, -1));
		// The 'G' modifier is available since PHP 5.1.0
        switch($last)
        {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
            default:
                break;
        }

        return $val;
    }

    public static function convertBytesToShorthand($bytes, $precision = 2)
    {
		// human readable format -- powers of 1024
        $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');

        return @round(
                        $bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision
                ) . ' ' .
                $unit[$i];
    }
    

    public static function isSidebarEnabled()
    {
        $allowed = get_option(self ::OPTION_SIDEBAR_ENABLED, 1);
        return (bool) $allowed;
    }

    public static function setSidebarEnabled($value = true)
    {
        update_option(self ::OPTION_SIDEBAR_ENABLED, (int) $value);
    }

    public static function getSidebarMaxWidth()
    {
        $width = get_option(self::OPTION_SIDEBAR_MAX_WIDTH, 0);

        return (int) $width;
    }

    public static function setSidebarMaxWidth($value = 0)
    {
        update_option(self::OPTION_SIDEBAR_MAX_WIDTH, (int) $value);
    }

    public static function isSidebarContributorEnabled()
    {
        $allowed = get_option(self ::OPTION_SIDEBAR_CONTRIBUTOR_ENABLED, 1);
        return $allowed;
    }

    public static function setSidebarContributorEnabled($value = true)
    {
        update_option(self ::OPTION_SIDEBAR_CONTRIBUTOR_ENABLED, $value);
    }
    
    
    /**
     * Check whether use can edit resolved questions or answers of a resolved question.
     * 
     * @return boolean
     */
    public static function canEditResolved()
    {
    	return CMA_Settings::getOption(CMA_Settings::OPTION_CAN_EDIT_RESOLVED);
    }
    
    public static function isReferralEnabled()
    {
        return get_option(self::OPTION_REFERRAL_ENABLED, 0);
    }

    public static function setReferralEnabled($mode)
    {
        update_option(self::OPTION_REFERRAL_ENABLED, $mode);
    }


    public static function getAffiliateCode()
    {
        return get_option(self::OPTION_AFFILIATE_CODE, '');
    }

    public static function setAffiliateCode($mode)
    {
        update_option(self::OPTION_AFFILIATE_CODE, $mode);
    }


    public static function getCustomCss()
    {
        return get_option(self::OPTION_CUSTOM_CSS, '');
    }

    public static function setCustomCss($mode)
    {
        update_option(self::OPTION_CUSTOM_CSS, trim($mode));
    }


	// ***************************************

    /*
     * ACCESS
     */

    /**
     * Checks if current page can be viewed
     * @return boolean
     */
    public static function canBeViewed()
    {
        $viewingSetting = CMA_Settings::getOption(CMA_Settings::OPTION_VIEW_ACCESS);

        switch($viewingSetting)
        {
            case CMA_Settings::ACCESS_EVERYONE:
                {
                    return TRUE;
                }
            case CMA_Settings::ACCESS_USERS:
                {
                    return is_user_logged_in();
                }
            case CMA_Settings::ACCESS_ROLE:
                {
                    $user = get_userdata(CMA::getPostingUserId());
                    if( !$user )
                    {
                        return FALSE;
                    }
                    $userRoles = $user->roles;
                    $accessRoles = (array)CMA_Settings::getOption(CMA_Settings::OPTION_VIEW_ACCESS_ROLES);

                    $hasRightRole = array_intersect($accessRoles, $userRoles);
                    return user_can($user, 'manage_options') || !empty($hasRightRole);
                }
            default:
                break;
        }
    }

    /**
     * Checks if current user can post questions
     * @return boolean
     */
    public static function canPostQuestions()
    {
        $postQuestionsSetting = CMA_Settings::getOption(CMA_Settings::OPTION_POST_QUESTIONS_ACCESS);

        switch($postQuestionsSetting)
        {
            case CMA_Settings::ACCESS_USERS:
                {
                    return is_user_logged_in();
                }
            case CMA_Settings::ACCESS_ROLE:
                {
                    $user = get_userdata(CMA::getPostingUserId());
                    if( !$user )
                    {
                        return FALSE;
                    }
                    $userRoles = $user->roles;
                    $accessRoles = (array)CMA_Settings::getOption(CMA_Settings::OPTION_POST_QUESTIONS_ACCESS_ROLES);

                    $hasRightRole = array_intersect($accessRoles, $userRoles);
                    return user_can($user, 'manage_options') || !empty($hasRightRole);
                }
            default:
            	return (apply_filters('cma_can_post_questions', $postQuestionsSetting) OR is_user_logged_in());
                break;
        }
    }

    /**
     * Checks if current user can post answers
     * @return boolean
     */
    public function canPostAnswers($userId = null)
    {
    	if (empty($userId)) $userId = CMA::getPostingUserId();
    	
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_RESTRICT_UNANSWERED_QUESTIONS_TO_EXPERTS) AND $this->getAnswersCount(false) == 0) {
    		if (!$this->isExpert($userId)) {
    			return false;
    		}
    	}
    	
        $postAnswersSetting = CMA_Settings::getOption(CMA_Settings::OPTION_POST_ANSWERS_ACCESS);
        switch($postAnswersSetting)
        {
            case CMA_Settings::ACCESS_USERS:
                {
                    return is_user_logged_in();
                }
            case CMA_Settings::ACCESS_ROLE:
                {
                    $user = get_userdata($userId);
                    if( !$user )
                    {
                        return FALSE;
                    }
                    $userRoles = $user->roles;
                    $accessRoles = (array)CMA_Settings::getOption(CMA_Settings::OPTION_POST_ANSWERS_ACCESS_ROLES);

                    $hasRightRole = array_intersect($accessRoles, $userRoles);
                    return user_can($user, 'manage_options') || !empty($hasRightRole);
                }
            case CMA_Settings::ACCESS_EXPERT:
            	if ($this->hasExperts()) {
            		return $this->isExpert($userId);
            	} else {
            		return is_user_logged_in();
            	}
            	
            default:
            	return (apply_filters('cma_can_post_answers', $postAnswersSetting) OR is_user_logged_in());
                break;
        }
    }
    
    
    public function canPostPrivateAnswer($userId = null) {
    	if (is_null($userId)) $userId = get_current_user_id();
    	return (CMA_Settings::getOption(CMA_Settings::OPTION_PRIVATE_ANSWERS_ENABLED)
    				AND $userId != $this->getAuthorId() AND !empty($userId));
    }

    public static function getDisclaimerContent()
    {
        return get_option(self::OPTION_DISCLAIMER_CONTENT, self::DEFAULT_DISCLAIMER_CONTENT);
    }

    public static function getDisclaimerContentAccept()
    {
        return get_option(self::OPTION_DISCLAIMER_CONTENT_ACCEPT, self::DEFAULT_DISCLAIMER_CONTENT_ACCEPT);
    }

    public static function getDisclaimerContentReject()
    {
        return get_option(self::OPTION_DISCLAIMER_CONTENT_REJECT, self::DEFAULT_DISCLAIMER_CONTENT_REJECT);
    }
    
    public static function setDisclaimerContent($content)
    {
        update_option(self::OPTION_DISCLAIMER_CONTENT, $content);
    }

    public static function setDisclaimerContentAccept($content)
    {
        update_option(self::OPTION_DISCLAIMER_CONTENT_ACCEPT, $content);
    }

    public static function setDisclaimerContentReject($content)
    {
        update_option(self::OPTION_DISCLAIMER_CONTENT_REJECT, $content);
    }

    public static function customOrder(WP_Query $query, $orderby)
    {
        switch($orderby)
        {
            case 'hottest':
                $query->set('orderby', 'modified');
                $query->set('order', 'DESC');
                break;
            case 'votes':
            	switch ( CMA_Settings::getOption(CMA_Settings::OPTION_VOTES_MODE)) {
                	case CMA_Settings::VOTES_MODE_ANSWERS_COUNT:
                		$query->set('meta_key', self::$_meta['votes_answers']);
                		break;
                	case CMA_Settings::VOTES_MODE_QUESTION_COUNT:
                		$query->set('meta_key', self::$_meta['votes_question']);
                		break;
                	case CMA_Settings::VOTES_MODE_QUESTION_ANSWERS_COUNT:
                		$query->set('meta_key', self::$_meta['votes_question_answers']);
                		break;
                	case CMA_Settings::VOTES_MODE_QUESTION_RATING:
                		$query->set('meta_key', self::$_meta['rating']);
                		break;
                	default:
                		$query->set('meta_key', self::$_meta['highestRatedAnswer']);
                }
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'views':
                $query->set('meta_key', self::$_meta['views']);
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'newest':
            default:
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
        }


        return $query;
    }

    public static function tagFilter(WP_Query $query, $tag)
    {
        $query->set('tag', $tag);
        return $query;
    }
    
    
    /**
     * Get thread ids which are associated with some category.
     * 
     * It doesn't include threads which are not associated with any category.
     * 
     * @return string
     */
    public static function getCategorizedThreadIdsSubquery() {
    	global $wpdb;
    	
    	return $wpdb->prepare("SELECT tr.object_id
			FROM $wpdb->term_relationships tr
			LEFT JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
    		WHERE tt.taxonomy = %s
    		", CMA_Category::TAXONOMY);
    	
    }
    
    
    public static function getCategoryAccessFilterSubquery($userId = null) {
    	global $wpdb;
    	
    	if (is_null($userId)) $userId = CMA::getPostingUserId();
    	if (empty($userId)) $userId = 0;
    	if (user_can($userId, 'manage_options')) { // Admin can view all categories
    		return $wpdb->prepare("SELECT tr.object_id
	    		FROM $wpdb->term_relationships tr
    			INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
	    		WHERE tt.taxonomy = %s", CMA_Category::TAXONOMY);
    	} else {
	    	$sql = "SELECT tr.object_id
	    		FROM $wpdb->term_relationships tr
	    		JOIN $wpdb->posts p ON p.ID = tr.object_id
	    		WHERE 1=1";
	    	if ($ids = CMA_Category::getVisibleTermTaxonomyIds($userId)) {
	    		// there are visible categories:
	    		$sql .= " AND tr.term_taxonomy_id IN (". implode(',', $ids) .")";
	    	} else {
	    		// there is no visible categories so reject all ids:
	    		$sql .= " AND 1=0 ";
	    	}
	    	if (CMA_Settings::getOption(CMA_Settings::OPTION_RESTRICT_UNANSWERED_QUESTIONS_TO_EXPERTS)) {
	    		$sql .= " AND (p.comment_count > 0"; // question is unanswered
	    		if ($ids = CMA_Category::getExpertsTermTaxonomyIds($userId)) {
	    			// or I'm the expert in question's category
	    			$sql .= " OR tr.term_taxonomy_id IN (". implode(',', $ids) .")";
	    		}
	    		$sql .= ")";
	    	}
	    	return $sql;
    	}
    	
    }
    

    public static function getQuestionsByUser($user_id, $limit = -1, $onlyVisible = true)
    {
        if( !$user_id )
        {
            return array();
        }

        $args = array(
            'author'         => $user_id,
            'post_type'      => self::POST_TYPE,
            'post_status'    => array('publish', 'draft'),
            'fields'         => 'ids',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'user_questions' => true
        );
        $args['posts_per_page'] = $limit;
        
        if ($onlyVisible) add_filter('posts_where_request', array('CMA_AnswerController', 'categoryAccessFilter'));
        $q = new WP_Query($args);
        $questions = array();
        $posts = $q->get_posts();
        if ($onlyVisible) remove_filter('posts_where_request', array('CMA_AnswerController', 'categoryAccessFilter'));
        
        foreach($posts as $id)
        {
        	if ($question = self::getInstance($id)) {
            	$questions[] = $question;
        	}
        }
        return $questions;
    }


    public static function getCountQuestionsByUser($user_id)
    {
        $answers = get_user_meta($user_id, self::USERMETA_COUNTER_QUESTIONS, true);
        if( empty($answers) )
        {
            self::updateQA($user_id);
        }
        return get_user_meta($user_id, self::USERMETA_COUNTER_QUESTIONS, true);
    }

    public static function getCountAnswersByUser($user_id)
    {
        $answers = get_user_meta($user_id, self::USERMETA_COUNTER_ANSWERS, true);
        if( empty($answers) )
        {
            self::updateQA($user_id);
        }
        return get_user_meta($user_id, self::USERMETA_COUNTER_ANSWERS, true);
    }

    public static function updateQA($userId)
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts}   WHERE post_type=%s AND post_status='publish' AND post_author=%d", self::POST_TYPE, $userId);
        $questions = $wpdb->get_var($sql);
        update_user_meta($userId, self::USERMETA_COUNTER_QUESTIONS, $questions);
        $sql2 = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->comments} c
        	JOIN {$wpdb->posts} p ON p.ID=c.comment_post_ID AND p.post_type=%s AND p.post_status='publish' AND c.user_id=%d", self::POST_TYPE, $userId);
        $answers = $wpdb->get_var($sql2);
        update_user_meta($userId, self::USERMETA_COUNTER_ANSWERS, $answers);
    }
    
    
    public static function updateAllQA() {
    	global $wpdb;
    	
    	// Update question count for each user
    	$sql = $wpdb->prepare("SELECT post_author AS user_id, COUNT(*) AS question_count
    			FROM {$wpdb->posts}
    			WHERE post_type=%s AND post_status='publish'
    			GROUP BY post_author", self::POST_TYPE);
    	$results = $wpdb->get_results($sql, ARRAY_A);
    	foreach ($results as $result) {
    		update_user_meta($result['user_id'], self::USERMETA_COUNTER_QUESTIONS, $result['question_count']);
    	}
    	
    	// Update answer count for each user
    	$sql = $wpdb->prepare("SELECT c.user_id, COUNT(*) AS answer_count
    			FROM {$wpdb->comments} c
    			JOIN {$wpdb->posts}   p ON p.ID=c.comment_post_ID AND p.post_type=%s AND p.post_status='publish'
    			GROUP BY c.user_id", self::POST_TYPE);
    	$results = $wpdb->get_results($sql, ARRAY_A);
    	foreach ($results as $result) {
    		update_user_meta($result['user_id'], self::USERMETA_COUNTER_ANSWERS, $result['answer_count']);
    	}
    	
    }
    

    public static function titleFilter($title)
    {
        return trim(wp_kses($title, array()));
    }

    public static function contentFilter($content, $userId)
    {
    	
    	if (CMA_Settings::getOption(CMA_Settings::OPTION_ESCAPE_PRE_CONTENT)) {
	        /*         * **Format Code Snippets ******************************* */
	        $content = preg_replace_callback("/<pre>([\s\S]+?)<\/pre>/", function($matches)
	        {
	
	            $snippet = $matches[1];
	            $snippet = htmlentities($snippet);
	            $snippet = nl2br($snippet);
	
	            return '<pre class="cma_snippet_background">' . $snippet . '</pre>';
	        }, $content);
	        /*         * ******************************************************* */
    	}
    	
        $content = wpautop($content, false);
        
        
        // Don't filter HTML for allowed roles
        if ($allowedRoles = CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_FULL_HTML_ROLES)) {
        	if ($userId AND $user = get_userdata($userId)) {
        		if (array_intersect($allowedRoles, $user->roles)) {
        			return trim($content);
        		}
        	}
        }

        $allowed_html = array();
        $allowedTags = array_filter(preg_split("/[^a-z0-9 ]/i", CMA_Settings::getOption(CMA_Settings::OPTION_CONTENT_ALLOWED_TAGS)));
        foreach ($allowedTags as $row) {
        $row = array_filter(explode(' ', $row));
        	$tag = trim(array_shift($row));
        	$attributes = array();
        	foreach ($row as $attr) {
        		$attributes[trim($attr)] = array();
        	}
        	$allowed_html[$tag] = $attributes;
        }
        
		if (user_can($userId, 'upload_files')) { // Only users with upload_files permission can add images
			$allowed_html['img'] = array('src' => 1);
		}
        $content = wp_kses($content, $allowed_html);

        return trim($content);
    }

    public function updateQuestionContent($userId, $title, $content)
    {
        global $wpdb;

        $errors = array();

        $title = self::titleFilter($title);
        
        $content = self::contentFilter($content, $userId);

        self::validateTitle($title, $editId = $this->getId(), $errors);
        if( !CMA_Settings::getOption(CMA_Settings::OPTION_QUESTION_DESCRIPTION_OPTIONAL) && empty($content) )
        {
            $errors[] = CMA::__('Content cannot be empty');
        }
        if (($badWord = CMA_BadWords::filterIfEnabled($content)) !== false) {
        	$errors[] = sprintf(CMA_Labels::getLocalized('msg_content_includes_bad_word'), $badWord);
        }

        if( empty($errors) )
        {
            if( $this->getAuthorId() == $userId )
            {
                $update = array('ID' => $this->post->ID, 'post_content' => $content, 'post_title' => $title);
                if( !wp_update_post($update) )
                {
                    $errors[] = 'Failed to update the question.';
                }
                
            }
            else
            {
                $errors[] = 'Cannot edit question of another author.';
            }
        }

        if( !empty($errors) )
        {
            throw new Exception(serialize($errors));
        }
        else
        {
        	do_action('cma_question_update_after', $this);
            return true;
        }
    }


    /**
     * Get the thread's permalink.
     * 
     * @param array $query Query args.
     * @param string $append Append a string to the URL path.
     * @return string
     */
    public function getPermalink(array $query = array(), $backlink = false, $append = '') {
    	
    	$result = get_permalink($this->getId());
    	if( strlen($append) > 0 ) {
    		$result .= $append;
    	}
    	
    	$query = CMA_Thread::sanitize_array($query, array(
    		'ajax' => array('int', null),
    		'post_id' => array('int', null),
    		'sort' => array('string', null),
    		CMA_AnswerController::PARAM_EDIT_ANSWER_ID => array('int', null),
    		CMA_AnswerController::PARAM_EDIT_QUESTION_ID => array('int', null),
    		CMA_AnswerController::PARAM_RESOLVE_QUESTION_ID => array('int', null),
    	));
    	
    	if ($backlink === true AND CMA_Settings::getOption(CMA_Settings::OPTION_BACKLINK_PARAM_ENABLED)) {
    		$query['backlink'] = base64_encode($_SERVER['REQUEST_URI']);
    	}
        
    	$query = array_filter($query);
        if (!empty($query)) $result = add_query_arg(urlencode_deep($query), $result);
        
        return $result;
        
    }
    
    
    public function getPermalinkWithBacklink(array $query = array(), $append = '') {
    	return $this->getPermalink($query, true, $append);
    }
    
    
    
    public function getEditURL() {
    	return $this->getPermalinkWithBacklink(array(
    		CMA_AnswerController::PARAM_EDIT_QUESTION_ID => $this->getId()
    	));
    }
    
    
    public function getBackendEditUrl() {
    	return admin_url('post.php?action=edit&post='. $this->getId());
    }
    
    public function getDeleteURL() {
    	return add_query_arg(array(
    		'cma-action' => 'delete',
    		'nonce' => wp_create_nonce('cma_thread_delete'),
    		'backlink' => urlencode(base64_encode(CMA::getReferer())),
    	), $this->getPermalink());
    }
    
    public function getFollowUrl($backlink = null) {
    	if (is_null($backlink) AND $backlink !== false) {
    		$backlink = $_SERVER['REQUEST_URI'];
    	}
    	return get_permalink($this->getId())
				. sprintf('?cma-action=follow&nonce=%s',
					wp_create_nonce(CMA_AnswerController::NONCE_FOLLOW)
				)
				. ($backlink === false ? '' : '&backlink='. urlencode(base64_encode($backlink)));
    }

    public static function getGravatarLink($userId)
    {
        $user = get_userdata($userId);
        $email = $user->user_email;
        $hash = md5(trim($email));
        $profileLink = (is_ssl() ? 'https://secure' : 'http://www' ) . '.gravatar.com/' . $hash;

        return $profileLink;
    }

    public static function areQuestionAttachmentsAllowed()
    {
    	$ext = CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_FILE_EXTENSIONS);
    	$result = (!empty($ext) AND CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_QUESTIONS_ALLOW));
    	return apply_filters('CMA_areQuestionAttachmentsAllowed', $result);
    }
    
    
    public static function validateUploadNames() {
        if ( !empty($_FILES) && !empty($_FILES['attachment']['name']) ) {
        	foreach ($_FILES['attachment']['name'] as $name) {
	            if (!empty($name) AND !self::checkAttachmentAllowed($name)) {
	            	return false;
	            }
        	}
        }
        return true;
    }
    
    
    /**
     * Checking whether the file extensions is allowed.
     * 
     * @param string $name
     * @return boolean
     */
    public static function checkAttachmentAllowed($name) {
    	$allowed = array_filter(CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_FILE_EXTENSIONS));
    	if (empty($allowed)) return true;
    	preg_match('/\.([a-z0-9]{1,4})$/i', $name, $match);
    	$ext = strtolower(isset($match[1]) ? $match[1] : null);
    	foreach ($allowed as $e) {
    		if (strtolower($e) == $ext) {
    			return true;
    		}
    	}
    	return false;
    }

    /**
     * Checking whether the file size is allowed.
     * 
     * @return boolean
     */
    public static function validateUploadSize()
    {
        if(!empty($_FILES) && !empty($_FILES['attachment']['size']) )
        {
        	$maxFileSize = CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_MAX_SIZE);
        	foreach ($_FILES['attachment']['size'] as $size) {
            	if ($size > $maxFileSize) {
            		return false;
            	}
        	}
        }
        return true;
    }
    
    
    public function addAttachment($attachmentId) {
    	$result = wp_update_post( array(
	    	'ID' => $attachmentId,
	    	'post_parent' => $this->getId(),
	    	'post_status' => 'inherit',
    	));
    	$this->addPostMeta(array(CMA_Thread::$_meta['attachment'] => $attachmentId), false);
    	return $this;
    }
    

    
    
    public function getTagsArray() {
    	$tags = get_the_tags($this->getId());
    	if (empty($tags)) $tags = array();
    	
   		foreach ($tags as &$tag) {
			$tag = $tag->name;
		}
    	
    	return $tags;
    }
    
    
    
    public function getTagsString() {
    	return implode(', ', $this->getTagsArray());
    }

    static public function getTags($id, $ajax = false)
    {
        $content = '';
        if( CMA_Settings::getOption(CMA_Settings::OPTION_TAGS_SWITCH) )
        {
            $content = '<div class="cma-thread-tags">';
            $posttags = get_the_tags($id);
            if( $posttags )
            {
                $content .= 'Tags: <ul class="cma-tags-list">';
                foreach($posttags as $tag)
                {
                    $url = add_query_arg(array('cmatag' => urlencode($tag->slug)), get_post_type_archive_link(CMA_Thread::POST_TYPE));
                    $class = ($ajax ? ' class="ajax_tag"' : '');
                    $content .= sprintf('<li><a href="%s"%s>%s</a></li>', esc_attr($url), $class, esc_html($tag->name));
                }
                $content .= '</ul>';
            }
            $content .= '</div>';
        }
        return $content;
    }
    
    
    public function canEditQuestion($userId = null) {
    	if (empty($userId)) {
    		$userId = get_current_user_id();
    	}
    	if ($this->getAuthorId() == $userId) {
    		if (!$this->isResolved() OR self::canEditResolved()) {
    			return self::checkEditMode($this->getCreationDate('U'));
    		}
    	}
    	return false;
    }
    
    
    
    
    
    
    public function getBestAnswerId() {
    	return $this->getPostMeta(self::$_meta['bestAnswer']);
    }
    
    
    public function getBestAnswer() {
    	if ($id = $this->getBestAnswerId()) {
    		return CMA_Answer::getById($id);
    	}
    }
    
    
    
    public function canUnmarkBestAnswer($userId = null) {
    	if (empty($userId)) {
    		$userId = get_current_user_id();
    	}
    	return (user_can($userId, 'manage_options') OR
    		(CMA_Settings::getOption(CMA_Settings::OPTION_ENABLED_UNMARK_BEST_ANSWER)
    			AND $this->getAuthorId() == $userId)
    	);
    }
    
    
    public function setBestAnswer($answerId) {
    	$this->savePostMeta(array(self::$_meta['bestAnswer'] => $answerId));
    	do_action('cma_thread_set_best_answer', $this);
    	return $this;
    }
    
    
    public function removeNotBestAnswers() {
    	$answers = CMA_Answer::getAnswersByThread($this->getId(), $approved = null);
    	foreach ($answers as $answer) {
    		if (!$answer->isBestAnswer()) {
    			$answer->trash();
    		}
    	}
    }
    
    
    public function unmarkBestAnswer() {
    	if ($answerId = $this->getBestAnswerId()) {
    		$this->deletePostMeta(array(self::$_meta['bestAnswer'] => $answerId));
    	}
    	return $this;
    }
    
    
    public function markAsSpam($value) {
    	$this->savePostMeta(array(self::$_meta['markedAsSpam'] => ($value ? 1 : 0)));
    }
    
    
    public function isMarkedAsSpam() {
    	return (boolean)$this->getPostMeta(self::$_meta['markedAsSpam']);
    }
    
    
    public function canMarkSpam() {
    	return (CMA_Settings::canReportSpam() AND !$this->isMarkedAsSpam());
    }
    
    
    public function canUnmarkSpam() {
    	return (current_user_can('manage_options') AND $this->isMarkedAsSpam());
    }
    
    
    

    public function canMarkFavorite($userId = null) {
    	if (empty($userId)) {
    		$userId = get_current_user_id();
    	}
    	return (CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_MARK_FAVORITE_QUESTIONS) AND !empty($userId));
    }
    
    
    public function setFavorite($favorite, $userId = null) {
    	if (empty($userId)) {
    		$userId = get_current_user_id();
    	}
    	if ($favorite) {
	    	if (!$this->isFavorite($userId)) {
	    		$this->addPostMeta(array(self::$_meta['usersFavorite'] => $userId));
	    	}
    	} else {
    		$this->deletePostMeta(array(self::$_meta['usersFavorite'] => $userId));
    	}
    	return $this;
    }
    
    

    public function getUsersFavorite() {
    	$result = $this->getPostMeta(self::$_meta['usersFavorite'], false);
    	if (empty($result) OR !is_array($result)) {
    		return array();
    	} else {
    		return $result;
    	}
    }
    
    
    public function isFavorite($userId = null) {
    	if (empty($userId)) {
    		$userId = get_current_user_id();
    	}
    	return in_array($userId, $this->getUsersFavorite());
    }
    
    
    
    /**
     * Check whether user can edit content with given creation time.
     * 
     * @param int $time Unix timestamp.
     * @return boolean
     */
    public static function checkEditMode($time) {
    	switch (CMA_Settings::getOption(CMA_Settings::OPTION_EDIT_MODE)) {
    		case CMA_Settings::EDIT_MODE_ANYTIME:
    			return true;
    		case CMA_Settings::EDIT_MODE_DISALLOWED:
    			return false;
    		case CMA_Settings::EDIT_MODE_WITHIN_HOUR:
    			return (time() - $time < 3600);
    		case CMA_Settings::EDIT_MODE_WITHIN_DAY:
    			return (time() - $time < 3600*24);
    	}
    }
    
    
    public function canResolve($userId = null) {
    	if (empty($userId)) {
    		$userId = get_current_user_id();
    	}
    	if (!$this->isResolved()) {
	    	if (user_can($userId, 'manage_options')) return true;
	    	else return (CMA_Settings::getOption(CMA_Settings::OPTION_RESOLVE_THREAD_ENABLED)
	    				AND $this->getAuthorId() == $userId);
    	} else return false;
    }
    
    
    public function canSubscribe($userId = null) {
    	return apply_filters('cma_thread_can_subscribe', self::canBeFollower($userId), $userId, $this->getId());
    }
    
    
    public static function canBeFollower($userId = null) {
    	if (!CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_THREAD_FOLLOWING)) return false;
    	else return CMA_FollowersEngine::canBeFollower($userId);
    }
    
    
    public static function canDisplayNotifyCheckbox($userId = null) {
    	return apply_filters('cma_thread_can_display_notify_checkbox', self::canBeFollower($userId), $userId);
    }
    
    
    public function resolve() {
    	$errors = array();
    	if ($this->canResolve()) {
    		$this->savePostMeta(array(self::$_meta['resolved'] => 1));
    		do_action('cma_thread_resolved', $this);
    	} else {
    		$errors[] = 'You cannot resolve this thread.';
    	}
    	if (!empty($errors)) {
    		throw new Exception(serialize($errors));
    	}
    }
    
    
    public static function getSidebarSettings($key = null) {
    	$settings = get_option(self::OPTION_SIDEBAR_SETTINGS, array());
    	if (is_null($key)) {
    		return $settings;
    	} else if (isset($settings[$key])) {
    		return $settings[$key];
    	}
    }
    
    
    public static function setSidebarSettings($key, $val = null) {
    	if (is_array($key)) {
    		update_option(self::OPTION_SIDEBAR_SETTINGS, $key);
    	} else {
    		$settings = self::getSidebarSettings();
    		$settings[$key] = $val;
    		update_option(self::OPTION_SIDEBAR_SETTINGS, $settings);
    	}
    }
    
    
    public static function showOnlyOwnQuestions() {
    	return (!current_user_can('manage_options')
    		AND CMA_Settings::getOption(CMA_Settings::OPTION_SHOW_ONLY_OWN_QUESTIONS));
    }


    

    public static function units2bytes($str) {
    	$units = array('B', 'K', 'M', 'G', 'T');
    	$unit = preg_replace('/[0-9]/', '', $str);
    	$unitFactor = array_search(strtoupper($unit), $units);
    	if ($unitFactor !== false) {
    		return preg_replace('/[a-z]/i', '', $str) * (1<<10*$unitFactor);
    	}
    }
    

    /**
     * Saninitize array, convert types and filter keys
     * @param array $arr array to be sanitized
     * @param array $descriptors array of descriptors for <code>$arr</code> fields
     * @return array
     * @throws InvalidArgumentException
     */
    public static function sanitize_array(array $arr, array $descriptors)
    {
    	static $mappers = null;
    
    	if($mappers === null)
    	{
    		$boolval = function_exists('boolval') ? 'boolval' : create_function('$b', 'return (boolean) $b;');
    		$arrayval = function_exists('arrayval') ? 'arrayval' : create_function('$b', 'return (array) $b;');
    		$mappers = array(
    				'integer' => 'intval',
    				'int' => 'intval',
    				'double' => 'doubleval',
    				'float' => 'doubleval',
    				'string' => 'strval',
    				'trim' => 'trim',
    				'array' => $arrayval,
    				'boolean' => $boolval,
    				'bool' => $boolval
    		);
    	}
    
    	$result = array();
    
    	foreach($descriptors as $key => $desc)
    	{
    		list($type, $default) = is_array($desc) ? $desc : array((string) $desc, null);
    
    		if($type !== '*' && !array_key_exists($type, $mappers))
    		{
    			throw new InvalidArgumentException();
    		}
    
    		if(array_key_exists($key, $arr))
    		{
    			if($type === '*')
    			{
    				$result[$key] = $arr[$key];
    			}
    			else
    			{
    				$result[$key] = call_user_func($mappers[$type], $arr[$key]);
    			}
    		}
    		else
    		{
    			$result[$key] = $default;
    		}
    	}
    
    	return $result;
    }
    
    
    public static function truncate ($str, $length=10, $trailing='...') {
    	// take off chars for the trailing
    	$length-=mb_strlen($trailing);
    	if (mb_strlen($str)> $length) {
    		// string exceeded length, truncate and add trailing dots
    		return mb_substr($str,0,$length).$trailing;
    	} else {
    		// string was already short enough, return the string
    		$res = $str;
    	}
    	return $res;
    }

    
    public function getViewData() {
    	return array(
    		'question-id' => $this->getId(),
    		'permalink' => $this->getPermalink(),
    		'favorite' => intval($this->isFavorite()),
    		'favorite-enabled' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_MARK_FAVORITE_QUESTIONS)),
    		'favorite-nonce' => wp_create_nonce('cma_favorite_question'),
    		'rating' => $this->getPostRating(),
    		'rating-enabled' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_QUESTION_RATING)),
    		'rating-nonce' => wp_create_nonce('cma_rate_thread_' . $this->getId()),
    		'rating-negative-allowed' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_NEGATIVE_RATING_ALLOWED)),
    		'resolve-enabled' => intval(CMA_Settings::getOption(CMA_Settings::OPTION_RESOLVE_THREAD_ENABLED)),
    		'can-resolve' => intval($this->canResolve()),
    		'can-spam' => intval($this->canMarkSpam()),
    		'spam' => intval($this->isMarkedAsSpam()),
    		'spam-nonce' => wp_create_nonce('cma_report_spam'),
    		'can-delete' => intval($this->canDelete()),
    		'can-subscribe' => intval($this->canSubscribe()),
    		'is-follower' => intval($this->getFollowersEngine()->isFollower()),
    		'backlink' => base64_encode(CMA::getReferer()),
    	);
    }
    
    
    function setCategoryCustomFields($values) {
    	if ($category = $this->getCategory()) {
    		$fields = $category->getCustomFields();
    		foreach ($fields as $i => $fieldName) {
    			if (empty($fieldName)) {
    				$values[$i] = null;
    			}
    			else if (!isset($values[$i])) {
    				$values[$i] = '';
    			}
    			$metaKey = self::$_meta['categoryCustomField'] .'_'. $i;
    			update_post_meta($this->getId(), $metaKey, $values[$i]);
    		}
    	}
    }
    
    
    
    function getCategoryCustomFields() {
    	$result = array();
    	if ($category = $this->getCategory()) {
    		$fields = $category->getCustomFields();
    		foreach ($fields as $i => $fieldName) {
    			$metaKey = self::$_meta['categoryCustomField'] .'_'. $i;
    			$result[$i] = get_post_meta($this->getId(), $metaKey, $single = true);
    		}
    	}
    	return $result;
    }
    
    
    
	public static function filterShortcodes($content) {
    	$whitelist = CMA_Settings::getOption(CMA_Settings::OPTION_SHORTCODES_WHITELIST);
    	if (!empty($whitelist) AND is_array($whitelist)) {
    		$pattern = get_shortcode_regex();
    		if (preg_match_all( '/'. $pattern .'/s', $content, $matches ) AND !empty($matches[2]) AND is_array($matches[2])) {
    			foreach ($matches[2] as $shrotcodeName) {
    				if (!in_array($shrotcodeName, $whitelist)) {
    					$content = str_replace('['. $shrotcodeName, '&lsqb;'. $shrotcodeName, $content);
    					$content = str_replace('[/'. $shrotcodeName, '&lsqb;/'. $shrotcodeName, $content);
    				}
    			}
    		}
    	}
    	return $content;
    }
    
    
    static function getMetaKeys() {
    	return CMA_Thread::$_meta;
    }
    
        

}
