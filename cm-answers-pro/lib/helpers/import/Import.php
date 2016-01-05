<?php

abstract class CMA_Import {
	
	function __construct() {
		set_time_limit(3600 * 5);
	}
	
	
	public function importUsers() {
		while ($user = $this->getUser()) {
			$this->importUser($user);
		}
	}
	
	
	public function importQuestions() {
		while ($question = $this->getQuestion()) {
			$this->importQuestion($question);
		}
	}
	

	public function importAnswers() {
		while ($answer = $this->getAnswer()) {
			$this->importAnswer($answer);
		}
	}
	
	
	public function importComments() {
		while ($comment = $this->getComment()) {
			$this->importComment($comment);
		}
	}
	
	

	protected function importUser(array $user) {
		$user['user_pass'] = md5(microtime() . rand(1, 99999999));
		$id = wp_insert_user($user);
		if (is_object($id) AND $id instanceof WP_Error) {
			if (!empty($id->errors['existing_user_email'])) {
				$user['user_email'] = str_replace('@', '+'. rand(1, 9999999) .'@', $user['user_email']);
				$id = wp_insert_user($user);
			}
		}
		if ($id AND is_numeric($id)) {
			update_user_meta($id, 'cma_import_old_id', $user['import_old_id']);
			return $id;
		}
	}
	
	protected function importQuestion(array $question) {
		global $wpdb;
		
		$question['post_status'] = 'publish';
		$question['post_type'] = 'cma_thread';
		$question['post_author'] = $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta
			WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $question['import_old_author_id']);
		
		if (empty($question['post_author'])) {
			throw new Exception(sprintf('Failed to import question with old ID %d. Unknown user with old ID %d.',
				$question['import_old_id'], $question['import_old_author_id']));
		}
		
		$id = wp_insert_post($question, true);
		if ($id AND is_numeric($id)) {
			
			update_post_meta($id, 'cma_import_old_id', $question['import_old_id']);
			
			$instance = CMA_Thread::getInstance($id);
			$instance->savePostMeta(array(CMA_Thread::$_meta['votes_answers'] => 0));
			$instance->savePostMeta(array(CMA_Thread::$_meta['votes_question'] => 0));
			$instance->savePostMeta(array(CMA_Thread::$_meta['votes_question_answers'] => 0));
			$instance->savePostMeta(array(CMA_Thread::$_meta['highestRatedAnswer'] => 0));
			$instance->savePostMeta(array(CMA_Thread::$_meta['stickyPost'] => 0));
			
// 			$votes = $this->getQuestionVotes($instance->getId());
			
			
			return $instance;
			
		} else {
			throw new Exception('Error: '. json_encode($id));
		}
	}
	
	protected function importAnswer(array $answer) {
		global $wpdb;
		
		$answer['comment_type'] = 'cma_answer';
		
		$answer['user_id'] = $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta
			WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $answer['import_old_author_id']);
		if (empty($answer['user_id'])) throw new Exception(sprintf('Failed to import answer with old id %d. Unknown user with id old %d',
			$answer['import_old_id'], $answer['import_old_author_id']));
		
		$answer['comment_post_ID'] = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta
			WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $answer['import_old_parent_id']);
		if (empty($answer['comment_post_ID'])) throw new Exception(sprintf('Failed to import answer with old id %d. Unknown question with old id %d',
			$answer['import_old_id'], $answer['import_old_parent_id']));
		
		$result = wp_insert_comment($answer);
		if ($result AND is_numeric($result)) {
			return $result;
		}
		
	}
	
	protected function importComment(array $comment) {
		global $wpdb;
		
		$comment['comment_type'] = 'cma_comment';
		
		$comment['user_id'] = $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta
			WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $comment['import_old_author_id']);
		if (empty($comment['user_id'])) throw new Exception(sprintf('Failed to import comment with old id %d. Unknown user with id old %d',
			$comment['import_old_id'], $comment['import_old_author_id']));
		
		if ($comment['import_old_abs_parent_id'] != $comment['import_old_parent_id']) { // comment to answer
			$comment['comment_parent'] = $wpdb->get_var("SELECT comment_id FROM $wpdb->commentmeta
				WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $comment['import_old_parent_id']);
			if (empty($comment['comment_parent'])) throw new Exception(sprintf('Failed to import comment with old id %d. Unknown answer with id old %d',
				$comment['import_old_id'], $comment['import_old_parent_id']));
		}
		
		$comment['comment_post_ID'] = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta
			WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $comment['import_old_abs_parent_id']);
		if (empty($comment['comment_post_ID'])) throw new Exception(sprintf('Failed to import comment with old id %d. Unknown question with id old %d',
			$comment['import_old_id'], $comment['import_old_abs_parent_id']));
			
		$result = wp_insert_comment($comment);
		if ($result AND is_numeric($result)) {
			return $result;
		}
		
	}
	
	
	
	abstract protected function getUser();
	abstract protected function getQuestion();
// 	abstract protected function getQuestionVotes($questionId);
	abstract protected function getAnswer();
// 	abstract protected function getAnswerVotes($answerId);
	abstract protected function getComment();
	
	
	
}

