<?php

add_action('widgets_init', array('CMA_RelatedQuestionsWidget', 'registerWidget'));

class CMA_RelatedQuestionsWidget extends CMA_WidgetAbstract {

	const WIDGET_TITLE = 'CMA Related Questions';
	const WIDGET_DESCRIPTION = 'Displays questions from the same category or including similar tags.';
	
	
	static protected $widgetFields = array(
		'title' => array(
			'label' => 'Title',
			'type' => CMA_Settings::TYPE_STRING,
			'default' => 'Related Questions',
		),
		'limit' => array(
			'label' => 'Title',
			'type' => CMA_Settings::TYPE_STRING,
			'default' => 5,
		),
		'match_category' => array(
			'label' => 'Match by category',
			'type' => CMA_Settings::TYPE_BOOL,
			'default' => 1,
		),
		'match_tags' => array(
			'label' => 'Match by tags',
			'type' => CMA_Settings::TYPE_BOOL,
			'default' => 1,
		),
	);
	
	protected $questions = array();
	
	
	protected function getRelatedQuestions($limit, $matchCategory, $matchTags) {
		$wp_query = CMA_AnswerController::$query;
		if (empty($this->questions) AND $wp_query->get('post_type') == CMA_Thread::POST_TYPE AND $wp_query->is_single()) {
			$post = $wp_query->get_posts();
			$post = reset($post);
			if (!empty($post) AND $post->post_type == CMA_Thread::POST_TYPE) {
				$thread = CMA_Thread::getInstance($post->ID);
				$questions = $thread->getRelated($limit, $matchCategory, $matchTags);
				$this->questions = array_map(array('CMA_Thread', 'getInstance'), $questions);
			}
		}
		return $this->questions;
	}
	
	
	public function widget($args, $instance) {
		$atts = shortcode_atts(array(
			'title' => '',
			'limit' => 5,
			'match_category' => 1,
			'match_tags' => 1,
		), $instance);
		if ($this->getRelatedQuestions($atts['limit'], $atts['match_category'], $atts['match_tags'])) {
			parent::widget($args, $instance);
		}
	}
	

    function getWidgetContent($args) {
    	$questions = $this->questions;
		return CMA_BaseController::_loadView('answer/widget/related-questions', compact('questions'));
    }

}
