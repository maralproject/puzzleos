<?php
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2019 PT SIMUR INDONESIA
 */

setlocale(LC_ALL, str_replace("-", "_", POSConfigGlobal::$default_language));
setlocale(LC_NUMERIC, 'C');

if (!isset($_COOKIE["postimezone"])) {
	Template::addHeader('<script>(()=>{let a=Intl.DateTimeFormat().resolvedOptions().timeZone;document.cookie = "postimezone = " + a + ";path=/";})();</script>');
	date_default_timezone_set(POSConfigGlobal::$timezone);
} else {
	date_default_timezone_set($_COOKIE["postimezone"]);
}

/* Define detected user timezone */
define("__USER_TIMEZONE", date_default_timezone_get());

/**
 * Timezone class
 */
class Timezone
{
	/**
	 * Get current timezone
	 * @return string
	 */
	public static function getTimeZoneArray()
	{
		return (DateTimeZone::listIdentifiers(DateTimeZone::ALL));
	}

	/**
	 * Print timezone list HTML in dropdown box
	 * @param $name DOM name element
	 * @param $val Active value
	 */
	public static function dumpDropdownList($name, $val)
	{
		echo ('<select name="' . $name . '" class="form-control" data-live-search="true">');
		foreach (Timezone::getTimeZoneArray() as $d) {
			echo ('<option value="' . $d . '" ' . ($val == $d ? "selected" : "") . '>' . $d . '</option>');
		}
		echo ('</select>');
	}
}
