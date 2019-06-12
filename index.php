<?php
$time = microtime(true);
date_default_timezone_set('UTC');

define('APPS', __DIR__);
define('FRAMEWORK', APPS.'/framework');
define('VCOMPONENT', APPS.'/components');

include FRAMEWORK.'/framework.php';

$cfg = App::getConfig();
error_reporting($cfg->reporting);

define('VBASE', '');
define('VURI', $_SERVER["SERVER_NAME"].VBASE);
define('VSITE', $cfg->protocol.'://'.VURI);

/*
 * check session
 */
session_start();

$user = App::getUser();

// construct website using `COMPONENT`
include VCOMPONENT."/index.php";
?>