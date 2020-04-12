<?php

class DatabaseTableBuilder
{
	/**
	 * [Type, PRIMARY, AllowNULL, Default, PRESISTENT]
	 * @var array
	 */
	private $arrayStructure = [];

	/**
	 * @var array
	 */
	private $rowStructure = [];

	/**
	 * @var string
	 */
	private $selectedColumn;

	/**
	 * [$name, $column, $type]
	 * @var array
	 */
	private $indexes = [];

	/**
	 * Execute truncate table on structure change.
	 * @var bool
	 */
	private $needToDrop = false;

	/**
	 * Add index to this table
	 * See Mysql reference about index
	 * 
	 * @param string $name Give the index a name
	 * @param array $column Provide column that you want to add to this index
	 * @param string $type Choose UNIQUE, FULLTEXT, SPATIAL, or leave it empty
	 * @return DatabaseTableBuilder
	 */
	public function createIndex(string $name, array $column, $type = '')
	{
		switch ($type) {
			case 'UNIQUE':
			case 'FULLTEXT':
			case 'SPATIAL':
			case '':
				break;
			default:
				throw new InvalidArgumentException('Index should be UNIQUE, FULLTEXT, SPATIAL, or leave it empty');
		}

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
		return $this;
	}

	/**
	 * Add column attribute to table
	 * @param string $name
	 * @param string $type Use Qualified Mysql data type (e.g. TEXT, TINYTEXT)
	 * @return DatabaseTableBuilder
	 */
	public function addColumn(string $name, string $type = 'TEXT')
	{
		if (strlen($name) > 50) throw new InvalidArgumentException('Max length for column name is 50 chars');
		$this->selectedColumn = $name;
		$this->arrayStructure[$this->selectedColumn] = [strtoupper($type), false, 'NOT NULL', null, false];
		return $this;
	}

	/**
	 * Change column selection
	 * @param string $name
	 * @return DatabaseTableBuilder
	 */
	public function selectColumn(string $name)
	{
		if (strlen($name) > 50) throw new InvalidArgumentException('Max length for column name is 50 chars');
		if (!isset($this->arrayStructure[$name])) throw new InvalidArgumentException("Column $name is not set");
		$this->selectedColumn = $name;
		return $this;
	}

