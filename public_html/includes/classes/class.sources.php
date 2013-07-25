<?

class sources {
	function sources($dblink,$sites_id) {
		$this->dblink = $dblink;
		$this->sites_id = $sites_id;
	}
	
	function set_sources_list($deal_id=0) {
		$sql = "SELECT d.* 
			FROM `sources` AS `d`
			JOIN `sources_to_sites` AS `ds` ON (ds.`sources_id` = d.`sources_id` AND ds.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "') 
			WHERE d.`sources_visibility` = 1 
		";		
		$res = query($sql,$this->dblink);
		
		if (!$res) 
			return false;
		
		while ( $deal = mysql_fetch_assoc($res	) ) {
			
			$this->sources[] = $deal;
			
		}
			
		$this->count = sizeof($this->sources);
	}
	
	function get_sources_list() {
		return $this->sources;
	}
	
	function get($id) {
		$sql = "SELECT * FROM `sources` WHERE `sources_id` = '" . mysql_real_escape_string($id) . "' LIMIT 1";
		$res = query($sql,$this->dblink);
		
		if ($res)
			return mysql_fetch_assoc($res);
		
		return false;
	}
	
	function insert($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		$sql = "INSERT INTO `sources` 
			SET `valid` = '" . $values['valid'] . "',
				`user_ID` = '" . $values['user_ID'] . "',
				`name` = '" . $values['name'] . "',
				`url` = '" . $values['url'] . "',
				`feed_url` = '" . $values['feed_url'] . "',
				`tag1` = '" . $values['tag1'] . "',
				`tag2` = '" . $values['tag2'] . "',
				`tag3` = '" . $values['tag3'] . "',
				`tag4` = '" . $values['tag4'] . "',
				`tag5` = '" . $values['tag5'] . "',
				`tag6` = '" . $values['tag6'] . "',
				`tag7` = '" . $values['tag7'] . "',
				`tag8` = '" . $values['tag8'] . "',
				`tag9` = '" . $values['tag9'] . "',
				`description` = '" . $values['description'] . "',
				`date_signup` = NOW(),
				`date_modify` = NOW()
		";

		$success = ($success && query($sql,$this->dblink));
		$id = mysql_insert_id($this->dblink);
		
		if ($id) {
			$sql = "INSERT INTO `sources_to_sites` 
				SET `sources_id` = '" . mysql_real_escape_string($id) . "', 
					`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'
			";
			$success = ($success &&  query($sql,$this->dblink));
			
			$success = ($success && $this->update_slug($id, $values['slug']));
		}
		
		return $success;
	}
	
	function update($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		if (!$values['sources_id']) { return false; }
		
		$sql = "UPDATE `sources` 
			SET `valid` = '" . $values['valid'] . "',
				`user_ID` = '" . $values['user_ID'] . "',
				`name` = '" . $values['name'] . "',
				`url` = '" . $values['url'] . "',
				`feed_url` = '" . $values['feed_url'] . "',
				`tag1` = '" . $values['tag1'] . "',
				`tag2` = '" . $values['tag2'] . "',
				`tag3` = '" . $values['tag3'] . "',
				`tag4` = '" . $values['tag4'] . "',
				`tag5` = '" . $values['tag5'] . "',
				`tag6` = '" . $values['tag6'] . "',
				`tag7` = '" . $values['tag7'] . "',
				`tag8` = '" . $values['tag8'] . "',
				`tag9` = '" . $values['tag9'] . "',
				`description` = '" . $values['description'] . "',
				`date_modify` = NOW()
			WHERE `sources_id` = '" . $values['sources_id'] . "'
			LIMIT 1
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		$success = ($success && $this->update_slug($values['sources_id'], $values['slug']));
		
		return $success;
	}
	
	function delete($id) {
		if (!$id)
			return false;
			
		$success = true;
		
		$tables = array('sources', 'sources_to_sites');
		
		foreach ($tables as $table) {
			$sql = "DELETE FROM `" . $table . "` WHERE `sources_id` = '" . mysql_real_escape_string($id) . "'";
			$success = ($success && query($sql, $this->dblink));
		}
		
		return $success;		
	}
	
	function update_slug($id, $slug='') {
		if (!$id) { return false; }
		
		$slug = trim($slug);
		
		if (!$slug) { // get name from id
			$sql = "SELECT s.`name` 
				FROM `sources` AS `s` 
				JOIN `sources_to_sites` AS `ss` ON (ss.`sources_id` = s.`sources_id` AND ss.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE s.`sources_id` = '" . mysql_real_escape_string($id) . "' 
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			if ($r = mysql_fetch_assoc($res)) {
				$slug = trim($r['name']);
			} else { return false; }
		}
		
		$NOT_FOUND = false; $i = 0;
		while (!$NOT_FOUND) {
			$slug_name = seo_format($slug);
			if (++$i > 1) { $slug_name .= '-'.$i; }
			$sql = "SELECT s.`sources_id` 
				FROM `sources` AS `s`
				JOIN `sources_to_sites` AS `ss` ON (ss.`sources_id` = s.`sources_id` AND ss.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE s.`slug` = '" . mysql_real_escape_string($slug_name) . "' 
					AND s.`sources_id` != '" . mysql_real_escape_string($id) . "' 
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			if (!mysql_num_rows($res)) { $NOT_FOUND = true; }
		}
		
		if ($slug_name) {
			$sql = "UPDATE `sources` 
				SET `slug` = '" . mysql_real_escape_string($slug_name) . "' 
				WHERE `sources_id` = '" . mysql_real_escape_string($id) . "' 
				LIMIT 1
			";
			$success = query($sql, $this->dblink);
			return $success;
		}
		
	}

	function add_to_site($id, $sites_id) {
		if (!$id || !$sites_id) 
			return false;
		$success = true;
		
		$sql = "INSERT INTO `sources_to_sites` 
			SET `sources_id` = '" . mysql_real_escape_string($id) . "', 
				`sites_id` = '" . mysql_real_escape_string($sites_id) . "'
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		return $success;
	}
	
	function delete_from_site($id,$sites_id) {
		if (!$id || !$sites_id) 
			return false;
		$success = true;
		
		if ($sites_id == $this->sites_id) { return false; }
			
		$sql = "DELETE FROM `sources_to_sites` 
			WHERE `sources_id` = '" . mysql_real_escape_string($id) . "' 
				AND `sites_id` = '" . mysql_real_escape_string($sites_id) . "' 
				AND `sites_id` != '" . mysql_real_escape_string($this->sites_id) . "' 
			LIMIT 1
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		return $success;
	}
	
	function delete_from_allsites($id) {
		if (!$id) 
			return false;
		$success = true;
		
		$sql = "DELETE FROM `sources_to_sites` 
			WHERE `sources_id` = '" . mysql_real_escape_string($id) . "' 
				AND `sites_id` != '" . mysql_real_escape_string($this->sites_id) . "'
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		return $success;
	}
	
}
?>