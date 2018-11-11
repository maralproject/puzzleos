<?php
/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2018 MARAL INDUSTRIES
 */

/**
 * Abstract class for storing User Session
 */
class PuzzleUserSession
{
	public $http_agent;
	public $remote_ip;
	public $http_host;

	public function __construct($http_agent, $remote_ip, $http_host)
	{
		$this->http_agent = $http_agent;
		$this->remote_ip = $remote_ip;
		$this->http_host = $http_host;
	}
}

/**
 * Handle Session more advanced than generic PHP
 * WARNING: Session will not be saved on CLI environment
 * 
 * This class take these user info for verification:
 * HTTP_USER_AGENT
 * REMOTE_ADDR
 * cookie
 */
class PuzzleSession implements SessionHandlerInterface
{
	private static $sess;

	/**
	 * [retain_on_same_pc,share_on_subdomain]
	 * @var array
	 */
	private $config;

	/**
	 * @var PuzzleUserSession
	 */
	private $client;

	/**
	 * Expiration duration in seconds
	 * by default 3600secs.
	 */
	private $expire;
	private $data;
	private $id;
	private $in_db;

	private $old_config;

	/**
	 * @return PuzzleUserSession
	 */
	private function getClient()
	{
		return new PuzzleUserSession(
			$_SERVER["HTTP_USER_AGENT"],
			$_SERVER["REMOTE_ADDR"],
			explode(":", $_SERVER['HTTP_HOST'])[0]//wrong wrong wrooong
		);
	}

	private function createId()
	{
		return sha1(serialize([
			$this->getClient(),
			microtime(true),
			$_SERVER["REMOTE_PORT"]
		]));
	}

	private function guessRootDomain()
	{
		if ($_SERVER["HTTP_HOST"] == "localhost" || filter_var($_SERVER["HTTP_HOST"], FILTER_VALIDATE_IP)) {
			return $_SERVER["HTTP_HOST"];
		} else {
			$d_array = explode(".", $_SERVER["HTTP_HOST"]);
			$root_domain = end($d_array);
			return prev($d_array) . "." . $root_domain;
		}
	}

	/**
	 * Compare user data in database and current environment
	 */
	private function verify_user(PuzzleUserSession $from_db, $share_on_subdomain)
	{
		if ($_SERVER["HTTP_USER_AGENT"] == $from_db->http_agent || $_SERVER["REMOTE_ADDR"] == $from_db->remote_ip) {
			return ($share_on_subdomain ? str_contains($_SERVER["HTTP_HOST"], $from_db->http_host) : (explode(":", $_SERVER['HTTP_HOST'])[0] == $from_db->http_host));
		} else {
			return false;
		}
	}

	private function instantiated()
	{
		return ($this->client !== null && $this->config !== null && $this->data !== null && $this->id !== null);
	}

	private function __construct()
	{
		if (defined("__SESSION_STARTED")) throw new PuzzleError("Session already created!");
		define("__SESSION_STARTED", 1);
		$this->expire = 3600;

		if (!is_cli() && isset($_COOKIE["puzzleos"])) {
			$db = Database::getRowByStatement("sessions", "where session_id='?'", $_COOKIE["puzzleos"]);
			if (is_array($db)) {
				//Session id exists in database, perform client checking
				$this->in_db = true;
				$this->client = unserialize($db["client"]);
				$this->config = unserialize($db["cnf"]);
				$this->old_config = $this->config;
				$this->data = $db["content"];
				$this->id = $db["session_id"];

				//If user not verified, or session expired, don't restore session
				if ($this->verify_user($this->client, $this->config["share_on_subdomain"])) {
					if (time() <= $db["expire"]) return;
				}
			}
		}

		$this->destroy();
	}

	public function open($savePath = "", $sessionName = "")
	{
		if (!$this->instantiated()) {
			$this->id = $this->createId();
			$this->data = "";
			$this->config = ["retain_on_same_pc" => false, "share_on_subdomain" => false];
			$this->client = $this->getClient();
		}
		session_id($this->id);
		return true;
	}

