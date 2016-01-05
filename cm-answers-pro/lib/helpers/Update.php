<?php

class CMA_Update {
	
	const OPTION_UPDATED = 'cma_pro_updated_version_';
	
	protected static $versions = array('2.4.13', '2.5.6', '2.5.9', '2.6.8', '2.9.3');
	

	public static function run() {
		global $wpdb;
		
		self::old_update();
		
		foreach (self::$versions as $version) {
			$v = strtr($version, '.', '_');
			if (!get_option(self::OPTION_UPDATED . $v)) {
				$methodName = 'update_'. $v;
				if (method_exists(__CLASS__, $methodName)) {
					call_user_func(array(__CLASS__, $methodName));
				}
				add_option(self::OPTION_UPDATED . $v, 1);
			}
		}
		
		// Update current version
		$oldVersion = get_option(CMA::OPTION_VERSION);
		$currentVersion = CMA::version();
		if ($oldVersion != $currentVersion) {
			update_option(CMA::OPTION_VERSION, $currentVersion);
		}

	}
	
	
	public static function old_update() {
		global $wpdb;
        
        $oldVersion = get_option(CMA::OPTION_VERSION);
        if (empty($oldVersion)) return;
        $currentVersion = CMA::version();
        if( empty($oldVersion) OR version_compare($oldVersion, $currentVersion, '<') )
        {
            update_option(CMA::OPTION_VERSION, $currentVersion);

            if (version_compare($oldVersion, '2.1.6', '<')) {
				// Update comment_type
	            $commentsIds = $wpdb->get_col($wpdb->prepare("SELECT `$wpdb->comments`.`comment_ID`
					FROM `$wpdb->comments`
					LEFT JOIN `$wpdb->posts` ON `$wpdb->comments`.`comment_post_ID` = `$wpdb->posts`.`ID`
					WHERE `$wpdb->posts`.`post_type` = %s AND `$wpdb->comments`.`comment_type` = ''", CMA_Thread::POST_TYPE));
	            $data = array('comment_type' => CMA_Answer::COMMENT_TYPE);
	            foreach($commentsIds as $comment_ID)
	            {
	                $rval = $wpdb->update($wpdb->comments, $data, compact('comment_ID'));
	            }
            }
            
            // Update new post meta: question+answers votes count
            if (version_compare($oldVersion, '2.1.7', '<')) {
	            $posts = get_posts(array('post_type' => CMA_Thread::POST_TYPE));
	            foreach ($posts as $post) {
	            	$thread = CMA_Thread::getInstance($post->ID);
	            	update_post_meta($post->ID, CMA_Thread::$_meta['votes_question'], 0);
	            	$thread->updateRatingCache();
	            }
            }
            
            // Update users counters
            if (version_compare($oldVersion, '2.1.9', '<=')) {
            	CMA_Thread::updateAllQA();
            }
            
            if (version_compare($oldVersion, '2.1.13', '<=')) {
            	// Create logs records for old posts
            	$posts = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = %s", CMA_Thread::POST_TYPE));
            	$postsLog = new CMA_QuestionPostLog;
            	foreach ($posts as $post) {
            		$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '. $postsLog->getMetaTableName() .' m
            				JOIN '. $postsLog->getTableName() .' l ON l.id = m.log_id
            				WHERE m.meta_name = %s AND m.meta_value = %s AND l.log_type = %s',
            				CMA_QuestionPostLog::META_QUESTION_ID,
            				(string)$post->ID,
            				CMA_QuestionPostLog::LOG_TYPE));
            		if ($count == 0) {
	            		$postsLog->create(array(
	            			CMA_Log::FIELD_CREATED => $post->post_date,
	            			CMA_Log::FIELD_USER_ID => $post->post_author,
	            			CMA_Log::FIELD_IP_ADDR => NULL,
	            		), array(
	            			CMA_QuestionPostLog::META_QUESTION_ID => $post->ID,
	            		));
            		}
            	}
            	// Create logs records for old comments
            	$answers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->comments WHERE comment_type = %s", CMA_Answer::COMMENT_TYPE));
            	$answersLog = new CMA_AnswerPostLog();
            	foreach ($answers as $answer) {
            		$count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '. $answersLog->getMetaTableName() .' m
            			JOIN '. $answersLog->getTableName() .' l ON l.id = m.log_id
            			WHERE m.meta_name = %s AND m.meta_value = %s AND l.log_type = %s',
            			CMA_QuestionPostLog::META_QUESTION_ID,
            			(string)$answer->comment_ID,
            			CMA_AnswerPostLog::LOG_TYPE));
            		if ($count == 0) {
	            		$postsLog->create(array(
	            			CMA_Log::FIELD_CREATED => $answer->comment_date,
	            			CMA_Log::FIELD_USER_ID => $answer->user_id,
	            			CMA_Log::FIELD_IP_ADDR => NULL,
	            		), array(
	            			CMA_AnswerPostLog::META_QUESTION_ID => $answer->comment_post_ID,
	            			CMA_AnswerPostLog::META_ANSWER_ID => $answer->comment_ID,
	            		));
            		}
            	}
            }
            
