<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Insert data to database
 * Call this class from Database::newRowAdvanced();
 */
class DatabaseRowInput
{
	private $rowStructure = [];

	public function __construct()
	{
		return $this;
	}

	/** 
	 * Set Column field
	 * @param string $column_name
	 * @param $value
	 * @return DatabaseRowInput
	 */
	public function setField($column_name, $value)
	{
		if ($column_name == "") throw new DatabaseError("Column name cannot be empty");
		$this->rowStructure[$column_name] = $value;
		return $this;
	}

	/**
	 * Get array from current RowInput
	 * @return array
	 */
	public function toArray()
	{
		return $this->rowStructure;
	}

	public function clearStructure()
	{
		if (!is_callbyme()) throw new DatabaseError("DatabaseRowInput violation!");
		$this->rowStructure = [];
		return $this;
	}

	public function getStructure()
	{
		if (!is_callbyme()) throw new DatabaseError("DatabaseRowInput violation!");
		return $this->rowStructure;
	}
}

/**
 * Build database and table structure
 */
class DatabaseTableBuilder
{
	private $arrayStructure = [];
	private $rowStructure = [];
	private $selectedColumn;
	private $indexes = [];
	private $needToDrop = false;

	public function __construct()
	{
		return $this;
	}

	/**
	 * Add index to this table
	 * See Mysql reference about index
	 * 
	 * @param string $name Give the index a name
	 * @param array $column Provide column that you want to add to this index
	 * @param string $type Choose UNIQUE, FULLTEXT, SPATIAL, or leave it empty
	 * @return DatabaseTableBuilder
	 */
	public function createIndex($name, $column, $type = "")
	{
		switch ($type) {
			case "UNIQUE":
			case "FULLTEXT":
			case "SPATIAL":
			case "":
				break;
			default:
				throw new DatabaseError("Index should be UNIQUE, FULLTEXT, SPATIAL, or leave it empty");
		}

		if (!is_array($column)) throw new DatabaseError('$column should be an array');
		$this->indexes[] = [$name, $column, $type];
		return $this;
	}

	/** 
	 * Create structure along with initial record
	 * If the table already have some record, than this data will not be inserted
	 * @param mixed ...$structure
	 * @return DatabaseTableBuilder
	 */
	public function newInitialRow(...$structure)
	{
		$this->rowStructure["simple"][] = $structure;
		return $this;
	}

	/** 
	 * Create structure along with initial record
	 * If the table already have some record, than this data will not be inserted
	 * @param DatabaseRowInput $structure
	 * @return DatabaseTableBuilder
	 */
	public function newInitialRowAdvanced($structure)
	{
		if (!is_a($structure, "DatabaseRowInput")) throw new DatabaseError("Please use DatabaseRowInput as a structure!");
		$this->rowStructure["advance"][] = clone $structure;
		$structure->clearStructure();
		return $this;
	}

	/**
	 * Make the table dropped whenever the table structure changed
	 * DANGER: Use this function wisely! It will drop the table is we detected a change in table structure checksum
	 */
	public function dropTable()
	{
		$this->needToDrop = true;
	}

	/**
	 * Add column attribute to table
	 * @param string $name
	 * @param string $type Use Qualified Mysql data type (e.g. TEXT, TINYTEXT)
	 * @return DatabaseTableBuilder
	 */
	public function addColumn($name, $type = "TEXT")
	{
		if (strlen($name) > 50) throw new DatabaseError("Max length for column name is 50 chars");
		$this->selectedColumn = $name;
		//Structure = [Type", PRIMARY, AllowNULL, Default]
		$this->arrayStructure[$this->selectedColumn] = [$type, false, false, null];
		return $this;
	}

	/**
	 * Change column selection
	 * @param string $name
	 * @return DatabaseTableBuilder
	 */
	public function selectColumn($name)
	{
		if (strlen($name) > 50) throw new DatabaseError("Max length for column name is 50 chars");
		if (!isset($this->arrayStructure[$name])) throw new DatabaseError("Column not found");
		$this->selectedColumn = $name;
		return $this;
	}

