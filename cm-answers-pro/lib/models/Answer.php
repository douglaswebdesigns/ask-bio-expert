<?php

class CMA_Answer {
	
	const COMMENT_TYPE = 'cma_answer';
	
	const ORDER_BY_VOTES = 'votes';
	const ORDER_BY_DATE = 'newest';
	
	const META_RATING = '_rating';
	const META_USERS_RATED = '_users_rated';
	const META_USER_RATING_POSITIVE = 'cma_user_rating_positive';
	const META_USER_RATING_NEGATIVE = 'cma_user_rating_negative';
	const META_USER_RATING_HANDICAP = 'cma_user_rating_handicap';
	const META_VOTE_IP = 'cma_vote_ip';
	const META_VOTE_UA = 'cma_vote_ua';
	const META_VOTE_TIME = 'cma_vote_time';
	const META_VOTE_COOKIE = 'cma_vote_cookie';
	const META_MARKED_AS_SPAM = 'CMA_marked_as_spam';
	const META_ATTACHMENT = 'CMA_answer_attachment';
	const META_PRIVATE = 'CMA_private_answer';
	
	
	protected $comment;
	
	public function __construct($comment) {
		$this->comment = (object)$comment;
	}
	
	
	public static function getById($commentId, $userId = null) {
		if ($comment = get_comment($commentId) AND $comment->comment_type == self::COMMENT_TYPE) {
			if( empty($userId) OR $comment->user_id == $userId ) {
				$answer = new self($comment);
				if ($answer->getThread()) {
					return $answer;
				}
			}
		}
	}
	
	public function trash() {
		return wp_trash_comment($this->getId());
	} 
	
	
	
