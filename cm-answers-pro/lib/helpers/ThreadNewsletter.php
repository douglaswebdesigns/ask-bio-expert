<?php

class CMA_ThreadNewsletter {
	
	const USERMETA_NEWSLETTER_BLACKLIST = 'cma_newsletter_thread_blacklist';
	const USERMETA_NEWSLETTER_WHITELIST = 'cma_newsletter_thread_whitelist';
	
	const TYPE_NEW_THREADS = 'new_threads';
	const TYPE_THREAD = 'thread';
	

	static function isNewsletterFollower($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_ENABLED)) {
			// all users are followers
			if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_OPTINOUT)) {
				// allowed to opt-out so checking the blacklist:
				return !(self::isUserOnNewsletterBlacklist($userId));
			} else {
				// all users are followers:
				return true;
			}
		} else {
			// users manually opt-in and opt-out:
			return (self::isUserOnNewsletterWhitelist($userId) AND !self::isUserOnNewsletterBlacklist($userId));
		}
	}
	
	
	static function getNewsletterFollowersIds() {
		return self::getNewsletterFollowers('ID');
	}
	
	static function getNewsletterFollowersEmails() {
		return self::getNewsletterFollowers('user_email');
	}
	
	
	static function getNewsletterFollowers($cols) {
		 global $wpdb;
		 if (!is_array($cols)) $cols = array($cols);
		 $colsStr = implode(', ', $cols);
		 if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_ENABLED)) {
		 	if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_OPTINOUT)) {
		 		$sql = $wpdb->prepare("SELECT $colsStr FROM $wpdb->users u
		 			LEFT JOIN $wpdb->usermeta b ON b.user_id = u.ID AND b.meta_key = %s
		 			WHERE b.user_id IS NULL
		 			GROUP BY $colsStr",
		 			self::USERMETA_NEWSLETTER_BLACKLIST
		 		);
		 	} else {
		 		$sql = "SELECT $colsStr FROM $wpdb->users u";
		 	}
		 } else {
			 $sql = $wpdb->prepare("SELECT $colsStr FROM $wpdb->users u
				LEFT JOIN $wpdb->usermeta w ON w.user_id = u.ID AND w.meta_key = %s
				LEFT JOIN $wpdb->usermeta b ON b.user_id = u.ID AND b.meta_key = %s
				WHERE w.user_id IS NOT NULL
				AND b.user_id IS NULL
			 	GROUP BY $colsStr",
				self::USERMETA_NEWSLETTER_WHITELIST,
				self::USERMETA_NEWSLETTER_BLACKLIST
			);
		 }
		 if (count($cols) > 1) {
		 	return $wpdb->get_results($sql, ARRAY_A);
		 } else {
		 	return $wpdb->get_col($sql);
		 }
	}
	
	
	static function isUserOnNewsletterBlacklist($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		$meta = get_user_meta($userId, self::USERMETA_NEWSLETTER_BLACKLIST, true);
		return !empty($meta);
	}
	
	
	static function addUserToNewsletterBlacklist($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		update_user_meta($userId, self::USERMETA_NEWSLETTER_BLACKLIST, time());
	}
	
	
	static function removeUserFromNewsletterBlacklist($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		delete_user_meta($userId, self::USERMETA_NEWSLETTER_BLACKLIST);
	}
	
	static function isUserOnNewsletterWhitelist($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		$meta = get_user_meta($userId, self::USERMETA_NEWSLETTER_WHITELIST, true);
		return !empty($meta);
	}
	
	
	static function addUserToNewsletterWhitelist($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		update_user_meta($userId, self::USERMETA_NEWSLETTER_WHITELIST, time());
	}
	
	
	static function removeUserFromNewsletterWhitelist($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		delete_user_meta($userId, self::USERMETA_NEWSLETTER_WHITELIST);
	}
	
	
	static function getOptOutUrl(CMA_Thread $thread, $type) {
		return CMA_BaseController::getUrl('newsletter', 'optout', array($type => $thread->getId(), 'token' => self::getOptOutToken($thread)));
	}
	
	
	static function getOptOutToken(CMA_Thread $thread) {
		return sha1(implode('|', array(NONCE_SALT, $thread->getId(), NONCE_KEY)));
	}
	
	
	static function canBeFollower() {
		return (is_user_logged_in() AND CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_OPTINOUT));
	}
	
}
