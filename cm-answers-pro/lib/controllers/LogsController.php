<?php

require_once(CMA_PATH .'/lib/models/QuestionPostLog.php');
require_once(CMA_PATH .'/lib/models/AnswerPostLog.php');
require_once(CMA_PATH .'/lib/models/QuestionVoteLog.php');
require_once(CMA_PATH .'/lib/models/AnswerVoteLog.php');

class CMA_LogsController extends CMA_BaseController {
	
	const PAGE_LIMIT = 100;
	const PARAM_NONCE = 'token';
	const NONCE_ACTION = 'logs';
	
	/**
	 * Available log types and its labels.
	 */
	static public $logTypes = array(
		'question_post' => 'Posted questions',
		'answer_post' => 'Posted answers',
		'question_vote' => 'Votes on questions',
		'answer_vote' => 'Votes on answers',
	);
	
	/**
	 * Available actions and its labels.
	 */
	static public $actions = array(
		'table' => 'Logs table',
		'graph' => 'Graph',
		'graphJSON' => 'Graph',
		'csv' => 'Download CSV',
		'clear' => 'Clear log',
	);
	
	/**
	 * Current action.
	 */
	static protected $action;
	
	/**
	 * Current log type.
	 */
	static protected $logType;
	
	
	/**
	 * Set the action to call and log type to work with. After that call the action.
	 */
	public static function init() {
		
		if (!empty($_GET['action']) AND in_array($_GET['action'], array_keys(self::$actions))) {
			self::$action = $_GET['action'];
		} else {
			self::$action = 'table';
		}
		
		if (!empty($_GET['log_type']) AND in_array($_GET['log_type'], array_keys(self::$logTypes))) {
			self::$logType = $_GET['log_type'];
		} else {
			self::$logType = 'question_post';
		}
		
		call_user_func(array(__CLASS__, self::$action));
	}
	
	
	/**
	 * Show logs table.
	 */
	public static function table() {
		
		$params = array('page' => 1, 'timeFrom' => null, 'timeTo' => null);
		$conditions = array();
		
		if (isset($_GET['date_from'])) {
			if ($dateFrom = strtotime($_GET['date_from'])) {
				$params['dateFilterFrom'] = Date('Y-m-d', $dateFrom);
				$conditions['created >='] = Date('Y-m-d 00:00:00', $dateFrom);
			}
		}
		if (isset($_GET['date_to'])) {
			if ($dateTo = strtotime($_GET['date_to'])) {
				$params['dateFilterTo'] = Date('Y-m-d', $dateTo);
				$conditions['created <='] = Date('Y-m-d 23:59:59', $dateTo);
			}
		}
		
		$model = self::getModel();
		$order = array($model->getTableName() .'.id' => 'DESC');
		$params['logs'] = $model->select($conditions, $order, $params['page']);
		
		self::render($params);
	}
	
	
	/**
	 * Show logs graph page.
	 */
	public static function graph() {
		self::render(array('urlJSON' => self::url('graphJSON', self::$logType)));
	}
	
	
	/**
	 * Load the graph JSON data.
	 */
	public static function graphJSON() {
		
		$model = self::getModel();
		$category = explode('-', (empty($_GET['category']) ? null : $_GET['category']));
		$period = array_pop($category);
		$category = array_pop($category);
		$params = array();
		
		$categories = array('days', 'weeks', 'months');
		if (!in_array($category, $categories)) {
			$category = 'days';
		}
		
		$logs = $model->stats($category, $period);
		foreach ($logs as $key => &$log) {
			$log = array((string)$key, (int)$log);
		}
		
		header("Content-type: application/json; charset=UTF-8");
		echo json_encode(array_values($logs));
		exit;
		
	}
	
	/**
	 * Download the CSV file.
	 */
	public static function csv() {
		$model = self::getModel();
		$logs = $model->select(array(), array(), null);
		
		$temp = tmpfile();
		foreach ($logs as $log) {
			$row = array_values($log);
			fputcsv($temp, $row);
		}
		fseek($temp, 0);
		 
		header("Cache-Control: must-revalidate");
		header("Pragma: must-revalidate");
		header("Content-type: text/csv; charset=UTF-8");
		header("Content-disposition: attachment; filename=log-cma-". str_replace('_', '-', self::$logType) .'-'. Date('YmdHis') .".csv");
		echo fread($temp, 1024 * 1024 * 50);
		fclose($temp);
		exit;
		
	}
	
	/**
	 * Clear logs records.
	 */
	public static function clear() {
		if (isset($_GET[self::PARAM_NONCE]) AND wp_verify_nonce($_GET[self::PARAM_NONCE], self::NONCE_ACTION)) {
			self::getModel()->clear();
		}
		wp_safe_redirect(self::url('table', self::$logType));
		exit;
	}
	
	/**
	 * Render view.
	 * 
	 * @param array $params
	 */
	public static function render(array $params = array()) {
		
		if (self::$action == 'graph') $view = 'graph';
		else $view = self::$action .'_'. self::$logType;
		
		$params['view'] = $view;
		$params = apply_filters(self::ADMIN_LOGS, $params);
		extract($params);
		ob_start();
		wp_enqueue_script('flot', CMA_RESOURCE_URL . 'flot/jquery.flot.js');
		wp_enqueue_script('flotCategories', CMA_RESOURCE_URL . 'flot/jquery.flot.categories.js');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
		wp_enqueue_style( 'admin-bar' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'wp-admin' );
		require(CMA_PATH . '/views/backend/logs.phtml');
		self::displayAdminPage(ob_get_clean());
	}
	
	
	/**
	 * Create URL string.
	 * 
	 * @param string $action
	 * @param string $logType
	 * @return string
	 */
	public static function url($action, $logType) {
		return admin_url(sprintf('admin.php?page=%s&action=%s&log_type=%s',
					self::ADMIN_LOGS, urlencode($action), urlencode($logType)));
	}
	
	
	/**
	 * Check if action should be called before headers sent.
	 * 
	 * @param string $action
	 * @return boolean
	 */
	public static function isActionBeforeRender($action) {
		return ($action == 'graphJSON' || $action == 'csv' || $action == 'clear');
	}
	
	
	/**
	 * Create an HTML select box.
	 * 
	 * @param string $name
	 * @param array $options
	 * @param string $current Value to select.
	 * @return string
	 */
	public static function getSelectBox($name, array $options, $current = null) {
		$content = '';
		foreach ($options as $value => $label) {
			$content .= sprintf('<option value="%s"%s>%s</option>',
				esc_attr($value),
				($current == $value ? ' selected="selected"' : ''),
				esc_html(CMA_Settings::__($label))
			);
		}
		return sprintf('<select name="%s">%s</select>', esc_attr($name), $content);
	}
	
	/**
	 * Get actions and its labels to the navigation.
	 * 
	 * @return array
	 */
	public static function getNavigationActions() {
		$actions = self::$actions;
		unset($actions['graphJSON']);
		return $actions;
	}
	

	/**
	 * Get model for current log type.
	 * 
	 * @return CMA_Log
	 */
	protected static function getModel() {
		$type = explode('_', self::$logType);
		foreach ($type as &$frag) {
			$frag = ucfirst($frag);
		}
		$className = 'CMA_'. implode('', $type) .'Log';
		return new $className;
	}
	
	
}
