<?php
$_SITE['start_time'] = microtime(true);
// config file
require_once('configure.php');

// ************************************************************
// PHP Quick Profiler
// ************************************************************
if (($_GET['debugPQP'] == 1 || $_COOKIE['debugPQP'] == 1) && substr(phpversion(),0,1) == 5) {
	ini_set("memory_limit", -1);
	require_once(DIR_CLASSES.'pqp/classes/PhpQuickProfiler.php');
	$PQP = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime(), str_replace($_SERVER['DOCUMENT_ROOT'], '', DIR_CLASSES).'pqp/');
}
if ($_GET['debugPQP'] == 1) { setcookie('debugPQP', 1, time()+60*60*24*365, '/', COOKIE_DOMAIN); }
elseif (isset($_GET['debugPQP']) && $_GET['debugPQP'] == 0) { setcookie('debugPQP', false, time()-60*60*24*365, '/', COOKIE_DOMAIN); }

// ************************************************************
// Symfony Universal Class Loader
// ************************************************************
require_once(DIR_CLASSES . 'Symfony/Component/ClassLoader/UniversalClassLoader.php');
require_once(DIR_CLASSES . 'Symfony/Component/ClassLoader/ApcUniversalClassLoader.php');
use Symfony\Component\ClassLoader\ApcUniversalClassLoader;
$loader = new ApcUniversalClassLoader(__FILE__);

// Register Namespaces
$loader->registerNamespaces(array(
	'Symfony' => DIR_CLASSES,
	'Stash' => DIR_CLASSES,
));

// ************************************************************
// include - classes
// ************************************************************
require_once(DIR_CLASSES.'class.contents.php');
require_once(DIR_CLASSES.'class.form_generator.php');
require_once(DIR_CLASSES.'PHPMailer/class.phpmailer.php');
require_once(DIR_CLASSES . 'class.baseView.php');
require_once(DIR_CLASSES . 'class.baseController.php');

// ************************************************************
// Zend Library - Load Autoloader
// ************************************************************
require_once(DIR_INCLUDES.'configure.zend.php');
$loader->registerPrefix('Zend_', DIR_ZEND_LIBRARY);
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . DIR_ZEND_LIBRARY);

// ************************************************************
// include - functions
// ************************************************************
require_once(DIR_FUNCTIONS . 'func.array_walk.php');
require_once(DIR_FUNCTIONS . 'func.database.php');
require_once(DIR_FUNCTIONS . 'func.debug.php');
register_shutdown_function('debug_shutdown');
require_once(DIR_FUNCTIONS . 'func.messages.php');
require_once(DIR_FUNCTIONS . 'func.general.php');
require_once(DIR_FUNCTIONS . 'func.url.php');

// ************************************************************
// include - scripts
// ************************************************************

// ************************************************************
// Register the autoloaders
// ************************************************************
$loader->register();

// ************************************************************
// connection to the database
// ************************************************************
/*
$link = db_connect() or die('Unable to connect to database server!');
$pdo = pdoDbConnect();
$linkInfo = (object) array('queries' => array());
*/
// ************************************************************
// caching script
// ************************************************************
/*
$stashApc = new Stash\Handler\Apc(array('ttl' => 3600));
Stash\Box::setHandler($stashApc); 
//$stashFileSystem = new Stash\Handler\FileSystem(array('path' => '/var/cache/stash/', 'dirSplit' => 16));
//Stash\Manager::setHandler('Slow Cache', $stashFileSystem);
$stashSqlite = new Stash\Handler\Sqlite(array('path' => '/var/cache/stash/'));
Stash\Manager::setHandler('Slow Cache', $stashSqlite);
if ($_GET['clearStash'] == '12345') {
	ini_set("max_execution_time", 0);
	ini_set("memory_limit", -1);
	Stash\Box::clearCache();
	Stash\Manager::clearCache('Slow Cache');
}
*/

// ************************************************************
// configure site
// ************************************************************
//require_once(DIR_INCLUDES.'configure.sites.php');

// ************************************************************
// other config files
// ************************************************************
include_once(DIR_INCLUDES . 'configure.s3.php');
include_once(DIR_INCLUDES . 'configure.facebook.php');
require_once(DIR_INCLUDES . 'configure.mailchimp.php');
require_once(DIR_INCLUDES . 'configure.geoip.php');

// ************************************************************
// start session
// ************************************************************
require_once(DIR_SCRIPTS . 'session_start.php');
require_once(DIR_SCRIPTS . 'session.member.php');
require_once(DIR_SCRIPTS . 'session.facebook.php');

