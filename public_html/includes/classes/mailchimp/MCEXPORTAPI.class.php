<?php

class MCEXPORTAPI {
    var $version = "1.0";
    var $errorMessage;
    var $errorCode;
    
    /**
     * Cache the information on the API location on the server
     */
    var $apiUrl;
    
    /**
     * Default to a 300 second timeout on server calls
     */
    var $timeout = 300; 
    
    /**
     * Default to a 8K chunk size
     */
    var $chunkSize = 8192;
    
    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $api_key;

    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $secure = false;
    
	var $output = 'json';
	
    /**
     * Connect to the MailChimp API for a given list.
     * 
     * @param string $apikey Your MailChimp apikey
     * @param string $secure Whether or not this should use a secure connection
     */
    function MCEXPORTAPI($apikey, $secure=false) {
        $this->secure = $secure;
        $this->apiUrl = parse_url("http://api.mailchimp.com/export/" . $this->version . "/");
        $this->api_key = $apikey;
    }
    function setTimeout($seconds){
        if (is_int($seconds)){
            $this->timeout = $seconds;
            return true;
        }
    }
    function getTimeout(){
        return $this->timeout;
    }
    function useSecure($val){
        if ($val===true){
            $this->secure = true;
        } else {
            $this->secure = false;
        }
    }
    
    /**
     * Actually connect to the server and call the requested methods, parsing the result
     * You should never have to call this function manually
     */
    function __call($method, $params) {
	    $dc = "us1";
	    if (strstr($this->api_key,"-")){
        	list($key, $dc) = explode("-",$this->api_key,2);
            if (!$dc) $dc = "us1";
        }
        $host = $dc.".".$this->apiUrl["host"];

        $this->errorMessage = "";
        $this->errorCode = "";
        $sep_changed = false;
        //sigh, apparently some distribs change this to &amp; by default
        if (ini_get("arg_separator.output")!="&"){
            $sep_changed = true;
            $orig_sep = ini_get("arg_separator.output");
            ini_set("arg_separator.output", "&");
        }
        //mutate params
        $mutate = array();
		$mutate["apikey"] = $this->api_key;
        foreach($params as $k=>$v){
            $mutate[$this->function_map[$method][$k]] = $v;
        }
        $post_vars = http_build_query($mutate);
        if ($sep_changed){
            ini_set("arg_separator.output", $orig_sep);
        }
        
        $payload = "POST " . $this->apiUrl["path"] . $method . "/ HTTP/1.0\r\n";
        $payload .= "Host: " . $host . "\r\n";
        $payload .= "User-Agent: MCAPImini/" . $this->version ."\r\n";
        $payload .= "Content-type: application/x-www-form-urlencoded\r\n";
        $payload .= "Content-length: " . strlen($post_vars) . "\r\n";
        $payload .= "Connection: close \r\n\r\n";
        $payload .= $post_vars;
        
        ob_start();
        if ($this->secure){
            $sock = fsockopen("ssl://".$host, 443, $errno, $errstr, 30);
        } else {
            $sock = fsockopen($host, 80, $errno, $errstr, 30);
        }
        if(!$sock) {
            $this->errorMessage = "Could not connect (ERR $errno: $errstr)";
            $this->errorCode = "-99";
            ob_end_clean();
            return false;
        }
        
        $response = "";
        fwrite($sock, $payload);
        stream_set_timeout($sock, $this->timeout);
        $info = stream_get_meta_data($sock);
        while ((!feof($sock)) && (!$info["timed_out"])) {
            $response .= fread($sock, $this->chunkSize);
            $info = stream_get_meta_data($sock);
        }
        if ($info["timed_out"]) {
            $this->errorMessage = "Could not read response (timed out)";
            $this->errorCode = -98;
        }
        fclose($sock);
        ob_end_clean();
        if ($info["timed_out"]) return false;
		
        list($throw, $response) = explode("\r\n\r\n", $response, 2);
        
        if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);
		
		$json_array = explode("\n", $response);
		foreach ((array)$json_array as $key => $val) { $json_array[$key] = json_decode($val, 1); }
		
		if($response && !is_array($json_array[0])) {
		   	$response = array("error" => "Bad Response.  Got This: " . $response, "code" => "-99");
		} else {
			$response = $json_array;
		}
		
		if(is_array($response[0]) && isset($response[0]["error"])) {
			$response = $response[0];
		}
		
        if(is_array($response) && isset($response["error"])) {
            $this->errorMessage = $response["error"];
            $this->errorCode = $response["code"];
            return false;
        }
		
		unset($json_array);
        return $response;
    }
    
    protected $function_map = array(
		'list' => array(
			'id',
			'status',
			'segment',
			'since',
		),
		'campaignSubscriberActivity' => array(
			'id',
			'include_empty',
		),
	);

}

?>