<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
 */

use Opis\Closure\SerializableClosure;

/**
 * Create separated process to do long-run task
 */
class Worker
{
	private $_processes = [];
	private $_task;
	private $_workernum;
	private $_unique;
	private $_app;
	private $_appdir;

	private $_secret;
	private static $_cipher = 'AES-256-CBC';
	private static $onWorker = false;

	public function __construct(int $number = 1)
	{
		if ($number < 1) throw new PuzzleError('Worker number expect at least one!');

		$caller = explode('/', str_replace(__ROOTDIR, '', btfslash(debug_backtrace(null, 1)[0]['file'])));
		if ($caller[1] != 'applications')
			throw new PuzzleError('Only applications can create Worker!');

		if (!defined('__H')) {
			if (!function_exists('proc_open'))
				throw new PuzzleError('To use Worker, please enable proc_open function!');

			if (!function_exists('openssl_encrypt'))
				throw new PuzzleError('To use Worker, please enable openssl_* function!');

			define('__H', 1);
		}

		preparedir(__ROOTDIR . '/storage/worker');
		$this->_workernum = floor($number);
		$this->_app = AppManager::getNameFromDirectory($caller[2]);
		$this->_appdir = $caller[2];

		return $this;
	}

	/**
	 * Get if current environment is in Worker Mode
	 * @return bool
	 */
	public static function inEnv()
	{
		return self::$onWorker;
	}

	/**
	 * Called by posworker to set PuzzleOS to run as a worker.
	 * @param array $argv 
	 */
	public static function do($argv)
	{
		if (!defined('__POSWORKER') || PHP_SAPI != 'cli' || basename($argv[0]) != 'posworker')
			throw new PuzzleError('Cannot execute Worker!');

		$job = $argv[1];
		$key = $argv[2];

		if (!file_exists(__WORKERDIR . "/$job.job"))
			throw new PuzzleError('Job not found!');

		if (($execute = unserialize(openssl_decrypt(
			file_get_contents(__WORKERDIR . "/$job.job"),
			self::$_cipher,
			$key,
			OPENSSL_RAW_DATA
		))) === false && error_get_last()['type'] == E_NOTICE)
			throw new PuzzleError('Job cannot be parsed!');

		@unlink(__WORKERDIR . "/$job.job");
		self::$onWorker = true;
		$_SESSION = array_merge($_SESSION, $execute['env']['session']);

		$cc = new ReflectionClass('POSConfigGlobal');
		foreach ($execute['env']['posconfigglobal'] as $vn => $v) $cc->getProperty($vn)->setValue(null, $v);

		$cc = new ReflectionClass('POSConfigMailer');
		foreach ($execute['env']['posconfigmailer'] as $vn => $v) $cc->getProperty($vn)->setValue(null, $v);

		$cc = new ReflectionClass('POSConfigMultidomain');
		unset($execute['env']['posconfigmdomain']['zone']); //Because this is private prop
		foreach ($execute['env']['posconfigmdomain'] as $vn => $v) $cc->getProperty($vn)->setValue(null, $v);

		if ($execute['env']['userid'] !== null)
			PuzzleUser::get($execute['env']['userid'])->logMeIn();

		global $WORKER;
		$WORKER = [
			'id' => explode('.', $job)[1],
			'app' => $execute['env']['app'],
			'appdir' => $execute['env']['appdir']
		];

		try {
			ob_start();
			$function = $execute['func']->getClosure();
			$result = $function(explode('.', $job)[1], $execute['env']['app'], $execute['env']['appdir']);
			@ob_get_clean();
			@ob_end_clean();
			echo @openssl_encrypt(serialize($result), self::$_cipher, $key, OPENSSL_RAW_DATA);
		} catch (\Throwable $e) {
			echo @openssl_encrypt(false, self::$_cipher, $key, OPENSSL_RAW_DATA);
			PuzzleError::saveErrorLog($e);
		}
		exit;
	}

