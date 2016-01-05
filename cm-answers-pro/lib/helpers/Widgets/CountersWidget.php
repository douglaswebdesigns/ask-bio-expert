<?php

add_action('widgets_init', array('CMA_CountersWidget', 'registerWidget'));

class CMA_CountersWidget extends CMA_WidgetAbstract {

	const WIDGET_TITLE = 'CMA Counters';
	const WIDGET_DESCRIPTION = 'Displays CMA questions number, answers number, comments number and users number.';
	
	static protected $widgetFields = array(
		'title' => array(
			'label' => 'Title',
			'type' => CMA_Settings::TYPE_STRING,
			'default' => array(__CLASS__, 'getDefaultTitle'),
		),
		'show_questions' => array(
			'label' => 'Show questions number',
			'type' => CMA_Settings::TYPE_BOOL,
			'default' => 1,
		),
		'show_answers' => array(
			'label' => 'Show answers number',
			'type' => CMA_Settings::TYPE_BOOL,
			'default' => 1,
		),
		'show_comments' => array(
			'label' => 'Show comments number',
			'type' => CMA_Settings::TYPE_BOOL,
			'default' => 1,
		),
		'show_users' => array(
			'label' => 'Show users number',
			'type' => CMA_Settings::TYPE_BOOL,
			'default' => 1,
		),
	);
	
	
	function getWidgetContent($instance) {
		global $wpdb;
		
		$atts = shortcode_atts(array(
			'title' => self::getDefaultTitle(),
			'show_questions' => 1,
			'show_answers' => 1,
			'show_comments' => 1,
			'show_users' => 1,
		), $instance);
		
		$questionsNumber = number_format($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_status = %s",
			CMA_Thread::POST_TYPE, 'publish')), 0);
		$answersNumber = number_format($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_type = %s AND comment_approved = 1",
			CMA_Answer::COMMENT_TYPE)), 0);
		$commentsNumber = number_format($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_type = %s AND comment_approved = 1",
			CMA_Comment::COMMENT_TYPE)), 0);
		$usersNumber = number_format($wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users WHERE user_status = 0"), 0);
		
		return CMA_BaseController::_loadView('answer/widget/counters', compact('atts', 'questionsNumber', 'answersNumber', 'commentsNumber', 'usersNumber'));
		
	}
	
	
	static function getDefaultTitle() {
		return CMA::__('Answers Stats');
	}
	
}
