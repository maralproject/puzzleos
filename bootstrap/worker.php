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

abstract class WorkerMessage{
	public $code;
	public $message;
}

/**
 * Create separated process to do long-run task
 */
class Worker{
	
	private $_processes=[];
	private $_task;
	private $_runmode;
	private $_workernum;
	
	private $_started = false;
	
	/**
	 * @var SuperClosure\Serializer
	 */
	private $_serialize;
	
	public function __construct($number = 1){
		if(!defined("_SUPERCLOSURE_H")){
			include("vendor/superclosure/autoload.php");
			define("_SUPERCLOSURE_H");
		}
		$this->_serialize = new SuperClosure\Serializer(new SuperClosure\Analyzer\TokenAnalyzer());
		$this->_workernum = $number;
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
	 * Start Worker as child process
	 * You have to call join() to make sure Worker doen't stop
	 * in the middle of progress.
	 * 
	 * Once the main Thread killed, this Worker will be killed
	 * 
	 * By calling this, you're able to send message between main Thread
	 * and this Worker.
	 * 
	 * @return bool
	 */
	public function runAsChild(){
		
	}
	
	/**
	 * Start Worker as zombie (separated process)
	 * If you want, you can call join() to wait this Worker.
	 * 
	 * Calling this, causes Worker continues to run
	 * even the main Thread is killed.
	 * 
	 * But, you can only receive the result of this Worker.
	 * You cannot send message between main Thread and This Worker.
	 * 
	 * @return bool
	 */
	public function runAsZombie(){
		
	}
	
	/**
	 * Wait for this worker to finish
	 * @return bool
	 */
	public function join(){
		
	}
	
	/**
	 * Check if this Worker still running
	 * @return bool
	 */
	public function isRunning(){
		
	}
	
	/**
	 * Fetch the message sent by Worker
	 * i.e. for receiving process status
	 * 
	 * @return WorkerMessage
	 */
	public function fetch(){
		
	}
	
	/**
	 * Send message to the Worker
	 * @param Object $object 
	 * @return bool
	 */
	public function send($object){
		
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
	
	/**
	 * For internal Use only
	 */
	public static function __do(){
		
	}
}

?>