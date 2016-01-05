<?php

class CMA_BuddyPress {

	const COMPONENT = 'cm-answers-pro';
	
	const ACTION_NOTIFICATION_THREAD = 'notification_thread';
	const ACTION_NOTIFICATION_ANSWER = 'notification_answer';
	
	
	static function bootstrap() {
		if (function_exists('bp_is_active')) {
			/*--------------------------------------------------------------------------*/
			/*  Setup Questions in BuddyPress User Profile
			/*--------------------------------------------------------------------------*/
			add_action( 'bp_setup_nav', array(get_class(), 'bp_setup_nav' ));
			add_action( 'cma_question_post_after', array(get_class(), 'thread_posted_activity'), 99);
			add_action( 'cma_answer_post_after', array(get_class(), 'answer_posted_activity'), 99, 2);
			add_filter('post_row_actions', array(__CLASS__, 'adminPostRowActionsFilter'), 10, 2);
			add_filter('comment_row_actions', array(__CLASS__, 'adminCommentRowActionsFilter'), 10, 2);
			add_action( 'bp_setup_globals', array(__CLASS__, 'notifier_setup_globals') );
			
			add_action( 'bp_include', array(__CLASS__, 'bp_include') );
			require_once dirname(__FILE__) . '/BuddyPressGroup.php';
			
		}
	}
	
	
	static function bp_include() {
		require_once dirname(__FILE__) . '/BuddyPressGroup.php';
	}
	
	
	/**
	 * Add navigation bar items.
	 */
	static function bp_setup_nav() {
		bp_core_new_nav_item(array(
			'name' => CMA_Labels::getLocalized('Questions'),
			'slug' => 'questions',
			'position' => 21,
			'show_for_displayed_user' => true,
			'screen_function' => array(get_class(), 'questions_list'),
			'item_css_id' => 'questions',
			'default_subnav_slug' => 'public'
		));
	}
	
	
	/**
	 * BB profile page for the user's questions list.
	 */
	static function questions_list() {
		add_action( 'bp_template_content', array(get_class(), 'profile_questions_loop' ));
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}
	

	/**
	 * User's questions list loop.
	 */
	static function profile_questions_loop() {
// 		global $dwqa_options;
// 		$submit_question_link = get_post_type_archive_link(CMA_Thread::POST_TYPE);
// 		$questions = get_posts(  array(
// 			'posts_per_page' => -1,
// 			'author'		 => bp_displayed_user_id(),
// 			'post_type'	=> CMA_Thread::POST_TYPE
// 		));
		$userId = bp_displayed_user_id();
// 		include CMA_PATH . '/views/frontend/buddypress/user-questions-loop.phtml';
		echo CMA_BaseController::_loadView('buddypress/user-questions-loop', compact('userId'));
	}
	
	

	/*--------------------------------------------------------------------------*/
	/*  Record Activities
	 /*--------------------------------------------------------------------------*/
	
	/**
	 * Create a wall activity for this user after posting a question.
	 * 
	 * @param CMA_Thread $instance
	 */
	static function thread_posted_activity( CMA_Thread $instance ) {
	
		if (!$instance->isPublished()) return;
		
		$post = $instance->getPost();
		$user_id = $instance->getAuthorId();
		$permalink = get_permalink($instance->getId());
		
		$action = sprintf(
			CMA::__( '%s asked a new question: %s' ),
			bp_core_get_userlink( $user_id ),
			sprintf('<a href="%s">%s</a>',
				esc_attr($permalink),
				esc_html($instance->getTitle())
			)
		);
		
		$content = $instance->getLightContent();
	
		if (function_exists('bp_activity_add')) bp_activity_add( array(
			'action' => $action,
			'content' => $content,
			'component' => self::COMPONENT,
			'type' => 'thread_posted',
			'primary_link' => $permalink,
			'user_id' => $user_id,
			'item_id' => $instance->getId(),
		));
		
		if ($category = $instance->getCategory()) {
			$groups = self::getRelatedGroups(array($category->getId(), $category->getParentId()));
			if ($groups) {
				foreach ($groups as $groupId) {
					if (function_exists('groups_record_activity')) groups_record_activity( array(
						'action' => $action,
						'content' => $content,
						'type' => 'new_groupblog_post',
						'primary_link' => $permalink,
						'item_id' => $groupId,
						'secondary_item_id' => $instance->getAuthorId(),
						'hide_sitewide' => 1,
					));
				}
			}
		}
		
	}
	
	
	