            if (version_compare($oldVersion, '2.2.2', '<=')) {
            	// Votes for questions
            	$metaKeys = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key = %s", CMA_Thread::$_meta['usersRated']));
            	foreach ($metaKeys as $mk) {
            		$value = @unserialize($mk->meta_value);
            		if (is_array($value)) {
            			$value = array_filter($value);
            			foreach ($value as $userId) {
            				add_post_meta($mk->post_id, CMA_Thread::$_meta['usersRated'], $userId, $uniq = false);
            			}
            			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_id = %d", $mk->meta_id));
            		}
            	}
            	// Votes for answers
            	$metaKeys = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->commentmeta WHERE meta_key = %s", CMA_Answer::META_USERS_RATED));
            	foreach ($metaKeys as $mk) {
            		$value = @unserialize($mk->meta_value);
            		if (is_array($value)) {
            			$value = array_filter($value);
            			foreach ($value as $userId) {
            				add_comment_meta($mk->comment_id, CMA_Answer::META_USERS_RATED, $userId, $uniq = false);
            			}
            			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->commentmeta WHERE meta_id = %d", $mk->meta_id));
            		}
            	}
            }
            
            
        }
	}


	public static function update_2_4_13() {
		global $wpdb;
		
		// Comment rating
		$commentsIds = array();
		if (CMA_Settings::getOption(CMA_Settings::OPTION_NEGATIVE_RATING_ALLOWED)) {
			// Copy comments' ratings to the rating handicap fields.
			$ratings = $wpdb->get_results($wpdb->prepare("SELECT m.comment_id, m.meta_value, c.comment_post_ID
				FROM $wpdb->commentmeta m
				INNER JOIN $wpdb->comments c ON m.comment_id = c.comment_ID
				WHERE m.meta_key = %s", CMA_Thread::$_meta['rating']), ARRAY_A);
			foreach ($ratings as $record) {
				add_comment_meta($record['comment_id'], CMA_Answer::META_USER_RATING_HANDICAP, $record['meta_value'], $unique = true);
				$commentsIds[$record['comment_id']] = $record['comment_id'];
			}
		} else {
			// Only positive ratings - move voters to the positive voters meta
			$voters = $wpdb->get_results($wpdb->prepare("SELECT m.comment_id, m.meta_value, c.comment_post_ID
				FROM $wpdb->commentmeta m
				INNER JOIN $wpdb->comments c ON m.comment_id = c.comment_ID
				WHERE m.meta_key = %s", CMA_Answer::META_USERS_RATED), ARRAY_A);
			foreach ($voters as $record) {
				$commentRatingPositive = get_comment_meta($record['comment_id'], CMA_Answer::META_USER_RATING_POSITIVE, $single = false);
				if (!in_array($record['meta_value'], $commentRatingPositive)) {
					add_comment_meta($record['comment_id'], CMA_Answer::META_USER_RATING_POSITIVE, $record['meta_value'], $unique = false);
				}
				$commentsIds[$record['comment_id']] = $record['comment_id'];
			}
		}
		
		// ----------------------------------------------------
		
		// Post rating
		$postsIds = array();
		if (CMA_Settings::getOption(CMA_Settings::OPTION_NEGATIVE_RATING_ALLOWED)) {
			// Copy posts' ratings to the rating handicap fields.
			$ratings = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta
				WHERE meta_key = %s", CMA_Thread::$_meta['rating']), ARRAY_A);
			foreach ($ratings as $record) {
				add_post_meta($record['post_id'], CMA_Thread::$_meta['userRatingHandicap'], $record['meta_value'], $unique = true);
				$postsIds[$record['post_id']] = $record['post_id'];
			}
		} else {
			// Only positive ratings - copy voters to the positive voters meta
			$voters = $wpdb->get_results($wpdb->prepare("SELECT post_id, meta_value FROM $wpdb->postmeta
				WHERE meta_key = %s", CMA_Thread::$_meta['usersRated']), ARRAY_A);
			foreach ($voters as $record) {
				$postRatingPositive = get_post_meta($record['post_id'], CMA_Thread::$_meta['userRatingPositive'], $single = false);
				if (!in_array($record['meta_value'], $postRatingPositive)) {
					add_post_meta($record['post_id'], CMA_Thread::$_meta['userRatingPositive'], $record['meta_value'], $unique = false);
				}
				$postsIds[$record['post_id']] = $record['post_id'];
			}
		}

		// ----------------------------------------------------
		
		// Update comments counter cache
		foreach ($commentsIds as $commentId) {
			if ($answer = CMA_Answer::getById($commentId)) {
				$answer->updateRatingCache();
			}
		}
		
		// Update posts counter cache
		foreach ($postsIds as $postId) {
			if ($thread = CMA_Thread::getInstance($postId)) {
				$thread->updateRatingCache();
			}
		}
		
		
	}
	
	
	public static function update_2_5_6() {
		global $wpdb;
		
		// Create views counter post meta for non-existing
		$ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s", CMA_Thread::POST_TYPE));
		foreach ($ids as $id) {
			if (!get_post_meta($id, CMA_Thread::$_meta['views'], $single = true)) {
				update_post_meta($id, CMA_Thread::$_meta['views'], 0);
			}
		}
		
	}
	
	
	public static function update_2_5_9() {
		global $wpdb;
		$oldVersion = get_option(CMA::OPTION_VERSION);
		$options = array(CMA_Settings::OPTION_THREAD_PAGE_TEMPLATE, CMA_Settings::OPTION_INDEX_PAGE_TEMPLATE);
		foreach ($options as $option) {
			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name = %s", $option));
			if ($count == 0) {
				if (!empty($oldVersion)) { // not first installation
					// set old default value '0'
					update_option($option, '0');
				} else {
					// first installation - leave blank to use new default page.php
				}
			} else {
				// option exists, do not touch
			}
		}
	}
	
	
	public static function update_2_6_8() {
		$oldVersion = get_option(CMA::OPTION_VERSION);
		if (!empty($oldVersion)) {
			update_option(CMA_Settings::OPTION_LOGS_ENABLED, '1');
		}
	}
	
	
	public static function update_2_9_3() {
		if ($questionsTitle = get_option('cma_questions_title')) {
			CMA_Labels::setLabel('index_page_title', $questionsTitle);
		}
	}


}
