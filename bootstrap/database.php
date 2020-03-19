<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
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

	public static function fromArray($dri)
	{
		switch (true) {
			case ($dri instanceof self):
				return $dri;
			case (is_array($dri)):
				$a = new self;
				foreach ($dri as $k => $d) $a->setField($k, $d);
				return $a;
			default:
				throw new \InvalidArgumentException("Expecting DatabaseRowInput object or array");
		}
	}
}

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
	public function createIndex(string $name, array $column, $type = "")
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
	public function addColumn(string $name, string $type = "TEXT")
	{
		if (strlen($name) > 50) throw new DatabaseError("Max length for column name is 50 chars");
		$this->selectedColumn = $name;
		//Structure = [Type, PRIMARY, AllowNULL, Default, PRESISTENT]
		$this->arrayStructure[$this->selectedColumn] = [strtoupper($type), false, false, null, false];
		return $this;
	}

	/**
	 * Change column selection
	 * @param string $name
	 * @return DatabaseTableBuilder
	 */
	public function selectColumn(string $name)
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
	public function allowNull(bool $bool = true)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][2] = $bool ? "NULL" : "NOT NULL";
		return $this;
	}

	/**
	 * Make this column presistent as. Effective for indexing.
	 * @param string $expression
	 * @return DatabaseTableBuilder
	 */
	public function presistentAs(string $expression = null)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][4] = $expression ? "AS ($expression) PERSISTENT" : false;
		return $this;
	}

	/**
	 * Set default value for this column.
	 * Make sure that your data type suppport defaultValue
	 * @return DatabaseTableBuilder
	 */
	public function defaultValue(string $str = null)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][3] = ($str === NULL ? "DEFAULT NULL" : "DEFAULT '$str'");
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
	public function setType(string $type)
	{
		if ($this->selectedColumn == "") throw new DatabaseError("Please select the column!");
		$this->arrayStructure[$this->selectedColumn][0] = strtoupper($type);
		return $this;
	}
}

class Database
{
	/**
	 * @var mysqli
	 */
	private static $link;
	private static $cache = [];
	private static $t_cache = [];
	private static $transaction_track = 0;
	private const IMPLICIT_COMMIT = [
		"ALTER DATABASE ... UPGRADE DATA DIRECTORY NAME",
		"ALTER EVENT",
		"ALTER FUNCTION",
		"ALTER PROCEDURE",
		"ALTER SERVER",
		"ALTER TABLE",
		"ALTER VIEW",
		"ANALYZE TABLE",
		"BEGIN",
		"CACHE INDEX",
		"CHANGE MASTER TO",
		"CHECK TABLE",
		"CREATE DATABASE",
		"CREATE EVENT",
		"CREATE FUNCTION",
		"CREATE INDEX",
		"CREATE PROCEDURE",
		"CREATE ROLE",
		"CREATE SERVER",
		"CREATE TABLE",
		"CREATE TRIGGER",
		"CREATE USER",
		"CREATE VIEW",
		"DROP DATABASE",
		"DROP EVENT",
		"DROP FUNCTION",
		"DROP INDEX",
		"DROP PROCEDURE",
		"DROP ROLE",
		"DROP SERVER",
		"DROP TABLE",
		"DROP TRIGGER",
		"DROP USER",
		"DROP VIEW",
		"FLUSH",
		"GRANT",
		"LOAD INDEX INTO CACHE",
		"LOCK TABLES",
		"OPTIMIZE TABLE",
		"RENAME TABLE",
		"RENAME USER",
		"REPAIR TABLE",
		"RESET",
		"REVOKE",
		"SET PASSWORD",
		"SHUTDOWN",
		"START SLAVE",
		"START TRANSACTION",
		"STOP SLAVE",
		"TRUNCATE TABLE"
	];

	public static function connect()
	{
		if (!is_callbyme()) throw new DatabaseError("Database violation!");

		self::$link = @new mysqli(POSConfigDB::$host, POSConfigDB::$username, POSConfigDB::$password, POSConfigDB::$database_name);
		if (self::$link->connect_error) {
			abort(503, "Internal Server Error", false);
			throw new DatabaseError(self::$link->connect_error, "PuzzleOS only supports MySQL or MariaDB", (int) self::$link->connect_errno);
		}
	}

	private static function dumpError()
	{
		throw new DatabaseError('MySQL Error', self::$link->error, self::$link->errno);
	}