	public function close()
	{
		return true;
	}

	public function read($id)
	{
		if (!$this->instantiated()) throw new PuzzleError("Cannot read or write from destroyed session");
		return $this->data;
	}

	public function write($id, $data)
	{
		if (is_cli()) return true;
		if (!$this->instantiated()) throw new PuzzleError("Cannot read or write from destroyed session");

		if (!$this->in_db) {
			$this->in_db = true;
			$this->data = $data;
			try {
				if ($this->config["share_on_subdomain"]) $this->client->http_host = $this->guessRootDomain();

				$di = new DatabaseRowInput;
				$di->setField("session_id", $this->id);
				$di->setField("content", $data);
				$di->setField("client", serialize($this->client));
				$di->setField("cnf", serialize($this->config));
				$di->setField("start", time());
				$di->setField("expire", time() + $this->expire);
				$di->setField("user", $_SESSION['account']['id']);
				return Database::insert("sessions",[$di]) ? true : false;
			} catch (DatabaseError $e) {
				$this->in_db = false;
			}
		} else {
			try {
				if (md5($this->data) != md5($data) || $this->config != $this->old_config) {
					$this->data = $data;
					$di = new DatabaseRowInput;
					$di->setField("content", $data);
					$di->setField("cnf", serialize($this->config));
					$di->setField("expire", time() + $this->expire);
					$di->setField("user", $_SESSION['account']['id']);
					return Database::update("sessions", $di, "session_id", $this->id) ? true : false;
				}
			} catch (DatabaseError $e) {
			}
		}

		return false;
	}

	private function writeCookie()
	{
		if (!$this->instantiated()) throw new PuzzleError("Cannot read or write from destroyed session");
		if (!defined("__COOKIE_OUT")) {
			setcookie(
				"puzzleos",
				$this->id,
				($this->config["retain_on_same_pc"] ? time() + $this->expire : 0),
				"/",
				($this->config["share_on_subdomain"] ? "." . $this->guessRootDomain() : null)
			);
			define("__COOKIE_OUT", 1);
		}
	}

	public function destroy($id = "")
	{
		if ($this->instantiated()) Database::delete("sessions", "session_id", $this->id);
		$this->client = null;
		$this->config = null;
		$this->data = null;
		$this->id = null;
		$this->in_db = false;
		return true;
	}

	public function gc($maxlifetime)
	{
		return Database::execute("delete from `sessions` where expire<=?", time()) ? true : false;
	}

	/**
	 * End all active session in PuzzleOS based on specific user id,
	 * except current user
	 * @return bool
	 */
	private function endUser($id)
	{
		return Database::execute("delete from `sessions` where `user`='?' and `session_id`!='?'", $id, $this->id) ? true : false;
	}

	/**
	 * WARNING!
	 * End all active session in PuzzleOS
	 */
	private function endAll()
	{
		$this->destroy();
		return Database::execute("delete from `sessions`") ? true : false;
	}

	public static function start()
	{
		self::$sess = new PuzzleSession;
		session_set_save_handler(self::$sess);
		ini_set('session.use_cookies', 0);
		session_start();
	}

	public function __get($k)
	{
		if (!$this->instantiated()) throw new PuzzleError("Cannot read or write from destroyed session");
		switch ($k) {
			case "session_id":
				return $this->id;
			default:
				throw new PuzzleError("Invalid input $k");
		}
	}

	public function __call($n, $args)
	{
		return call_user_func([$this, $n], $args);
	}

	public function __set($k, $v)
	{
		switch ($k) {
			case "share_on_subdomain":
				$this->config["share_on_subdomain"] = $v ? true : false;
				break;
			case "retain_on_same_pc":
				$this->config["retain_on_same_pc"] = $v ? true : false;
				break;
			case "expire":
				$this->expire = $v;
				break;
			default:
				throw new PuzzleError("Invalid input $k");
		}
	}

	public static function get()
	{
		return self::$sess;
	}

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array([self::$sess, $name], $arguments);
	}

}

PuzzleSession::start();
