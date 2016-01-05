<?php

abstract class CMA_MetaBoxAbstract {
	
	const META_BOX_TITLE = '';
	const META_BOX_PRIORITY = 'normal';
	
	
	static function bootstrap() {
		add_action('add_meta_boxes', array(get_called_class(), 'registerMetaBox'));
		add_action('save_post', array(get_called_class(), 'savePost'));
	}
	
	
	static function registerMetaBox() {
		$postTypes = static::getMetaBoxPostTypes();
		foreach ($postTypes as $postType) {
			add_meta_box(static::getMetaBoxId(), static::getTitle(), array(get_called_class(), 'render'), $postType, static::getPriority());
		}
	}
	
	
	static function savePost($postId) {
		$nonceFieldName = static::getNonceFieldName();
		$nonceAction = static::getNonceAction();
		if (isset($_POST[$nonceFieldName]) AND wp_verify_nonce($_POST[$nonceFieldName], $nonceAction)) {
			static::save($postId);
		}
	}
	
	static function getMetaBoxId() {
		return get_called_class();
	}
	
	
	static function getNonceFieldName() {
		return static::getMetaBoxId() .'-nonce';
	}
	
	
	static function getNonceAction() {
		return static::getMetaBoxId();
	}
	
	
	static function generateNonce() {
		return wp_create_nonce(static::getNonceAction());
	}
	
	
	static function getViewName() {
		return strtolower(preg_replace('/(.)([A-Z])/', '$1-$2', strtr(get_called_class(), array('CMA_' => '', 'MetaBox' => ''))));
	}
	
	
	static function loadView($options = array()) {
		return static::_loadView(static::getViewName(), $options);
	}
	
	
	static function _loadView($name, $options = array()) {
		return CMA_BaseController::_loadView('../backend/meta-box/'. $name, $options);
	}
	
	
	static function getTitle() {
		return static::META_BOX_TITLE;
	}
	
	
	static function getPriority() {
		return static::META_BOX_PRIORITY;
	}
	

	static function getMetaBoxPostTypes() {
		return get_post_types();
	}
	
	
	static function render($post) {
		
	}
	
	static function save($postId) {
		
	}
	
}
