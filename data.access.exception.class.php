<?php

/**
 * Requires Logger class, to log exception messages
 */
require_once 'data.access.logger.php';

if (!class_exists('DataAccessObjException')) {
	
	/**
	 * Data access object exception class
	 *
	 * When exception is thrown, it triggers logger class to log message
	 *
	 * @author Nils Lindenthaal <nils@dfworks.lv>
	 * @copyright 2013 Dragonfly Works, LLC
	 * @license MIT
	 * @package data-access-obj
	 * @link https://github.com/Deele/data-access-obj GitHub repo for full package
	 * 
	 * @todo documentation
	 */
	class DataAccessObjException extends Exception {
		
		/**
		 * 
		 */
		public function __construct(
			$error_code = 10000, 
			$error_notes = ''
		) {
			parent::__construct(
				DataAccessObjErrors::getErrorMessage(
					$error_code, 
					$error_notes
				), 
				$error_code
			);
			Logger::newExceptionMessage($this);
		}
	}
}

if (!class_exists('DataAccessObjErrors')) {
	
	/**
	 * Data access object error class
	 *
	 * Contains error codes and messages that can accour during data access object
	 * class execution.
	 *
	 * @author Nils Lindenthaal <nils@dfworks.lv>
	 * @copyright 2013 Dragonfly Works, LLC
	 * @license MIT
	 * @package data-access-obj
	 * @link https://github.com/Deele/data-access-obj GitHub repo for full package
	 * 
	 * @todo documentation
	 */
	class DataAccessObjErrors {
		
		const UNEXPECTED_ERROR 			= 10000;
		const PDO_EXCEPTION 			= 10001;
		const SERVER_CONNECT_FAIL 		= 10002;
		const RETRIEVE_ATTRIBUTES_FAIL 	= 10003;
		
		public static function getErrorMessage($error_code, $error_notes = '')
		{
			switch($error_code) {
				case self::RETRIEVE_ATTRIBUTES_FAIL:
					$error_message = 'Could not retrieve table structure attributes.';
				break;
				case self::SERVER_CONNECT_FAIL:
					$error_message = 'Could not connect to database.';
				break;
				case self::PDO_EXCEPTION:
					$error_message = 'PDO exception:';
				break;
				case self::UNEXPECTED_ERROR:
				default:
					$error_message = 'An unexpected error has occurred';
				break;
			}
			return $error_message.(
				strlen($error_notes) ? 
				' Notes: '.$error_notes : 
				''
			);
		}
	}
}