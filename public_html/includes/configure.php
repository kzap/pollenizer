<?php
// set the level of error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
//error_reporting(0);

// ************************************************************
// site identification
// ************************************************************
define('DEFAULT_SITES_ID','1');	// default sites id

// ************************************************************
// server specific configuration options
// ************************************************************
// root directory
if (!$_SERVER['DOCUMENT_ROOT']) { $_SERVER['DOCUMENT_ROOT'] = '/server/pollenizer.dev/'; }
define('DIR_ADMIN_ROOT',$_SERVER['DOCUMENT_ROOT'].'/adminpanel/'); 
define('DIR_ROOT',$_SERVER['DOCUMENT_ROOT'].'/'); 
define('DIR_WS_ROOT','/'); 

// ************************************************************
// database connectivity
// ************************************************************
define('DB_SERVER', '127.0.0.1');
define('DB_SERVER_USERNAME', 'root');
define('DB_SERVER_PASSWORD', 'JKI5eng77ZnC');
define('DB_DATABASE', 'pollenizer');

// ************************************************************
// filesystem paths
// ************************************************************
define('DIR_ADMIN_INCLUDES', DIR_ADMIN_ROOT . 'includes/');
define('DIR_INCLUDES', DIR_ROOT . 'includes/');
define('DIR_FUNCTIONS', DIR_INCLUDES . 'functions/');
define('DIR_CLASSES', DIR_INCLUDES . 'classes/');
define('DIR_CONTROLLERS',   DIR_INCLUDES . 'controllers/');
define('DIR_VIEWS', DIR_INCLUDES . 'views/');
define('DIR_BOXES', DIR_INCLUDES . 'boxes/');
define('DIR_ADMIN_BOXES', DIR_ADMIN_INCLUDES . 'boxes/');
define('DIR_SCRIPTS', DIR_INCLUDES . 'scripts/');
define('DIR_CONTENT',   DIR_INCLUDES . 'content/');

// ************************************************************
// webserver paths
// ************************************************************
define('DIR_TEMPLATES', DIR_WS_ROOT . 'templates/');
define('DIR_CSS',    DIR_WS_ROOT . 'includes/css/');
define('DIR_JAVASCRIPT',    DIR_WS_ROOT . 'includes/javascript/');
define('DIR_IMAGES', DIR_WS_ROOT . 'images/');
define('DIR_IMAGES_LOCAL', DIR_ROOT . 'images/');

// ************************************************************
// database extras
// ************************************************************
define('DB_ENCRYPTION_KEY','ilovesalmonellageorgebuggerit');
define('DB_LINK','link');

// ************************************************************
// variables
// ************************************************************
if (!isset($PHP_SELF)) { $PHP_SELF = $_SERVER['PHP_SELF']; }

$_SITE = array(
	'flickr_api_key' => 'f3f15bfb9ab984c0cacbc73c6714315b',
	'flickr_app_secret' => '41efc0024d92cc78',
);
global $_SITE;

define('COOKIE_DOMAIN', (substr_count($_SERVER['HTTP_HOST'], '.') > 1 && (substr_count(substr($_SERVER['HTTP_HOST'], -7), '.') < 2 && strlen($_SERVER['HTTP_HOST']) > 7) ? substr($_SERVER['HTTP_HOST'], strrpos($_SERVER['HTTP_HOST'], '.', (0-(strlen($_SERVER['HTTP_HOST'])-strrpos($_SERVER['HTTP_HOST'], '.')+1)))) : '.'.$_SERVER['HTTP_HOST']));
define('SESSION_LIFETIME', 60*60*24*1);
define('INFILE', true);
define('ADMIN_MAIN', '1,2');
define('ADMIN_EXPIRE', (60*60*1)); // 1 hour
define('ADMIN_2STEP', 0);
