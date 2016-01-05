<?php

require_once CMA_PATH . '/lib/helpers/IPGeolocation.php';

/**
 * Model for downloads logs.
 */
abstract class CMA_Log {
	
	const LOG_TYPE = 'default';
	
	const TABLE_NAME = 'cma_logs';
	const FIELD_ID = 'id';
	const FIELD_LOG_TYPE = 'log_type';
	const FIELD_CREATED = 'created';
	const FIELD_USER_ID = 'user_id';
	const FIELD_IP_ADDR = 'ip_addr';
	
	const TABLE_META_NAME = 'cma_logs_meta';
	const FIELD_META_ID = 'id';
	const FIELD_META_LOG_ID = 'log_id';
	const FIELD_META_NAME = 'meta_name';
	const FIELD_META_VALUE = 'meta_value';
	
	const META_COUNTRY_CODE = 'author_country_code';
	const META_COUNTRY_NAME = 'author_country_name';
	const META_USER_AGENT = 'user_agent';
	
	
	const PAGE_LIMIT = 100;
	
	public $groups = array(
		'days' => array(
			'datePattern' => 'Y-m-d 00:00:00',
			'label' => 'Y-m-d',
			'sqlGroup' => 'DATE(created)',
			'defaultPeriod' => 7,
		),
		'weeks' => array(
			'datePattern' => 'Y-m-d 00:00:00',
			'label' => 'W',
			'sqlGroup' => 'WEEKOFYEAR(created)',
			'defaultPeriod' => 5,
		),
		'months' => array(
			'datePattern' => 'Y-m-01 00:00:00',
			'label' => 'Y-m',
			'sqlGroup' => 'CONCAT(YEAR(created), "-", LPAD(MONTH(created), 2, "0"))',
			'defaultPeriod' => 12,
		),
	);
	
	
	public function __construct() {
		$this->createTable();
	}
	
	
	/**
	 * Create log record and return ID.
	 * 
	 * @param array $data (optional)
	 * @param array $meta (optional)
	 * @return number
	 */
	public function create($data = array(), $meta = array()) {
		
		$data = array_merge($this->getLogDefaults(), $data);
		$meta = array_merge($this->getMetaDefaults(), $meta);
		
		// Insert log record
		if ($id = $this->createQuery($data)) {
			// Add meta values
			foreach ($meta as $key => $value) {
				$this->createMetaQuery($id, $key, $value);
			}
			return $id;
		}
		
	}
	
	
	protected function createQuery($data) {
		global $wpdb;
		$wpdb->field_types[self::FIELD_USER_ID] = '%d';
		if ($wpdb->insert($this->getTableName(), $data)) {
			return $wpdb->insert_id;
		}
	}
	
	
	protected function createMetaQuery($logId, $name, $value) {
		global $wpdb;
		$wpdb->field_types[self::FIELD_META_LOG_ID] = '%d';
		$result = $wpdb->insert($this->getMetaTableName(), array(
			self::FIELD_META_LOG_ID => $logId,
			self::FIELD_META_NAME => $name,
			self::FIELD_META_VALUE => $value,
		));
		if ($result) {
			return $wpdb->insert_id;
		}
	}
	
	
	public function getTableName() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}
	
	
	public function getMetaTableName() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_META_NAME;
	}
	
	
	protected function getLogDefaults() {
		$defaults = array(
			self::FIELD_LOG_TYPE => static::LOG_TYPE,
			self::FIELD_CREATED => Date('Y-m-d H:i:s'),
		);
		
		// Add ip addr
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			$defaults[self::FIELD_IP_ADDR] = $_SERVER['REMOTE_ADDR'];
		}
		
		// Add current user data
		if ($userId = CMA::getPostingUserId()) {
			$user = get_userdata($userId);
			$defaults[self::FIELD_USER_ID] = $userId;
		}
		return $defaults;
	}
	
	
	protected function getMetaDefaults() {
		return array();
	}
	
	
	protected function appendMetaGeolocation($meta, $ip) {
		// Find country by IPGeolocation
		if ($apiKey = CMA_Settings::getOption(CMA_Settings::OPTION_GEOLOCIATION_API_KEY)) {
			$service = new CMA_IPGeolocation();
			$service->setKey($apiKey);
			$response = $service->getCountry($ip);
			if (!empty($response['countryCode']) AND $response['countryCode'] != '-') {
				$meta[self::META_COUNTRY_CODE] = $response['countryCode'];
			}
			if (!empty($response['countryName']) AND $response['countryName'] != '-') {
				$meta[self::META_COUNTRY_NAME] = $response['countryName'];
			}
		}
		return $meta;
	}
	
	
	/**
	 * Select stats by given group and period, of given download (optional).
	 * 
	 * @param string $groupName
	 * @param int $period (optional)
	 * @param array $filter (optional)
	 * @return array Label => value
	 */
	function stats($groupName, $period = null, $type = null, $filter = array()) {
		global $wpdb;
		
		// Choose group
		if (empty($this->groups[$groupName])) return array();
		$group = $this->groups[$groupName];
		
		// Create start date
		if (empty($period)) $period = $group['defaultPeriod'];
		$startDate = Date($group['datePattern'], strtotime(sprintf('-%d %s', $period-1, $groupName)));
		
		$extra = '';
		
		// Perform SQL query
		$query = $wpdb->prepare('SELECT '. $group['sqlGroup'] .' AS `group`, COUNT(*) AS `count`
				FROM '. $this->getTableName() .'
				WHERE '. self::FIELD_LOG_TYPE .' = %s AND '. self::FIELD_CREATED .' >= %s '. $extra .'
				GROUP BY `group`
				ORDER BY `group` ASC',
				static::LOG_TYPE, $startDate);
		$result = $wpdb->get_results($query, ARRAY_A);
		
		// Create label-value array
		$grouped = array_combine(
			array_map(function($val) { return $val['group']; }, $result),
			array_map(function($val) { return intval($val['count']); }, $result)
		);
		
		// Add missing labels with value=0
		for ($i=0; $i<=$period-1; $i++) {
			$label = Date($group['label'], strtotime(sprintf('-%d %s', $i, $groupName)));
			if (empty($grouped[$label])) {
				$grouped[$label] = 0;
			}
		}
		ksort($grouped);
		
		return $grouped;
		
	}
	

	/**
	 * Select logs records by given conditions.
	 * 
	 * @param array $joinMeta
	 * @param array $conditions
	 * @param array $order
	 * @param int $page
	 * @return array
	 */
	protected function _select($joinMeta = array(), $conditions = array(), $order = array(), $page = 1) {
		global $wpdb;
		
		$tableName = $this->getTableName();
		$selectString = "`$tableName`.*";
		$joinString = '';
		$groupByString = '';
		
		foreach ($joinMeta as $name) {
			$selectString .= sprintf(', `%s`.`meta_value` AS `%s`',
					$this->getMetaTableAlias($name),
					$this->getMetaFieldAlias($name)
			);
			$joinString .= sprintf(' LEFT JOIN `%s` AS `%s` ON `%s`.`%s` = `%s`.`%s` AND `%s`.`%s` = %s ',
				$this->getMetaTableName(),
				$this->getMetaTableAlias($name),
				$this->getMetaTableAlias($name),
				self::FIELD_META_LOG_ID,
				$tableName,
				self::FIELD_ID,
				$this->getMetaTableAlias($name),
				self::FIELD_META_NAME,
				$wpdb->prepare('%s', $name)
			);
		}
		if (!empty($joinMeta)) {
			$groupByString = sprintf('GROUP BY `%s`.`%s`', $this->getTableName(), self::FIELD_ID);
		}
		
		$whereString = '';
		foreach ($conditions as $name => $value) {
			if (!empty($whereString)) $whereString .= ' AND ';
			$whereString .= $name;
			if (!preg_match('/[\=\>\<\!]/', $name)) $whereString .= ' = ';
			$whereString .= $wpdb->prepare('%s', $value);
		}
		if (!empty($whereString)) {
			$whereString = ' WHERE '. $whereString;
		}
		
		$orderString = '';
		foreach ($order as $name => $sort) {
			if (!empty($orderString)) $orderString .= ', ';
			$orderString .= "$name $sort";
		}
		if (!empty($orderString)) {
			$orderString = ' ORDER BY '. $orderString;
		}
		
		if (!is_null($page)) {
			$from = ($page-1) * self::PAGE_LIMIT;
			$limitString = 'LIMIT '. $from .', '. self::PAGE_LIMIT;
		} else {
			$limitString = '';
		}
		
		$query = "SELECT $selectString FROM $tableName $joinString $whereString $groupByString $orderString $limitString";
		return $wpdb->get_results($query, ARRAY_A);
		
	}
	
	
	
	public function getMetaTableAlias($name) {
		return 'm_'. $name;
	}
	
	public function getMetaFieldAlias($name) {
		return 'meta_'. $name;
	}
	
	
	/**
	 * Clear all logs.
	 */
	function clear() {
		global $wpdb;
		return $wpdb->delete($this->getTableName(), array(self::FIELD_LOG_TYPE => static::LOG_TYPE));
	}
	
	
	public static function instance() {
		return new static();
	}
	
	
	
	protected function tableExists() {
		global $wpdb;
		$tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
		foreach ($tables as &$val) {
			$val = array_pop($val);
		}
		return in_array($this->getTableName(), $tables);
	}
	
	
	protected function createTable() {
		
		$tableName = $this->getTableName();
		$metaTableName = $this->getMetaTableName();
		
		if ($this->tableExists()) return;
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$sql = sprintf('CREATE TABLE IF NOT EXISTS `%s` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `log_type` varchar(50) NOT NULL,
		  `created` datetime NOT NULL,
		  `user_id` int(10) unsigned DEFAULT NULL,
		  `ip_addr` varchar(40) DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `log_name` (`log_type`),
		  KEY `created` (`created`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8', $tableName);
		dbDelta( $sql );
		$sql = sprintf('CREATE TABLE IF NOT EXISTS `%s` (
		  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		  `log_id` int(10) unsigned NOT NULL,
		  `meta_name` varchar(255) NOT NULL,
		  `meta_value` text,
		  PRIMARY KEY (`id`),
		  KEY `log_id` (`log_id`),
		  KEY `meta_name` (`meta_name`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8', $metaTableName);
		dbDelta( $sql );
		$sql = sprintf('ALTER TABLE `%s`
  			ADD CONSTRAINT `%s_log_fk` FOREIGN KEY (`log_id`) REFERENCES `%s` (`id`) ON DELETE CASCADE ON UPDATE CASCADE',
			$metaTableName,
			$metaTableName,
			$tableName);
		dbDelta( $sql );
	}
	
	
}
