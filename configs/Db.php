<?php


/**
 * DB
 */
class DB extends PDO
{
	
	function __construct()
	{
		try {
			
			$dsn = "mysql:host=localhost;dbname=billing";
			$username = "root";
			$password = "";
			$db = parent::__construct($dsn, $username, $password);
			parent::setAttribute(parent::ATTR_ERRMODE, parent::ERRMODE_EXCEPTION);

			return $db;

		} catch (PDOException $e) {
			die($e->getMessage());
		}
	}
}