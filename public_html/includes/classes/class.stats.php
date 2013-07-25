<?php

class stats {
	function stats($dblink,$sites_id) {
		$this->dblink = $dblink;
		$this->sites_id = $sites_id;
	}
	
	function insert_emails($values) {
		$success = true;
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
						
		$sql = "INSERT INTO `stats_emails`
			SET `sites_id` = '" . mysql_real_escape_string($this->sites_id) . "',
				`time_sent` = '" . $values['time_sent'] . "',   
				`campaign_id` = '" . $values['campaign_id'] . "', 
				`campaign_name` = '" . $values['campaign_name'] . "', 
				`campaign_subject` = '" . $values['campaign_subject'] . "', 
				`syntax_errors` = '" . $values['syntax_errors'] . "', 
				`hard_bounces` = '" . $values['hard_bounces'] . "', 
				`soft_bounces` = '" . $values['soft_bounces'] . "', 
				`unsubscribes` = '" . $values['unsubscribes'] . "', 
				`abuse_reports` = '" . $values['abuse_reports'] . "', 
				`forwards` = '" . $values['forwards'] . "', 
				`forwards_opens` = '" . $values['forwards_opens'] . "', 
				`opens` = '" . $values['opens'] . "', 
				`last_open` = '" . $values['last_open'] . "', 
				`unique_opens` = '" . $values['unique_opens'] . "', 
				`clicks` = '" . $values['clicks'] . "', 
				`unique_clicks` = '" . $values['unique_clicks'] . "', 
				`last_click` = '" . $values['last_click'] . "', 
				`users_who_clicked` = '" . $values['users_who_clicked'] . "', 
				`emails_sent` = '" . $values['emails_sent'] . "',  
				`cities_id` = '" . $values['cities_id'] . "' 
		";
		if ($this->show_sql) { echo $sql.(defined('STDIN') ? "\n" : '<br />'); }

		$success = ($success && query($sql,$this->dblink));
		$id = mysql_insert_id($this->dblink);
		
		if ($success) { return $id; }
		
		return false;
	}
	
	function insert_emails_actions($values) {
		$success = true;
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
						
		$sql = "INSERT INTO `stats_emails_actions`
			SET `sites_id` = '" . mysql_real_escape_string($this->sites_id) . "',  
				`members_id` = '" . $values['members_id'] . "', 
				`campaign_id` = '" . $values['campaign_id'] . "', 
				`campaign_name` = '" . $values['campaign_name'] . "', 
				`campaign_subject` = '" . $values['campaign_subject'] . "', 
				`action` = '" . $values['action'] . "', 
				`action_datetime` = '" . $values['action_datetime'] . "', 
				`url` = '" . $values['url'] . "', 
				`ip_address` = '" . $values['ip_address'] . "', 
				`cities_id` = '" . $values['cities_id'] . "', 
				`deals_id` = '" . $values['deals_id'] . "' 
		";
		if ($this->show_sql) { echo $sql.(defined('STDIN') ? "\n" : '<br />'); }

		$success = ($success && query($sql,$this->dblink));
		$id = mysql_insert_id($this->dblink);
		
		if ($success) { return $id; }
		
		return false;
	}
	
	function insert_views($values) {
		$success = true;
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
						
		$sql = "INSERT INTO `stats_views`
			SET `sites_id` = '" . mysql_real_escape_string($this->sites_id) . "',
				`view_datetime` = '" . mysql_real_escape_string($values['view_datetime']) . "',
				`ip_address` = '" . $values['ip_address'] . "', 
				`device_id` = '" . $values['device_id'] . "', 
				`session_id` = '" . $values['session_id'] . "', 
				`referrer` = '" . $values['referrer'] . "', 
				`useragent` = '" . $values['useragent'] . "', 
				`url` = '" . $values['url'] . "', 
				`host` = '" . $values['host'] . "', 
				`path` = '" . $values['path'] . "', 
				`query` = '" . $values['query'] . "', 
				`fragment` = '" . $values['fragment'] . "', 
				`source` = '" . $values['source'] . "', 
				`medium` = '" . $values['medium'] . "', 
				`term` = '" . $values['term'] . "', 
				`content` = '" . $values['content'] . "', 
				`campaign` = '" . $values['campaign'] . "', 
				`members_id` = '" . $values['members_id'] . "', 
				`cities_id` = '" . $values['cities_id'] . "', 
				`deals_id` = '" . $values['deals_id'] . "'
		";
		if ($this->show_sql) { echo $sql.(defined('STDIN') ? "\n" : '<br />'); }

		$success = ($success && query($sql,$this->dblink) or die(mysql_error()));
		$id = mysql_insert_id($this->dblink);
		
		if ($success) { return $id; }
		
		return false;
	}
	
	function record_deal_view($deals_id, $cities_id, $members_id = null) {
		
		$url = $_SERVER['REQUEST_URI'];
		if (!$url) { $url = $_SERVER['REDIRECT_URL']; }
		if (!$url) { $url = $_SERVER['PHP_SELF']; }
		$parsed_url = parse_url($url);
		if (!$parsed_url['host']) { $parsed_url['host'] = $_SERVER['HTTP_HOST']; }
		parse_str($parsed_url['query'], $query_string);
		$source = $query_string['utm_source'];
		$medium = $query_string['utm_medium'];
		$term = $query_string['utm_term'];
		$content = $query_string['utm_content'];
		$campaign = $query_string['utm_campaign'];
				
		$values = array(
			'view_datetime' => date('Y-m-d H:i:s'),
			'ip_address' => get_ip(),
			'session_id' => session_id(),
			'device_id' => '',
			'referrer' => $_SERVER['HTTP_REFERER'],
			'useragent' => $_SERVER['HTTP_USER_AGENT'],
			'url' => $url,
			'host' => $parsed_url['host'],
			'path' => $parsed_url['path'],
			'query' => $parsed_url['query'],
			'fragment' => $parsed_url['fragment'],
			'source' => $source,
			'medium' => $medium,
			'term' => $term,
			'content' => $content,
			'campaign' => $campaign,
			'members_id' => (int)$members_id,
			'cities_id' => (int)$cities_id,
			'deals_id' => (int)$deals_id,
		);
				
		return $this->insert_views($values);
	}
		
}
?>