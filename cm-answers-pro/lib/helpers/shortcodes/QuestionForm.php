<?php

class CMA_QuestionFormShortcode {
	
	
	public static function init() {
		if (!CMA::isLicenseOk()) return;
		add_shortcode('CMA-question-form', array(__CLASS__, 'shortcode'));
		add_shortcode('cma-question-form', array(__CLASS__, 'shortcode'));
		
	}
	
	
	static function shortcode($atts = array()) {
		
		$atts = shortcode_atts(array(
			'title' => 0,
			'backlink' => 1,
			'cat' => null,
		), $atts);
		
		CMA_BaseController::loadScripts();
		
		if (CMA_Thread::canPostQuestions()) {
			$post = $thread = null;
			$catId = $atts['cat'];
			$redirectAfterPost = '_thread';
			$displayOptions = array(
				'hideTitle' => empty($atts['title']),
				'showBacklink' => !empty($atts['backlink']),
			);
			return CMA_BaseController::_loadView('answer/widget/question-form', compact('post', 'thread', 'catId', 'displayOptions', 'redirectAfterPost'));
		}
		
	}
	
}

add_action('init', array('CMA_QuestionFormShortcode', 'init'), PHP_INT_MAX);
