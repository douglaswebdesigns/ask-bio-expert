<?php

class CMA_Comment {
	
	const COMMENT_TYPE = 'cma_comment';
	
	protected $comment;
	protected $answerId;
	
	
	
	public function __construct($comment) {
		$this->comment = (object)$comment;
	}
	

	public static function getById($commentId, $userId = null) {
		if ($comment = get_comment($commentId) AND $comment->comment_type == self::COMMENT_TYPE) {
			if( empty($userId) OR $comment->user_id == $userId ) {
				return new self($comment);
			}
		}
	}
	
	
	
	/**
	 * Get all comments in given thread.
	 * 
	 * @param int $threadId
	 * @return array
	 */
	public static function getCommentsByThread($threadId, $approved = 1) {
		global $wpdb;
		
		$where = '';
		if (!is_null($approved)) {
			$where .= ' AND c.comment_approved = '. intval($approved);
		}
		$sql = $wpdb->prepare("SELECT c.*
				FROM $wpdb->comments c
				WHERE c.comment_post_ID = %d
					AND c.comment_type = %s
					$where
				ORDER BY c.comment_date_gmt ASC",
				$threadId,
				self::COMMENT_TYPE
			);
		$comments = $wpdb->get_results($sql);
		$result = array();
		foreach ($comments as $comment) {
			$result[intval($comment->comment_parent)][] = new self($comment);
		}
		return $result;
	}
	
	
	/**
	 * Create new comment.
	 * 
	 * @param string $content
	 * @param int $userId
	 * @param int $threadId
	 * @param int $answerId (optional)
	 * @throws Exception
	 * @return CMA_Comment
	 */
	public static function create($content, $userId, $threadId, $answerId = null) {
		$user = get_userdata($userId);
		if (empty($userId) OR empty($user)) throw new Exception(CMA::__('Invalid user.'));
		
		$thread = CMA_Thread::getInstance($threadId);
		if (!$thread OR !$thread->isVisible()) throw new Exception(CMA::__('You have no permission to post this comment.'));
		if ($answerId) {
			$answer = CMA_Answer::getById($answerId);
			if (!$answer OR !$answer->isVisible()) throw new Exception(CMA::__('You have no permission to post this comment.'));
		}
		
		$content = str_replace(';)', ':)', strip_tags($content));
		if (empty($content)) throw new Exception(CMA::__('Content cannot be empty'));
		
		if (($badWord = CMA_BadWords::filterIfEnabled($content)) !== false) {
			throw new Exception(sprintf(CMA_Labels::getLocalized('msg_content_includes_bad_word'), $badWord));
		}
		
		$approved = (CMA_Settings::getOption(CMA_Settings::OPTION_COMMENTS_AUTO_APPROVE) || CMA_Thread::isAuthorAutoApproved($userId)) ? 1 : 0;
		$comment = new self(array(
			'comment_post_ID'      => $threadId,
            'comment_author'       => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_author_IP'    => $_SERVER['REMOTE_ADDR'],
            'comment_parent'       => intval($answerId),
            'comment_content'      => apply_filters('comment_text', $content),
            'comment_approved'     => intval($approved),
            'comment_date'         => current_time('mysql'),
            'comment_type'         => self::COMMENT_TYPE,
			'user_id'              => $userId,
		));
		do_action('cma_comment_post_before', $comment);
		if ($comment->save()) {
			do_action('cma_comment_post_after', $comment);
			if ($approved) {
				$comment->sendNotifications();
			} else {
				wp_notify_moderator($comment->getId());
			}
			return $comment;
		} else {
			throw new Exception(CMA::__('Failed to add comment.'));
		}
	}
	
	
	public function setContent($content) {
		$this->comment->comment_content = apply_filters('comment_text', $content);
	}
	
	
	public static function canCreate($userId = null) {
		if (empty($userId)) $userId = CMA::getPostingUserId();
		$access = CMA_Settings::getOption(CMA_Settings::OPTION_POST_COMMENTS_ACCESS);
		switch ($access) {
			case CMA_Settings::ACCESS_USERS:
				return !empty($userId);
			case CMA_Settings::ACCESS_ROLE:
				$user = get_userdata($userId);
				if (empty($userId) OR empty($user)) return false;
				$hasRightRole = array_intersect(CMA_Settings::getOption(CMA_Settings::OPTION_POST_COMMENTS_ACCESS_ROLES), $user->roles);
				return user_can($user, 'manage_options') || !empty($hasRightRole);
			default:
				return (apply_filters('cma_can_post_comments', $access) OR is_user_logged_in());
		}
	}
	
	
	public function canEdit() {
		return ($this->getAuthorId() == get_current_user_id());
	}
	
