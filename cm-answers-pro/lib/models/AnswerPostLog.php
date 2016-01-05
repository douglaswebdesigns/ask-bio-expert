<?php

require_once CMA_PATH . '/lib/models/Log.php';

/**
 * Model for downloads logs.
 */
class CMA_AnswerPostLog extends CMA_Log {
	
	const LOG_TYPE = 'answer_post';
	
	const META_ANSWER_ID = 'answer_id';
	const META_QUESTION_ID = 'question_id';
	
	
	public function log($answerId, $data = array(), $meta = array()) {
		if ($answer = CMA_Answer::getById($answerId)) {
			
			$meta[self::META_ANSWER_ID] = $answerId;
			$meta[self::META_QUESTION_ID] = $answer->getThreadId();
			if (!empty($_SERVER['HTTP_USER_AGENT'])) {
				$meta[self::META_USER_AGENT] = $_SERVER['HTTP_USER_AGENT'];
			}
			if (!empty($_SERVER['REMOTE_ADDR'])) {
				$meta = $this->appendMetaGeolocation($meta, $_SERVER['REMOTE_ADDR']);
			}
			
			return $this->create($data, $meta);
			
		}
	}
	
	

	public function select($conditions = array(), $order = array(), $page = 1) {
		$joinMeta = array(self::META_COUNTRY_NAME, self::META_QUESTION_ID, self::META_ANSWER_ID);
		$conditions[self::FIELD_LOG_TYPE] = self::LOG_TYPE;
		return $this->_select($joinMeta, $conditions, $order, $page);
	}
	
	
	
}
