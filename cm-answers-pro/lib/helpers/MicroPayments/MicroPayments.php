<?php
// Exit if accessed directly
if( !defined('ABSPATH') OR !defined('CMA_PATH') )
{
	exit;
}

require_once(CMA_PATH . '/lib/models/MicroPaymentsModel.php');

class CMA_MicroPayments
{
	/**
	 * Singleton instance.
	 */
	protected static $instance = NULL;
	
	
	protected static $checkConfigFilters = array(
		'cm_micropayments_are_points_defined' => 'Points costs have to be defined to purchase points by users.',
		'cm_micropayments_are_wallets_assigned' => 'The "Assign wallets to customers" option has to be enabled.',
		'cm_micropayments_are_paypal_settings_defined' => 'PayPal settings are not defined.',
		'cm_micropayments_is_wallet_page' => 'The Wallet page is not defined.',
		'cm_micropayments_is_checkout_page' => 'The Checkout page is not defined.',
	);
	

	/**
	 * Construct.
	 */
	public function __construct()
	{
		add_action('init', array($this, 'init'), 1);
	}

	public function init()
	{
		if( self::isMicroPaymentsAvailable() ) { // Setup backend hooks
			
			add_filter('cma_settings_pages', function($pages) {
				$lastVal = end($pages);
				$lastKey = key($pages);
				array_pop($pages);
				$pages['micropayments'] = 'MicroPayments';
				$pages[$lastKey] = $lastVal;
				return $pages;
			});
	
			add_filter('cma_settings_pages_groups', function($subcategories) {
				$subcategories['micropayments']['general'] = 'General';
				return $subcategories;
			});
			
			CMA_Labels::loadLabelFile(dirname(__FILE__) . '/labels.tsv');
			
			if (self::isMicroPaymentsConfigured()) { // Setup frontend hooks
				
				add_action('cma_question_post_before', array($this, 'questionPostBefore'), 10, 1);
				add_action('cma_answer_post_before', array($this, 'answerPostBefore'), 10, 2);
				add_action('cma_frontend_question_form_bottom', array($this, 'questionFormBottom'));
				add_action('cma_question_post_msg_success', array($this, 'questionPostMessageSuccess'));
				add_action('cma_frontend_answer_form_bottom', array($this, 'answerFormBottom'));
				add_action('cma_answer_post_msg_success', array($this, 'answerPostMessageSuccess'));
				add_action('cma_thread_resolved', array($this, 'threadResolved'), 10, 1);
				add_action('cma_thread_set_best_answer', array($this, 'threadResolved'), 10, 1);
				
			} else {
				add_action( 'admin_notices', function() {
					if (CMA_MicroPayments::instance()->getAnswerPoints() != 0 OR CMA_MicroPayments::instance()->getQuestionPoints() != 0) {
						CMA_MicroPayments::displayAdminWarning();
					}
				});
			}
			
		}
		
	}
	
	
	
	public static function displayAdminWarning($class = null) {
		if (empty($class)) $class = 'error';
		$reasons = '';
		foreach (self::$checkConfigFilters as $filter => $msg) {
			if (!apply_filters($filter, FALSE)) {
				$reasons .= sprintf('<li>%s</li>', CMA::__($msg));
			}
		}
		printf('<div class="%s"><p>%s</p><ul style="list-style:disc;margin:0 0 1em 2em;">%s</ul><p>%s</p></div>',
			esc_attr($class),
			CMA::__('<strong>CM Answers Pro</strong> would not integrate with the <strong>CM Micropayments</strong> plugin because of the following reasons:'),
			$reasons,
			sprintf('<a href="%s" class="button">%s</a>',
				esc_attr(admin_url('admin.php?page=cm-micropayment-platform-settings')),
				CMA::__('CM Micropayments Settings')
			)
		);
	}
	

