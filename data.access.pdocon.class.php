<?php

/**
 * Requires DataAccessObjException class, to throw exceptions
 */
require_once 'data.access.exception.class.php';

if (!class_exists('PDOCon')) {
	
	/**
	 * PDO connection class
	 *
	 * Retreives configuration for connection and uses PDO to create persistent 
	 * connections to database.
	 *
	 * @author Nils Lindenthaal <nils@dfworks.lv>
	 * @copyright 2013 Dragonfly Works, LLC
	 * @license MIT
	 * @package data-access-obj
	 * @link https://github.com/Deele/data-access-obj GitHub repo for full package
	 * 
	 * @todo documentation
	 */
	class PDOCon {
		
		/**
		 * Connection creation method
		 * 
		 * Retrieves configuration data to connect to database and tries connection.
		 * 
		 * @param array $dataArray Associative array with object data
		 * 
		 * @uses PDO
		 * 
		 * @throws DataAccessObjException If connection failed
		 * 
		 * @return object Returns PDO connection object
		 */
		public static function nection() {
			
			// Get config values from config file or other matters
			$host 		= 'localhost';
			$db 		= '';
			$user 		= 'root';
			$password 	= '';
			$charset 	= 'utf8';
			
			// Try connecting
			try {
				$dbh = new PDO(
					'mysql:host='.$host.';dbname='.$db,
					$user,
					$password,
					array(
						PDO::ATTR_PERSISTENT => true,
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
					)
				);
				// Set data transfer charset
				$dbh->query("SET NAMES '".$charset."'");
				
				return $dbh;
			}
			catch (PDOException $e) {
				throw new DataAccessObjException(
					DataAccessObjErrors::SERVER_CONNECT_FAIL, 
					$e->getMessage()
				);
			}
		}
	}
}