	/**
	 * Set current column as Primary Key
	 * @return DatabaseTableBuilder
	 */
	public function setAsPrimaryKey()
	{
		if ($this->selectedColumn == '') throw new InvalidArgumentException('Please select the column first');
		foreach ($this->arrayStructure as $key => $x) {
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
		if ($this->selectedColumn == '') throw new InvalidArgumentException('Please select the column first');
		foreach ($this->arrayStructure as $key => $x) {
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
		if ($this->selectedColumn == '') throw new InvalidArgumentException('Please select the column first');
		$this->arrayStructure[$this->selectedColumn][2] = $bool ? 'NULL' : 'NOT NULL';
		return $this;
	}

	/**
	 * Make this column presistent as. Effective for indexing.
	 * @param string $expression
	 * @return DatabaseTableBuilder
	 */
	public function presistentAs(string $expression = null)
	{
		if ($this->selectedColumn == '') throw new InvalidArgumentException('Please select the column first');
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
		if ($this->selectedColumn == '') throw new InvalidArgumentException('Please select the column first');
		$this->arrayStructure[$this->selectedColumn][3] = ($str === NULL ? 'DEFAULT NULL' : "DEFAULT '$str'");
		return $this;
	}

	/**
	 * Set this column as auto increment value
	 * @return DatabaseTableBuilder
	 */
	public function auto_increment()
	{
		if ($this->selectedColumn == '') throw new InvalidArgumentException('Please select the column first');
		$this->arrayStructure[$this->selectedColumn][3] = 'AUTO_INCREMENT';
		return $this;
	}

	/**
	 * Set data tpe for this column
	 * @param string $type
	 * @return DatabaseTableBuilder
	 */
	public function setType(string $type)
	{
		if ($this->selectedColumn == '') throw new InvalidArgumentException('Please select the column first');
		$this->arrayStructure[$this->selectedColumn][0] = strtoupper($type);
		return $this;
	}
}

class Database
{
	/** @var mysqli */
	private static $link = null;
	/** @var mysqli_result|bool */
	private static $last_insert_id = null;
	private static $last_mysqli_result = null;
	private static $cache = [];
	private static $t_cache = [];
	private static $transaction_track = 0;

	public static function connect()
	{
		if (!is_callbyme()) throw new DatabaseError('Database violation!');

		self::$link = @new mysqli(POSConfigDB::$host, POSConfigDB::$username, POSConfigDB::$password, POSConfigDB::$database_name);
		if (self::$link->connect_error) {
			abort(503, 'Internal Server Error', false);
			throw new DatabaseError(self::$link->connect_error, 'PuzzleOS only supports MySQL or MariaDB', (int) self::$link->connect_errno);
		}
	}

	private static function dumpError()
	{
		throw new DatabaseError('MySQL Error', self::$link->error, self::$link->errno);
	}

	/**
	 * @return mysqli_result
	 */
	private static function query(string $query, ...$param)
	{
		$escaped = self::escapeQuery($query, ...$param);
		if ($a = self::$cache['query_result'][$escaped]) {
			$a->field_seek(0);
			$a->data_seek(0);
			return $a;
		}

		if (defined('DB_DEBUG')) {
			$re = debug_backtrace()[1];
			file_put_contents(__LOGDIR . '/db.log', $re['file'] . ':' . $re['line'] . "\r\n\t$escaped\r\n\r\n", FILE_APPEND);
		}

		if (!($r = self::$link->query($escaped))) {
			if (self::$link->errno == '2014') {
				self::$link->close();
				self::flushCache();
				self::connect();
				if (!($r = self::$link->query($escaped))) {
					throw new DatabaseError(self::$link->error, $escaped, (int) self::$link->errno);
				}
			} else
				throw new DatabaseError(self::$link->error, $escaped, (int) self::$link->errno);
		}

		if ($r === true) {
			self::$last_insert_id = self::$link->insert_id;
			if (self::$link->affected_rows > 0) {
				// Rows changed, flushing cache
				self::flushCache();
			}

			if (self::$transaction_track > 0) {
				$transaction_progress = (bool) self::$link->query('SHOW VARIABLES WHERE `Variable_name`=\'in_transaction\'')->fetch_row()[1];
				if (!$transaction_progress) {
					// Implicit commit was detected
					self::$transaction_track = 0;
					Log::warning('DATABASE: Implicit commit was detected.', debug_backtrace());

					if (defined('DB_DEBUG')) {
						$debug = debug_backtrace();
						Log::debug('DatabaseInfo: Implicit commit was detected.', $debug);
						file_put_contents(__LOGDIR . '/db.log', "IMPLICIT COMMIT DETECTED\r\n", FILE_APPEND);
					}
				}
			}
		} else if ($r instanceof mysqli_result) {
			// This is a mysql result. Store the result in the cache
			self::$cache['query_result'][$escaped] = $r;
		}
		return self::$last_mysqli_result = $r;
	}

	private static function x_verify($find)
	{
		if ($find == '') return false;
		$stack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);

		$filename = $stack[str_contains($stack[2]['function'], 'call_user_func') ? 2 : 1]['file'];

		if (starts_with($filename, 'closure://')) {
			if (Worker::inEnv()) {
				$appname = $WORKER['app'];
				if ((preg_match('/app_' . $appname . '_/', $find))) return true;
			}
		}

		$filename = explode('/', str_replace(__ROOTDIR, '', btfslash($filename)));
		switch ($filename[1]) {
			case 'bootstrap':
				switch ($filename[2]) {
					case 'application.php':
					case 'services.php':
					case 'database.php':
					case 'systables.php':
						return true;
					case 'cron.php':
						if ((preg_match('/cron/', $find))) return true;
						break;
					case 'isession.php':
						if ((preg_match('/sessions/', $find))) return true;
						break;
					case 'configman.php':
						if ((preg_match('/multidomain_config/', $find))) return true;
						break;
					case 'userdata.php':
					case 'boot.php':
						if ((preg_match('/userdata/', $find))) return true;
						break;
				}
				break;
			case 'applications':
				$appname = isset($filename[2]) ? $filename[2] : '';
				$appname = AppManager::getNameFromDirectory($appname);
				if ((preg_match('/app_' . $appname . '_/', $find))) return (true);
		}

		throw new DatabaseError('Database table violation.');
	}

	/**
	 * Flush database cache
	 */
	public static function flushCache()
	{
		self::$cache = [];
		self::$t_cache = [];
		if (defined('DB_DEBUG')) file_put_contents(__LOGDIR . '/db.log', "CACHE PURGED\r\n", FILE_APPEND);
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
			return self::$link->savepoint('T' . self::$transaction_track++);
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
			return self::$link->release_savepoint('T' . --self::$transaction_track);
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
			return self::$link->query('ROLLBACK TO T' . --self::$transaction_track);
		}
	}

