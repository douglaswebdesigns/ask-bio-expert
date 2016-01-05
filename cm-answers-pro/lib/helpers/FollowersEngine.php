<?php

class CMA_FollowersEngine {
	
	protected $metaKeyPrefix;
	protected $id;
	
	public function __construct($metaKeyPrefix, $id) {
		$this->metaKeyPrefix = $metaKeyPrefix;
		$this->id = $id;
	}
	

	public static function canBeFollower($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		$result = !empty($userId);
		return apply_filters('cma_followes_engine_can_be_follower', $result, $userId);
	}
	
	
	public function getFollowers() {
		global $wpdb;
		$sql = $wpdb->prepare("SELECT DISTINCT `user_id` FROM `$wpdb->usermeta` WHERE `meta_key` LIKE %s", $this->getMetaKey());
		$usersIds = array_map('intval', $wpdb->get_col($sql));
		return array_filter($usersIds);
	}
	
	
	public function getMetaKey() {
		return $this->metaKeyPrefix .'_'. $this->id;
	}
	
	
	public static function getFollowed($metaKeyPrefix, $userId = null) {
		global $wpdb;
		if (empty($userId)) $userId = get_current_user_id();
		$sql = $wpdb->prepare("SELECT DISTINCT `meta_value` FROM `$wpdb->usermeta` WHERE user_id = %d AND `meta_key` LIKE %s",
				intval($userId),
				$metaKeyPrefix .'_%'
			);
		$result = $wpdb->get_results($sql, ARRAY_N);
		
		foreach ($result as &$val) {
			$val = intval(array_pop($val));
		}
		
		return array_filter($result);
	}
	
	
	public function isFollower($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		return in_array($userId, $this->getFollowers());
	}
	
	public function addFollower($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		if ($userId) {
			update_user_meta($userId, $this->getMetaKey(), $this->id);
		}
		return $this;
	}
	
	
	public function removeFollower($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		delete_user_meta($userId, $this->getMetaKey());
		return $this;
	}
	
	
}