	static function getRelatedGroups(array $categoriesIds) {
		global $wpdb;
		$categoriesIds = array_unique(array_filter($categoriesIds));
		$sql = $wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s AND option_value IN (". implode(',', $categoriesIds) .")",
			CMA_Settings::OPTION_BP_RELATED_CATEGORY .'_%'
		);
		$results = $wpdb->get_col($sql);
		$groups = array();
		foreach ($results as $col) {
			$col = explode('_', $col);
			$groups[] = array_pop($col);
		}
		return $groups;
	}
	

	/**
	 * Create a wall activity for this user after posting an answer.
	 *
	 * @param CMA_Thread $instance
	 * @param CMA_Answer $answer
	 */
	static function answer_posted_activity( CMA_Thread $instance, CMA_Answer $answer ) {

		if (!$instance->isPublished()) return;
		else if (!$answer->isApproved() OR $answer->isPrivate()) return;
		
		$post = $instance->getPost();
		$user_id = $answer->getAuthorId();
		$permalink = $answer->getPermalink();
		
		$action = sprintf(
			CMA::__( '%s answered to the question "%s"' ),
			bp_core_get_userlink( $user_id ),
			sprintf('<a href="%s">%s</a>',
				esc_attr($permalink),
				esc_html($instance->getTitle())
			)
		);
		
		$content = CMA_Thread::lightContent($answer->getContent());
		
		if (function_exists('bp_activity_add')) bp_activity_add( array(
			'action' => $action,
			'content' => $content,
			'component' => self::COMPONENT,
			'type' => 'answer_posted',
			'primary_link' => $permalink,
			'user_id' => $user_id,
			'item_id' => $answer->getId(),
		));
		
		if ($category = $instance->getCategory()) {
			$groups = self::getRelatedGroups(array($category->getId(), $category->getParentId()));
			if ($groups) {
				foreach ($groups as $groupId) {
					if (function_exists('groups_record_activity')) groups_record_activity( array(
						'action' => $action,
						'content' => $content,
						'type' => 'new_groupblog_post',
						'primary_link' => $permalink,
						'item_id' => $groupId,
						'secondary_item_id' => $answer->getAuthorId(),
						'hide_sitewide' => 0,
					));
				}
			}
		}
	}
	
	
	public static function adminPostRowActionsFilter($actions, $post) {
		if ($post->post_type == CMA_Thread::POST_TYPE AND $thread = CMA_Thread::getInstance($post->ID)) {
			$page = CMA_BaseController::ADMIN_BP_NOTIFY;
			$actions['bp_notify_all'] = self::createNotifyLink('post_id', $thread->getId());
		}
		return $actions;
	}
	
	
	public static function adminCommentRowActionsFilter($actions, $comment) {
		if ($comment->comment_type == CMA_Answer::COMMENT_TYPE AND $answer = CMA_Answer::getById($comment->comment_ID)) {
			$page = CMA_BaseController::ADMIN_BP_NOTIFY;
			$actions['bp_notify_all'] = self::createNotifyLink('comment_id', $answer->getId());
		}
		return $actions;
	}
	
	
	static function notifier_setup_globals() {
	    global $bp, $current_blog;
	    $bp->cma_notifier = new stdClass();
	    $bp->cma_notifier->id = 'cma_notifier';
	    $bp->cma_notifier->slug = 'cma_notifier';
	    $bp->cma_notifier->notification_callback = array(__CLASS__, 'formatNotification');
	    $bp->active_components[$bp->cma_notifier->id] = $bp->cma_notifier->id;
		
	    do_action( 'notifier_setup_globals' );
	}
	
	

	public static function formatNotification( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
		$text = $link = null;
		switch ($action) {
			case self::ACTION_NOTIFICATION_THREAD:
				if ($thread = CMA_Thread::getInstance($item_id)) {
					$text = CMA_Labels::getLocalized('Question') .': '. sprintf('<a href="%s">%s</a>',
						esc_attr($thread->getPermalink()),
						esc_html($thread->getTitle()));
					$link = $thread->getPermalink();
				}
				break;
			case self::ACTION_NOTIFICATION_ANSWER:
				if ($answer = CMA_Answer::getById($item_id) AND $thread = $answer->getThread()) {
					$text = sprintf(CMA::__('Answer in thread %s'), sprintf('<a href="%s">%s</a>',
						esc_attr($thread->getPermalink()),
						esc_html($thread->getTitle())
					)) .': '. sprintf('<a href="%s">%s</a>',
						esc_attr($answer->getPermalink()),
						esc_html($answer->getExcerpt()));
					$link = $answer->getPermalink();
				}
				break;
		}
		
		if (!is_null($text)) {
			if ('string' == $format) {
				return $text;
			} else {
				return array('text' => strip_tags($text), 'link' => $link);
			}
		}
		
	}
	
	
	protected static function createNotifyLink($param, $id) {
		$page = CMA_BaseController::ADMIN_BP_NOTIFY;
		$url = sprintf('./?page=%s&%s=%d&nonce=%s&backlink=%s',
			urlencode($page),
			urlencode($param),
			urlencode($id),
			urlencode(wp_create_nonce($page)),
			urlencode(base64_encode($_SERVER['REQUEST_URI']))
		);
		return sprintf('<a href="%s" title="%s">%s</a>',
			esc_attr($url),
			esc_attr(CMA::__('Notify all users using BuddyPress notifications')),
			esc_html(CMA::__('Notify all BP users'))
		);
	}
	
	
	public static function notifyAllUsers() {
		global $wpdb;
		
		if (!empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], CMA_BaseController::ADMIN_BP_NOTIFY)) {
			
			$usersIds = $wpdb->get_col("SELECT ID FROM $wpdb->users");
			
			if (!empty($_GET['post_id']) AND $thread = CMA_Thread::getInstance($_GET['post_id'])) {
				
				$notification = array(
					'item_id'           => $thread->getId(),
					'secondary_item_id' => 0,
					'component_name'    => 'cma_notifier',
					'component_action'  => self::ACTION_NOTIFICATION_THREAD,
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
					'allow_duplicate'   => true,
				);
				
				foreach ($usersIds as $userId) {
					if ($thread->isVisible($userId)) {
						$notification['user_id'] = $userId;
						bp_notifications_add_notification($notification);
					}
				}
				
			}
			
			if (!empty($_GET['comment_id']) AND $answer = CMA_Answer::getById($_GET['comment_id'])) {
				
				$notification = array(
					'item_id'           => $answer->getId(),
					'secondary_item_id' => 0,
					'component_name'    => 'cma_notifier',
					'component_action'  => self::ACTION_NOTIFICATION_ANSWER,
					'date_notified'     => bp_core_current_time(),
					'is_new'            => 1,
					'allow_duplicate'   => true,
				);
				
				foreach ($usersIds as $userId) {
					if ($answer->isVisible($userId)) {
						$notification['user_id'] = $userId;
						bp_notifications_add_notification($notification);
					}
				}
				
			}
			
		}
		
		wp_safe_redirect(CMA::getReferer());
		exit;
		
	}
	
	
}


