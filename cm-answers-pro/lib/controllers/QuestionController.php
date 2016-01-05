<?php

class CMA_QuestionController extends CMA_BaseController {
	
	
	public static function initialize() {
		
		if (!CMA::isLicenseOk()) return;
		
		add_action('cma_index_question_form', array(__CLASS__, 'indexQuestionForm'), 1000, 2);
		add_filter('template_include', array(__CLASS__, 'overrideTemplate'), PHP_INT_MAX);
// 		add_filter('the_content', array(__CLASS__, 'the_content'), PHP_INT_MAX);
		
	}
	
	
	static function overrideTemplate($template) {
		if (get_query_var('CMA-question-add')) {
			$tempalte = self::prepareSinglePage(
				$title = CMA_Labels::getLocalized('ask_a_question'),
				$content = '',
				$newQuery = true
			);
		}
		
		return $template;
		
	}
	
	
	static function addHeader() {
		
		if (!CMA_Thread::canPostQuestions()) {
			self::addMessage(self::MESSAGE_ERROR, 'You cannot post questions.');
			wp_redirect(CMA::getReferer());
		}
		
		self::loadScripts();
		
	}
	
	
	static function addAction() {
		$content = CMA_QuestionFormShortcode::shortcode(array(
			'cat' => CMA_BaseController::_getParam('category')
		));
		return compact('content');
	}
	
	
	static function indexQuestionForm($catId, $place) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_QUESTION_FORM_BUTTON)) {
			$url = home_url('question/add/');
			echo CMA_BaseController::_loadView('answer/widget/question-form-button', compact('url'));
		} else {
			echo CMA_BaseController::_loadView('answer/widget/question-form', compact('catId'));
		}
	}
	
	
}
