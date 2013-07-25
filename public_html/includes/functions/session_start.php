<?php

$SessionLifetime = 60*60*24*7;
$SessionDomain = ((substr_count($_SERVER['SERVER_NAME'], '.') > 1 && stristr($_SERVER['SERVER_NAME'], 'dev.') === FALSE) ? substr($_SERVER['SERVER_NAME'], strrpos(substr($_SERVER['SERVER_NAME'], 0, strrpos($_SERVER['SERVER_NAME'], '.')), '.')+1) : $_SERVER['SERVER_NAME']);
ini_set("session.gc_maxlifetime", $SessionLifetime);
ini_set("session.gc_divisor", "1");
ini_set("session.gc_probability", "1");
ini_set("session.cookie_lifetime", $SessionLifetime);
ini_set("session.cookie_domain", $SessionDomain);

function on_session_start($save_path, $session_name) {
//	error_log($session_name . " ". session_id());
	return true;
}

function on_session_end() {
	// Nothing needs to be done in this function
	// since we used persistent connection.
	return true;
}

function on_session_read($key) {
	global $SessionLifetime, $link;
//	error_log($key);
	$stmt = "SELECT `session_data` 
		FROM `sessions` 
		WHERE `session_id` = '" . mysql_real_escape_string($key) . "' 
			AND UNIX_TIMESTAMP(`session_expiration`) > UNIX_TIMESTAMP(NOW())
	";
	$sth = mysql_query($stmt, $link);

	if($row = mysql_fetch_assoc($sth)) {
		return($row['session_data']);
	} else {
		return '';
	}
}

function on_session_write($key, $val) {
	global $SessionLifetime, $link;
//	error_log("$key = $value");
	
	$insert_stmt  = "REPLACE INTO `sessions` 
		SET `session_id` = '" . mysql_real_escape_string($key) . "', 
			`session_data` = '" . mysql_real_escape_string($val) . "', 
			`session_expiration` = NOW() + INTERVAL " . $SessionLifetime . " SECOND
	";
		
	mysql_query($insert_stmt, $link);
	return true;
}

function on_session_destroy($key) {
	global $link;
	mysql_query("DELETE FROM `sessions` 
		WHERE `session_id` = '" . mysql_real_escape_string($key) . "'
	", $link);
	return true;
}

function on_session_gc($max_lifetime) {
	global $link;
	mysql_query("DELETE FROM `sessions` 
		WHERE `session_expiration` < NOW()
	", $link);
	return true;
}
	
    	    
// Set the save handlers
session_set_save_handler("on_session_start",   "on_session_end",
			"on_session_read",    "on_session_write",
			"on_session_destroy", "on_session_gc");
	
session_start();
