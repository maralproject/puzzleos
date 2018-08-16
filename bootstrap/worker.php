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

/**
 * Create separated process to do long-run task
 */
class Worker{
	
	private $_processes=[];
	private $_task;
	private $_workernum;
	private $_unique;
	private $_app;
	private $_pipe=[];
	
	private $_secret;
	private static $_cipher = "AES-256-CBC";
		
	/**
	 * @var SuperClosure\Serializer
	 */
	private $_serialize;
	
	private static function __do($job, $key){
		if(!file_exists(__WORKERDIR . "/$job.job"))
			throw new WorkerError("Job not found!");
		
		$execute = unserialize(openssl_decrypt(
			file_get_contents(__WORKERDIR . "/$job.job"),
			self::$_cipher, $key, OPENSSL_RAW_DATA
		));
		
		if($execute === false && error_get_last()["type"] == E_NOTICE) throw new WorkerError("Job cannot be parsed!");
		
		register_shutdown_function(function() use($job){
			@unlink(__WORKERDIR . "/$job.job");
		});
		
		$_SESSION = array_merge($_SESSION, $execute["env"]["session"]);
		Accounts::addSession($execute["env"]["userid"]);
		
		include("vendor/superclosure/autoload.php");
		$function = (new SuperClosure\Serializer(new SuperClosure\Analyzer\TokenAnalyzer()))->unserialize($execute["func"]);
		
		try{
			$result = $function(explode(".",$job)[1]);
		}catch(Exception $e){
			$result = false;
		}
		
		echo serialize($result);
		exit;
	}
	
	public static function __callstatic($func,$args){
		switch($func){
		case "__do":
			error_reporting(E_ALL);
			if(!defined("__POSWORKER") || PHP_SAPI != "cli" || basename($args[0][0]) != "puzzleworker") 
				throw new WorkerError("Cannot execute Worker!");
			self::__do($args[0][1],$args[0][2]);
			break;
		}
	}
	
	public function __construct($number = 1){
		if($number < 1) throw new WorkerError("Worker number expect at least one!");
		
		$caller = explode("/",str_replace(__ROOTDIR,"",btfslash(debug_backtrace(null,1)[0]["file"])));
		if($caller[1] != "applications") 
			throw new WorkerError("Only applications can create Worker!");
		
		if(!defined("_SUPERCLOSURE_H")){
			if(!function_exists("proc_open")) 
				throw new WorkerError("To use Worker, please enable proc_open function!");
			
			if(!function_exists("openssl_encrypt")) 
				throw new WorkerError("To use Worker, please enable openssl_* function!");
			
			include("vendor/superclosure/autoload.php");
			define("_SUPERCLOSURE_H");
		}
		
		preparedir(__ROOTDIR . "/storage/worker");
		$this->_serialize = new SuperClosure\Serializer(new SuperClosure\Analyzer\TokenAnalyzer());
		$this->_workernum = floor($number);
		$this->_app = AppManager::getNameFromDirectory($caller[2]);
		
		return $this;
	}
	
	/**
	 * Set the task for this Worker
	 * @param Object $callable 
	 * @param \... $args 
	 * @return Worker
	 */
	public function setTask($callable){
		if(!is_callable($callable)) throw new WorkerError('$callable expect a Callable function!');
		$this->_task = $callable;
		
		return $this;
	}
	
	/**
	 * Start Worker
	 * 
	 * @param array $options wait_on_shutdown
	 * @return bool
	 */
	public function run($options=[]){
		if($this->isRunning()) throw new WorkerError("Worker already started!");
		
		$this->_processes = [];
		$this->_pipe = [];
		$this->_secret = randStr(32);
		$this->_unique = randStr(8);
		
		$execute = serialize([
			"env"	=> [
				"session"	=> $_SESSION,
				"userid" 	=> Accounts::getUserId(),
				"app"		=> $this->_app
			],
			"func"	=> $this->_serialize->serialize($this->_task)
		]);
		
		for($i=0;$i<$this->_workernum;$i++){
			file_put_contents(
				__WORKERDIR . "/{$this->_unique}.$i.job",
				@openssl_encrypt($execute, self::$_cipher, $this->_secret, OPENSSL_RAW_DATA)
			);
			
			$this->_processes[$i] = proc_open(
				php_bin() . " " . __ROOTDIR . "/puzzleworker {$this->_unique}.$i {$this->_secret}", 
				[
					0 => ["pipe", "r"],
					1 => ["pipe", "w"],
					//1 => ["file", __WORKERDIR . "/{$this->_unique}.$i.result", "w"],
					2 => ["file", __ROOTDIR . "/error.log", "a"]
				], 
				$this->_pipe[$i]
			);
			
			$process = $this->_processes[$i];
			$unique = $this->_unique;
			
			register_shutdown_function(function() use($unique,$i,$process,$options){
				if($options["wait_on_shutdown"]){
					@proc_close($process);
				}else{
					@proc_terminate($process);
				}
				@unlink(__WORKERDIR . "/$unique.$i.job");
			});
		}
		
		return true;
	}
	
	/**
	 * Wait for this worker to finish
	 * @return bool
	 */
	public function join(){
		set_time_limit(0); //Prevent from dying
		while($this->isRunning()) usleep(100);
		set_time_limit(TIME_LIMIT);
	}
	
	/**
	 * Check if this Worker still running
	 * @return bool
	 */
	public function isRunning(){
		foreach($this->_processes as $p)
			if(@proc_get_status($p)["running"]) return true;
	}
	
	/**
	 * Kill worker process
	 * Force killing Worker will remove it's process
	 * 
	 * @return bool
	 */
	public function kill(){
		foreach($this->_processes as $p) proc_terminate($p);
		$this->_processes = [];
		$this->_pipe = [];
	}
	
	/**
	 * Get the result from Worker.
	 * After reading the result, the process pointer will be cleared!
	 * 
	 * @return array
	 */
	public function result(){
		if($this->isRunning())
			throw new WorkerError("Worker haven't finished it's job yet!");
		
		$result=[];
		foreach($this->_processes as $id=>$p){
			$stdin = "";
			while(!feof($this->_pipe[$id][1])){
				$buffer = fgets($this->_pipe[$id][1], 1024);
				$stdin .= $buffer;
				if(strlen($buffer) == 0) break;
			}
			fclose($this->_pipe[$id][1]);
			proc_close($p);
			
			$result[$id] = unserialize($stdin);
		}
		
		$this->_processes = [];
		$this->_pipe = [];
		return $result;
	}
}

?>