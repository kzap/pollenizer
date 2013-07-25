<?php

function _stripslashes(&$data,$key) {
	$data = stripslashes($data);
}

function _addslashes(&$data,$key) {
	$data = addslashes($data);
}

function _trim(&$data,$key) {
	$data = trim($data);
}

function _htmlentities(&$data,$key) {
	$data = htmlentities($data);
}

function _mysql_escape_string(&$data,$key) {
	$data = mysql_escape_string($data);
}

function _mysql_real_escape_string(&$data,$key) {
	$data = mysql_real_escape_string($data);
}

function map_entities( $str ){
	return htmlentities($str, ENT_QUOTES, 'UTF-8');
}

function array_map_r( $func, $arr ){
    $newArr = array();
   
    foreach( $arr as $key => $value )
    {
        $newArr[ $key ] = ( is_array( $value ) ? array_map_r( $func, $value ) : ( is_array($func) ? call_user_func_array($func, $value) : $func( $value ) ) );
    }
       
    return $newArr;
}