	/**
	 * @return mysqli_result
	 */
	private static function query($query, ...$param)
	{
		$query = trim($query);
		$escaped = "";
		$token = strtok($query, '?');
		reset($param);
		$processedLen = 0;
		do {
			$escaped .= $token;
			$processedLen += strlen($token) + 1;
			$currentParam = current($param);
			if ($processedLen >= strlen($query)) {
				if ($currentParam !== false) $escaped .= $currentParam === null ? "NULL" : self::escape($currentParam);
				break;
			} else if ($currentParam === false) {
				throw new DatabaseError("Not enough parameter");
			} else {
				$escaped .= $currentParam === null ? "NULL" : self::escape($currentParam);
				next($param);
			}
		} while ($token = strtok('?'));

		if (defined("DB_DEBUG")) {
			$re = debug_backtrace()[1];
			file_put_contents(__LOGDIR . "/db.log", $re["file"] . ":" . $re["line"] . "\r\n\t$escaped\r\n\r\n", FILE_APPEND);
		}

		if (!($r = self::$link->query($escaped))) {
			if (self::$link->errno == "2014") {
				self::$link->close();
				self::flushCache();
				self::connect();
				if (!($r = self::$link->query($escaped))) {
					throw new DatabaseError(self::$link->error, $escaped, (int) self::$link->errno);
				}
			} else
				throw new DatabaseError(self::$link->error, $escaped, (int) self::$link->errno);
		}

		if ($r && !($r instanceof mysqli_result) && self::$link->affected_rows > 0) {
			// Rows changed, flushing cache
			self::flushCache();

			if (self::$transaction_track > 0) {
				foreach (self::IMPLICIT_COMMIT as $statement) {
					// Auto commit was detected, ignoring current transaction
					if (starts_with($escaped, $statement)) {
						self::$transaction_track = 0;
						break;
					}
				}
			}
		}
		return $r;
	}

	private static function x_verify($find)
	{
		if ($find == "") return false;
		$stack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
		$filename = $stack[str_contains($stack[2]["function"], "call_user_func") ? 2 : 1]["file"];
		if (is_cli() && isset($GLOBALS["_WORKER"])) {
			$appname = $GLOBALS["_WORKER"]["app"];
			if ((preg_match('/app_' . $appname . '_/', $find))) return true;
		}

		$filename = explode("/", str_replace(__ROOTDIR, "", btfslash($filename)));
		switch ($filename[1]) {
			case "bootstrap":
				switch ($filename[2]) {
					case "application.php":
					case "services.php":
					case "database.php":
					case "systables.php":
						return true;
					case "cron.php":
						if ((preg_match('/cron/', $find))) return true;
						break;
					case "isession.php":
						if ((preg_match('/sessions/', $find))) return true;
						break;
					case "configman.php":
						if ((preg_match('/multidomain_config/', $find))) return true;
						break;
					case "userdata.php":
					case "boot.php":
						if ((preg_match('/userdata/', $find))) return true;
						break;
				}
				break;
			case "applications":
				$appname = isset($filename[2]) ? $filename[2] : "";
				$appname = AppManager::getNameFromDirectory($appname);
				if ((preg_match('/app_' . $appname . '_/', $find))) return (true);
		}

		throw new DatabaseError("Database table violation.");
	}

	/**
	 * Flush database cache
	 */
	private static function flushCache()
	{
		self::$cache = [];
		self::$t_cache = [];
		if (defined("DB_DEBUG")) file_put_contents(__LOGDIR . "/db.log", "CACHE PURGED\r\n", FILE_APPEND);
	}

	/**
	 * Begin a transaction manually.
	 * @return bool
	 */
	private static function transaction_begin()
	{
		if (self::$transaction_track == 0) {
			self::$transaction_track++;
			return self::$link->begin_transaction();
		} else {
			return self::$link->savepoint("T" . self::$transaction_track++);
		}
	}

	/**
	 * Commit transaction manually.
	 * @return bool
	 */
	private static function transaction_commit()
	{
		if (self::$transaction_track <= 1) {
			self::$transaction_track = 0;
			return self::$link->commit();
		} else {
			return self::$link->release_savepoint("T" . --self::$transaction_track);
		}
	}

