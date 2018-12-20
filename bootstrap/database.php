<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Interface for creating Database Row values.
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
		/**
		 * PHP use different number separator for different locale internally.
		 * MySQL only accept '.' as separator. As we detect the value 
		 * is numeric, we automatically perform conversion.
		 */
		if (is_numeric($value)) $value = str_replace(',', '.', $value);
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
 * Interface for defining Database table structure.
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
	 * @param array $structure
	 * @return DatabaseTableBuilder
	 */
	public function insertFresh(array $structure)
	{
		$this->rowStructure = $structure;
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
		$this->arrayStructure[$this->selectedColumn] = [strtoupper($type), false, false, null];
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
		foreach ($this->arrayStructure as $key => $data) {
			$this->arrayStructure[$key][1] = false;
		}
		return $this;
	}

	/**
	 * Allow column to be NULL
	 * @param bool $bool
	 * @return DatabaseTableBuilder
	 */
	public function allowNull($bool = true)
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
	public function defaultValue($str = null)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][3] = $str;
		return $this;
	}

	/**
	 * Set this column as auto increment value
	 * @return DatabaseTableBuilder
	 */
	public function auto_increment()
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][3] = "AUTO_INCREMENT";
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
		$this->arrayStructure[$this->selectedColumn][0] = strtoupper($type);
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

		self::$link = @new mysqli(POSConfigDB::$host, POSConfigDB::$username, POSConfigDB::$password, POSConfigDB::$database_name);
		if (self::$link->connect_error) {
			throw new DatabaseError(self::$link->connect_error, "PuzzleOS only supports MySQL or MariaDB");
		}
	}

	private static function dumpError()
	{
		throw new DatabaseError('MySQL Error: ' . self::$link->error);
	}

	/**
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
			if (self::$link->errno == "2014") {
				/* Perform a reconnect */
				self::$link->close();
				self::flushCache();
				self::connect();
				if (!($r = self::$link->query($escaped))) {
					throw new DatabaseError('Could not execute(' . self::$link->errno . '): ' . self::$link->errno, $escaped);
				}
			} else
				throw new DatabaseError('Could not execute(' . self::$link->errno . '): ' . self::$link->errno, $escaped);
		}
	}

	private static function x_verify($find)
	{
		if ($find == "") return false;
		$stack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
		$filename = $stack[str_contains($stack[3]["function"], "call_user_func") ? 3 : 2]["file"];
		if (is_cli() && isset($GLOBALS["_WORKER"])) {
			$appname = $GLOBALS["_WORKER"]["appdir"];
			if (!file_exists(__ROOTDIR . "/applications/$appname/manifest.ini")) throw new DatabaseError("Application do not have manifest!");
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
		if (defined("DB_DEBUG")) file_put_contents(__ROOTDIR . "/db.log", "CACHE PURGED\r\n", FILE_APPEND);
	}

	/**
	 * Get max value from column in table
	 * @param string $table Table Name
	 * @param string $column Column Name
	 * @param string $statement Custom parameter
	 * @param string $param Custom parameter
	 * @return string
	 */
	public static function max($table, $column, $statement = "", ...$param)
	{
		self::x_verify($table);
		if (!isset(self::$cache["max"][$table . $column])) {
			if ($r = self::query("SELECT MAX(`?`) FROM `?` $statement", $column, $table, ...$param)) {
				self::$cache["max"][$table . $column] = [$r->fetch_row()[0]];
				$r->free();
			} else {
				self::dumpError();
			}
		}

		return self::$cache["max"][$table . $column][0];
	}

	/**
	 * Read a single row.
	 * @param string $table Table Name
	 * @param string $find_column Column need to be matched
	 * @param string $find_value Value inside $find_column need to be matched
	 * @return string
	 */
	public static function getRow($table, $find_column, $find_value)
	{
		self::x_verify($table);
		if (!isset(self::$cache["getRow"][$table . $find_column . $find_value])) {
			if ($r = self::query("SELECT * FROM `?` WHERE `?`='?' LIMIT 1", $table, $find_column, $find_value)) {
				self::$cache["getRow"][$table . $find_column . $find_value] = [$r->fetch_assoc()];
				$r->free();
			} else {
				self::dumpError();
			}
		}
		return self::$cache["getRow"][$table . $find_column . $find_value][0];
	}

	/**
	 * Read a single row.
	 * @param string $table Table Name
	 * @param string $statement Custom statement
	 * @param string $param Parameterized value
	 * @return string
	 */
	public static function getRowByStatement($table, $statement, ...$param)
	{
		self::x_verify($table);
		$c = $table . $statement . serialize($param);
		if (!isset(self::$cache["getRowByStatement"][$c])) {
			if ($r = self::query("SELECT * FROM `?` $statement LIMIT 1", $table, ...$param)) {
				self::$cache["getRowByStatement"][$c] = [$r->fetch_assoc()];
				$r->free();
			} else {
				self::dumpError();
			}
		}
		return self::$cache["getRowByStatement"][$c][0];
	}

	/**
	 * Read a single column.
	 * @param string $table Table Name
	 * @param string $column Column Name
	 * @param string $find_column Column need to be matched
	 * @param string $find_value Value inside $find_column need to be matched
	 * @return string
	 */
	public static function read($table, $column, $find_column, $find_value)
	{
		self::x_verify($table);
		return self::getRow($table, $find_column, $find_value)[$column];
	}

	/**
	 * Read a single column with custom argument.
	 * @param string $table Table Name
	 * @param string $column Column Name
	 * @param string $statement Custom statement
	 * @param string $param Additional custom parameter
	 * @return string
	 */
	public static function readByStatement($table, $column, $statement, ...$param)
	{
		self::x_verify($table);
		return self::getRowByStatement($table, $statement, ...$param)[$column];
	}

	/**
	 * Write new record
	 * @param string $table Table Name
	 * @param array $row_input
	 * @return mysqli_result
	 */
	public static function insert($table, array $row_input)
	{
		self::x_verify($table);
		if (count($row_input) < 1) return true;

		$data = (object)["columns" => [], "values" => []];
		foreach ($row_input as $d) {
			if (!$d instanceof DatabaseRowInput) throw new DatabaseError('$row_input should be a DatabaseRowInput');
			$next_values = [];
			foreach ($d->getStructure() as $column => $value) {
				if (!isset($data->columns[$column])) $data->columns[$column] = count($data->columns);
				$next_values[$data->columns[$column]] = $value;
			}
			$data->values[] = $next_values;
		}

		$query = "INSERT INTO `$table` (";
		foreachx($data->columns, function ($index, $last, $column, $i) use (&$query) {
			$query .= "`$column`";
			if (!$last) $query .= ",";
		});
		$query .= ") VALUES ";
		foreachx($data->values, function ($i, $last, $k, $values) use (&$query) {
			$query .= "(";
			foreachx($values, function ($i, $last2, $k, $value) use (&$query) {
				$value = Database::escape($value);
				$query .= "'$value'";
				if (!$last2) $query .= ",";
			});
			$query .= ")";
			if (!$last) $query .= ",";
		});

		return self::query($query);
	}

	/**
	 * Update database row using DatabaseRowInput
	 * @param string $table
	 * @param DatabaseRowInput $row_input
	 * @param string $find_column
	 * @param string $find_value
	 * @return bool
	 */
	public static function update($table, DatabaseRowInput $row_input, $find_column, $find_value)
	{
		self::x_verify($table);
		$s = $row_input->getStructure();
		$query = "UPDATE `$table` SET ";
		foreachx($s, function ($i, $l, $column, $value) use (&$query) {
			$value = Database::escape($value);
			$query .= "`$column`='$value'";
			if (!$l) $query .= ",";
		});
		$query .= " WHERE `$find_column`='?'";
		return self::query($query, $find_value);
	}

	/**
	 * Returns the auto generated id used in the latest query
	 */
	public static function lastId()
	{
		return self::$link->insert_id;
	}

	/**
	 * Delete a record
	 * @param string $table Table Name
	 * @param string $find_column Column need to be matched
	 * @param string $find_value Value inside $find_column need to be matched
	 * @return bool
	 */
	public static function delete($table, $find_column, $find_value)
	{
		self::x_verify($table);
		return self::query("DELETE FROM `?` WHERE `?`='?';", $table, $find_column, $find_value);
	}

	/**
	 * Delete a record with custom argument
	 * @param string $table Table Name
	 * @param string $statement Custom statement
	 * @param array ...$param
	 * @return bool
	 */
	public static function deleteByStatement($table, $statement, ...$param)
	{
		self::x_verify($table);
		return self::query("DELETE FROM `?` $statement", $table, ...$param);
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
	 * Execute raw query.
	 * @param string $query For better security, use '?' as a mark for each parameter.
	 * @param mixed ...$param Will replace the '?' as parameterized queries
	 * @return mysqli_result
	 */
	public static function execute($query, ...$param)
	{
		self::x_verify($query);
		return self::query($query, ...$param);
	}

	/**
	 * Escape string for database query
	 * @return string
	 */
	public static function escape($str)
	{
		return self::$link->real_escape_string($str);
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
			while ($r = $q->fetch_row()) self::$cache["tables"][$r[0]] = 1;
		}
		return isset(self::$cache["tables"][$table]);
	}

	/**
	 * Read all record in a table
	 * @param string $table Table name
	 * @param string $statement Additional queries syntax. e.g. "SORT ASC BY `id`"
	 * @param array $param
	 * @return array
	 */
	public static function readAll($table, $statement = "", ...$param)
	{
		self::x_verify($table);
		$c = serialize($param);
		if (!isset(self::$cache["readAll"][$table . $statement . $c])) {
			$array = self::toArray(self::query("SELECT * FROM `$table` $statement", ...$param));
			self::$cache["readAll"][$table . $statement . $c] = [$array];
		}
		return self::$cache["readAll"][$table . $statement . $c][0];
	}

	/**
	 * Read all record in a table, and process it with custom iterator
	 * @param string $table Table name
	 * @param callable $iterator
	 * @param string $statement Additional queries syntax. e.g. "SORT ASC BY `id`"
	 * @param array $param
	 * @return array
	 */
	public static function readAllCustom($table, $iterator, $statement = "", ...$param)
	{
		if (!is_callable($iterator)) throw new DatabaseError('$iterator should be Callable!');
		self::x_verify($table);
		$c = $table . $statement . serialize($param) . spl_object_hash($iterator);
		if (!isset(self::$cache["readAllCustom"][$c])) {
			$array = self::toCustom(self::query("SELECT * FROM `$table` $statement", ...$param), $iterator);
			self::$cache["readAllCustom"][$c] = [$array];
		}
		return self::$cache["readAllCustom"][$c][0];
	}

	/**
	 * Fetch all mysql result and convert it into array
	 * @param mysqli_result $result 
	 * @return array
	 */
	public static function toArray(mysqli_result $result)
	{
		if (!$result) self::dumpError();
		return $result->fetch_all(MYSQLI_ASSOC);
	}

	/**
	 * Fetch all mysql result and convert it into custom object.
	 * Return true to skip object, or return array to put array inside container.
	 * Return false to quit
	 * 
	 * @param mysqli_result $result 
	 * @param Closure $iterator 
	 * @return object
	 */
	public static function toCustom(mysqli_result $result, $iterator)
	{
		if (!is_callable($iterator)) throw new DatabaseError('$iterator should be Callable!');
		if (!$result) self::dumpError();
		$array = [];
		while ($row = $result->fetch_assoc()) {
			if (($r = $iterator($row)) === true) continue;
			elseif ($r === false) break;
			else $array[] = $iterator($row);
		}
		return $array;
	}

	/**
	 * Do a transaction in MySQL.
	 * If some of the function throws an error,
	 * we will rollback the database action.
	 * 
	 * @param callable $handler
	 * @return mixed
	 */
	public static function transaction(callable $handler)
	{
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
					self::query("DROP TABLE `?`", $table);
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
			$query = "CREATE TABLE `$table` (";
			
			//Appending table structure
			foreach ($structure as $k => $d) {
				if ($d[1]) $pkey = $k;
				if ($d[3] !== null && $d[3] !== "AUTO_INCREMENT") $d[3] = "DEFAULT '$d[3]'";
				$query .= "`$k` $d[0] " . ($d[2] === true ? "NULL" : "NOT NULL") . " $d[3],";
			}
			
			//Appending Primary key
			if ($pkey != "") $query .= "PRIMARY KEY (`$pkey`),";
			
			//Appending all index
			foreach ($indexes as $i) {
				$c = "";
				foreach ($i[1] as $ci) $c .= "`$ci`,";
				$c = rtrim($c, ",");
				$query .= "$i[2] INDEX `$i[0]` ($c),";
			}
			
			//Using InnoDB engine is better than MyISAM
			$query = rtrim($query, ",");
			$query .= ") COLLATE='utf8_general_ci' ENGINE=InnoDB;";

			if (defined("DB_DEBUG")) file_put_contents(__ROOTDIR . "/db.log", "$query\r\n", FILE_APPEND);

			if (self::$link->query($query)) {
				if ($insertData) self::insert($table, $initialData);
				if ($write_cache_file) file_put_contents(__ROOTDIR . "/storage/dbcache/$table", self::$t_cache[$table]);
				set_time_limit(TIME_LIMIT);
				return true;
			} else {
				throw new DatabaseError(self::$link->error, $query);
			}
		} else {
			//Getting current table structure
			$current_structure = new DatabaseTableBuilder;
			$old_primary = null;
			$new_primary = null;
			foreach (self::toArray(self::query("show columns from `$table`")) as $column_def) {
				$current_structure->addColumn($column_def["Field"], $column_def["Type"])->allowNull($column_def["Null"] == "YES");
				if ($column_def["Extra"] == "auto_increment") $current_structure->defaultValue("AUTO_INCREMENT");
				else $current_structure->defaultValue($column_def["Default"]);
			}
			$current_index = [];
			foreach (self::toArray(self::query("show index from `$table`")) as $index_def) {
				if ($index_def["Key_name"] == "PRIMARY") {
					$current_structure->selectColumn($index_def["Column_name"])->setAsPrimaryKey();
					$old_primary = $index_def["Column_name"];
				} else {
					$current_index[$index_def["Key_name"]] = 1;
				}
			}
			//Structure = [Type, PRIMARY, AllowNULL, Default]
			$current_structure = $current_structure->x_getStructure();

			$query = "ALTER TABLE `$table` ";
			$pre_e = null;
			foreach ($structure as $column => $d) {
				//Cek ada atau tidak
				if (isset($current_structure[$column])) {
					//Lihat perubahan
					if ($current_structure[$column] != $structure[$column]) {
						//CHANGE COLUMN
						if ($d[1]) $new_primary = $column;
						if ($d[3] !== null && $d[3] !== "AUTO_INCREMENT") $d[3] = "DEFAULT '$d[3]'";
						$p = ($pre_e === null) ? "FIRST" : "AFTER `$pre_e`";
						$query .= "CHANGE COLUMN `$column` `$column` $d[0] " . ($d[2] === true ? "NULL" : "NOT NULL") . " $d[3] $p,";
					}
				} else {
					//ADD COLUMN
					if ($d[1]) $new_primary = $column;
					if ($d[3] !== null && $d[3] !== "AUTO_INCREMENT") $d[3] = "DEFAULT '$d[3]'";
					$p = ($pre_e === null) ? "FIRST" : "AFTER `$pre_e`";
					$query .= "ADD COLUMN `$column` $d[0] " . ($d[2] === true ? "NULL" : "NOT NULL") . " $d[3] $p,";
				}
				$pre_e = $column;
			}

			//DROP COLUMN
			foreach ($current_structure as $column => $d) {
				if (!isset($structure[$column])) $query .= "DROP COLUMN `$column`,";
			}

			//Primary key
			if ($old_primary != null) $query .= "DROP PRIMARY KEY,";
			if ($new_primary != null) {
				$query .= "ADD PRIMARY KEY (`$new_primary`),";
			}

			//DROP INDEX
			foreach ($current_index as $index => $d) {
				$query .= "DROP INDEX `$index`,";
			}

			//ADD INDEX
			foreach ($indexes as $i) {
				$c = "";
				foreach ($i[1] as $ci) $c .= "`$ci`,";
				$c = rtrim($c, ",");
				$query .= "ADD $i[2] INDEX `$i[0]` ($c),";
			}

			if (defined("DB_DEBUG")) file_put_contents(__ROOTDIR . "/db.log", "$query\r\n", FILE_APPEND);

			if (self::$link->query(rtrim($query, ","))) {
				if ($write_cache_file) file_put_contents(__ROOTDIR . "/storage/dbcache/$table", self::$t_cache[$table]);
				set_time_limit(TIME_LIMIT);
				return true;
			} else {
				throw new DatabaseError(self::$link->error, $query);
			}
		}
	}
}

Database::connect();
