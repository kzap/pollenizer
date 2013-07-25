<?php

global $_SITE;
$_SITE = array();

// get sites_id from HTTP_HOST
$domain = $_SERVER['HTTP_HOST'];
unset($sites_id);
while (1) {
	$sql = "SELECT `sites_id` 
		FROM `sites_domains` 
		WHERE UPPER(`domain`) = '" . strtoupper(mysql_real_escape_string($domain)) . "'
		ORDER BY `sites_id` 
		LIMIT 1
	";
	$res = query($sql, $link);
	if ($r = mysql_fetch_assoc($res)) { 
		$sites_id = $r['sites_id'];
		break;
	} elseif (strpos($domain, '.') !== FALSE) {
		$domain = substr($domain, strpos($domain, '.')+1);
	} else { break; }
}
// default sites_id is 1 if none found
define('SITES_ID', ($sites_id ? $sites_id : DEFAULT_SITES_ID));

// put site variables into $_SITE
$sql = "SELECT c.`configuration_key`, sc.`configuration_value` 
	FROM `configuration` AS `c`
	JOIN `sites_configuration` AS `sc` ON (c.`configuration_id` = sc.`configuration_id`) 
	WHERE sc.`sites_id` = '" . mysql_real_escape_string(SITES_ID) . "'
";
$res = query($sql, $link);
while ($config = mysql_fetch_assoc($res)) {	$_SITE[$config['configuration_key']] = $config['configuration_value']; }
if ($_SITE['site_name'] && !$_SITE['organization_name']) { $_SITE['organization_name'] = $_SITE['site_name']; }

// ************************************************************
// webserver paths
// ************************************************************
if ($_SERVER['HTTPS']) { $_SITE['homepage_url'] = str_replace('http://', 'https://', $_SITE['homepage_url']); }
define('DIR_WS_ROOT', $_SITE['homepage_url'] ? $_SITE['homepage_url'] : '/');
define('DIR_CSS', DIR_WS_ROOT . 'includes/css/');
define('DIR_JAVASCRIPT', DIR_WS_ROOT . 'includes/javascript/');
define('DIR_IMAGES', DIR_WS_ROOT . 'images/');
define('DIR_TEMPLATES', DIR_WS_ROOT . 'templates/');
define('DIR_CONTENT', DIR_WS_ROOT . 'articles/');

// Canonical
$_SITE['canonical'] = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
if (substr($_SITE['canonical'], 0, 1) == '/') { $_SITE['canonical'] = substr($_SITE['canonical'], 1); }
if (strtolower(substr($_SITE['canonical'], 0, 9)) == 'index.php') { $_SITE['canonical'] = substr($_SITE['canonical'], 9); }
$_SITE['canonical'] = DIR_WS_ROOT . $_SITE['canonical'];

// turn the site on or off
if( (isset($_SITE['enable_site']) && !$_SITE['enable_site']) && !preg_match('/adminpanel/',$_SERVER['PHP_SELF']) ) {
	echo 'Site is currently undergoing planned maintenance. Please try again later.';
	exit;
}