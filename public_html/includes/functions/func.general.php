<?php

function seo_format($str) {
	$seo_path = $str;
	$seo_path =	str_replace(array('"', "'",), '', $seo_path);
	$seo_path = preg_replace ("/[^a-z0-9]/","-",strtolower($seo_path));
	while(stristr($seo_path, '--') !== FALSE) {
		$seo_path = str_replace ("--","-",$seo_path);
	}
	$seo_path = trim($seo_path, '-');
	return $seo_path;
}

function randomString($randStringLength, $ignoreStr = '') {
    $timestring = microtime();
    $secondsSinceEpoch=(integer) substr($timestring, strrpos($timestring, " "), 100);
    $microseconds=(double) $timestring;
    $seed = mt_rand(0,1000000000) + 10000000 * $microseconds + $secondsSinceEpoch;
    mt_srand($seed);
    $randstring = '';
    for($i=0; $i < $randStringLength; $i++) {
        if ($i%2) { 
			do {
				$letter = mt_rand(0, 9);
			} while (stristr($ignoreStr, "$letter") !== FALSE); 
			$randstring .= $letter;
		} else { 
			do {
				$letter = chr(ord('A') + mt_rand(0, 25));
			} while (stristr($ignoreStr, "$letter") !== FALSE); 
			$randstring .= $letter;
		}
	}
    return($randstring);
}

function randomNumber($randStringLength, $ignoreStr = '') {
    $timestring = microtime();
    $secondsSinceEpoch=(integer) substr($timestring, strrpos($timestring, " "), 100);
    $microseconds=(double) $timestring;
    $seed = mt_rand(0,1000000000) + 10000000 * $microseconds + $secondsSinceEpoch;
    mt_srand($seed);
    $randstring = '';
    for($i=0; $i < $randStringLength; $i++) {
		do {
			$letter = mt_rand(0, 9);
		} while (stristr($ignoreStr, "$letter") !== FALSE); 
		$randstring .= $letter;
	}
    return($randstring);
}

function time_to_sec($time) { 
    $hours = substr($time, 0, -6); 
    $minutes = substr($time, -5, 2); 
    $seconds = substr($time, -2); 

    return $hours * 3600 + $minutes * 60 + $seconds; 
}

function sec_to_time($seconds) { 
    $hours = floor($seconds / 3600); 
    $minutes = floor($seconds % 3600 / 60); 
    $seconds = $seconds % 60; 

    return sprintf("%d:%02d:%02d", $hours, $minutes, $seconds); 
}

function get_include_contents($filename) {
    if (is_file($filename)) {
        ob_start();
        include $filename;
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }
    return false;
}

function get_ip() {
	$ip;
	
	if (getenv("HTTP_CLIENT_IP")) 
		$ip = getenv("HTTP_CLIENT_IP");
		
	else if(getenv("HTTP_X_FORWARDED_FOR")) 
		$ip = getenv("HTTP_X_FORWARDED_FOR");
		
	else if(getenv("REMOTE_ADDR")) 
		$ip = getenv("REMOTE_ADDR");
		
	else 
		$ip = "unk.unk.unk";
	
	return $ip;
}

function WeekDate($week, $year, $format="Y-m-d") { 
    $firstDayInYear=date("N",mktime(0,0,0,1,1,$year)); 
    if ($firstDayInYear<5) 
        $shift=-($firstDayInYear-1)*86400; 
    else 
        $shift=(8-$firstDayInYear)*86400; 
    if ($week>1) $weekInSeconds=($week-1)*604800; else $weekInSeconds=0; 
    $timestamp=mktime(0,0,0,1,1,$year)+$weekInSeconds+$shift; 
    return date($format,$timestamp); 
}

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}

function arrayToObject($d) {
	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return (object) array_map(__FUNCTION__, $d);
	}
	else {
		// Return object
		return $d;
	}
}