	public static function getAnswersByThread($threadId, $approved = true, $orderby = self::ORDER_BY_DATE, $sort = null, $limit = null) {
		global $wpdb;
		
		if (is_null($orderby)) {
			$orderby = self::getOrderBy();
		}
		if (!is_null($sort) AND strtolower($sort) != 'asc') $sort = 'desc';
		
		$join = '';
		if( $orderby == CMA_Answer::ORDER_BY_VOTES ) {
			$join = $wpdb->prepare("LEFT JOIN $wpdb->commentmeta cmv ON cmv.comment_ID = c.comment_ID AND cmv.meta_key = %s", self::META_RATING);
			$order = ' ORDER BY cmv.meta_value';
		} else {
			$order = 'ORDER BY c.comment_date_gmt';
		}
		$order .= ' ' . (is_null($sort) ? (CMA_Settings::getOption(CMA_Settings::OPTION_ANSWER_SORTING_DESC) ? 'DESC' : 'ASC') : $sort);
		
		$limitPart = '';
		if (!is_null($limit)) {
			$limitPart = ' LIMIT '. $limit;
		}
		
		$where = '';
		if (is_bool($approved)) {
			$where .= ' AND c.comment_approved = '. intval($approved);
		}
		
		$comments = $wpdb->get_results($wpdb->prepare("SELECT c.* FROM $wpdb->comments c
				". $join ."
				WHERE c.comment_post_ID = %d
					AND c.comment_type = %s
					$where
				$order
				$limitPart",
			$threadId,
			self::COMMENT_TYPE
		));
		$result = array();
		foreach ($comments as $comment) {
				$result[] = new self($comment);
		}
		return $result;
	}
	
	
	public static function countForUser($userId, $approved = null, $onlyVisible = true) {
		global $wpdb;
		if( !$userId ) return 0;
		
		$where = '';
		if (!is_null($approved)) {
			$where .= ' AND c.comment_approved = '. intval($approved);
		}
		
		if ($onlyVisible) {
			if ($userId != get_current_user_id()) {
				$where .= ' AND (cmpa.meta_value IS NULL OR cmpa.meta_value <> 1)';
			}
			if (CMA_Category::isAnyCategoryResticted()) {
				$where .= ' AND (c.comment_post_id IN ('. CMA_Thread::getCategoryAccessFilterSubquery() .')
	    					OR c.comment_post_id NOT IN ('. CMA_Thread::getCategorizedThreadIdsSubquery() .'))';
			}
		}
		
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT c.comment_id) FROM $wpdb->comments c
			INNER JOIN $wpdb->posts p ON p.ID = c.comment_post_ID
			LEFT JOIN $wpdb->commentmeta cmpa ON c.comment_id = cmpa.comment_id AND cmpa.meta_key = %s
			WHERE c.user_id = %d
				AND c.comment_type = %s",
				self::META_PRIVATE,
				$userId,
				self::COMMENT_TYPE)
			. $where);
		
	}
	
	
	public static function getByUser($user_id, $approved = null, $limit = -1, $page = 1, $onlyVisible = true) {
		global $wpdb;
		
		if( !$user_id ) return array();
		
		$limitPart = '';
		if( $limit > 1 ) {
			$limitPart = 'LIMIT '. intval($limit) .' OFFSET '. intval($limit * ($page-1));
		}
		
		$where = '';
		if (!is_null($approved)) {
			$where .= ' AND c.comment_approved = '. intval($approved);
		}
		
		if ($onlyVisible) {
			if ($user_id != get_current_user_id()) {
				$where .= ' AND (cmpa.meta_value IS NULL OR cmpa.meta_value <> 1)';
			}
			if (CMA_Category::isAnyCategoryResticted()) {
				$where .= ' AND (c.comment_post_id IN ('. CMA_Thread::getCategoryAccessFilterSubquery() .')
	    					OR c.comment_post_id NOT IN ('. CMA_Thread::getCategorizedThreadIdsSubquery() .'))';
			}
		}
		
		$query = $wpdb->prepare("SELECT c.* FROM $wpdb->comments c
				INNER JOIN $wpdb->posts p ON p.ID = c.comment_post_ID
				LEFT JOIN $wpdb->commentmeta cmpa ON c.comment_id = cmpa.comment_id AND cmpa.meta_key = %s
				WHERE user_id = %d
				AND c.comment_type = %s",
				self::META_PRIVATE,
				$user_id,
				self::COMMENT_TYPE
		) . " $where ORDER BY comment_id DESC $limitPart";
		
		$comments = $wpdb->get_results($query);
		
		$result = array();
		foreach($comments as $comment) {
			$result[] = new self($comment);
		}
		return $result;
	}
	
	
	public function getId() {
		return (isset($this->comment->comment_ID) ? $this->comment->comment_ID : null);
	}
	
	
	public function isApproved() {
		return ($this->comment->comment_approved == 1);
	}
	
	public function getThreadId() {
		return $this->comment->comment_post_ID;
	}
	
	public function getQuestionId() {
		return $this->getThreadId();
	}
	
	
	public function getStatus() {
		return CMA::__($this->isApproved() ? 'approved' : 'pending');
	}
	

	public function markAsSpam($value) {
		update_comment_meta($this->getId(), self::META_MARKED_AS_SPAM, ($value ? 1 : 0));
	}
	
	public function canUnmarkSpam() {
    	return (current_user_can('manage_options') AND $this->isMarkedAsSpam());
    }
	
	public function isMarkedAsSpam() {
		return (boolean)get_comment_meta($this->getId(), self::META_MARKED_AS_SPAM, true);
	}
	
	public function getPermalink(array $query = array(), $backlink = false, $append = '') {
		$append .= '#answer-' . $this->getId();
		return $this->getThread()->getPermalink($query, $backlink, $append);
	}
	
	public function getPermalinkWithBacklink(array $query = array(), $append = '') {
		return $this->getPermalink($query, true, $append);
	}
	
	public function getAuthorId() {
		return $this->comment->user_id;
	}
	
	public function getAuthorIP() {
		return $this->comment->comment_author_IP;
	}
	
	public function getCommentAuthor() {
		return $this->comment->comment_author;
	}
	
	public function getContent() {
		return $this->comment->comment_content;
	}
	
	
	public function getExcerpt() {
		return get_comment_excerpt($this->getId());
	}
	
	
	public function updateContent($content, $userId) {

		$errors = array();
		$content = CMA_Thread::contentFilter($content, $userId);

		if( empty($content) ) {
			$errors[] = CMA::__('Content cannot be empty');
		} else {
			if( !$this->saveContent($content) ) {
				$errors[] = 'Failed to update the answer.';
			}
        }

        if( !empty($errors) ) {
            throw new Exception(serialize($errors));
        } else {
            return $this;
        }
        
	}
	
	
	public function isPrivate() {
		return get_comment_meta($this->getId(), self::META_PRIVATE, $single = true);
	}
	
	
	public function isVisible($userId = null) {
		if (is_null($userId)) $userId = CMA::getPostingUserId();
		if (user_can($userId, 'manage_options')) return true;
		
		if ($this->isApproved() AND $this->getThread()->isVisible($userId)) {
			if ($userId != $this->getThread()->getAuthorId()) {
				if ($userId != $this->getAuthorId()) {
					switch (CMA_Settings::getOption(CMA_Settings::OPTION_ACCESS_VIEW_ANSWERS)) {
						case CMA_Settings::ACCESS_EVERYONE:
							break;
						case CMA_Settings::ACCESS_USERS:
							if (!is_user_logged_in()) return false;
							break;
						case CMA_Settings::ACCESS_AUTHOR:
							return false;
							break;
						case CMA_Settings::ACCESS_ROLE:
							if ($userId AND $user = get_userdata($userId)) {
								$accessRoles = CMA_Settings::getOption(CMA_Settings::OPTION_ACCESS_VIEW_ANSWERS_ROLES);
								$common = array_intersect($user->roles, $accessRoles);
								if (empty($common)) {
									return false;
								}
							} else {
								return false;
							}
							break;
					}
					return (!$this->isPrivate());
				} else {
					return true;
				}
			} else {
				return true;
			}
		}

	}
	
	
	public function setPrivate($private) {
		update_comment_meta($this->getId(), self::META_PRIVATE, intval($private));
		return $this;
	}
	
	
	public function saveContent($content) {
		global $wpdb;
		if ($content == $this->comment->comment_content) {
			return true;
		} else {
			$this->comment->comment_content = $content;
			$rval = $wpdb->update($wpdb->comments, (array)$this->comment, array('comment_ID' => $this->getId()));
			clean_comment_cache($this->getId());
			wp_update_comment_count($this->getThreadId());
			return $rval;
		}
	}
	
	
	
	public function getEditURL() {
		return $this->getThread()->getPermalinkWithBacklink(array(
			CMA_AnswerController::PARAM_EDIT_ANSWER_ID => $this->getId(),
		));
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
	
	public function canEdit($userId = null) {
		if (empty($userId)) {
			$userId = CMA::getPostingUserId();
		}
		if ($this->getAuthorId() == $userId) {
			$thread = CMA_Thread::getInstance($this->getThreadId());
			if (!$thread->isResolved() OR CMA_Thread::canEditResolved()) {
				return CMA_Thread::checkEditMode(strtotime($this->getDate()));
			}
		}
		return false;
	}
	
	public function getRating() {
		return intval(get_comment_meta($this->getId(), self::META_RATING, true));
	}
	
	
	public function getAuthor() {
		return CMA_Thread::getUser($this->getAuthorId(), $this);
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
    	}
    	else if ($author = $this->getCommentAuthor()) {
    		return $author;
    	} else {
    		return CMA::__('unknown');
    	}
    }
	
	
	public function getThread() {
		return CMA_Thread::getInstance($this->getThreadId());
	}
	
	
	public function getQuestion() {
		return $this->getThread();
	}
	
	
	
	public function addAttachment($attachmentId) {
		wp_update_post( array(
			'ID' => $attachmentId,
			'post_parent' => $this->getThreadId(),
			'post_status' => 'inherit',
		));
		add_comment_meta($this->getId(), CMA_Answer::META_ATTACHMENT, $attachmentId, false);
		return $this;
	}
	
	
	public function getAttachments() {
		return CMA_AnswerAttachment::selectForAnswer($this);
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
	
	
	
	public function getVoters() {
    	$voters = array_merge(
    		get_comment_meta($this->getId(), self::META_USER_RATING_POSITIVE, $single = false),
    		get_comment_meta($this->getId(), self::META_USER_RATING_NEGATIVE, $single = false)
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
			return in_array(CMA_Thread::getUserVotingId(), $this->getVoters());
		} else {
			return false;
		}
	}
	
	

	public function voteUp() {
		return $this->_vote($positive = true);
	}
	
	public function voteDown() {
		return $this->_vote($positive = false);
	}
	
	
	protected function _vote($positive) {
		$current = $this->getRating();
		$point = $positive ? 1 : -1;
		$userVotingId = CMA_Thread::getUserVotingId();
    	$metaId = add_comment_meta($this->getId(), $positive ? self::META_USER_RATING_POSITIVE : self::META_USER_RATING_NEGATIVE, $userVotingId, $unique = false);
    	if ($metaId) {
    		add_post_meta($this->getId(), self::META_VOTE_IP .'_'. $metaId, $_SERVER['REMOTE_ADDR']);
    		add_post_meta($this->getId(), self::META_VOTE_UA .'_'. $metaId, $_SERVER['HTTP_USER_AGENT']);
    		add_post_meta($this->getId(), self::META_VOTE_TIME .'_'. $metaId, time());
    		if (!empty($_COOKIE[CMA_Thread::COOKIE_ANONYMOUS_UID])) {
    			add_post_meta($this->getId(), self::META_VOTE_COOKIE .'_'. $metaId, $_COOKIE[CMA_Thread::COOKIE_ANONYMOUS_UID]);
    		}
	    	$this->updateRatingCache();
	    	
	    	if (CMA_Settings::getOption(CMA_Settings::OPTION_LOGS_ENABLED)) {
	    		CMA_AnswerVoteLog::instance()->log($this->getId(), $point);
	    	}
	    	
    	} else $point = 0;
    	return $current + $point;
    }
    
    
    public function updateRatingCache() {
    	
    	$rating = $this->getRatingHandicap() + $this->getRatingPositiveCount() - $this->getRatingNegativeCount();
    	update_comment_meta($this->getId(), self::META_RATING, $rating);
    	
    	$this->getThread()->updateRatingCache();
    }
    
    
	public function getRatingHandicap() {
    	return intval(get_comment_meta($this->getId(), self::META_USER_RATING_HANDICAP, $single = true));
    }
    
	public function getRatingPositiveCount() {
    	return count(get_comment_meta($this->getId(), self::META_USER_RATING_POSITIVE, $single = false));
    }
    
	public function getRatingNegativeCount() {
    	return count(get_comment_meta($this->getId(), self::META_USER_RATING_NEGATIVE, $single = false));
    }
    
	
	
	public static function getOrderBy() {
		return CMA_Settings::getOption(CMA_Settings::OPTION_ANSWER_SORTING_BY);
	}
	
	
	public static function areAnswerAttachmentsAllowed() {
		$ext = CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_FILE_EXTENSIONS);
		$result = (!empty($ext) AND CMA_Settings::getOption(CMA_Settings::OPTION_ATTACHMENTS_ANSWERS_ALLOW));
		return apply_filters('CMA_areAnswerAttachmentsAllowed', $result);
	}
	
	public function getComment() {
		return $this->comment;
	}
	
	
	public function isBestAnswer() {
		return ($this->getThread()->getBestAnswerId() == $this->getId());
	}
	
	
	function getViewData() {
		return array(
			'answer-id' => $this->getId(),
    		'permalink' => $this->getPermalink(),
    		'best-answer' => intval($this->isBestAnswer()),
    		'spam' => intval($this->isMarkedAsSpam()),
    		'private' => intval($this->isPrivate()),
    		'rating' => intval($this->getRating()),
		);
	}
	
	

	public function canMarkBestAnswer($userId = null) {
		if (empty($userId)) {
			$userId = get_current_user_id();
		}
		$thread = $this->getThread();
		return (user_can($userId, 'manage_options') OR
			(CMA_Settings::getOption(CMA_Settings::OPTION_ENABLED_MARK_BEST_ANSWER)
				AND $thread->getAuthorId() == $userId
				AND (CMA_Settings::getOption(CMA_Settings::OPTION_ALLOW_OWN_BEST_ANSWER)
					OR $thread->getAuthorId() != $this->getAuthorId()
				)
			)
		);
	}
	
	
	
	static function getMetaKeys() {
		return array(
			self::META_ATTACHMENT,
			self::META_RATING,
			self::META_MARKED_AS_SPAM,
			self::META_PRIVATE,
			self::META_USER_RATING_HANDICAP,
			self::META_USER_RATING_NEGATIVE,
			self::META_USER_RATING_POSITIVE,
			self::META_USERS_RATED,
			self::META_VOTE_COOKIE,
			self::META_VOTE_IP,
			self::META_VOTE_TIME,
			self::META_VOTE_UA,
		);
	}
	
}
