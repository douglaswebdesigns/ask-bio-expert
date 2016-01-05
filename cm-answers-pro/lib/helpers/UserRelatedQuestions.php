<?php

CMA_UserRelatedQuestions::bootstrap();

class CMA_UserRelatedQuestions {
	
	static function bootstrap() {
		add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
		add_action('save_post', array(__CLASS__, 'save_post'), 10, 1);
	}
	
	
	static function add_meta_boxes() {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_USER_RELATED_QUESTIONS_ENABLE)) {
			add_meta_box( 'cma-user-related-questions', 'User Related Questions', array(__CLASS__, 'render_meta_box'),
				CMA_Thread::POST_TYPE, 'normal', 'high' );
		}
	}
	
	
	static function render_meta_box($post) {
		wp_enqueue_style('cma-backend', CMA_RESOURCE_URL . 'backend.css');
		wp_enqueue_script('cma-admin-script', CMA_RESOURCE_URL . 'admin_script.js');
		if ($thread = CMA_Thread::getInstance($post)) {
			$questions = $thread->getUserRelatedQuestions(false);
		} else {
			$questions = array();
		}
		$nonce = wp_create_nonce(__CLASS__);
		include CMA_PATH . '/views/backend/hooks/user_related_questions_metabox.phtml';
	}
	
	
	static function save_post($postId) {
		if ($thread = CMA_Thread::getInstance($postId) AND self::save_post_verify_nonce()) {
			$related = array();
			if (!empty($_POST['cma_related_questions'])) {
				$related = $_POST['cma_related_questions'];
			}
			if (!empty($_POST['cma_related_questions_add'])) {
				$related = array_merge($related, self::getIdsFromRaw($_POST['cma_related_questions_add']));
			}
			$thread->setUserRelatedQuestions($related);
		}
	}
	
	
	protected static function save_post_verify_nonce() {
		$field = 'cma_related_questions_nonce';
		return (!empty($_POST[$field]) AND wp_verify_nonce($_POST[$field], __CLASS__));
	}
	
	
	/**
	 * Translate raw textarea data with ids and urls to array of thread ids.
	 * 
	 * @param string|array $related
	 * @return array
	 */
	static function getIdsFromRaw($related) {
		global $wpdb;
		if (is_scalar($related)) {
			$related = array_filter(array_map('trim', explode("\n", $related)));
		}
		$ids = array();
		foreach ($related as $id) {
			if (!is_numeric($id)) { // url
				$url = $id;
				$id = url_to_postid($url);
				if (empty($id)) {
					$sql = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = %s", $url);
					$id = $wpdb->get_var($sql);
				}
			}
			if (!empty($id) AND is_numeric($id)) {
				$ids[$id] = $id;
			}
		}
		return $ids;
	}
	
}

