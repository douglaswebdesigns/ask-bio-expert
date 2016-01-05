<?php

class CMA_AnswerAttachment extends CMA_Attachment {
	
	protected $answerId;
	
	
	public function __construct($post, $answerId = null) {
		parent::__construct($post);
		$this->setAnswerId($answerId);
	}
	
	
	public static function selectForAnswer(CMA_Answer $answer) {
		$ids = get_comment_meta($answer->getId(), CMA_Answer::META_ATTACHMENT, false);
		if (!empty($ids)) {
			$result = parent::select($answer->getThreadId(), get_comment_meta($answer->getId(), CMA_Answer::META_ATTACHMENT, false));
			foreach ($result as $attachment) {
				$attachment->setAnswerId($answer->getId());
			}
			return $result;
		} else {
			return array();
		}
	}
	
	
	
	public function setAnswerId($answerId) {
		$this->answerId = $answerId;
	}
	
	
	public function getAnswerId() {
		return $this->answerId;
	}
	
	
}