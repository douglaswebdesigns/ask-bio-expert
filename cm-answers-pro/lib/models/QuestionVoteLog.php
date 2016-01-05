<?php

require_once CMA_PATH . '/lib/models/Log.php';

class CMA_QuestionVoteLog extends CMA_Log {
	
	const LOG_TYPE = 'question_vote';
	
	const META_QUESTION_ID = 'question_id';
	const META_VOTE = 'vote';
	
	
	public function log($questionId, $vote, $data = array(), $meta = array()) {
		$meta[self::META_QUESTION_ID] = $questionId;
		$meta[self::META_VOTE] = $vote;
		if (!empty($_SERVER['HTTP_USER_AGENT'])) {
			$meta[self::META_USER_AGENT] = $_SERVER['HTTP_USER_AGENT'];
		}
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			$meta = $this->appendMetaGeolocation($meta, $_SERVER['REMOTE_ADDR']);
		}
		
		return $this->create($data, $meta);
	}
	
	
	
	public function select($conditions = array(), $order = array(), $page = 1) {
		$joinMeta = array(self::META_COUNTRY_NAME, self::META_QUESTION_ID, self::META_VOTE);
		$conditions[self::FIELD_LOG_TYPE] = self::LOG_TYPE;
		return $this->_select($joinMeta, $conditions, $order, $page);
	}
	
	
}
