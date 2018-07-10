<?php
defined("__POSEXEC") or die("No direct access allowed!");
__requiredSystem("2.0.0") or die("You need to upgrade the system");
/**
 * PuzzleOS
 * Build your own web-based application
 * 
 * @package      maral.puzzleos.core.bootstrap
 * @author       Mohammad Ardika Rifqi <rifweb.android@gmail.com>
 * @copyright    2014-2017 MARAL INDUSTRIES
 * 
 * @software     Release: 2.0.0
 */
 
/**
 * Theme provided by https://bootswatch.com/ *
 */

/* Bootstrap CSS */
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/bootstrap-3.3.7/css/bootstrap.min.css"/>');
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/themes/paper.bootstrap.min.css"/>');
Template::addHeader('<link rel="stylesheet" href="'.$pubdir.'/themes/cosmo.bootstrap.min.css"/>');
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/themes/readable.bootstrap.min.css"/>');
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/themes/spacelab.bootstrap.min.css"/>');
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/themes/yeti.bootstrap.min.css"/>');
//Template::addHeader('<link rel="stylesheet" href="'.__SITEURL.'/applications/bootstrap/lib/bootstrap-3.3.5/css/bootstrap-theme.min.css"/>');

/* JQuery and Bootstrap things */
//Template::addHeader('<script type="text/javascript" src="'.__SITEURL.'/applications/bootstrap/lib/jquery-1.11.3.min.js"></script>');
Template::addHeader('<script type="text/javascript" src="'.$pubdir.'/jquery-3.2.1.min.js"></script>');
Template::addHeader('<script type="text/javascript" src="'.$pubdir.'/jquery-migrate-3.0.1.js"></script>');
//Template::addHeader('<script type="text/javascript" src="'.__SITEURL.'/applications/bootstrap/lib/bootstrap-3.3.7/js/bootstrap.min.js"></script>');
Template::appendBody('<script type="text/javascript" src="'.$pubdir.'/bootstrap-3.3.7/js/bootstrap.min.js"></script>');

/* Optimized one, disable block rendering */
//Template::addHeader('<style type="text/css">'.file_get_contents(IO::physical_path("/applications/bootstrap/lib/bootstrap-3.3.7/css/bootstrap.min.css")).'</style>');
//Template::addHeader('<style type="text/css">'.file_get_contents(IO::physical_path("/applications/bootstrap/lib/themes/paper.bootstrap.min.css")).'</style>');

//Template::addHeader('<script type="text/javascript">'.file_get_contents(IO::physical_path("/applications/bootstrap/lib/jquery-1.11.3.min.js")).'</script>');
//Template::addHeader('<script type="text/javascript">'.file_get_contents(IO::physical_path("/applications/bootstrap/lib/bootstrap-3.3.7/js/bootstrap.min.js")).'</script>');
?>