	/**
	 * Rollback transaction manually.
	 * @return bool
	 */
	private static function transaction_rollback()
	{
		if (self::$transaction_track <= 1) {
			self::$transaction_track = 0;
			return self::$link->rollback();
		} else {
			return self::$link->query("ROLLBACK TO " . "T" . --self::$transaction_track);
		}
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
	 * @return array
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
	 * @return array
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
	 * @param DatabaseRowInput[] $row_input use DRI()
	 * @param bool $ignore Allow insert to be ignored
	 * @return bool
	 */
	public static function insert($table, array $row_input, bool $ignore = false)
	{
		foreach ($row_input as &$i) $i = DatabaseRowInput::fromArray($i);
		self::x_verify($table);
		if (count($row_input) < 1) return true;

		$args = [""];

		$data = (object) ["columns" => [], "values" => []];
		foreach ($row_input as $d) {
			$next_values = [];
			foreach ($d->getStructure() as $column => $value) {
				if (!isset($data->columns[$column])) $data->columns[$column] = count($data->columns);
				$next_values[$data->columns[$column]] = $value;
			}
			$data->values[] = $next_values;
		}

		$query = "INSERT " . ($ignore ? "IGNORE " : "") . "INTO `$table` (";
		foreachx($data->columns, function ($index, $last, $column, $i) use (&$query) {
			$query .= "`$column`";
			if (!$last) $query .= ",";
		});
		$query .= ") VALUES ";
		foreachx($data->values, function ($i, $last, $k, $values) use (&$query, &$args) {
			$query .= "(";
			foreachx($values, function ($i, $last2, $k, $value) use (&$query, &$args) {
				if ($value === null) {
					$query .= "NULL";
				} elseif ($value === 0) {
					$query .= "0";
				} else {
					$args[] = $value;
					$query .= "'?'";
				}
				if (!$last2) $query .= ",";
			});
			$query .= ")";
			if (!$last) $query .= ",";
		});

		$args[0] = $query;
		return call_user_func_array([self::class, "query"], $args);
	}

	/**
	 * Update database row using DatabaseRowInput
	 * @param string $table
	 * @param DatabaseRowInput $row_input
	 * @param string $find_column
	 * @param string $find_value
	 * @return bool
	 */
	public static function update($table, $row_input, $find_column, $find_value)
	{
		if (is_array($row_input)) {
			$row_input = DatabaseRowInput::fromArray($row_input);
		} else if (!($row_input instanceof DatabaseRowInput)) {
			throw new InvalidArgumentException("Expecting DatabaseRowInput");
		}

		self::x_verify($table);
		$s = $row_input->getStructure();

		$args = [""];

		$query = "UPDATE `$table` SET ";
		foreachx($s, function ($i, $l, $column, $value) use (&$query, &$args) {
			$query .= "`$column`=";
			if ($value === null) {
				$query .= "NULL";
			} elseif ($value === 0) {
				$query .= "0";
			} else {
				$args[] = $value;
				$query .= "'?'";
			}
			if (!$l) $query .= ",";
		});
		$query .= " WHERE `$find_column`='?'";

		$args[0] = $query;
		$args[] = $find_value;
		return call_user_func_array([self::class, "query"], $args);
	}

	/**
	 * Returns the auto generated id used in the latest query
	 */
	public static function lastId()
	{
		return self::$link->insert_id;
	}

	/**
	 * Returns the number of affected rows
	 */
	public static function affectedRows()
	{
		return self::$link->affected_rows;
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
	 * @return mysqli_result|bool
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
			$q = self::query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '?'", POSConfigDB::$database_name);
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
		self::transaction_begin();
		try {
			$r = $handler();
			self::transaction_commit();
			return $r;
		} catch (Throwable $e) {
			self::transaction_rollback();
			throw $e;
		}
	}

	/**
	 * Acquire a table lock
	 * @return bool
	 */
	public static function lock($table, $for = "WRITE")
	{
		self::x_verify($table);
		switch ($for) {
			case 'WRITE':
			case 'READ':
				break;
			default:
				throw new InvalidArgumentException("Only WRITE or READ allowed");
		}
		return self::query("LOCK TABLES `$table` $for;");
	}

	/**
	 * Release a table lock
	 * @return bool
	 */
	public static function unlock()
	{
		return self::query("UNLOCK TABLES;");
	}

	/**
	 * Create or change table structure
	 * @param string $table Table name
	 * @param DatabaseTableBuilder $structure
	 * @return bool
	 */
	public static function newStructure($table, DatabaseTableBuilder $structure)
	{
		self::x_verify($table);

		set_time_limit(0);
		$reflection = new ReflectionClass(DatabaseTableBuilder::class);

		$props = [];
		foreach ($reflection->getProperties() as $p) {
			$p->setAccessible(true);
			$props[$p->name] = $p;
		}

		$indexes = $props["indexes"]->getValue($structure);
		$initialData = $props["rowStructure"]->getValue($structure);
		$needToDrop = $props["needToDrop"]->getValue($structure);
		$structure = $props["arrayStructure"]->getValue($structure);

		$current_checksum = hash("md4", serialize([$structure, $indexes, $initialData]));
		if (self::isTableExist($table)) {
			if (!self::$t_cache[$table]) {
				if (file_exists(__ROOTDIR . "/storage/dbcache/$table")) {
					self::$t_cache[$table] = file_get_contents(__ROOTDIR . "/storage/dbcache/$table");
				}
			}

			if (self::$t_cache[$table]) {
				if ($current_checksum == self::$t_cache[$table]) return true;
			}

			// Truncate table if asked
			if ($needToDrop) self::$link->query("TRUNCATE TABLE `$table`");

			$tableQuery = [];
			$primaryColumn = null;

			// Fetching Columns
			$lookup = self::$link->query("SHOW COLUMNS FROM `$table`");
			$old_columns = [];
			while ($row = $lookup->fetch_row()) $old_columns[$row[0]] = true;

			// Fetching Index
			$lookup = self::$link->query("SHOW INDEX FROM `$table`");
			$old_hasPrimary = false;
			$old_index = [];
			while ($row = $lookup->fetch_row()) {
				if ($row[2] == "PRIMARY") {
					$old_hasPrimary = true;
				} else {
					$old_index[$row[2]] = true;
				}
			}

			// Columns
			$previous_column = null;
			foreach ($structure as $column_name => $d) {
				list($type, $primary, $nullable, $default, $presistentExpr) = $d;
				$position = $previous_column ? "AFTER `$previous_column`" : "FIRST";
				if ($old_columns[$column_name]) {
					$tableQuery[] = "CHANGE COLUMN `$column_name` `$column_name` $type $nullable $default $presistentExpr $position";
				} else {
					$tableQuery[] = "ADD COLUMN `$column_name` $type $nullable $default $presistentExpr $position";
				}
				unset($old_columns[$column_name]);
				if ($primary) $primaryColumn = $column_name;
				$previous_column = $column_name;
			}

			// Drop Columns
			foreach ($old_columns as $column => $b) $tableQuery[] = "DROP COLUMN `$column`";

			// Primary Key
			if ($old_hasPrimary) $tableQuery[] = "DROP PRIMARY KEY";
			if ($primaryColumn) $tableQuery[] = "ADD PRIMARY KEY (`$primaryColumn`)";

			// Index
			foreach ($old_index as $index => $b) $tableQuery[] = "DROP INDEX `$index`";
			foreach ($indexes as $i) {
				list($name, $columns, $type) = $i;
				foreach ($columns as &$c) $c = "`$c`";
				$tableQuery[] = "ADD $type INDEX `$name` (" . implode(",", $columns) . ")";
			}

			$query = "ALTER TABLE `$table` " . implode(",", $tableQuery);
			if (defined("DB_DEBUG")) file_put_contents(__LOGDIR . "/db.log", "$query\r\n", FILE_APPEND);

			if (self::$link->query($query)) {
				if ($needToDrop && !empty($initialData)) self::insert($table, $initialData);
				file_put_contents(__ROOTDIR . "/storage/dbcache/$table", self::$t_cache[$table] = $current_checksum);
				set_time_limit(TIME_LIMIT);
				return self::$cache["tables"][$table] = true;
			} else {
				throw new DatabaseError(self::$link->error, $query);
			}
		} else {
			$tableQuery = [];
			$primaryColumn = null;

			// Columns
			foreach ($structure as $column_name => $d) {
				list($type, $primary, $nullable, $default, $presistentExpr) = $d;
				$tableQuery[] = "`$column_name` $type $nullable $default $presistentExpr";
				if ($primary) $primaryColumn = $column_name;
			}

			// Primary key
			if ($primaryColumn) $tableQuery[] = "PRIMARY KEY (`$primaryColumn`)";

			// Index
			foreach ($indexes as $i) {
				list($name, $columns, $type) = $i;
				foreach ($columns as &$c) $c = "`$c`";
				$tableQuery[] = "$type INDEX `$name` (" . implode(",", $columns) . ")";
			}

			$query = "CREATE TABLE `$table` (" . implode(",", $tableQuery) . ") COLLATE='utf8_general_ci' ENGINE=InnoDB;";
			if (defined("DB_DEBUG")) file_put_contents(__LOGDIR . "/db.log", "$query\r\n", FILE_APPEND);

			if (self::$link->query($query)) {
				if (!empty($initialData)) self::insert($table, $initialData);
				file_put_contents(__ROOTDIR . "/storage/dbcache/$table", self::$t_cache[$table] = $current_checksum);
				set_time_limit(TIME_LIMIT);
				self::$cache["tables"][$table] = true;
				return true;
			} else {
				throw new DatabaseError(self::$link->error, $query);
			}
		}
	}
}

Database::connect();

function DRI(): DatabaseRowInput
{
	return new DatabaseRowInput;
}

function DTB(): DatabaseTableBuilder
{
	return new DatabaseTableBuilder;
}