	/**
	 * Check whether MicroPayments platform is available and configured.
	 *
	 * @return boolean
	 */
	public static function isMicroPaymentsAvailable()
	{
		return apply_filters('cm_micropayments_is_working', FALSE);
	}
	
	
	public static function isMicroPaymentsConfigured() {
		if (self::isMicroPaymentsAvailable()) {
			foreach (self::$checkConfigFilters as $filter => $msg) {
				if (!apply_filters($filter, FALSE)) return false;
			}
			return true;
		} else {
			return false;
		}
	}
	
	

	/**
	 * Check if wallet assigned to given user ID exists.
	 *
	 * @param int $userId
	 * @return boolean
	 */
	public function checkUsersWalletExists($userId)
	{
		$userWallet = apply_filters('cm_micropayments_user_wallet_id', $userId);
		return !empty($userWallet);
	}

	/**
	 * Check if user has enough points.
	 *
	 * @param int $userId
	 * @param int $points
	 */
	public function hasUserEnoughPoints($userId, $points)
	{
		if( $user = get_user_by('id', $userId) )
		{
			$result = apply_filters('user_has_enough_points', array('username' => $user->user_login, 'points' => abs($points)));
			return (!empty($result['success']) AND $result['success'] === true);
		}
		return false;
	}

	/**
	 * Charge user wallet.
	 *
	 * @param int $userId
	 * @param int $points Positive or negative integer or zero.
	 * @throws CMA_MPNotEnoughPointsException
	 */
	public function chargeUserWallet($userId, $points)
	{
		if( !$this->checkUsersWalletExists($userId) )
		{
			throw new CMA_MPMissingUserWalletException;
		}
		if( $points < 0 )
		{
			if( !$this->hasUserEnoughPoints($userId, abs($points)) )
			{
				throw new CMA_MPNotEnoughPointsException;
			}
		}
		$args = array('user_id' => $userId, 'amount' => $points);
		
		$result = apply_filters('charge_user_wallet', $args);
// 		var_dump($result);exit;
		if( $result )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function getQuestionPoints()
	{
		return $this->getPointsByAction(
						CMA_Settings::getOption(CMA_Settings::OPTION_MP_POST_QUESTION_ACTION), CMA_Settings::getOption(CMA_Settings::OPTION_MP_POST_QUESTION_POINTS)
		);
	}

	public function getAnswerPoints()
	{
		return $this->getPointsByAction(
						CMA_Settings::getOption(CMA_Settings::OPTION_MP_POST_ANSWER_ACTION), CMA_Settings::getOption(CMA_Settings::OPTION_MP_POST_ANSWER_POINTS)
		);
	}

	protected function getPointsByAction($action, $points)
	{
		$points = abs($points);
		switch($action)
		{
			case CMA_MicroPaymentsModel::ACTION_GRANT_POINTS:
				return $points;
			case CMA_MicroPaymentsModel::ACTION_CHARGE_POINTS:
				return -$points;
			default:
				return 0;
		}
	}

	/**
	 * Performing the cma_question_post action on posting the question.
	 *
	 * @param CMA_Thread $thread
	 * @throws CMA_MPNotEnoughPointsException
	 */
	public function questionPostBefore(array $postData)
	{
		if( ($points = $this->getQuestionPoints()) != 0 )
		{
			$this->chargeUserWallet($postData['post_author'], $points);
		}
	}

	/**
	 * Performing the cma_answer_post action on posting the answer.
	 *
	 * @param CMA_Thread $thread
	 * @param array $answer
	 */
	public function answerPostBefore(CMA_Thread $thread, CMA_Answer $answer)
	{
		if( ($points = $this->getAnswerPoints()) != 0 )
		{
			$this->chargeUserWallet($answer->getAuthorId(), $points);
		}
	}

	/**
	 * Action called after rendering the bottom of the question form.
	 *
	 * @param string $msg
	 * @return string
	 */
	public function questionFormBottom()
	{
		if( ($points = $this->getQuestionPoints()) != 0 )
		{
			$userId = get_current_user_id();

			if( $points > 0 )
			{
				if( $this->checkUsersWalletExists($userId) )
				{
					$content = ($points > 1 ? sprintf(CMA_Labels::getLocalized('mp_posting_question_grants'), $points) : CMA_Labels::getLocalized('mp_posting_question_grants_one'));
				}
				else
				{
					$content = CMA_Labels::getLocalized('mp_posting_question_grants_no_wallet');
				}
			}
			else
			{
				if( $this->checkUsersWalletExists($userId) AND $this->hasUserEnoughPoints($userId, $points) )
				{
					$content = $this->infoCharge($points);
				}
				else
				{
					$content = (abs($points) > 1 ? sprintf(CMA_Labels::getLocalized('mp_posting_question_costs'), abs($points)) : CMA_Labels::getLocalized('mp_posting_question_costs_one'));
					$content .= '<br />';
					if( $this->checkUsersWalletExists($userId) )
					{
						$content .= $this->infoNotEnoughPoints();
					}
					else
					{
						$content .= CMA_Labels::getLocalized('you_have_no_wallet');
					}
				}
			}
			printf('<div class="cma-micropayments-info">%s</div>', $content);
		}
	}

	public function getUsersWalletURL()
	{
		return apply_filters('cm_micropayments_user_wallet_url', array());
	}

	public function getPointsPurchaseURL()
	{
		return apply_filters('cm_micropayments_checkout_url', array());
	}

	/**
	 * Action called after rendering the bottom of the answer form.
	 *
	 * @param string $msg
	 * @return string
	 */
	public function answerFormBottom()
	{
		if( ($points = $this->getAnswerPoints()) != 0 )
		{
			$userId = get_current_user_id();

			if( $points > 0 )
			{
				if( $this->checkUsersWalletExists($userId) )
				{
					$content = ($points > 1 ? sprintf(CMA_Labels::getLocalized('mp_posting_answer_grants'), $points) : CMA_Labels::getLocalized('mp_posting_answer_grants_one'));
				}
				else
				{
					$content = CMA_Labels::getLocalized('mp_posting_answer_grants_no_wallet');
				}
			}
			else
			{
				if( $this->checkUsersWalletExists($userId) AND $this->hasUserEnoughPoints($userId, $points) )
				{
					$content = $this->infoCharge($points);
				}
				else
				{
					$content = (abs($points) > 1 ? sprintf(CMA_Labels::getLocalized('mp_posting_answer_cost'), abs($points)) : CMA_Labels::getLocalized('mp_posting_answer_cost_one'));
					$content .= '<br />';
					if( $this->checkUsersWalletExists($userId) )
					{
						$content .= $this->infoNotEnoughPoints();
					}
					else
					{
						$content .= CMA_Labels::getLocalized('you_have_no_wallet');
					}
				}
			}
			printf('<div class="cma-micropayments-info">%s</div>', $content);
		}
	}

	/**
	 * Filter the success message after posting a question.
	 *
	 * @param string $msg
	 * @return string
	 */
	public function questionPostMessageSuccess($msg)
	{
		$userId = get_current_user_id();
		if( $this->checkUsersWalletExists($userId) )
		{
			$points = $this->getQuestionPoints();
			if( $points > 0 )
			{
				$msg .= ' ' . $this->infoGranted($points);
			}
			else if( $points < 0 )
			{
				$msg .= ' ' . $this->infoTaken($points);
			}
		}
		return $msg;
	}

	/**
	 * Filter the success message after posting an answer.
	 *
	 * @param string $msg
	 * @return string
	 */
	public function answerPostMessageSuccess($msg)
	{
		$userId = get_current_user_id();
		if( $this->checkUsersWalletExists($userId) )
		{
			$points = $this->getAnswerPoints();
			if( $points != 0 )
			{
				$msg .= ' ' . $this->infoGranted($points);
			}
		}
		return $msg;
	}
	
	
	/**
	 * Action then the thread has been resolved.
	 * 
	 * @param CMA_Thread $thread
	 */
	public function threadResolved(CMA_Thread $thread) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_MP_REWARD_BEST_ANSWER_ENABLE)) {
			if ($points = CMA_Settings::getOption(CMA_Settings::OPTION_MP_REWARD_BEST_ANSWER_POINTS)) {
				if ($thread->isResolved() AND $bestAnswer = CMA_Answer::getById($thread->getBestAnswerId())) {
					if ($userId = $bestAnswer->getAuthorId() AND $user = get_userdata($userId)) {
						if ($this->chargeUserWallet($userId, $points)) {
							add_filter('cma_question_resolved_msg_success', array($this, 'questionResolvedMessageSuccess'));
							add_filter('cma_question_mark_best_answer_msg_success', array($this, 'questionResolvedMessageSuccess'));
						}
					}
				}
			}
		}
	}
	
	
	public function questionResolvedMessageSuccess($msg) {
		$points = intval(CMA_Settings::getOption(CMA_Settings::OPTION_MP_REWARD_BEST_ANSWER_POINTS));
		$msg .= '<br />' . sprintf(CMA_Labels::getLocalized('mp_thread_reward_best_answer_msg'), $points);
		return $msg;
	}
	

	/**
	 * Main Instance
	 *
	 * Insures that only one instance of class exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @return The one true CMA_MicroPayments
	 */
	public static function instance()
	{
		if( empty(static::$instance) )
		{
			static::$instance = new static;
		}
		return static::$instance;
	}

	

	protected function infoCharge($points)
	{
		return (abs($points) > 1 ? sprintf(CMA_Labels::getLocalized('mp_info_charge'), abs($points)) : CMA_Labels::getLocalized('mp_info_charge_one'))
				. sprintf(' <a href="%s">%s</a>', esc_attr($this->getUsersWalletURL()), CMA_Labels::getLocalized('view_wallet')
		);
	}

	protected function infoNotEnoughPoints()
	{
		return sprintf(CMA_Labels::getLocalized('mp_not_enough_points'), sprintf('<a href="%s">%s</a>', esc_attr($this->getUsersWalletURL()), CMA_Labels::getLocalized('your_wallet')
						)
				)
				. sprintf(' <a href="%s">%s</a>', esc_attr($this->getPointsPurchaseURL()), CMA_Labels::getLocalized('mp_please_purchase_points')
		);
	}

	protected function infoTaken($points)
	{
		$points = abs($points);
		return ($points > 1 ? sprintf(CMA_Labels::getLocalized('mp_info_taken'), $points) : CMA_Labels::getLocalized('mp_info_taken_one'))
				. sprintf(' <a href="%s">%s</a>', esc_attr($this->getUsersWalletURL()), CMA_Labels::getLocalized('view_wallet')
		);
	}

	protected function infoGranted($points)
	{
		$points = abs($points);
		return ($points > 1 ? sprintf(CMA_Labels::getLocalized('mp_info_granted'), $points) : CMA_Labels::getLocalized('mp_info_granted_one'))
				. sprintf(' <a href="%s">%s</a>', esc_attr($this->getUsersWalletURL()), CMA_Labels::getLocalized('view_wallet')
		);
	}

}

// ------------------------------------------------------------------------------------------------------------------------------
// Exceptions


class CMA_MPException extends Exception
{
	const ERROR_MSG = 'An error occured in the CM MicroPayments module. Please try again.';

	function __construct()
	{
		parent::__construct(CMA_Labels::getLocalized(static::ERROR_MSG));
	}

}

class CMA_MPNotEnoughPointsException extends CMA_MPException
{
	const ERROR_MSG = 'mp_error_not_enough_points';

}

class CMA_MPMissingUserWalletException extends CMA_MPException
{
	const ERROR_MSG = 'mp_error_wallet_not_exists';

}
// ------------------------------------------------------------------------------------------------------------------------------
// Create a singleton instance.

CMA_MicroPayments::instance();
