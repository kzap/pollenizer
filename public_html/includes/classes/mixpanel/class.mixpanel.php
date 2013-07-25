<?php

class Mixpanel {
	
    public $token;
	public $api_key;
	public $api_secret;
	public $test;
    public $api_url = 'http://api.mixpanel.com';
	public $async = false;
	
    public function __construct($token_string, $api_key = null, $api_secret = null) {
        $this->token = $token_string;
		$this->api_key = $api_key;
		$this->api_secret = $api_secret;
    }
	
    public function track($event, $properties=array()) {
    	$this->api_url = 'http://api.mixpanel.com';
		
        $params = array(
            'event' => $event,
            'properties' => $properties
            );

        if (!isset($params['properties']['token'])){
            $params['properties']['token'] = $this->token;
        }
        $url = $this->api_url . '/track/?data=' . base64_encode(json_encode($params));
		if ($this->test) { $url .= '&test=' . $this->test; }
        //you still need to run as a background process
       if ($this->async) {
        	exec("curl '" . $url . "' >/dev/null 2>&1 &"); 
		} else {
			$curl_handle=curl_init();
	        curl_setopt($curl_handle, CURLOPT_URL, $url);
	        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	        $data = curl_exec($curl_handle);
	        curl_close($curl_handle);
			
			return $data;
		} 
    }
	
	public function import($event, $properties=array()) {
		$this->api_url = 'http://api.mixpanel.com';
		if (!$this->api_key) {
			echo 'API_KEY Required';
			exit;
		}
		
        $params = array(
            'event' => $event,
            'properties' => $properties
            );

        if (!isset($params['properties']['token'])){
            $params['properties']['token'] = $this->token;
        }
        $url = $this->api_url . '/import/?data=' . base64_encode(json_encode($params)) . '&api_key=' . urlencode($this->api_key);
		
        //you still need to run as a background process
        if ($this->async) {
        	exec("curl '" . $url . "' >/dev/null 2>&1 &"); 
		} else {
			$curl_handle=curl_init();
	        curl_setopt($curl_handle, CURLOPT_URL, $url);
	        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	        $data = curl_exec($curl_handle);
	        curl_close($curl_handle);
			
			return $data;
		}
    }
	
	public function set($distinct_id, $properties) {
		$params = array(
			'$set' => $properties,
			'$distinct_id' => $distinct_id,
		);
		return $this->engage($params);
	}

	public function add($distinct_id, $properties) {
		$params = array(
			'$add' => $properties,
			'$distinct_id' => $distinct_id,
		);
		return $this->engage($params);
	}
	
	public function engage($params) {
		$this->api_url = 'http://api.mixpanel.com';
        
        if (!isset($params['token'])){
            $params['token'] = $this->token;
        }
        $url = $this->api_url . '/engage/?data=' . base64_encode(json_encode($params));
        //you still need to run as a background process
        if ($this->async) {
        	exec("curl '" . $url . "' >/dev/null 2>&1 &"); 
		} else {
			$curl_handle=curl_init();
	        curl_setopt($curl_handle, CURLOPT_URL, $url);
	        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	        $data = curl_exec($curl_handle);
	        curl_close($curl_handle);
			
			return $data;
		} 
    }
    
	public function request($methods, $params, $format='json') {
		$this->api_url = 'http://mixpanel.com/api';
		$this->version = '2.0';
		
        // $end_point is an API end point such as events, properties, funnels, etc.
        // $method is an API method such as general, unique, average, etc.
        // $params is an associative array of parameters.
        // See http://mixpanel.com/api/docs/guides/api/

        if (!isset($params['api_key']))
            $params['api_key'] = $this->api_key;
        
        $params['format'] = $format;
        
        if (!isset($params['expire'])) {
            $current_utc_time = time() - date('Z');
            $params['expire'] = $current_utc_time + 600; // Default 10 minutes
        }
        
        $param_query = '';
        foreach ($params as $param => &$value) {
            if (is_array($value))
                $value = json_encode($value);
            $param_query .= '&' . urlencode($param) . '=' . urlencode($value);
        }
        
        $sig = $this->signature($params);
        
        $uri = '/' . $this->version . '/' . join('/', $methods) . '/';
        $request_url = $uri . '?sig=' . $sig . $param_query;
        
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->api_url . $request_url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl_handle);
        curl_close($curl_handle);
        
        return json_decode($data, 1);
    }

	public function export($params, $format='json') {
		$this->api_url = 'http://data.mixpanel.com/api';
		$this->version = '2.0';
		$methods = array('export');
		
        // $end_point is an API end point such as events, properties, funnels, etc.
        // $method is an API method such as general, unique, average, etc.
        // $params is an associative array of parameters.
        // See http://mixpanel.com/api/docs/guides/api/

        if (!isset($params['api_key']))
            $params['api_key'] = $this->api_key;
        
        $params['format'] = $format;
        
        if (!isset($params['expire'])) {
            $current_utc_time = time() - date('Z');
            $params['expire'] = $current_utc_time + 600; // Default 10 minutes
        }
        
        $param_query = '';
        foreach ($params as $param => &$value) {
            if (is_array($value))
                $value = json_encode($value);
            $param_query .= '&' . urlencode($param) . '=' . urlencode($value);
        }
        
        $sig = $this->signature($params);
        
        $uri = '/' . $this->version . '/' . join('/', $methods) . '/';
        $request_url = $uri . '?sig=' . $sig . $param_query;
        
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $this->api_url . $request_url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl_handle);
        curl_close($curl_handle);
		
		if ($data[0] != '{' && strlen($data)) {
			return array(
				'Error' => $data,
			);
		}
		
		$dataByLine = explode("\n", $data);
		$dataArray = array();
		foreach ($dataByLine as $dataLine) {
			$dataArray[] = json_decode($dataLine, 1);
		}
		
        return $dataArray;
    }
    
    private function signature($params) {
        ksort($params);
        $param_string ='';
        foreach ($params as $param => $value) {
            $param_string .= $param . '=' . $value;
        }
        
        return md5($param_string . $this->api_secret);
    }
	
}
