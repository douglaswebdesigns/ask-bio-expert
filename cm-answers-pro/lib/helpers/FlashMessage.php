<?php

class CMA_FlashMessage {
	
	const TRANSIENT_PREFIX = 'cma_msg_';
	
	const SUCCESS = 'success';
	const ERROR = 'error';
	
	static $messages = array();
	
	
	static function init() {
		self::$messages = get_transient(self::getTransientKey());
		if (!array(self::$messages)) self::$messages = array();
	}
	
	
	static function push($msg, $type = self::SUCCESS) {
		self::$messages[$type][] = $msg;
		self::save();
	}
	
	
	static function pop() {
		delete_transient(self::getTransientKey());
		return self::$messages;
	}
	
	
	static function getTransientKey() {
		$sid = wp_get_session_token();
		return self::TRANSIENT_PREFIX . md5($sid);
	}
	
	
	static function save() {
		if (!empty(self::$messages)) {
			set_transient(self::getTransientKey(), self::$messages);
		}
	}
	
}

add_action('plugins_loaded', array('CMA_FlashMessage', 'init'));
