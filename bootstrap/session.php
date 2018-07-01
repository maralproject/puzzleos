<?php
defined("__POSEXEC") or die("No direct access allowed!");
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @package      maral.puzzleos.core
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 *
 * @software     Release: 2.0.0
 */

/**
 * Handle Session more advanced than generic PHP
 * WARNING: Session will not be saved on CLI environment
 * 
 * This class take these user info for verification:
 * HTTP_USER_AGENT
 * HTTP_REFERER
 * REMOTE_ADDR
 * cookie
 */
class PuzzleSession implements SessionHandlerInterface{
	
	/**
	 * $cnf structure
	 * [(bool)retain_on_same_pc=false,share_on_subdomain=false]
	 */
	private $cnf;
	
	/**
	 * $client structure
	 * [User Agent, Client IP, Domain which user is accessing]
	 */
	private $client;
	private $start_time;
	private $id;
	private $expire;
	private $destroyed;
	private $data;
	private $new_data;
	private $cnf_old;

    public function open($savePath = "", $sessionName = ""){
		$this->destroyed = false;
		$this->new_data = true;
		
		//1. Restore session id from cookie if exists
        if($_COOKIE["puzzleos"] != "") {
					
			//2. Restore all data and check if this session id is match with the real client
			//Session is constructed using cookie, http_host, and user_agent
			$data = Database::readAll("sessions","where session_id='?' limit 1", $_COOKIE["puzzleos"])->data[0];
			if($data["session_id"] != ""){
				
				//Session id exists in database, perform client checking
				$c = unserialize($data["cnf"]);
				$i = unserialize($data["client"]);
				
				if($i[2]=="localhost" || filter_var($i, FILTER_VALIDATE_IP)){
					$root_domain = $i[2];
				}else{
					$d_array = explode(".",$i[2]);
					$root_domain = end($d_array);
					$root_domain = prev($d_array) . "." . $root_domain;
				}
				
				if($_SERVER["HTTP_USER_AGENT"] == $i[0] || $i[1] == $_SERVER["REMOTE_ADDR"]){
					if(($c[1] && (strpos($_SERVER["HTTP_HOST"],$root_domain) === false)) || (!$c[1] && explode(":",$_SERVER['HTTP_HOST'])[0] != $i[2])){
						//Failed to verify user
					}else{
						//User verified
						$this->data = $data["content"];
						$this->cnf = $c;
						$this->cnf_old = $c;
						$this->client = $i;
						$this->expire = (int) $data["expire"] - $data["start"];
						session_id($_COOKIE["puzzleos"]);
						$this->id = $_COOKIE["puzzleos"];
						//Set the session_id()
						session_id($this->id);
						$this->new_data = false;
						return true;
					}
				}
			}
		}
		
		$this->client = [$_SERVER["HTTP_USER_AGENT"],$_SERVER["REMOTE_ADDR"],explode(":",$_SERVER['HTTP_HOST'])[0]];
		$this->id = md5(json_encode([$this->client,time()]));
		$this->cnf = [0,false];
		
		/**
		 * Session will be expired in 1 hour if user do not open the browser again
		 * App can change this variable through service
		 */
		$this->expire = 60 * 60;
		$this->data = "";
		
		//Set the session_id()
		session_id($this->id);
		return true;
    }

    public function close(){
		return true;
	}

    public function read($id){
		if($this->destroyed) throw new PuzzleError("Cannot read or write from destroyed session");
		return $this->data;
    }

    public function write($id, $data){
		if(defined("__POSCLI")) return true; //Donot write session to the database on CLI.
        if($this->destroyed) throw new PuzzleError("Cannot read or write from destroyed session");
		
		/* Only rewrite data when it's needed to */
		if($this->new_data){
			try{
				Database::newRow("sessions", $this->id, $data, serialize($this->client), serialize($this->cnf), time(), (int)time() + $this->expire, $_SESSION['account']['id']);
			}catch(DatabaseError $e){
				//For unknown reason, browser sent two or more request at the same time, which cause multiple session with same key to be created.
				//To overcome this problem, we will load that keys instead of creating new one
				$_COOKIE["puzzleos"] = $this->id;
				return self::open("","");
			}			
			$this->new_data = false;
		}else if(md5($this->data) != md5($data) || $this->cnf != $this->cnf_old){
			$di = new DatabaseRowInput;
			$di->setField("content",$data);
			$di->setField("cnf",serialize($this->cnf));
			$di->setField("client",serialize($this->client));
			$di->setField("user",$_SESSION['account']['id']);
			Database::updateRowAdvanced("sessions",$di,"session_id",$this->id);
			$this->cnf_old = $this->cnf;
		}
		return true;
    }
	
	public function write_cookie(){
		if($this->destroyed) throw new PuzzleError("Cannot read or write from destroyed session");
		
		/**
		 * If HTTP host is localhost or IP address, then put NULL in setcookie()
		 * else, put ".domain.com"
		 */
		if($this->client[2]=="localhost" || filter_var($this->client[2], FILTER_VALIDATE_IP)){
			$root_domain = NULL;
		}else{
			$d_array = explode(".",$this->client[2]);
			$root_domain = end($d_array);
			$root_domain = "." . prev($d_array) . "." . $root_domain;
		}
		
		setcookie("puzzleos", $this->id, ($this->cnf[0] ? time() + $this->expire : NULL), "/", ($this->cnf[1] ? $root_domain : NULL));
	}

    public function destroy($id){
        //Remove session from database
		Database::deleteRow("sessions","session_id",$id);
		$this->destroyed = true;
		return true;
    }

    public function gc($maxlifetime){
		Database::exec("delete from `sessions` where expire<=?",(int)time());
    }
	
	public function __set($k,$v){
		switch($k){
		case "share_on_subdomain":
			$this->cnf[1] = (bool) $v;
			break;
		case "retain_on_same_pc":
			$this->cnf[0] = (bool) $v;
			break;
		case "expire":
			$this->expire = (int) $v;
			break;
		default:
			throw new PuzzleError("Invalid input $k");
		}
	}
	
	/**
	 * WARNING!
	 * End all active session in PuzzleOS
	 */
	public function endAll(){
		Database::exec("delete from `sessions`");
		$this->destroyed = true;
	}
	
	/**
	 * End all active session in PuzzleOS based on specific user id,
	 * except current user
	 */
	public function endUser($id){
		if($id == NULL) return false;
		Database::exec("delete from `sessions` where `user`='?' and `session_id`!='?'",$id,$this->id);
	}
	
	public function __get($k){
		if($this->destroyed) throw new PuzzleError("Cannot read or write from destroyed session");
		switch($k){
		case "session_id":
			return $this->id;
		default:
			throw new PuzzleError("Invalid input $k");
		}
	}
}

/* Starting session session */
POSGlobal::$session = new PuzzleSession;
session_set_save_handler(POSGlobal::$session);
ini_set('session.use_cookies', 0); 
session_start();

?>