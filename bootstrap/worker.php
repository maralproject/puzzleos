<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.1
 */
 
define("__WORKERDIR", __ROOTDIR . "/storage/worker");

class WorkerMessage{
	
	private $_queue=[];
	
	/**
	 * Send message to/from Worker
	 * @param Object $message 
	 * @return bool
	 */
	public function send($message){
		
	}
	
	/**
	 * Read message to/from Worker
	 * @return Object
	 */
	public function read(){
		
	}
	
	/**
	 * Check if read buffer is available
	 * @return bool
	 */
	public function available(){
		
	}
}

/**
 * Create separated process to do long-run task
 */
class Worker{
	
	private $_processes=[];
	private $_task;
	private $_zombie;
	private $_workernum;
	private $_unique;
	private $_app;
	
	private $_secret;
	private static $_cipher = "AES-256-CBC";
	
	private $_started = false;
	
	/**
	 * @var WorkerMessage
	 */
	public $message;
	
	/**
	 * @var SuperClosure\Serializer
	 */
	private $_serialize;
	
	public function __get($var){
		
	}
	
	public function __construct($number = 1){
		if($number < 1) throw new PuzzleError("Worker number expect at least one!");
		
		$caller = explode("/",str_replace(__ROOTDIR,"",btfslash(debug_backtrace(null,1)[0]["file"])));
		if($caller[1] != "applications") 
			throw new PuzzleError("Only applications can create Worker!");
		
		if(!defined("_SUPERCLOSURE_H")){
			if(!function_exists("proc_open")) 
				throw new PuzzleError("To use Worker, please enable proc_open function!");
			
			if(!function_exists("openssl_encrypt")) 
				throw new PuzzleError("To use Worker, please enable openssl_* function!");
				
			if(!function_exists("pcntl_waitpid")) 
				throw new PuzzleError("To use Worker, please enable pcntl_waitpid function!");
			
			include("vendor/superclosure/autoload.php");
			define("_SUPERCLOSURE_H");
		}
		
		preparedir(__ROOTDIR . "/storage/worker");
		$this->_serialize = new SuperClosure\Serializer(new SuperClosure\Analyzer\TokenAnalyzer());
		$this->_workernum = floor($number);
		$this->_unique = randStr(8);
		$this->_app = AppManager::getNameFromDirectory($caller[2]);
	}
	
	/**
	 * Set the task for this Worker
	 * @param Object $callable 
	 * @param \... $args 
	 * @return bool
	 */
	public function setTask($callable, ...$args){
		if(!is_callable($callable)) throw new PuzzleError('$callable expect a Callable function!');
		$this->_task = [$callable,$args];
	}
	
	/**
	 * Start Worker
	 * 
	 * You can run PuzzleOS as zombie (separated process), or
	 * as a child.
	 * 
	 * @param bool $as_zombie
	 * @return bool
	 */
	public function run($as_zombie = false){
		$this->_processes = [];
		$this->message = new WorkerMessage($this);
		$this->_secret = randStr(32);
		$this->_zombie = $as_zombie;
		
		//Preparing execution file
		$execute=[
			"env"	=> [
				"session"	=> $_SESSION,
				"userid" 	=> Accounts::getUserId(),
				"app"		=> $this->_app
			],
			"func"		=> $this->_serialize->serialize($this->_task[0]),
			"args"		=> $this->_task[1]
		];
		
		for($i=0;$i<$this->_workernum;$i++){
			file_put_contents(
				__WORKERDIR . "/{$this->_unique}.$i.job",
				openssl_encrypt(serialize($execute), self::$_cipher, $this->_secret)
			);
			
			$unique = $this->_unique;
			register_shutdown_function(function() use($unique,$i){
				@unlink(__WORKERDIR . "/{$this->_unique}.$i.job");
			});
			
			$this->_processes[$i] = [
				"pipe" => NULL,
				"process" => proc_open(
					PHP_BINARY . " puzzleworker $pwid {$this->_secret}", 
					[
						0 => ["pipe", "r"],
						1 => $as_zombie ? ["file", __WORKERDIR . "/{$this->_unique}.$i.result", "a"] : ["pipe", "w"],
						2 => ["file", __ROOTDIR . "/error.log", "a"]
					], 
					$this->_processes[$i]["pipe"]
				)
			];
		}
	}
	
	/**
	 * Wait for this worker to finish
	 * @return bool
	 */
	public function join(){
		set_time_limit(0); //Preventing from dying
		foreach($this->_processes as $p){
			$status = proc_get_status($p["process"]);
			if($status["running"]){
				pcntl_waitpid($status["pid"], $child_status);
			}
		}
		set_time_limit(TIME_LIMIT);
	}
	
	/**
	 * Check if this Worker still running
	 * @return bool
	 */
	public function isRunning(){
		
	}
	
	/**
	 * Kill worker process
	 * @return bool
	 */
	public function kill(){
		
	}
	
	/**
	 * Check if this Worker finished it's job
	 * @return bool
	 */
	public function finished(){
		
	}
	
	/**
	 * Get the result from Worker
	 * @return Object
	 */
	public function result(){
		
	}
}

?>