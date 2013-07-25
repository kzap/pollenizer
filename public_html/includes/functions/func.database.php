<?php

function db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = DB_LINK) {
	global $$link;
	
	$$link = mysql_connect($server, $username, $password);
	
	if ($$link) mysql_select_db($database);
	
	return $$link;
}

function db_pconnect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = DB_LINK) {
	global $$link;
	
	$$link = mysql_pconnect($server, $username, $password);
	
	if ($$link) mysql_select_db($database);
	
	return $$link;
}

function query($sql, $link = DB_LINK) {
	global $$link;
	
	list($usec, $sec) = explode(" ", microtime());
    $start = (float)$usec + (float)$sec;
	
	$res = @mysql_query($sql, $link);
	
	$rows = @mysql_num_rows($res);
	if (!$rows) { $rows = @mysql_affected_rows($link); }
	
	if (($_GET['debugPQP'] == 1 || $_COOKIE['debugPQP'] == 1) && substr(phpversion(),0,1) == 5) {
		logQuery($sql, $start, $rows);
	}
/*	
	if (!$res)
		echo '<strong>MySQL Error:</strong> '.mysql_error($link).'.';
*/	
	return $res;
}

function pdoDbConnect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'pdo') {
	global $$link;
	
	try {
		$$link = new PDO('mysql:host=' . $server . ';dbname=' . $database, $username, $password);
	} catch (PDOException $e) {
	    print 'Error!: ' . $e->getMessage() . '<br />';
	    die();
	}
	
	return $$link;
}

function pdoQuery($sql, $pdoLink) {
	global $$pdoLink;
	
	$stmt = $pdoLink->query($sql);
	
	return $stmt;
}

function logQuery($sql, $start, $rows = 0) {
	global $linkInfo;
	
	list($usec, $sec) = explode(" ", microtime());
    $end = (float)$usec + (float)$sec;
	$time = $end - $start;
	
    $query = array(
        'sql' => $sql,
        'time' => ($time*1000),
		'rows' => $rows,
		'start' => $start,
		'end' => $end,
    );
    array_push($linkInfo->queries, $query);
}
