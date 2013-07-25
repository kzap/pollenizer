<?php

class sites {
	function sites($dblink,$sites_id) {
		$this->dblink = $dblink;
		$this->sites_id = $sites_id;
	}
	
	function set_sites_list() {
		$sql = "SELECT s.* 
			FROM `sites` AS `s`
		";		
		$res = query($sql,$this->dblink);
		
		if (!$res) 
			return false;
		
		while ( $deal = mysql_fetch_assoc($res	) ) {
			
			$this->sites[] = $deal;
			
		}
			
		$this->count = sizeof($this->sites);
	}
	
	function get_sites_list() {
		return $this->sites;
	}
	
	function get($id) {
		$sql = "SELECT * FROM `sites` WHERE `sites_id` = '" . mysql_real_escape_string($id) . "' LIMIT 1";
		$res = query($sql,$this->dblink);
		
		if ($res)
			return mysql_fetch_assoc($res);
		
		return false;
	}
	
	function insert($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		$sql = "INSERT INTO `sites` SET
			`sites_name` = '" . $values['sites_name'] . "'
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		$id = mysql_insert_id($this->dblink);
		
		if ($id && $values['domain']) {
			$sql = "INSERT INTO `sites_domains` 
				SET `domain` = '" . mysql_real_escape_string($values['domain']) . "', 
					`sites_id` = '" . mysql_real_escape_string($id) . "'
			";
			$success = ($success &&  query($sql,$this->dblink));
		}
		
		return $success;
	}
	
	function update($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		if (!$values['sites_id']) { return false; }
		
		$sql = "UPDATE `sites` SET
			`sites_name` = '" . $values['sites_name'] . "'
			WHERE `sites_id` = '" . $values['sites_id'] . "'
			LIMIT 1
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		return $success;
	}
	
	function delete($id) {
		if (!$id)
			return false;
			
		$success = true;
		
		$tables = array('sites');
		
		foreach ($tables as $table) {
			$sql = "DELETE FROM `" . $table . "` WHERE `sites_id` = '" . mysql_real_escape_string($id) . "'";
			$success = ($success && query($sql, $this->dblink));
		}
		
		return $success;		
	}

	function set_domains_list() {
		$sql = "SELECT sd.* 
			FROM `sites_domains` AS `sd`
			WHERE sd.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'
		";		
		$res = query($sql,$this->dblink);
		
		if (!$res) 
			return false;
		
		while ( $deal = mysql_fetch_assoc($res	) ) {
			
			$this->domains[] = $deal;
			
		}
			
		$this->count = sizeof($this->domains);
	}
	
	function get_domains_list() {
		return $this->domains;
	}
	
	function get_domain($id) {
		$sql = "SELECT * 
			FROM `sites_domains` 
			WHERE `domains_id` = '" . mysql_real_escape_string($id) . "' 
			LIMIT 1
		";
		$res = query($sql,$this->dblink);
		
		if ($res)
			return mysql_fetch_assoc($res);
		
		return false;
	}
	
	function insert_domains($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		$sql = "INSERT INTO `sites_domains` 
			SET `domain` = '" . $values['domain'] . "',
				`sites_id` = '" . mysql_real_escape_string($values['sites_id']) . "'
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		return $success;
	}
	
	function update_domains($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		if (!$values['domains_id']) { return false; }
		
		$sql = "UPDATE `sites_domains` 
			SET `domain` = '" . $values['domain'] . "'
			WHERE `domains_id` = '" . $values['domains_id'] . "'
			LIMIT 1
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		return $success;
	}
	
	function delete_domains($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		if (!$values['domains_id']) { return false; }
		
		
		$sql = "DELETE FROM `sites_domains` 
			WHERE `domains_id` = '" . mysql_real_escape_string($values['domains_id']) . "'
				AND `sites_id` = '" . mysql_real_escape_string($values['sites_id']) . "'
		";
		$success = ($success && query($sql, $this->dblink));
		
		return $success;		
	}
	
	function delete_domains_byname($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		if (!$values['domain']) { return false; }
		
		
		$sql = "DELETE FROM `sites_domains` 
			WHERE `domain` = '" . mysql_real_escape_string($values['domain']) . "'
				AND `sites_id` = '" . mysql_real_escape_string($values['sites_id']) . "'
		";
		$success = ($success && query($sql, $this->dblink));
		
		return $success;		
	}
	
	function delete_domains_all($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		if (!$values['sites_id']) { return false; }
		
		
		$sql = "DELETE FROM `sites_domains` 
			WHERE `sites_id` = '" . mysql_real_escape_string($values['sites_id']) . "'
		";
		$success = ($success && query($sql, $this->dblink));
		
		return $success;		
	}
	
}
