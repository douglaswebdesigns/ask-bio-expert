<?php

require_once dirname(__FILE__) . '/Import.php';

abstract class CMA_SQLImport extends CMA_Import {
	
	protected $pdo;
	
	
	function __construct($dsn, $user, $pass) {
		parent::__construct();
		$this->pdo = new PDO(
			$dsn,
			$user,
			$pass,
			array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
			)
		);
	}
	
	
}