	/**
	 * Set the task for this Worker
	 * @param Closure $callable Callable will receive (int $id, string $appname, string $appdir)
	 * @return self
	 */
	public function setTask(Closure $callable)
	{
		$this->_task = new SerializableClosure($callable);
		return $this;
	}

	/**
	 * Start Worker
	 * @param array $options wait_on_shutdown, standalone
	 * @return bool
	 */
	public function run(array $options = null)
	{
		if ($this->isRunning()) throw new PuzzleError('Worker already started!');

		if ($options === null) {
			// By default, parent must wait for worker to finish
			$options = [
				'wait_on_shutdown' => true
			];
		}

		$this->_processes = [];
		$this->_secret = rand_str(32);
		$this->_unique = rand_str(8);

		$execute = serialize([
			'env' => [
				'session' => $_SESSION,
				'userid' => PuzzleUser::check() ? PuzzleUser::active()->id : null,
				'app' => $this->_app,
				'appdir' => $this->_appdir,
				'posconfigglobal' => (new ReflectionClass('POSConfigGlobal'))->getStaticProperties(),
				'posconfigmailer' => (new ReflectionClass('POSConfigMailer'))->getStaticProperties(),
				'posconfigmdomain' => (new ReflectionClass('POSConfigMultidomain'))->getStaticProperties()
			],

			// Function in opis wrapper automatically serialized
			'func' => $this->_task
		]);

		for ($i = 0; $i < $this->_workernum; $i++) {
			file_put_contents(
				__WORKERDIR . "/{$this->_unique}.$i.job",
				@openssl_encrypt($execute, self::$_cipher, $this->_secret, OPENSSL_RAW_DATA)
			);

			$this->_processes[$i] = proc_open(
				php_bin() . ' ' . __ROOTDIR . "/posworker {$this->_unique}.$i {$this->_secret}",
				[
					0 => ['pipe', 'r'],
					1 => ['file', __WORKERDIR . "/{$this->_unique}.$i.result", 'w'],
					2 => ['file', __LOGDIR . '/logging.log', 'a']
				],
				$pipe
			);

			$process = $this->_processes[$i];
			$unique = $this->_unique;

			register_shutdown_function(function () use ($unique, $i, $process, $options) {
				if ($options['wait_on_shutdown']) {
					@proc_close($process);
				} else {
					if (!$options['standalone']) @proc_terminate($process);
				}
				@unlink(__WORKERDIR . "/$unique.$i.result");
			});
		}

		return true;
	}

	/**
	 * Wait for this worker to finish
	 * @return bool
	 */
	public function join()
	{
		set_time_limit(0); //Prevent from dying
		foreach ($this->_processes as $p) @proc_close($p);
		set_time_limit(TIME_LIMIT);
	}

	/**
	 * Check if this Worker still running
	 * @return bool
	 */
	public function isRunning()
	{
		foreach ($this->_processes as $p)
			if (@proc_get_status($p)['running']) return true;
	}

	/**
	 * Kill worker process
	 * Force killing Worker will remove it's process
	 * @return bool
	 */
	public function kill()
	{
		foreach ($this->_processes as $p) @proc_terminate($p);
		$this->_processes = [];
	}

	/**
	 * Get the result from Worker.
	 * After reading the result, the process pointer will be cleared!
	 * @return array
	 */
	public function result()
	{
		if ($this->isRunning()) throw new PuzzleError('Worker haven\'t finished it\'s job yet!');

		$result = [];
		foreach ($this->_processes as $id => $p) {
			if (!file_exists(__WORKERDIR . "/{$this->_unique}.$id.result")) continue;
			$result[$id] = unserialize(openssl_decrypt(
				@file_get_contents(__WORKERDIR . "/{$this->_unique}.$id.result"),
				self::$_cipher,
				$this->_secret,
				OPENSSL_RAW_DATA
			));
		}

		$this->_processes = [];
		return $result;
	}
}
