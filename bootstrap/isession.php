<?php

/**
 * PuzzleOS
 * Build your own web-based application
 *
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
 */

/**
 * Handle Session more advanced than generic PHP
 * WARNING: Session will not be saved on CLI environment
 * 
 * This class take these user info for verification:
 * HTTP_USER_AGENT
 * REMOTE_ADDR
 * cookie
 * 
 * @property-read string $session_id
 */
class PuzzleSession implements SessionHandlerInterface
{
    /** @var self */
    private static $sess;

    #region Global Settings
    private static $share_on_subdomain = false;
    private static $retain_on_same_pc = false;
    private static $expire_time = 30 * T_MINUTE;
    #endregion

    #region Vars.
    private $session_id;
    private $client_agent;
    private $remote_addr;
    private $domain;
    private $expire;

    private $tmp_read_content;
    private $tmp_logged_user;
    private $tmp_cookie_out = false;
    #endregion

    #region SessionHandlerInterface
    public function open(string $save_path = null, string $session_name = null)
    {
        $db = Database::getRow("sessions", "session_id", $this->session_id);
        if (empty($db)) {
            $this->expire = time() + self::$expire_time;
            Database::insert("session_id", [[
                "session_id" => $this->session_id,
                "agent" => $this->client_agent,
                "remote" => $this->remote_addr,
                "domain" => $this->domain,
                "expire" => $this->expire
            ]]);
            $this->tmp_logged_user = null;
        } else {
            $this->expire = (int) $db["expire"];
            $this->tmp_logged_user = $db["user"] ? ((int) $db["user"]) : null;
        }
        $this->tmp_read_content = null;
        return true;
    }

    public function read(string $session_id)
    {
        return $this->tmp_read_content = (string) Database::read("sessions", "content", "session_id", $session_id);
    }

    public function write(string $session_id, string $data)
    {
        $currentUser = PuzzleUser::check() ? PuzzleUser::active()->id : null;
        if ($data == $this->tmp_read_content && $this->tmp_logged_user == $currentUser) {
            return true;
        } else {
            return (bool) Database::update(
                "sessions",
                DRI()
                    ->setField("content", $data)
                    ->setField("user", $currentUser),
                "session_id",
                $session_id
            );
        }
    }

    public function close()
    {
        $this->tmp_read_content = null;
        $this->tmp_logged_user = null;
        return true;
    }

    public function destroy(string $session_id)
    {
        return (bool) Database::delete("sessions", "session_id", $session_id);
    }

    public function gc($maxlifetime)
    {
        return true;
    }
    #endregion

    private function createSID($requested_session_id)
    {
        $old_session_id = $requested_session_id;
        if (isset($old_session_id)) {
            $db = Database::getRow("sessions", "session_id", $old_session_id);
            if (!empty($db)) {
                if (
                    $db["agent"] === $this->client_agent &&
                    $db["domain"] === $this->domain &&
                    $db["remote"] === $this->remote_addr
                ) {
                    if (self::$retain_on_same_pc || $db["expire"] <= time()) return $old_session_id;
                }
            }
        }
        return (rand_str(40));
    }

    public function __get($name)
    {
        switch ($name) {
            case "session_id":
                return $this->session_id;
        }
    }

    private function __construct(string $session_id = null)
    {
        $this->client_agent = $_SERVER["HTTP_USER_AGENT"];
        $this->domain = self::guessRootDomain();
        $this->remote = $_SERVER["REMOTE_ADDR"];
        $this->session_id = $this->createSID($session_id);

        session_id($this->session_id);
    }

    /**
     * Write cookie for this session
     */
    public function writeCookie()
    {
        if ($this->tmp_cookie_out) return;
        setcookie(
            SESSION_KEY,
            $this->session_id,
            self::$retain_on_same_pc ? $this->expire : 0,
            "/",
            self::$share_on_subdomain ? "." . $this->domain : null,
            __HTTP_SECURE,
            true
        );
        $this->tmp_cookie_out = true;
    }

    /**
     * Guess the TLD or localhost with cookie compliance result.
     * Sometimes, HTTP_HOST is just 127.0.0.1 insteadof localhost
     */
    private static function guessRootDomain()
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
     * Start the session using PuzzleSession
     */
    public static function start()
    {
        if (is_cli()) return;
        if (!is_callbyme()) throw new PuzzleError("PuzzleSession startup violence");
        $session_handler = new PuzzleSession($_COOKIE[SESSION_KEY]);
        session_set_save_handler($session_handler);
        ini_set('session.use_cookies', 0);
        session_start();
        self::$sess = $session_handler;
    }

    /**
     * End all active session in PuzzleOS based on specific user id,
     * except current user.
     * @param int $id PuzzleUser id that about to be ended.
     * @return bool
     */
    public static function endUser(int $id)
    {
        return (bool) Database::execute("delete from `sessions` where `user`='?' and `session_id`!='?'", $id, self::get()->session_id);
    }

    /**
     * Get session singleton
     * @return self
     */
    public static function get()
    {
        return self::$sess;
    }

    /**
     * Set the global value for PuzzleSession
     * @param string $name share_on_subdomain | retain_on_same_pc | expire_time
     * @param mixed $value
     */
    public static function config($name, $value)
    {
        switch ($name) {
            case "share_on_subdomain":
                $this->share_on_subdomain = (bool) $value;
                break;
            case "retain_on_same_pc":
                $this->retain_on_same_pc = (bool) $value;
                break;
            case "expire_time":
                $this->expire_time = (int) $value;
                break;
        }
    }
}

PuzzleSession::start();