	/**
	 * Set current column as Primary Key
	 * @return DatabaseTableBuilder
	 */
	public function setAsPrimaryKey()
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		foreach ($this->arrayStructure as $key => $data) {
			$this->arrayStructure[$key][1] = ($this->selectedColumn == $key);
		}
		return $this;
	}

	/**
	 * Remove any current primary key from table
	 * @return DatabaseTableBuilder
	 */
	public function removePrimaryKey()
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][1] = false;
		return $this;
	}

	/**
	 * Allow column to be NULL
	 * @param bool $bool
	 * @return DatabaseTableBuilder
	 */
	public function allowNull($bool)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][2] = $bool;
		return $this;
	}

	/**
	 * Set default value for this column.
	 * Make sure that your data type suppport defaultValue
	 * @param mixed $str
	 * @return DatabaseTableBuilder
	 */
	public function defaultValue($str)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][3] = (string)$str;
		return $this;
	}

	/**
	 * Set data tpe for this column
	 * @param string $type
	 * @return DatabaseTableBuilder
	 */
	public function setType($type)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][0] = $type;
		return $this;
	}

	public function x_getStructure()
	{
		if (!is_callbyme()) throw new DatabaseError("DatabaseTableBuilder violation!");
		return ($this->arrayStructure);
	}

	public function x_getIndexes()
	{
		if (!is_callbyme()) throw new DatabaseError("DatabaseTableBuilder violation!");
		return ($this->indexes);
	}

	public function x_getInitialRow()
	{
		if (!is_callbyme()) throw new DatabaseError("DatabaseTableBuilder violation!");
		return ($this->rowStructure);
	}

	public function x_needToDropTable()
	{
		if (!is_callbyme()) throw new DatabaseError("DatabaseTableBuilder violation!");
		return ($this->needToDrop);
	}
}

/**
 * Database operation class
 */
class Database
{
	/**
	 * @var mysqli
	 */
	private static $link;
	private static $cache = [];
	private static $t_cache = [];

	public static function connect()
	{
		if (!is_callbyme()) throw new DatabaseError("Database violation!");
		self::$link = mysqli_connect(
			POSConfigDB::$host,
			POSConfigDB::$username,
			POSConfigDB::$password,
			POSConfigDB::$database_name
		);
		if (!self::$link) {
			throw new DatabaseError(
				mysqli_connect_error(),
				"Fyi, PuzzleOS only support MySQL server for now. Please re-configure database information in config.php"
			);
		}
	}

	private static function dumpError()
	{
		throw new DatabaseError('DB -> Could not get data: ' . mysqli_error(self::$link));
	}

	/**
	 * Perform MySQL queries
	 * @return mysqli_result
	 */
	private static function query($query, ...$param)
	{
		$escaped = "";
		$token = strtok($query, '?');
		reset($param);
		$processedLen = 0;
		do {
			$escaped .= $token;
			$processedLen += strlen($token) + 1;
			if ($processedLen >= strlen($query)) {
				if (current($param) !== false) $escaped .= self::escape(current($param));
				break;
			} else if (current($param) === false) {
				throw new DatabaseError("Not enough parameter");
			} else {
				$escaped .= self::escape(current($param));
				next($param);
			}
		} while ($token = strtok('?'));

		switch (strtoupper(explode(" ", $escaped)[0])) {
			case "SELECT":
			case "SHOW":
				break;
			default:
				self::flushCache();
		}
		
		//See Database caching performance
		if (defined("DB_DEBUG")) {
			$re = debug_backtrace()[1];
			file_put_contents(__ROOTDIR . "/db.log", $re["file"] . ":" . $re["line"] . "\r\n\t$escaped\r\n\r\n", FILE_APPEND);
		}

		if ($r = self::$link->query($escaped)) {
			return $r;
		} else {
			if (mysqli_errno(self::$link) == "2014") {
				/* Perform a reconnect */
				self::$link->close();
				self::connect();
				self::flushCache();
				if (!($r = self::$link->query($escaped))) {
					throw new DatabaseError('Could not execute(' . mysqli_errno(self::$link) . '): ' . mysqli_error(self::$link), $escaped);
				}
			} else
				throw new DatabaseError('Could not execute(' . mysqli_errno(self::$link) . '): ' . mysqli_error(self::$link), $escaped);
		}
	}

