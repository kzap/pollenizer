<?php

// preview contents of var or array
function pre($var,$exit=false,$return_string=false) {

	if ( substr(phpversion(),0,3) >= 4.3  && $return_string)
		return '<pre>'.print_r($var,true).'</pre>';

	echo'<pre>';
	print_r($var);
	echo '</pre>';
	
	if ($exit) 
		exit;
}

function debug_msg($msg, $error = false) {
	global $_SITE;
	
	if (is_array($msg)) { $msg = pre($msg,0,1); }
	
	$_SITE['debugMsgs'][] = $msg;
	
	if ($error) { $_SITE['debugErrors'] = 1; }
}

function debug_shutdown() {
	global $_SITE;
	
	if (!$_SITE['debug']) { return; }
	
	if (!empty($_SITE['debugMsgs'])) {
		$_SITE['debugMsgs'][] = pre($_SERVER, 0, 1);
		
		$html_email = '<html><body>';
		$html_email .= pre($_SITE['debugMsgs'], 0, 1);
		$html_email .= '</body></html>';
		$headers = "From: ".$_SITE['organization_name']." <".$_SITE['email_support'].">\n" . 
			"MIME-Version: 1.0\n" . 
			"Content-type: text/html; charset=iso-8859-1";
		mail('andre@enthropia.com', $_SITE['organization_name'].' Debug'.($_SITE['debugErrors'] ? ' Errors' : ' Messages'), $html_email, $headers);
	}
}
