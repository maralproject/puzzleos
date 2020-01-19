<?php

/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2020 PT SIMUR INDONESIA
 */

class PuzzleCLI
{
	private static $list = [];
	private static function init()
	{
		$caller = debug_backtrace()[1]["file"];
		$filenameStr = str_replace(__ROOTDIR, "", btfslash($caller));
		$filename = explode("/", $filenameStr);
		if ($filename[1] != "applications") throw new PuzzleError("CLI command registration must be called from applications");
		$appDir = $filename[2];
		$appname = AppManager::getNameFromDirectory($appDir);
		return ($appname);
	}

	private static function system($app, $arg)
	{
		$sys = explode("sys/", $app)[1];
		if ($sys == "cron") {
			if ($arg["run"]) CronJob::run((bool) $arg["force"]);
			else throw new PuzzleError("Invalid action");
		} else if ($sys == "cache") {
			if ($arg["flush"]) {
				IO::remove_r("/public/cache");
				IO::remove_r("/public/res");
				IO::remove_r("/storage/cache");
			} else throw new PuzzleError("Invalid action");
		} else if ($sys == "maintenance") {
			if ($arg["on"]) {
				file_put_contents(__ROOTDIR . "/site.offline", "");
				echo "Maintenance mode enabled.\n";
			} else {
				@unlink(__ROOTDIR . "/site.offline");
				echo "Maintenance mode disabled.\n";
			}
		} else
			throw new PuzzleError("Invalid parameter");
	}

	/**
	 * Application developer function
	 */
	private static function app($app, $arg, $io)
	{
		$io->out("Developer Tools for Application\n---\n");

		$sys = explode("app/", $app)[1];
		if ($sys == "new") {
			$io->out("Application rootname (lowercase, no spaces): ");
			$rootname = strtolower($io->in());

			$io->out("Application title: ");
			$title = $io->in();

			$io->out("Application description: ");
			$description = $io->in();

			$io->out("Permission (0: SU, 1: Employee, 2: Registered, 3: Public): ");
			$permission = $io->in();

			$io->out("Can be a default app (Y/N): ");
			$default = $io->in() == "Y" ? 1 : 0;

			$io->out("Application directory name (The folder name only): ");
			$dirname = $io->in();

			$appdir = __ROOTDIR . "/applications/$dirname";
			mkdir($appdir);
			$manifest = file_get_contents(__ROOTDIR . "/applications/manifest.ini.sample");
			$manifest = str_replace([
				"rootname=",
				"title=",
				"description=",
				"permission=",
				"canBeDefault=",
			], [
				"rootname=$rootname",
				"title=$title",
				"description=$description",
				"permission=$permission",
				"canBeDefault=$default",
			], $manifest);
			file_put_contents($appdir . "/manifest.ini", $manifest);
			touch($appdir . "/control.php");
			touch($appdir . "/viewPage.php");
			touch($appdir . "/viewSmall.php");

			$io->out("\nBasic application directory has been created!\n");
			$io->out("$appdir\n");
			$io->out("Happy coding :)\n");
		} else
			throw new PuzzleError("Invalid parameter");
	}

	/**
	 * Register a function to be called from CLI
	 * @param callable $F ($io, $args)
	 */
	public static function register($F)
	{
		if (!is_cli()) return false;
		$app = self::init();
		if (isset(self::$list[$app])) throw new PuzzleError("One app  allowed to register one CLI commands function!");
		if (!is_callable($F)) throw new PuzzleError("Incorrect parameter");
		self::$list[$app] = &$F;
	}

	/**
	 * Run CLI routine, calling PuzzleOS from CLI will automatically
	 * authenticate user as USER_AUTH_SU
	 * 
	 * @see accounts/class.php
	 * @param array $a
	 */
	public static function run($a)
	{
		try {
			if (!is_cli()) return false;
			if (!ends_with($a[0], "puzzleos")) throw new PuzzleError("Please use 'sudo -u www-data php puzzleos'\n\n");

			set_time_limit(0);
			ini_set('max_execution_time', 0);

			// Generating Argument list
			reset($a);
			next($a);
			$p = next($a);
			$arg = [];
			while (1) {
				if ($p === false) break;
				$arg[$p] = (substr($p, 0, 2) == "--") ? next($a) : true;
				$p = next($a);
				if ($p === false) break;
			}

			// IO Shortcut
			$io = new PObject([
				"in" => function () {
					return (PHP_OS == 'WINNT') ? stream_get_line(STDIN, 1024, PHP_EOL) : readline();
				},
				"out" => function ($o) {
					echo trim($o, "\t");
					flush();
				}
			]);

			$app = $a[1];
			if (starts_with($app, "sys/")) {
				self::system($app, $arg);
			} else if (starts_with($app, "app/")) {
				self::app($app, $arg, $io);
			} else {
				$appProp = iApplication::run($a[1], true);
				if ($app == "") exit;
				if (!isset(self::$list[$app])) throw new PuzzleError("Application doesn't register handler for CLI");

				$f = self::$list[$app];
				$f($io, $arg);
			}
		} catch (\Throwable $e) {
			PuzzleError::printErrorPage($e);
		}
	}
}