	private static function escRowInput($v)
	{
		if ($v === null) {
			return 'NULL';
		} else if (is_int($v) || is_float($v)) {
			return $v;
		} else if (is_bool($v)) {
			return (int) $v;
		} else {
			return "'" . self::escape($v) . "'";
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
	public static function max(string $table, string $column, string  $statement = '', ...$param)
	{
		self::x_verify($table);
		if (!isset(self::$cache['max'][$table . $column])) {
			if ($r = self::query("SELECT MAX(`?`) FROM `?` $statement", $column, $table, ...$param)) {
				self::$cache['max'][$table . $column] = [$r->fetch_row()[0]];
				$r->free();
			} else {
				self::dumpError();
			}
		}

		return self::$cache['max'][$table . $column][0];
	}

	/**
	 * Read a single row.
	 * @param string $table Table Name
	 * @param string $find_column Column need to be matched
	 * @param string $find_value Value inside $find_column need to be matched
	 * @return array
	 */
	public static function getRow(string $table, string $find_column, string  $find_value)
	{
		self::x_verify($table);
		if (!isset(self::$cache['getRow'][$table . $find_column . $find_value])) {
			if ($r = self::query('SELECT * FROM `?` WHERE `?`=\'?\' LIMIT 1', $table, $find_column, $find_value)) {
				self::$cache['getRow'][$table . $find_column . $find_value] = [$r->fetch_assoc()];
				$r->free();
			} else {
				self::dumpError();
			}
		}
		return self::$cache['getRow'][$table . $find_column . $find_value][0];
	}

	/**
	 * Read a single row.
	 * @param string $table Table Name
	 * @param string $statement Custom statement
	 * @param string $param Parameterized value
	 * @return array
	 */
	public static function getRowByStatement(string $table, string $statement, ...$param)
	{
		self::x_verify($table);
		$c = $table . $statement . serialize($param);
		if (!isset(self::$cache['getRowByStatement'][$c])) {
			if ($r = self::query("SELECT * FROM `?` $statement LIMIT 1", $table, ...$param)) {
				self::$cache['getRowByStatement'][$c] = [$r->fetch_assoc()];
				$r->free();
			} else {
				self::dumpError();
			}
		}
		return self::$cache['getRowByStatement'][$c][0];
	}

	/**
	 * Read a single column.
	 * @param string $table Table Name
	 * @param string $column Column Name
	 * @param string $find_column Column need to be matched
	 * @param string $find_value Value inside $find_column need to be matched
	 * @return string
	 */
	public static function read(string $table, string $column, string $find_column, string $find_value)
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
	public static function readByStatement(string $table, string $column, string $statement, ...$param)
	{
		self::x_verify($table);
		return self::getRowByStatement($table, $statement, ...$param)[$column];
	}

	/**
	 * Insert new records
	 * @param string $table
	 * @param array $row_input
	 * @param bool $ignore
	 * @return bool
	 */
	public static function insert(string $table, array $row_input, bool $ignore = false)
	{
		self::x_verify($table);

		if (empty($row_input)) {
			return true;
		}

		$columns_order = [];
		$columns = [];
		$values = [];
		foreach ($row_input as $row) {
			$value = [];
			foreach ($row as $field => $v) {
				$field = "`$field`";
				if (!in_array($field, $columns)) {
					$order = array_push($columns, $field);
					$columns_order[$field] = $order;
				}
				$value[$columns_order[$field]] = self::escRowInput($v);
			}
			$values[] = '(' . implode(',', $value) . ')';
		}
		$query = 'INSERT ' . ($ignore ? 'IGNORE ' : '') . " INTO `$table` (" . implode(',', $columns) . ') VALUES ' . implode(',', $values);
		return self::query($query);
	}

	/**
	 * Insert single record on duplicate update
	 * @param string $table
	 * @param array $row_input
	 * @return bool
	 */
	public static function insertOnDuplicateUpdate(string $table, array $row_input)
	{
		self::x_verify($table);

		if (empty($row_input)) {
			return true;
		}

		$columns = [];
		$values = [];
		$updateSet = [];
		foreach ($row_input as $field => $value) {
			$value = self::escRowInput($value);
			$columns[] = $field;
			$values[] = $value;
			$updateSet[] = "`$field`=$value";
		}

		$updateSet = implode(',', $updateSet);

		$query = "INSERT INTO `$table` (" . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ") ON DUPLICATE KEY UPDATE $updateSet";
		return self::query($query);
	}

	/**
	 * Update database record
	 * @param string $table
	 * @param array $row_input
	 * @param string $find_column
	 * @param string $find_value
	 * @return bool
	 */
	public static function update(string $table, array $row_input, string $find_column, string $find_value)
	{
		self::x_verify($table);

		if (empty($row_input)) {
			return true;
		}

		$set = [];
		foreach ($row_input as $field => $v) {
			$v = self::escRowInput($v);
			$set[] = "`$field`=$v";
		}
		$query = "UPDATE `$table` SET " . implode(',', $set) . " WHERE `$find_column`='" . self::escape($find_value) . "'";
		return self::query($query);
	}

	/**
	 * Update database record with statement
	 * @param string $table
	 * @param array $row_input
	 * @param string $find_column
	 * @param string $find_value
	 * @return bool
	 */
	public static function updateByStatement(string $table, array $row_input, string $statement, ...$param)
	{
		self::x_verify($table);

		if (empty($row_input)) {
			return true;
		}

		$set = [];
		foreach ($row_input as $field => $v) {
			$v = self::escRowInput($v);
			$set[] = "`$field`=$v";
		}
		$query = "UPDATE `$table` SET " . implode(',', $set) . " " . $statement;
		return self::query($query, ...$param);
	}

	/**
	 * Returns the last result produced by the latest query
	 * @return mysqli_result|bool
	 */
	public static function lastResult()
	{
		return self::$last_mysqli_result;
	}

	/**
	 * Returns the auto generated id used in the latest query
	 */
	public static function lastId()
	{
		return self::$last_insert_id;
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
	public static function delete(string $table, string $find_column, string $find_value)
	{
		self::x_verify($table);
		return self::query('DELETE FROM `?` WHERE `?`=\'?\';', $table, $find_column, $find_value);
	}

	/**
	 * Delete a record with custom argument
	 * @param string $table Table Name
	 * @param string $statement Custom statement
	 * @param array ...$param
	 * @return bool
	 */
	public static function deleteByStatement(string $table, string $statement = '', ...$param)
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
	public static function dropTable(string $table)
	{
		self::x_verify($table);
		return (self::query('DROP TABLE `?`', $table));
	}

	/**
	 * Execute raw query.
	 * @param string $query For better security, use '?' as a mark for each parameter.
	 * @param mixed ...$param Will replace the '?' as parameterized queries
	 * @return mysqli_result|bool
	 */
	public static function execute(string $query, ...$param)
	{
		self::x_verify($query);
		return self::query($query, ...$param);
	}

	/**
	 * Escape string for database query
	 * @return string
	 */
	public static function escape(string $str)
	{
		return self::$link->real_escape_string($str);
	}

	/**
	 * Escape the entire query using parameter
	 * @return string
	 */
	public static function escapeQuery(string $query, ...$param)
	{
		$query = trim($query);
		$escaped = '';
		$token = strtok($query, '?');
		reset($param);
		$processedLen = 0;
		do {
			$escaped .= $token;
			$processedLen += strlen($token) + 1;
			$currentParam = current($param);
			if ($processedLen >= strlen($query)) {
				if ($currentParam !== false) $escaped .= $currentParam === null ? 'NULL' : self::escape($currentParam);
				break;
			} else if ($currentParam === false) {
				throw new DatabaseError('Not enough parameter');
			} else {
				$escaped .= $currentParam === null ? 'NULL' : self::escape($currentParam);
				next($param);
			}
		} while ($token = strtok('?'));

		return $escaped;
	}

	/**
	 * Check if table is exists
	 * @param string $table Table name
	 * @return bool
	 */
	public static function isTableExist(string $table)
	{
		if ($table == '') throw new DatabaseError('Table name cannot be empty!');
		self::x_verify($table);
		if (!isset(self::$cache['tables'])) {
			$q = self::query('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = \'?\'', POSConfigDB::$database_name);
			while ($r = $q->fetch_row()) self::$cache['tables'][$r[0]] = 1;
		}
		return isset(self::$cache['tables'][$table]);
	}

	/**
	 * Read all record in a table
	 * @param string $table Table name
	 * @param string $statement Additional queries syntax. e.g. "SORT ASC BY `id`"
	 * @param array $param
	 * @return array
	 */
	public static function readAll(string $table, string $statement = '', ...$param)
	{
		self::x_verify($table);
		$c = serialize($param);
		if (!isset(self::$cache['readAll'][$table . $statement . $c])) {
			$array = self::toArray(self::query("SELECT * FROM `$table` $statement", ...$param));
			self::$cache['readAll'][$table . $statement . $c] = [$array];
		}
		return self::$cache['readAll'][$table . $statement . $c][0];
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
	public static function lock(string $table, string $for = 'WRITE')
	{
		self::x_verify($table);
		switch ($for) {
			case 'WRITE':
			case 'READ':
				break;
			default:
				throw new InvalidArgumentException('Only WRITE or READ allowed');
		}
		return self::query("LOCK TABLES `$table` $for");
	}

	/**
	 * Release a table lock
	 * @return bool
	 */
	public static function unlock()
	{
		return self::query('UNLOCK TABLES');
	}

	/**
	 * Create or change table structure
	 * @param string $table Table name
	 * @param DatabaseTableBuilder $structure
	 * @return bool
	 */
	public static function newStructure(string $table, DatabaseTableBuilder $structure)
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
				if ($presistentExpr) {
					$nullable = "";
					$default = "";
				}
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
				if ($presistentExpr) {
					$nullable = "";
					$default = "";
				}
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

function DTB(): DatabaseTableBuilder
{
	return new DatabaseTableBuilder;
}
