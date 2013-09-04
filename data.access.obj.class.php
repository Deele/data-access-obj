<?php

/**
 * Requires DataAccessObjException class, to throw exceptions
 */
require_once 'data.access.exception.class.php';

/**
 * Requires PDOCon class, to connect to database
 */
require_once 'data.access.pdocon.class.php';


if (!class_exists('DataAccessObj')) {
	
	/**
	 * Data Access Object
	 *
	 * Uses PDO connection to allow create data models based on database tables with
	 * simple syntax.
	 *
	 * @author Nils Lindenthaal <nils@dfworks.lv>
	 * @copyright 2013 Dragonfly Works, LLC
	 * @license MIT
	 * @package data-access-obj
	 * @link https://github.com/Deele/data-access-obj GitHub repo for full package
	 * 
	 * @todo documentation
	 */
	class DataAccessObj {
		
		public $_attributes = array();
		
		/**
		 * Provides SQL database table name.
		 * 
		 * When another model extends this class, it should override this function
		 * and return correct table name.
		 *
		 * @return string Returns name of table.
		 */
		public static function dbtable() {
			return;
		}
		
		/**
		 * Provides SQL database table primary key name.
		 * 
		 * When another model extends this class, it should override this function 
		 * and return correct table  primary key name, otherwise it will return 
		 * default 'id'.
		 *
		 * @return string Returns name of database table primary key. 
		 */
		public static function primaryKey() {
			return 'id';
		}
		
		/**
		 * Enables creation of object using array of values.
		 * 
		 * Fills _attributes array with current table data array
		 * 
		 * @param array $dataArray Associative array with object data
		 * 
		 * @uses DataAccessObj::_attributes and fills it with getAttributes() output
		 * @uses DataAccessObj::getAttributes()
		 * @uses DataAccessObj::primaryKey()
		 * 
		 * @throws DataAccessObjException If could not retrieve table attributes
		 * 
		 * @return object Returns this object instance.
		 */
		public function __construct($dataArray = array()) {
			$this->_attributes = static::getAttributes();
			if (!empty($this->_attributes)) {
				if (!empty($dataArray)) {
					foreach ($this->_attributes as $k => $v) {
						if (isset($dataArray[$k])) {
							$this->{$k} = $dataArray[$k];
						}
						elseif ($k != static::primaryKey()) {
							$this->{$k} = $v->default;
						}
					}
				}
				else {
					foreach ($this->_attributes as $k => $v) {
						if ($k != static::primaryKey()) {
							$this->{$k} = $v->default;
						}
					}
				}
				return $this;
			}
			else {
				throw new DataAccessObjException(
					DataAccessObjErrors::RETRIEVE_ATTRIBUTES_FAIL
				);
			}
		}
		
		/**
		 * Returns single DataAccessObj instance, with data retrieved from database, 
		 * filtered by primary key
		 */
		public static function findByPk($id) {
			$dbh = PDOCon::nection();
			try {
				$stmt = $dbh->prepare(
					"SELECT * FROM ".
					static::dbtable().
					" WHERE ".
					static::primaryKey().
					" = :id LIMIT 1"
				);
				$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				$stmt->execute();
				$class = get_called_class();
				$data = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($data) {
					return new $class($data);
				}
			}
			catch(PDOException $e) {
				throw new DataAccessObjException(
					DataAccessObjErrors::PDO_EXCEPTION, 
					$e->getMessage()
				);
			}
		}
		
		/**
		 * Returns conditional attributes from input data
		 */
		public static function getCondition($condition) {
			$returnData = (object) array(
				'where' => '',
				'limit' => '',
				'order' => '',
			);
			if (is_string($condition)) {
				$returnData->where = $condition;
			}
			elseif (is_array($condition)) {
				if (isset($condition[0])) {
					$returnData->where = $condition[0];
				}
				if (isset($condition['select'])) {
					$returnData->select = $condition['select'];
				}
				if (isset($condition['condition'])) {
					$returnData->where = $condition['condition'];
				}
				if (isset($condition['limit'])) {
					$returnData->limit = $condition['limit'];
				}
				if (isset($condition['order'])) {
					$returnData->order = $condition['order'];
				}
			}
			if (!isset($returnData->select) || strlen($returnData->select) == 0) {
				$returnData->select = '*';
			}
			return $returnData;
		}
		
		/**
		 * Returns array with DataAccessObj instances, with data retrieved from 
		 * database, filtered with conditions
		 */
		public static function findAll($condition = '', $params = array()) {
			$condition = static::getCondition($condition);
			$dbh = PDOCon::nection();
			try {
				$sql = "SELECT ".
					(strlen($condition->select) > 0 ? $condition->select : '').
					" FROM ".static::dbtable().
					(
						strlen($condition->where) > 0 ? 
						" WHERE ".$condition->where : 
						''
					).
					(
						strlen($condition->order) > 0 ? 
						" ORDER BY ".$condition->order : 
						''
					).
					(
						strlen($condition->limit) > 0 ? 
						" LIMIT ".$condition->limit : 
						''
					);
				$stmt = $dbh->prepare($sql);
				if (!empty($params)) {
					foreach ($params as $k => $v) {
						$stmt->bindParam($k, $v);
					}
				}
				$exec = $stmt->execute();
				$class = get_called_class();
				$fetchAll = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$return = array();
				if (!empty($fetchAll)) {
					foreach ($fetchAll as $v) {
						$return[$v[static::primaryKey()]] = new $class($v);
					}
				}
				return $return;
			}
			catch(PDOException $e) {
				throw new DataAccessObjException(
					DataAccessObjErrors::PDO_EXCEPTION, 
					$e->getMessage()
				);
			}
		}
		
		/**
		 * Returns count of rows, for data retrieved from database,  filtered with 
		 * conditions
		 */
		public static function count($condition = '', $params = array()) {
			$condition = static::getCondition($condition);
			$dbh = PDOCon::nection();
			try {
				$sql = "SELECT count(*) FROM ".
					static::dbtable().
					(
						strlen($condition->where) > 0 ? 
						" WHERE ".$condition->where : 
						''
					);
				$stmt = $dbh->prepare($sql);
				if (!empty($params)) {
					foreach ($params as $k => $v) {
						$stmt->bindParam($k, $v);
					}
				}
				$stmt->execute();
				$fetch = $stmt->fetchColumn();
				$return = (int) $fetch;
				return $return;
			}
			catch(PDOException $e) {
				throw new DataAccessObjException(
					DataAccessObjErrors::PDO_EXCEPTION, 
					$e->getMessage()
				);
			}
		}
		
		/**
		 * Stores DataAccessObj instance data into database
		 */
		public function save() {
			$returnData = false;
			$dbh = PDOCon::nection();
			try {
				if (
					isset($this->{static::primaryKey()}) && 
					$this->{static::primaryKey()} > 0
				) {
					// Update
					$dataArray = array();
					$valueArray = array(':id'=>$this->{static::primaryKey()});
					foreach ($this->_attributes as $k => $v) {
						if (isset($this->{$k}) && $k != static::primaryKey()) {
							$dataArray[] = $k.' = :'.$k;
							$valueArray[':'.$k] = $this->{$k};
						}
					}
					$dataArray = implode(',',$dataArray);
					$stmt = $dbh->prepare(
						"UPDATE ".
						static::dbtable().
						" SET ".$dataArray.
						" WHERE ".
						static::primaryKey().
						" = :id"
					);
					$returnData = $stmt->execute($valueArray);
				}
				else {
					// Insert
					$columns = array();
					$valueColumns = array();
					$valueArray = array();
					foreach ($this->_attributes as $k => $v) {
						if (isset($this->{$k})) {
							$columns[] = $k;
							$valueColumns[] = ':'.$k;
							$valueArray[':'.$k] = $this->{$k};
						}
					}
					$columns = implode(',',$columns);
					$valueColumns = implode(',',$valueColumns);
					if (!empty($columns)) {
						try {
							$dbh->beginTransaction();
							$stmt = $dbh->prepare(
								"INSERT INTO ".
								static::dbtable().
								" (".$columns.") ".
								"VALUES (".$valueColumns.")"
							);
							$insert = $stmt->execute($valueArray);
							if ($insert) {
								$this->{static::primaryKey()} = $dbh->lastInsertId();
								$returnData = $dbh->lastInsertId();
							}
							$dbh->commit();
						}
						catch(PDOException $e) {
							$dbh->rollback();
							echo $e->getMessage();
						}
					}
				}
			}
			catch(PDOException $e) {
				throw new DataAccessObjException(
					DataAccessObjErrors::PDO_EXCEPTION, 
					$e->getMessage()
				);
			}
			return $returnData;
		}
		
		/**
		 * Deletes DataAccessObj instance entry from database
		 */
		public function delete() {
			if (isset($this->{static::primaryKey()})) {
				$dbh->query(
					"DELETE FROM ".
					static::dbtable().
					" WHERE ".
					static::primaryKey().
					" = :id"
				);
				$stmt->bindParam(
					':id', 
					$this->{static::primaryKey()}, 
					PDO::PARAM_INT
				);
				$stmt->execute();
			}
		}
		
		/**
		 * Queryies SQL table description
		 */
		public static function describe() {
			$dbh = PDOCon::nection();
			try {
				$stmt = $dbh->query("DESCRIBE ".static::dbtable());
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			catch(PDOException $e) {
				throw new DataAccessObjException(
					DataAccessObjErrors::PDO_EXCEPTION, 
					$e->getMessage()
				);
			}
		}
		
		/**
		 * Creates informational array about table fiels
		 */
		public static function getAttributes() {
			$description = static::describe();
			$attributes = array();
			foreach ($description as $col) {
				$type = static::getType($col->Type);
				$extrasExploded = explode(' ',$col->Extra);
				$attributes[$col->Field] = (object) array(
					'type' => $type->type,
					'column' => $col->Type,
					'isUnsigned' => $type->isUnsigned,
					'zerofill' => $type->zerofill,
					'nullAllowed' => ($col->Null != 'NO'),
					'key' => ($col->Key == 'PRI' ? 'primary' : $col->Key),
					'default' => (
						$col->Null == 'NO' && $col->Default == null ? 
						(
							$type->type == 'int' || $type->type == 'float' ? 
							0 : 
							''
						) : 
						$col->Default
					),
					'hasAutoIncrement' => (
						count($extrasExploded) > 0 && 
						in_array('auto_increment', $extrasExploded)
					),
				);
			}
			return $attributes;
		}
		
		/**
		 * Determines table field type depending on SQL table field type description
		 */
		public static function getType($type) {
			$typeExploded = explode(' ',$type);
			$type = $typeExploded[0];
			$isUnsigned = (
				count($typeExploded) > 0 ? 
				(in_array('unsigned', $typeExploded)) : 
				false
			);
			$zerofill = (
				count($typeExploded) > 0 ? 
				(in_array('zerofill', $typeExploded)) : 
				false
			);
			// Numeric
			if (
				substr($type, 0, 7) == 'tinyint' || 
				substr($type, 0, 8) == 'smallint' || 
				substr($type, 0, 9) == 'mediumint' || 
				substr($type, 0, 3) == 'bool'
			) {
				$type = 'int';
			}
			elseif (substr($type, 0, 3) == 'int') {
				if ($isUnsigned) {
					$type = 'string';
				}
				else {
					$type = 'int';
				}
				
			}
			elseif (
				(
					substr($type, 0, 6) == 'bigint' || 
					substr($type, 0, 6) == 'serial'
				) || (
					substr($type, 0, 3) == 'dec' || 
					substr($type, 0, 7) == 'numeric' || 
					substr($type, 0, 5) == 'fixed'
				)
			) {
				$type = 'string';
			}
			elseif (
				substr($type, 0, 5) == 'float' || 
				substr($type, 0, 6) == 'double'
			) {
				$type = 'float';
			}
			// Binary
			elseif (substr($type, 0, 3) == 'bit') {
				$type = 'bin';
			}
			// String
			elseif (
				(
					substr($type, 0, 4) == 'char' || 
					substr($type, 0, 4) == 'text'
				) || 
				substr($type, 0, 7) == 'varchar' || 
				substr($type, 0, 6) == 'binary' || 
				substr($type, 0, 9) == 'varbinary' || 
				$type == 'tinyblob' || 
				substr($type, 0, 4) == 'blob' || 
				$type == 'tinytext' || 
				substr($type, 0, 4) == 'text' || 
				$type == 'mediumblob' || 
				$type == 'mediumtext' || 
				$type == 'longblob' || 
				$type == 'longtext' || 
				substr($type, 0, 4) == 'enum' || 
				substr($type, 0, 3) == 'set'
			) {
				$type = 'string';
			}
			return (object) array(
				'type' => $type,
				'isUnsigned' => $isUnsigned,
				'zerofill' => $zerofill,
			);
		}
	}
}
