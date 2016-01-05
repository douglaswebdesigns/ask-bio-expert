<?php

class CMA_NewsletterController extends CMA_BaseController {
	
	protected static $contentMessage = '';
	
	
	public static function initialize() {
		
		if (!CMA::isLicenseOk()) return;
		
		self::addAjaxHandler('cma-follow-new-threads', array(__CLASS__, 'ajaxHandler'));
		
	}
	
	
	static function optoutHeader() {
		
		self::loadScripts();
		
		if (is_user_logged_in()) {
			
			if ($threadId = self::_getParam(CMA_ThreadNewsletter::TYPE_NEW_THREADS)) {
				// All new threads following:
				self::optoutNewThreads($threadId);
			}
			else if ($threadId = self::_getParam(CMA_ThreadNewsletter::TYPE_THREAD)) {
				// Single thread following:
				self::optoutThread($threadId);
			}
			
			
		}
	}
	
	
	protected static function optoutNewThreads($threadId) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_OPTINOUT)) {
			$token = self::_getParam('token');
			if ($thread = CMA_Thread::getInstance($threadId) AND CMA_ThreadNewsletter::getOptOutToken($thread) == $token) {
					
				if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_ENABLED)) {
					CMA_ThreadNewsletter::addUserToNewsletterBlacklist();
				} else {
					CMA_ThreadNewsletter::removeUserFromNewsletterWhitelist();
				}
				self::$contentMessage = CMA_Labels::getLocalized('newsletter_optout_success');
					
			} else {
				self::$contentMessage = 'An error occurred.';
			}
		
		} else self::$contentMessage = CMA_Labels::getLocalized('newsletter_optout_disabled');
	}
	
	
	protected static function optoutThread($threadId) {
		$token = self::_getParam('token');
		if ($thread = CMA_Thread::getInstance($threadId) AND CMA_ThreadNewsletter::getOptOutToken($thread) == $token) {
				
			$thread->getFollowersEngine()->removeFollower();
			self::$contentMessage = CMA_Labels::getLocalized('unfollow_success');
				
		} else {
			self::$contentMessage = 'An error occurred.';
		}
	}
	
	
	static function optoutAction() {
		return array('message' => self::$contentMessage);
	}
	
	
	static function optoutTitle() {
		return CMA_Labels::getLocalized('newsletter_page_title');
	}

	
	
	static function ajaxHandler() {
		$response = array('success' => 0, 'message' => 'An error occurred.');
		if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_OPTINOUT)) {
			if (isset($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], 'cma_follow')) {
				if (CMA_ThreadNewsletter::isNewsletterFollower()) {
					if (CMA_Settings::getOption(CMA_Settings::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_ENABLED)) {
						CMA_ThreadNewsletter::addUserToNewsletterBlacklist();
					}
					CMA_ThreadNewsletter::removeUserFromNewsletterWhitelist();
					$response = array('success' => 1, 'message' => CMA_Labels::getLocalized('newsletter_new_therads_disabled_success'));
				} else {
					CMA_ThreadNewsletter::removeUserFromNewsletterBlacklist();
					CMA_ThreadNewsletter::addUserToNewsletterWhitelist();
					$response = array('success' => 1, 'message' => CMA_Labels::getLocalized('newsletter_new_therads_enabled_success'));
				}
			}
		}
		header('content-type: application/json');
		echo json_encode($response);
		exit;
	}
	
}
