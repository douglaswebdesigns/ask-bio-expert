<?php

require_once 'MetaBoxAbstract.php';

class CMA_RelatedQuestionsMetaBox extends CMA_MetaBoxAbstract {
	
	const META_BOX_TITLE = 'CM Anwers: Related Questions';
	
	const POST_META_RELATED_QUESTIONS_ENABLED = '_cma_show_related_questions';
	const FIELD_NAME = 'cma-show-related-questions';
	
	
	static function bootstrap() {
		parent::bootstrap();
		add_filter('the_content', array(__CLASS__, 'theContent'), PHP_INT_MAX);
	}
	
	
	static function render($post) {
		$nonce = static::generateNonce();
		$nonceField = static::getNonceFieldName();
		$options = array('' => 'Follow global setting', 'enable' => 'Enable', 'disable' => 'Disable');
		$currentValue = get_post_meta($post->ID, static::POST_META_RELATED_QUESTIONS_ENABLED, true);
		$postTypesEnabled = CMA_Settings::getOption(CMA_Settings::OPTION_RELATED_QUESTIONS_POST_TYPES);
		if (empty($postTypesEnabled) OR !is_array($postTypesEnabled)) $postTypesEnabled = array();
		$globalValue = in_array($post->post_type, $postTypesEnabled);
		$fieldName = self::FIELD_NAME;
		echo static::loadView(compact('nonce', 'nonceField', 'options', 'currentValue', 'globalValue', 'fieldName'));
	}
	
	

	static function save($postId) {
		if (isset($_POST[self::FIELD_NAME])) {
			update_post_meta($postId, self::POST_META_RELATED_QUESTIONS_ENABLED, $_POST[self::FIELD_NAME]);
		}
	}
	
	
	static function theContent($content) {
		global $post;
		$supportedPostTypes = CMA_Settings::getOption(CMA_Settings::OPTION_RELATED_QUESTIONS_POST_TYPES);
		if (is_main_query() AND is_single() AND !get_query_var('cma_prepared_single') AND $post AND isset($post->post_type)) {
			if (in_array($post->post_type, $supportedPostTypes) OR 'enable' == get_post_meta($post->ID, self::POST_META_RELATED_QUESTIONS_ENABLED, true)) {
				$params = CMA_Settings::getOption(CMA_Settings::OPTION_RELATED_QUESTIONS_SHORTCODE_PARAMS);
				$postTags = array_map(function($tag) { return $tag->slug; }, wp_get_post_tags($post->ID));
				if (!empty($postTags)) {
					$code = '[cma-questions tag="'. implode(',', $postTags) .'" '. $params .']';
					$content .= do_shortcode($code);
				}
			}
		}
		return $content;
	}
	
	
}