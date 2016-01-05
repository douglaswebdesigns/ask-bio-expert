<?php

class CMA_QuestionAttachment extends CMA_Attachment {
	
	
	
	public static function selectForQuestion(CMA_Thread $thread) {
		$ids = $thread->getPostMeta(CMA_Thread::$_meta['attachment'], false);
		if (!empty($ids)) {
			return parent::select($thread->getId(), $ids);
		} else {
			return array();
		}
	}
	
	
	
}