	private static function x_verify($find)
	{
		if ($find == "") return false;
		$filename = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3)[2]["file"];
		if (__isCLI() && isset($GLOBALS["_WORKER"])) {
			$appname = $GLOBALS["_WORKER"]["appdir"];

			if (!file_exists(__ROOTDIR . "/applications/$appname/manifest.ini"))
				throw new DatabaseError("Application do not have manifest!");

			$manifest = parse_ini_file(__ROOTDIR . "/applications/$appname/manifest.ini");
			$appname = $manifest["rootname"];

			if ((preg_match('/app_' . $appname . '_/', $find))) return (true);
		} else {
			$filename = explode("/", str_replace(__ROOTDIR, "", btfslash($filename)));
			switch ($filename[1]) {
				case "bootstrap":
					switch ($filename[2]) {
						case "appFramework.php":
						case "services.php":
						case "database.php":
						case "systables.php":
							return (true);
						case "cron.php":
							if ((preg_match('/`cron`/', $find))) return (true);
							break;
						case "session.php":
							if ((preg_match('/`sessions`/', $find))) return (true);
							break;
						case "configman.php":
							if ((preg_match('/`multidomain_config`/', $find))) return (true);
							break;
						case "userdata.php":
						case "boot.php":
							if ((preg_match('/`userdata`/', $find))) return (true);
							break;
					}
					break;
				case "applications":
					$appname = isset($filename[2]) ? $filename[2] : "";

					if (!file_exists(__ROOTDIR . "/applications/$appname/manifest.ini"))
						throw new DatabaseError("Application do not have manifest!");

					$manifest = parse_ini_file(__ROOTDIR . "/applications/$appname/manifest.ini");
					$appname = $manifest["rootname"];

					if ((preg_match('/app_' . $appname . '_/', $find))) return (true);
			}
		}
		return (false);
	}

	/**
	 * Flush database cache
	 */
	public static function flushCache()
	{
		self::$cache = [];
		if (defined("DB_DEBUG")) {
			file_put_contents(__ROOTDIR . "/db.log", "CACHE PURGED\r\n", FILE_APPEND);
		}
	}

	/**
	 * Get max value from column in table
	 * @param string $table Table Name
	 * @param string $column Column Name
	 * @param string $arg Custom parameter
	 * @param string $param Custom parameter
	 * @return string
	 */
	public static function max($table, $column, $arg = "", ...$param)
	{
		self::x_verify($table);
		if (!isset(self::$cache["max"][$table . $column])) {
			if ($r = self::query("SELECT MAX(`?`) FROM `?` $arg", $column, $table, ...$param)) {
				while ($row = $r->fetch_array(MYSQLI_ASSOC)) {
					self::$cache["max"][$table . $column] = $row;
					return ($row["MAX(`" . $column . "`)"]);
				}
			} else {
				self::dumpError();
			}
		} else {
			return (self::$cache["max"][$table . $column]["MAX(`" . $column . "`)"]);
		}
	}

	/**
	 * Read a single column.
	 * @param string $table Table Name
	 * @param string $column Column Name
	 * @param string $findByCol Column need to be matched
	 * @param string $findByVal Value inside $findByCol need to be matched
	 * @return string
	 */
	public static function read($table, $column, $findByCol, $findByVal)
	{
		self::x_verify($table);
		if (!isset(self::$cache["read"][$table . $findByCol . $findByVal])) {
			if ($retval = self::query("SELECT * FROM `?` WHERE `?`='?' LIMIT 1;", $table, $findByCol, $findByVal)) {
				while ($row = $retval->fetch_array(MYSQLI_ASSOC)) {
					self::$cache["read"][$table . $findByCol . $findByVal] = $row;
					return ($row[$column]);
				}
			} else {
				self::dumpError();
			}
		} else {
			return (self::$cache["read"][$table . $findByCol . $findByVal][$column]);
		}
	}

	/**
	 * Read a single column with custom argument.
	 * @param string $table Table Name
	 * @param string $column Column Name
	 * @param string $arg Additional custom parameter
	 * @param string $param Additional custom parameter
	 * @return string
	 */
	public static function readArg($table, $column, $arg = "", ...$param)
	{
		self::x_verify($table);
		$c_param = serialize($param);
		if (!isset(self::$cache["readArg"][$table . $arg . $c_param])) {
			if ($retval = self::query("SELECT * FROM `?` " . $arg . ";", $table, ...$param)) {
				while ($row = mysqli_fetch_array($retval, MYSQLI_ASSOC)) {
					self::$cache["readArg"][$table . $arg . $c_param] = $row;
					return (self::$cache["readArg"][$table . $arg . $c_param][$column]);
				}
				self::$cache["readArg"][$table . $arg . $c_param] = [];
			} else {
				self::dumpError();
			}
		} else {
			return (self::$cache["readArg"][$table . $arg . $c_param][$column]);
		}
	}

	/**
	 * Write a new record
	 * NOTE: AUTO_INCRECEMENT column will be ignored
	 * 
	 * @param string $table
	 * @param mixed ...$array
	 * @return mysqli_result
	 */
	public static function newRow($table, ...$array)
	{
		self::x_verify($table);
		if (count($array) < 1) throw new DatabaseError('$array expecting array');

		$fList = "";
		$cList = "";
		$columns = self::toArray(self::query("show columns from `?`;", $table))->data;
		reset($array);

		foreach ($columns as $k) {
			//Skip auto_increment column
			if (str_contains($k["Extra"], "auto_increment")) continue;
			if (current($array) === false) {
				if ($k["Default"] != null) break;
				throw new DatabaseError("Not enough parameter");
			} else {
				$cList .= '`' . $k["Field"] . '`,';
				$fList .= current($array) === null ? "NULL," : "'" . self::escape(current($array)) . "',";
				next($array);
			}
		}

		return (self::query("INSERT INTO `$table` (" . rtrim($cList, ",") . ") VALUES (" . rtrim($fList, ",") . ");"));
	}

	/**
	 * Write a new record by specifiying column name
	 * @param string $table Table Name
	 * @param DatabaseRowInput $row_input
	 * @return mysqli_result
	 */
	public static function newRowAdvanced($table, DatabaseRowInput $row_input)
	{
		self::x_verify($table);
		$structure = $row_input->getStructure();
		$col_list = "";
		$value_list = "";
		foreach ($structure as $column => $value) {
			if ($column == "") continue;
			$col_list .= "`$column`, ";
			$value_list .= ($value === null) ? "NULL," : "'" . self::escape($value) . "', ";
		}
		$row_input->clearStructure();
		return (self::query("INSERT INTO `" . $table . "` (" . rtrim($col_list, ", ") . ") VALUES (" . rtrim($value_list, ", ") . ");"));
	}

	/**
	 * Update database row using DatabaseRowInput
	 * @param string $table
	 * @param DatabaseRowInput $row_input
	 * @param string $findByCol
	 * @param string $findByVal
	 * @return bool
	 */
	public static function updateRowAdvanced($table, DatabaseRowInput $row_input, $findByCol, $findByVal)
	{
		self::x_verify($table);
		$findByCol = self::escape($findByCol);
		$findByVal = self::escape($findByVal);
		$structure = $row_input->getStructure();
		$data = "";
		foreach ($structure as $column => $value) {
			if ($column == "") continue;
			$data .= ($value === null) ? "`$column`=NULL," : "`$column`='" . self::escape($value) . "',";
		}
		return (self::query("UPDATE `$table` SET " . rtrim($data, ",") . " WHERE `$findByCol`='$findByVal'"));
	}

	/**
	 * Delete a record
	 * @param string $table Table Name
	 * @param string $findByCol Column need to be matched
	 * @param string $findByVal Value inside $findByCol need to be matched
	 * @return bool
	 */
	public static function deleteRow($table, $findByCol, $findByVal)
	{
		self::x_verify($table);
		return (self::query("DELETE FROM `?` WHERE `?`='?';", $table, $findByCol, $findByVal));
	}

	/**
	 * Delete a record with custom argument
	 * @param string $table Table Name
	 * @param string $arg Table Name
	 * @param array ...$param
	 * @return bool
	 */
	public static function deleteRowArg($table, $arg, ...$param)
	{
		self::x_verify($table);
		return (self::query("DELETE FROM `?` " . $arg . ";", $table, ...$param));
	}

	/**
	 * NOTE: BE CAREFUL! CANNOT BE UNDONE!
	 * Drop a table.
	 * @param string $table Table Name
	 * @return bool
	 */
	public static function dropTable($table)
	{
		self::x_verify($table);
		return (self::query("DROP TABLE `?`;", $table));
	}

	/**
	 * Execute a single query.
	 * @param string $query For better security, use '?' as a mark for each parameter.
	 * @param mixed ...$param Will replace the '?' as parameterized queries
	 * @return mysqli_result
	 */
	public static function exec($query, ...$param)
	{
		self::x_verify($query);
		if (!isset(self::$cache["exec"][$query . serialize($param)])) {
			self::$cache["exec"][$query . serialize($param)] = self::query($query, ...$param);
		}
		return self::$cache["exec"][$query . serialize($param)];
	}

	/**
	 * Escape string for database query
	 * @return string
	 */
	public static function escape($str)
	{
		return (self::$link->real_escape_string($str));
	}

	/**
	 * Check if table is exists
	 * @param string $table Table name
	 * @return bool
	 */
	public static function isTableExist($table)
	{
		if ($table == "") throw new DatabaseError("Table name cannot be empty!");
		self::x_verify($table);
		if (!isset(self::$cache["tables"])) {
			$q = self::query("SHOW TABLES");
			while ($r = mysqli_fetch_array($q)) {
				self::$cache["tables"][$r[0]] = 1;
			}
		}
		return (isset(self::$cache["tables"][$table]));
	}

	/**
	 * Read all record in a table
	 * @param string $table Table name
	 * @param string $options Additional queries syntax. e.g. "SORT ASC BY `id`"
	 * @param array $param
	 * @return object
	 */
	public static function readAll($table, $options = "", ...$param)
	{
		if ($table == "") throw new DatabaseError("Please fill the table name!");
		self::x_verify($table);
		if (!isset(self::$cache["readAll"][$table . $options . serialize($param)])) {
			$array = self::toArray(self::query("SELECT * FROM `$table` $options;", ...$param));
			self::$cache["readAll"][$table . $options . serialize($param)] = $array;
			return ($array);
		} else {
			return (self::$cache["readAll"][$table . $options . serialize($param)]);
		}
	}

	/**
	 * Read all record in a table, and process it with custom iterator
	 * @param string $table Table name
	 * @param callable $iterator
	 * @param string $options Additional queries syntax. e.g. "SORT ASC BY `id`"
	 * @param array $param
	 * @return array
	 */
	public static function readAllCustom($table, $iterator, $options = "", ...$param)
	{
		if ($table == "") throw new DatabaseError("Please fill the table name!");
		if (!is_callable($iterator)) throw new DatabaseError('$iterator should be Callable!');
		self::x_verify($table);
		if (!isset(self::$cache["readAllCustom"][$table . $options . serialize($param) . spl_object_hash($iterator)])) {
			$array = self::toCustom(self::query("SELECT * FROM `$table` $options;", ...$param), $iterator);
			self::$cache["readAllCustom"][$table . $options . serialize($param)] = $array;
			return ($array);
		} else {
			return (self::$cache["readAllCustom"][$table . $options . serialize($param) . spl_object_hash($iterator)]);
		}
	}

	/**
	 * Fetch all mysql result and convert it into array
	 * @param mysqli_result $result 
	 * @return object
	 */
	public static function toArray(mysqli_result $result)
	{
		$array = ["data" => [], "num" => 0];
		if (!$result) self::dumpError();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$array["data"][] = $row;
			$array["num"]++;
		}
		return ((object)$array);
	}

	/**
	 * Fetch all mysql result and convert it into custom object
	 * @param mysqli_result $result 
	 * @param Closure $iterator 
	 * @return object
	 */
	public static function toCustom(mysqli_result $result, $iterator)
	{
		if (!is_callable($iterator)) throw new DatabaseError('$iterator should be Callable!');
		$array = ["data" => [], "num" => 0];
		if (!$result) self::dumpError();
		while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
			$array["data"][] = $iterator($row);
			$array["num"]++;
		}
		return ((object)$array);
	}

	/**
	 * Do a transaction in Database Engine.
	 * If some of the function throws an error,
	 * we will Rollback the database action.
	 * 
	 * @param callable $handler
	 * @return mixed
	 */
	public static function transaction($handler)
	{
		if (!is_callable($handler)) throw new DatabaseError('$handler should be callable!');
		self::$link->begin_transaction();
		try {
			$r = $handler();
			self::$link->commit();
			return $r;
		} catch (Exception $e) {
			self::$link->rollback();
			throw new PuzzleError($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Begin a transaction manually.
	 * @return bool
	 */
	public static function transaction_begin()
	{
		return self::$link->begin_transaction();
	}

	/**
	 * Commit transaction manually.
	 * @return bool
	 */
	public static function transaction_commit()
	{
		return self::$link->commit();
	}

	/**
	 * Rollback transaction manually.
	 * @return bool
	 */
	public static function transaction_rollback()
	{
		return self::$link->rollback();
	}

	/**
	 * Create or change table structure
	 * @param string $table Table name
	 * @param DatabaseTableBuilder $structure
	 * @return array
	 */
	public static function newStructure($table, DatabaseTableBuilder $structure)
	{
		set_time_limit(0);
		$indexes = $structure->x_getIndexes();
		$initialData = $structure->x_getInitialRow();
		$needToDrop = $structure->x_needToDropTable();
		$structure = $structure->x_getStructure();

		self::x_verify($table);

		if (!isset(self::$t_cache[$table])) {
			if (file_exists(__ROOTDIR . "/storage/dbcache/$table")) {
				self::$t_cache[$table] = file_get_contents(__ROOTDIR . "/storage/dbcache/$table");
			} else {
				self::$t_cache[$table] = null;
			}
		}
		
		/* Checking checksum */
		$old_checksum = self::$t_cache[$table];
		$current_checksum = hash("crc32b", serialize([$structure, $indexes, $initialData]));
		$write_cache_file = false;
		if ($old_checksum != "") {
			//Old table, new structure
			if ($current_checksum == $old_checksum) {
				if (self::isTableExist($table)) {
					set_time_limit(TIME_LIMIT);
					return true;
				}
				//Checksum is found, but table is not exists
				$q = false;
				$insertData = true;
			} else {
				self::$t_cache[$table] = $current_checksum;
				$write_cache_file = true;
				$q = self::isTableExist($table);
				/* Drop table if necessary */
				if ($needToDrop && $q) {
					self::query("DROP TABLE `?`;", $table);
					$insertData = true;
					$q = false;
				}
			}
		} else {
			//Brand new table
			self::$t_cache[$table] = $current_checksum;
			$write_cache_file = true;
			$insertData = true;
			$q = self::isTableExist($table);
			if ($q) $insertData = false;
		}

		if (!$q) {
			//Create Table
			$query = "CREATE TABLE `" . $table . "` ( ";
			
			//Appending table structure
			foreach ($structure as $k => $d) {
				if ($d[1]) $pkey = $k;
				$ds = (($d[3] === null) ? "" : "DEFAULT '" . $d[3] . "'");
				$ds = ($d[3] === "AUTO_INCREMENT" ? "AUTO_INCREMENT" : $ds);

				$query .= "`" . $k . "` " . strtoupper($d[0]) . " " . ($d[2] === true ? "NULL" : "NOT NULL") . " " . $ds . ",";
			}
			
			//Appending Primary key
			if ($pkey != "") $query .= "PRIMARY KEY (`" . $pkey . "`),";
			
			//Appending all index
			foreach ($indexes as $i) {
				$c = "";
				foreach ($i[1] as $ci) $c .= "`$ci`,";
				$c = rtrim($c, ",");
				$query .= "{$i[2]} INDEX `{$i[0]}` ($c),";
			}
			
			//Using InnoDB engine is better than MyISAM
			$query = rtrim($query, ",");
			$query .= ") COLLATE='utf8_general_ci' ENGINE=InnoDB;";

			if (defined("DB_DEBUG")) file_put_contents(__ROOTDIR . "/db.log", "$query\r\n", FILE_APPEND);

			if (mysqli_query(self::$link, $query)) {
				if ($insertData) {
					foreach ($initialData["simple"] as $row) {
						self::newRow($table, ...$row);
					}
					foreach ($initialData["advance"] as $row) {
						self::newRowAdvanced($table, $row);
					}
				}
				if ($write_cache_file) file_put_contents(__ROOTDIR . "/storage/dbcache/$table", self::$t_cache[$table]);
				set_time_limit(TIME_LIMIT);
				return true;
			} else {
				throw new DatabaseError(mysqli_error(self::$link), $query);
			}
		} else {
			//Update Tables
			$query = "";
			$a = [];
			$cpkey = "";
			
			//Fetching table data, and remove auto_increment column
			$q = mysqli_query(self::$link, "show columns from `$table`");
			while ($d = mysqli_fetch_array($q)) {
				$fd = ($d["Extra"] == "auto_increment" ? "AUTO_INCREMENT" : $d["Default"]);
				$a[$d["Field"]] = array(strtoupper($d["Type"]), ($d["Null"] == "YES" ? true : false), 1, $fd, false);
				if ($d["Key"] == "PRI") $cpkey = $d["Field"];
				if ($fd == "AUTO_INCREMENT") {
					$query .= "ALTER TABLE `" . $table . "` CHANGE COLUMN `" . $d["Field"] . "` `" . $d["Field"] . "` " . strtoupper($d["Type"]) . " " . ($d["Null"] === true ? "NULL" : "NOT NULL") . " ;\n";
					$a[$d["Field"]][4] = true;
				}
			}
			$pkey = "";
			
			//DROP Column
			foreach ($a as $k => $b) {
				if (!isset($structure[$k])) {
					if ($cpkey == $k) $cpkey = "";
					$query .= "ALTER TABLE `" . $table . "` DROP COLUMN `" . $k . "`;\n";
				}
			}
			
			//ALTER Table
			foreach ($structure as $k => $d) {
				if ($d[1]) $pkey = $k;
				if ($a[$k][2] != 1) {
					//Add new column
					$ds = (($d[3] === null) ? "" : "DEFAULT '" . $d[3] . "'");
					$ds = ($d[3] === "AUTO_INCREMENT" ? "AUTO_INCREMENT" : $ds);
					if ($d[3] === "AUTO_INCREMENT") {
						//In this part, we don't care much about null value, since auto_increment always not_null.
						//Auto increment value, will be automatially be the primary key
						if ($cpkey != "") {
							$query .= "ALTER TABLE `" . $table . "` DROP PRIMARY KEY;\n";
							$cpkey = "";
						}
						$pkey = "";
						$query .= "ALTER TABLE `" . $table . "` ADD COLUMN `" . $k . "` " . strtoupper($d[0]) . " NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY(`$k`);\n";
					} else {
						$query .= "ALTER TABLE `" . $table . "` ADD COLUMN `" . $k . "` " . strtoupper($d[0]) . " " . ($d[2] === true ? "NULL" : "NOT NULL") . " $ds;\n";
					}
					unset($query1);
				} else {
					//Change old column
					$st = preg_match("/" . strtoupper($d[0]) . "/", $a[$k][0]);
					$an = $d[2] == $a[$k][1];
					$def = $a[$k][3] == strtoupper($d[3]);
					if (!$st || !$an || !$def || $a[$k][4]) {
						$ds = (($d[3] !== "0" && empty($d[3])) ? "" : "DEFAULT '" . $d[3] . "'");
						$ds = ($d[3] === "AUTO_INCREMENT" ? "AUTO_INCREMENT" : $ds);

						if ($d[3] === "AUTO_INCREMENT") {
							//In this part, we don't care much about null value, since auto_increment always not_null.
							//Auto increment value, will be automatially be the primary key
							if ($cpkey != "") {
								if ($cpkey != $k) $query .= "ALTER TABLE `" . $table . "` DROP PRIMARY KEY, ADD PRIMARY KEY(`$k`);\n";
								$cpkey = "";
							} else {
								$query .= "ALTER TABLE `" . $table . "` ADD PRIMARY KEY(`$k`);\n";
							}
							$pkey = "";
							$query .= "ALTER TABLE `" . $table . "` CHANGE COLUMN `" . $k . "` `" . $k . "` " . strtoupper($d[0]) . " NOT NULL AUTO_INCREMENT;\n";
						} else {
							$query .= "ALTER TABLE `" . $table . "` CHANGE COLUMN `" . $k . "` `" . $k . "` " . strtoupper($d[0]) . " " . ($d[2] === true ? "NULL" : "NOT NULL") . " $ds;\n";
						}

					}
				}
			}

			/**
			 * Change Primary Key
			 * Correct order
			 * Add primary key first then add AI
			 * When removing primary key, make sure that table is not AI anymore
			 */
			if ($cpkey != $pkey) {
				//Check if db have primary key
				$needtodropkey = ($cpkey != "");
				if ($needtodropkey) {
					if ($pkey != "") $addpk = ", ADD PRIMARY KEY (`" . $pkey . "`)";
					$pri_ai_col = mysqli_fetch_array(mysqli_query(self::$link, "show columns from `$table` where `Extra` = 'auto_increment' AND `Key`='PRI';"));
					if (count($pri_ai_col) < 1)
						$query .= "ALTER TABLE `" . $table . "` DROP PRIMARY KEY$addpk;\n";
					else {
						//Addition is disabling the AUTO_INCREMENT before DROP PRIMARY Key.
						$addition = "ALTER TABLE `$table` CHANGE COLUMN `" . $pri_ai_col["Field"] . "` `" . $pri_ai_col["Field"] . "` " . $pri_ai_col["Type"] . " " . ($pri_ai_col["Null"] = "NO" ? "NOT NULL" : "NULL") . ";\n";
						$query .= $addition . "ALTER TABLE `" . $table . "` DROP PRIMARY KEY$addpk;\n";
					}
				} else {
					if ($pkey != "") $query .= "ALTER TABLE `" . $table . "` ADD PRIMARY KEY (`" . $pkey . "`);";
				}
			}
			
			//First Alter Execution
			if ($query != "") {
				if (defined("DB_DEBUG")) {
					file_put_contents(__ROOTDIR . "/db.log", "$query\r\n", FILE_APPEND);
				}
				if (!mysqli_multi_query(self::$link, $query)) throw new DatabaseError(mysqli_error(self::$link), $query);
				do {
					if ($result = mysqli_store_result(self::$link)) {
						mysqli_free_result($result);
					}
				} while (mysqli_next_result(self::$link));
			}

			//Reorder column position
			$query = "";
			$request = mysqli_query(self::$link, "show columns from `$table`");
			$tableContent = [];
			while ($r = mysqli_fetch_array($request)) {
				$tableContent[$r["Field"]] = $r;
			}
			$firstCol = true;
			$prevCol;
			foreach ($structure as $k => $d) {
				$i = $tableContent[$k];
				$type = $i["Type"];
				$nullon = ($i["Null"] == "YES" ? "NULL" : "NOT NULL");
				$def = ($i["Default"] != "" ? "DEFAULT '" . $i["Default"] . "'" : $i["Extra"]);
				$pos = ($firstCol ? "FIRST" : "AFTER `$prevCol`");
				$query .= "ALTER TABLE `$table` CHANGE COLUMN `$k` `$k` $type $nullon $def $pos;\n";
				$firstCol = false;
				$prevCol = $k;
			}
			
			//Deleting all Index
			$q = mysqli_query(self::$link, "show index from `$table`");
			$dik = [];
			while ($d = mysqli_fetch_array($q)) {
				if ($d["Key_name"] == "PRIMARY") continue;
				if (in_array($d["Key_name"], $dik)) continue;
				$dik[] = $d["Key_name"];
				$query .= "ALTER TABLE `$table` DROP INDEX `{$d["Key_name"]}`;\n";
			}
			
			//Re add defined index
			foreach ($indexes as $i) {
				$c = "";
				foreach ($i[1] as $ci) $c .= "`$ci`,";
				$c = rtrim($c, ",");
				$query .= "ALTER TABLE `$table` ADD {$i[2]} INDEX `{$i[0]}` ($c);\n";
			}

			if (defined("DB_DEBUG")) file_put_contents(__ROOTDIR . "/db.log", "$query\r\n", FILE_APPEND);
			
			//Second Round Execution
			if (!mysqli_multi_query(self::$link, $query)) throw new DatabaseError(mysqli_error(self::$link), $query);
			do {
				if ($result = mysqli_store_result(self::$link)) {
					mysqli_free_result($result);
				}
			} while (mysqli_next_result(self::$link));

			if ($write_cache_file) file_put_contents(__ROOTDIR . "/storage/dbcache/$table", self::$t_cache[$table]);
			set_time_limit(TIME_LIMIT);
			return true;
		}
	}
}

/* Opening connection to database */
Database::connect();
?>
