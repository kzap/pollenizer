<?php

if (!function_exists('http_build_url')) {
	function http_build_url($purl) {
		$url = "";
		$url .= $purl['scheme'].'://'.$purl['host'];
		if ($purl['user']!='') $url .= '@'.$purl['user'];
		if ($purl['pass']!='') $url .= ':'.$purl['pass'];
		$url .= $purl['path'];  
		if ($purl['query']!='') $url .= '?'.$purl['query'];
		if ($purl['fragment']!='') $url .= '#'.$purl['fragment'];
		return $url;
/*	
    [scheme] => http
    [host] => hostname
    [user] => username
    [pass] => password
    [path] => /path
    [query] => arg=value
    [fragment] => anchor
*/
	}
}

if (!function_exists('http_build_query')) {
	function http_build_query($vars) {
		$query = '';
		$i = 0;
		if (sizeof($vars) > 0)
		foreach ($vars as $key => $value) {
			$i++;
			if ($i > 1) { $query .= '&'; }
			$query .= $key.'='.urlencode($value);
		}
		return $query;
	}
}

function add_url_param($url,$param_key,$param_value) {
	$purl = parse_url($url);

	$added = false;

	parse_str($purl['query'],$vars);
	foreach ($vars as $key => $value) {
		 if ($key == $param_key){
			$added = true;
			$vars[$param_key] = $param_value;
		}
	}

	if ($added == false) {
		$vars[$param_key] = $param_value;
	}

	$purl['query'] = http_build_query($vars);

	return http_build_url($purl);
}

function remove_url_param($url,$param_key) {
	$purl = parse_url($url);

	parse_str($purl['query'],$vars);
	foreach ($vars as $key => $value) {
		if ($key == $param_key) {
			unset($vars[$param_key]);
		}
	}
	$purl['query'] = http_build_query($vars);

	return http_build_url($purl);
}

function add_google_campaign_vars($url, $campaignVars = array()) {
	
	if (empty($campaignVars)) { $campaignVars = $_GET; }
	
	$campaignVarList = array(
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_content',
		'utm_term',
	);
	
	foreach ($campaignVarList as $campaignVarName) {
		if (isset($campaignVars[$campaignVarName])) {
			$url = add_url_param($url, $campaignVarName, $campaignVars[$campaignVarName]);
		}
	}
	
	return $url;
}