	public function canDelete() {
		return ($this->getAuthorId() == get_current_user_id());
	}
	
	
	public function getId() {
		return (isset($this->comment->comment_ID) ? $this->comment->comment_ID : null);
	}
	
	public function getAuthorId() {
		return $this->comment->user_id;
	}
	
	
	public function getAuthor() {
		return CMA_Thread::getUser($this->getAuthorId(), $this);
	}
	
	
	public function getContent() {
		return $this->comment->comment_content;
	}
	
	
	public function getDate() {
		return $this->comment->comment_date;
	}
	
	public function getCreationDate($format = '') {
		if( empty($format) )
		{
			$format = get_option('date_format') . ' ' . get_option('time_format');
		}
		return date_i18n($format, strtotime($this->getDate()));
	}
	
	
	public function getAnswerId() {
		return $this->comment->comment_parent;
	}
	
	
	public function getThreadId() {
		return $this->comment->comment_post_ID;
	}
	
	
	public function getQuestionId() {
		return $this->getThreadId();
	}
	
	
	public function isApproved() {
		return ($this->comment->comment_approved == 1);
	}
	
	
	public function isVisible($userId = null) {
		if (is_null($userId)) $userId = CMA::getPostingUserId();
		if (user_can($userId, 'manage_options')) return true;
		return ($this->isApproved() AND $this->getThread()->isVisible($userId));
	}
	
	
	public function trash() {
		return wp_set_comment_status($this->getId(), 'trash');
	}
	
	
	
	
	public function getAuthorLink($simple = false) {
    	if ($user = $this->getAuthor()) {
    		return ($simple ? $user->link : $user->richLink);
    	}
    	else if ($author = $this->getCommentAuthor()) {
    		return $author;
    	} else {
    		return CMA::__('unknown');
    	}
    }
	
	public function save() {
		if ($id = $this->getId()) {
			return wp_update_comment((array)$this->comment);
		} else {
			if ($id = wp_insert_comment((array)$this->comment)) {
				$this->comment->comment_ID = $id;
				return true;
			} else {
				return false;
			}
		}
	}
	

	public function getThread() {
		return CMA_Thread::getInstance($this->getThreadId());
	}
	
	
	public function getPermalink() {
		return $this->getThread()->getPermalink(array(), false, '#cma-comment-id-'. $this->getId());
	}
	
	
	public function getCommentAuthor() {
		return $this->comment->comment_author;
	}
	
	
	public function sendNotifications() {
		
		$thread = $this->getThread();
		$receivers = array();
		
		// Admin notification emails
		if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_COMMENT_ADMIN_NOTIFICATION_ENABLED)) {
			$receivers = array_merge($receivers, CMA_Settings::getOption(CMA_Settings::OPTION_POST_ADMIN_NOTIFICATION_EMAIL));
		}
		
		// User subscribers email
		if (CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_THREAD_FOLLOWING)
				AND CMA_Settings::getOption(CMA_Settings::OPTION_NEW_COMMENT_NOTIFICATION_ENABLED)) {
			$followers = $thread->getFollowersEngine()->getFollowers();
			if (!empty($followers)) {
				foreach($followers as $user_id) {
					if ($user_id != $this->getAuthorId() AND $thread->isVisible($user_id)) {
						$user = get_userdata($user_id);
						if( !empty($user->user_email) ) {
							$receivers[] = $user->user_email;
						}
					}
				}
			}
		}
		
		$receivers = array_filter(array_unique($receivers));
		if (!empty($receivers)) {
			$replace = array(
				'[blogname]' => get_bloginfo('name'),
				'[question_title]' => strip_tags($thread->getTitle()),
				'[question_body]' => strip_tags($thread->getContent()),
				'[comment_link]' => $this->getPermalink(),
				'[comment]' => strip_tags($this->getContent()),
				'[author]' => strip_tags($this->getAuthor()->display_name),
				'[opt_out_url]' => CMA_ThreadNewsletter::getOptOutUrl($this->getThread(), CMA_ThreadNewsletter::TYPE_THREAD),
			);
			$subject = strtr(CMA_Settings::getOption(CMA_Settings::OPTION_NEW_COMMENT_NOTIFICATION_TITLE), $replace);
			$message = strtr(CMA_Settings::getOption(CMA_Settings::OPTION_NEW_COMMENT_NOTIFICATION_CONTENT), $replace);
			
			CMA_Email::send($receivers, $subject, $message);
			
			/* $headers = array();
            foreach($receivers as $email) {
            	$email = trim($email);
            	if (is_email($email)) {
            		$headers[] = ' Bcc: '. $email;
            	}
            }
            
            if (!empty($headers)) wp_mail(null, $subject, $message, $headers); */

		}
		
	}
	
	public function getComment() {
		return $this->comment;
	}
	
	
	static function getMetaKeys() {
		return array();
	}
	
}
