<?php
class contents {
	function contents($link,$sites_id) {
		define('SCROLLABLE',1);
		define('NOT_SCROLLABLE',0);
		global $_SITE; // site specific configuration values used in feeds
		$this->site = $_SITE;
		$this->set_dblink($link);
		$this->set_sites_id($sites_id);
		// do nothing
	}

	function set_sites_id($sites_id) {
		$this->sites_id = $sites_id;
	}
	
	function set_dblink($link) {
		$this->dblink = $link;
	}

	function prepare_feed($feed) {
		
		if (empty($feed)) 
			return false;
		
		// replace site specific configuration values in a given feed
		if (preg_match_all('/\$_SITE\[\'?(\w+)\'?\]/',$feed, $matches)) {
			$site_config_keys = array_unique($matches[1]);
			
			foreach($site_config_keys as $key) {
				$feed = preg_replace('/\$_SITE\[\'?('.$key.')\'?\]/',stripslashes($this->site[$key]),$feed);
			}			
		}
		
		// replace site specific configuration values in a given feed
		if (preg_match_all('/\$_SESSION\[\'?(\w+)\'?\]\[\'?(\w+)\'?\]/',$feed, $matches)) {
			$matching_keys = array_keys($matches[1]);
			
			foreach($matching_keys as $key) {
			
				$feed = preg_replace('/\$_SESSION\[\'?('.$matches[1][$key].')\'?\]\[\'?('.$matches[2][$key].')\'?\]/',stripslashes($_SESSION[$matches[1][$key]][$matches[2][$key]]),$feed);
			}			
		}
		
		// replace site specific configuration values in a given feed
		if (preg_match_all('/\$_SESSION\[\'?(\w+)\'?\]/',$feed, $matches)) {
			$matching_keys = array_keys($matches[1]);
			
			foreach($matching_keys as $key) {
			
				$feed = preg_replace('/\$_SESSION\[\'?('.$matches[1][$key].')\'?\]/',stripslashes($_SESSION[$matches[1][$key]]),$feed);
			}			
		}
		
		$feed = stripslashes($feed);
		
		return $feed;
	}
	
	function get($id) {
		if (!$id) { return false; }
		
		$sql = "SELECT *
			FROM `contents` AS `c`
			JOIN `contents_to_sites` AS `cs` ON (cs.`contents_id` = c.`contents_id` AND cs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE c.`contents_id` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($r = mysql_fetch_assoc($res)) {
			$article = $r;
			return $article;
		}
		
		return false;
	}
	
	function get_categories($id) {
		if (!$id) { return false; }
		
		$categories = array();
		
		$sql = "SELECT cc.* 
			FROM `contents_categories` AS `cc`
			JOIN `contents_to_categories` AS `ctc` ON (ctc.`categories_id` = cc.`categories_id`)
			JOIN `contents_categories_to_sites` AS `ccts` ON (ccts.`categories_id` = cc.`categories_id` AND ccts.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE ctc.`contents_id` = '" . mysql_real_escape_string($id) . "'
		";
		$res = query($sql, $this->dblink);
		while ($r = mysql_fetch_assoc($res)) {
			$categories[] = $r;
		}
		
		return $categories;
	}
	
	function get_byname($feed_name) {
		if (!$feed_name) { return false; }
		
		$sql = "SELECT c.*
			FROM `contents` AS `c`
			JOIN `contents_to_sites` AS `cs` ON (cs.`contents_id` = c.`contents_id` AND cs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE c.`contents_slug` = '" . mysql_real_escape_string($feed_name) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);

		if ($r = mysql_fetch_assoc($res)) {
			$article = $this->get($r['contents_id']);
			$article['contents_text'] = $this->prepare_feed($article['contents_text']);
			return $article;
		}
		
		return false;
	}
	
	function show($feed_name,$scrollable=NOT_SCROLLABLE) {
		if( $scrollable )
			echo '<p><div style="overflow: auto; height: 380px; width: 520px; padding-right: 10px;">';
		
		echo $this->get($feed_name);
	
		if( $scrollable ) 
			echo '</div></p>';
	}
	
	function insert($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		
		$sql = "INSERT INTO `contents` 
			SET `contents_title` = '" . $values['contents_title'] . "', 
				`contents_text` = '" . $values['contents_text'] . "', 
				`contents_image` = '" . $values['contents_image'] . "', 
				`contents_date` = '" . $values['contents_date'] . "', 
				`members_only` = '" . $values['members_only'] . "', 
				`internal_only` = '" . $values['internal_only'] . "', 
				`social` = '" . $values['social'] . "', 
				`comments` = '" . $values['comments'] . "' 
		";
		
		$success = ($success && query($sql,$this->dblink));
		$id = mysql_insert_id($this->dblink);
		
		if ($id) {
			$sql = "INSERT INTO `contents_to_sites` 
				SET `contents_id` = '" . mysql_real_escape_string($id) . "', 
					`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'
			";
			$success = ($success &&  query($sql,$this->dblink));
			
			$success = ($success && $this->update_slug($id, $values['contents_slug']));
			
		}
		
		return $success;
	}
	
	function update($values) {
		if (!$values['contents_id'])
			return false;
		
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
	
		$sql = "UPDATE `contents` 
			SET `contents_title` = '" . $values['contents_title'] . "', 
				`contents_text` = '" . $values['contents_text'] . "', 
				`contents_image` = '" . $values['contents_image'] . "', 
				`contents_date` = '" . $values['contents_date'] . "', 
				`members_only` = '" . $values['members_only'] . "' , 
				`internal_only` = '" . $values['internal_only'] . "', 
				`social` = '" . $values['social'] . "', 
				`comments` = '" . $values['comments'] . "' 
			WHERE `contents_id` = '" . mysql_real_escape_string($values['contents_id']) . "'
			LIMIT 1
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		$success = ($success && $this->update_slug($values['contents_id'], $values['contents_slug']));
		
		return $success;
	}

	function delete($id) {
		if (!$id)
			return false;
		
		$success = true;
		
		$tables = array('contents', 'contents_to_sites', 'contents_to_categories');
		
		foreach ($tables as $table) {
			$sql = "DELETE FROM `" . $table . "` 
				WHERE `contents_id` = '" . mysql_real_escape_string($id) . "'
			";
			$success = ($success && query($sql, $this->dblink));
		}
		
		return $success;
	}
	
	function update_slug($id, $slug='') {
		if (!$id) { return false; }
		
		$slug = trim($slug);
		
		if (!$slug) { // get name from id
			$sql = "SELECT `contents_title` 
				FROM `contents` 
				WHERE `contents_id` = '" . mysql_real_escape_string($id) . "' 
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			if ($r = mysql_fetch_assoc($res)) {
				$slug = trim($r['contents_title']);
			} else { return false; }
		}
		
		$NOT_FOUND = false; $i = 0;
		while (!$NOT_FOUND) {
			$slug_name = seo_format($slug);
			if (++$i > 1) { $slug_name .= '-'.$i; }
			$sql = "SELECT c.`contents_id` 
				FROM `contents` AS `c`
				JOIN `contents_to_sites` AS `cs` ON (cs.`contents_id` = c.`contents_id` AND cs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE c.`contents_slug` = '" . mysql_real_escape_string($slug_name) . "' 
					AND c.`contents_id` != '" . mysql_real_escape_string($id) . "' 
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			if (!mysql_num_rows($res)) { $NOT_FOUND = true; }
		}
		
		if ($slug_name) {
			$sql = "UPDATE `contents` 
				SET `contents_slug` = '" . mysql_real_escape_string($slug_name) . "' 
				WHERE `contents_id` = '" . mysql_real_escape_string($id) . "' 
				LIMIT 1
			";
			$success = query($sql, $this->dblink);
			return $success;
		}
		
	}
	
	function add_to_category($contents_id,$categories_id) {
		if (!$contents_id || !$categories_id) 
			return false;
			
		$sql = "INSERT INTO `contents_to_categories` 
			SET `contents_id` = '" . mysql_real_escape_string($contents_id) . "', 
				`categories_id` = '" . mysql_real_escape_string($categories_id) . "'
		";
		return query($sql,$this->dblink);
	}

	function delete_from_category($contents_id,$categories_id) {
		if (!$contents_id || !$categories_id) 
			return false;
			
		$sql = "DELETE FROM `contents_to_categories` 
			WHERE `contents_id` = '" . mysql_real_escape_string($contents_id) . "' 
				AND `categories_id` = '" . mysql_real_escape_string($categories_id) . "' LIMIT 1";
		return query($sql,$this->dblink);
	}
	
	function delete_from_allcategories($contents_id) {
		if (!$contents_id) 
			return false;
			
		$sql = "DELETE FROM `contents_to_categories` 
			WHERE `contents_id` = '" . mysql_real_escape_string($contents_id) . "'
		";
		return query($sql,$this->dblink);
	}
	
	function get_category($id) {
		if (!$id) { return false; }
		
		$sql = "SELECT * 
			FROM `contents_categories` AS `cc`
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE cc.`categories_id` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($res)
			return mysql_fetch_assoc($res);
	}
	
	function get_category_byseourl($seo_url) {
		if (!$seo_url) { return false; }
		
		$sql = "SELECT * 
			FROM `contents_categories` AS `cc`
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE cc.`seo_url` = '" . mysql_real_escape_string($seo_url) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($res)
			return mysql_fetch_assoc($res);
	}
	
	function insert_category($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		
		$success = true;
		
		// products sort
		if ($values['categories_parent_id']) {
			$sql = "SELECT MIN(`categories_sort`) 
				FROM `contents_categories` 
				WHERE `categories_parent_id` = '" . mysql_real_escape_string($values['categories_parent_id']) . "'
			";
			$res = query($sql, $this->dblink);
			$min_sort = ((int)@mysql_result($res,0,0)-10);
		} else { $min_sort = 0; }
		
		$sql = "INSERT INTO `contents_categories` 
			SET `categories_parent_id` = '" . $values['categories_parent_id'] . "', 
			 	`categories_name` = '" . $values['categories_name'] . "', 
				`categories_visibility` = '" . $values['categories_visibility'] . "', 
				`categories_sort` = '" . ($min_sort) . "', 
				`displays_items` = '" . $values['displays_items'] . "', 
				`default_sort_field` = '" . $values['default_sort_field'] . "', 
				`default_sort_order` = '" . $values['default_sort_order'] . "'
		";
		$success = ( $success && query($sql, $this->dblink) );
		$id = mysql_insert_id($this->dblink);
		
		if ($id) {
			$sql = "INSERT INTO `contents_categories_to_sites` 
				SET `categories_id` = '" . mysql_real_escape_string($id) . "', 
					`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'
			";
			$success = ($success &&  query($sql,$this->dblink));
			
			//$success = ($success && $this->update_slug($id, $values['contents_slug']));
		}
		
		return $success;
	}

	function update_category($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		
		$success = true;
		
		// product details
		$sql = "UPDATE `contents_categories` SET ";
		$sql_fields = '';
		if (isset($values['categories_parent_id'])) { $sql_fields .= "`categories_parent_id` = '" . $values['categories_parent_id'] . "', "; }
		if (isset($values['categories_name'])) { $sql_fields .= "`categories_name` = '" . $values['categories_name'] . "', "; }
		if (isset($values['categories_sort'])) { $sql_fields .= "`categories_sort` = '" . $values['categories_sort'] . "', "; }
		if (isset($values['categories_visibility'])) { $sql_fields .= "`categories_visibility` = '" . $values['categories_visibility'] . "', "; }
		if (isset($values['displays_items'])) { $sql_fields .= "`displays_items` = '" . $values['displays_items'] . "', "; }
		if (isset($values['default_sort_field'])) { $sql_fields .= "`default_sort_field` = '" . $values['default_sort_field'] . "', "; }
		if (isset($values['default_sort_order'])) { $sql_fields .= "`default_sort_order` = '" . $values['default_sort_order'] . "', "; }
		if (isset($values['seo_url'])) { $sql_fields .= "`seo_url` = '" . $values['seo_url'] . "', "; }
		
		if (!$sql_fields) { return false; }
		$sql .= substr($sql_fields, 0, -2);
		$sql .= " WHERE `categories_id` = '" . mysql_real_escape_string($values['categories_id']) . "'
			LIMIT 1
		";
		
		$success = ($success && query($sql, $this->dblink));
		
		return $success;
	}
	
	function delete_category($id) {
		if (!$id) { return false; }
		$success = true;
		
		$tables = array('contents_categories', 'contents_to_categories', 'contents_categories_to_sites');
		
		foreach ($tables as $table) {
			$sql = "DELETE FROM `" . $table . "` 
				WHERE `categories_id` = '" . mysql_real_escape_string($id) . "'
			";
			$success = ($success && query($sql, $this->dblink));
		}
		
		return $success;		
	}
	
	function move_up($id) {
		if (!$id) { return false; }
		$success = true;
		
		if ($category = $this->get_category($id)) {
			
			$sql = "SELECT cc.`categories_id`, cc.`categories_sort`, cc.`categories_parent_id`
				FROM `contents_categories` AS `cc`
				JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE cc.`categories_sort` < '" . mysql_real_escape_string($category['categories_sort']) . "' 
					AND cc.`categories_parent_id` = '" . mysql_real_escape_string($category['categories_parent_id']) . "' 
					AND cc.`categories_id` != '" . mysql_real_escape_string($category['categories_id']) . "'
				ORDER BY cc.`categories_sort` DESC
				LIMIT 1";
			$res = query($sql,$this->dblink);
			
			if ($previous = mysql_fetch_assoc($res)) {
				
				$sql = "UPDATE `contents_categories` 
					SET `categories_sort` = '" . mysql_real_escape_string($previous['categories_sort']) . "' 
					WHERE `categories_id` = '" . mysql_real_escape_string($category['categories_id']) . "' 
					LIMIT 1
				";
				$success = ($success && query($sql,$this->dblink));
				
				$sql = "UPDATE `contents_categories` 
					SET `categories_sort` = '" . mysql_real_escape_string($category['categories_sort']) . "' 
					WHERE `categories_id` = '" . mysql_real_escape_string($previous['categories_id']) . "' 
					LIMIT 1
				";
				$success = ($success && query($sql,$this->dblink));
				
				return $success;
			}
			
		} 
				
		return false;
	}
	
	function move_down($id) {
		if (!$id) { return false; }
		$success = true;
		
		if ($category = $this->get_category($id)) {
		
			$sql = "SELECT cc.`categories_id`, cc.`categories_sort`, cc.`categories_parent_id`
				FROM `contents_categories` AS `cc`
				JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE cc.`categories_sort` > '" . mysql_real_escape_string($category['categories_sort']) . "' 
					AND cc.`categories_parent_id` = '" . mysql_real_escape_string($category['categories_parent_id']) . "'
					AND cc.`categories_id` != '" . mysql_real_escape_string($category['categories_id']) . "'
				ORDER BY cc.`categories_sort` DESC
				LIMIT 1";
			$res = query($sql,$this->dblink);
			
			if ($previous = mysql_fetch_assoc($res)) {
				
				$sql = "UPDATE `contents_categories` 
					SET `categories_sort` = '" . mysql_real_escape_string($previous['categories_sort']) . "' 
					WHERE `categories_id` = '" . mysql_real_escape_string($category['categories_id']) . "' 
					LIMIT 1
				";
				$success = ($success && query($sql,$this->dblink));
				
				$sql = "UPDATE `contents_categories` 
					SET `categories_sort` = '" . mysql_real_escape_string($category['categories_sort']) . "' 
					WHERE `categories_id` = '" . mysql_real_escape_string($previous['categories_id']) . "' 
					LIMIT 1
				";
				$success = ($success && query($sql,$this->dblink));
				
				return $success;
			}
			
		} 
				
		return false;
	}
	
	function set_seo_url($categories_parent_id = 0, $seo_url = '', $cpath = '') {
		
		// reset seo_url and cpath
		$sql = "UPDATE `contents_categories` AS `cc`
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			SET cc.`seo_url` = '', cc.`cpath` = ''
			WHERE 1
		";
		if ($categories_parent_id) {
			$sql .= " AND cc.`categories_parent_id` = '" . mysql_real_escape_string($categories_parent_id) . "'";
		} else {
			$sql .= " AND (cc.`categories_parent_id` IS NULL OR cc.`categories_parent_id` = 0)";
		}
		query($sql, $this->dblink);
		
		// get subcats
		$sql = "SELECT cc.`categories_id`, cc.`categories_name` 
			FROM `contents_categories` AS `cc`
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE 1
		";
		if ($categories_parent_id) {
			$sql .= " AND cc.`categories_parent_id` = '" . mysql_real_escape_string($categories_parent_id) . "'";
		} else {
			$sql .= " AND (cc.`categories_parent_id` IS NULL OR cc.`categories_parent_id` = 0)";
		}
		$res = query($sql, $this->dblink);
		
		while( $cat = mysql_fetch_assoc($res) ){
			unset($url, $path);
			if ($seo_url) { $url = $seo_url.'/'.seo_format($cat['categories_name']); }
			else { $url = seo_format($cat['categories_name']); }
			
			unset($i, $extraSlug);
			do {
				if (++$i > 1) { $extraSlug = '-'.$i; }
				$sql = "SELECT cc.`categories_id` 
					FROM `contents_categories` AS `cc`
					JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
					WHERE cc.`seo_url` = '" . mysql_real_escape_string($url.$extraSlug) . "'
				";
				$res2 = query($sql, $this->dblink);
			} while (mysql_num_rows($res2));
			$url .= $extraSlug;
			
			if ($cpath) { $path = $cpath.'_'.$cat['categories_id']; }
			else { $path = $cat['categories_id']; }
			
			$sql = "UPDATE `contents_categories` 
				SET `seo_url` = '" . mysql_real_escape_string($url) . "', 
					`cpath` = '" . mysql_real_escape_string($path) . "'
				WHERE `categories_id` = '" . mysql_real_escape_string($cat['categories_id']) . "'
				LIMIT 1
			";
			query($sql, $this->dblink);
			
			$this->set_seo_url($cat['categories_id'], $url, $path);
		}
	}
	
	function build_categories_navigation($categories_parent_id = 0, $flat = false, $max_depth = false, $count_items = false, $show_all = false, $current_depth = 0) {
		
		$sql = "SELECT cc.`categories_id`, cc.`categories_parent_id`, cc.`categories_name`, cc.`seo_url`, cc.`cpath`
			FROM `contents_categories` as `cc`
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE 1
		";
		if ($categories_parent_id) {
			$sql .= " AND cc.`categories_parent_id` = '" . mysql_real_escape_string($categories_parent_id) . "'";
		} else {
			$sql .= " AND (cc.`categories_parent_id` IS NULL OR cc.`categories_parent_id` = 0)";
		}
		if (!$show_all) {
			$sql .= " AND cc.`categories_visibility` = '1'";
		}
		$sql .= " ORDER BY cc.`categories_sort`, cc.`categories_name`";
		$res = query($sql, $this->dblink);
		
		$navigation = array();
		while ( $category = mysql_fetch_assoc($res) ) {
			$nav = array();
			$nav['name']  = $category['categories_name'];
			$nav['url'] = $category['seo_url'] . '/';
			$nav['path'] = $category['cpath'];
			$nav['depth'] = $current_depth;
			$nav['id'] = $category['categories_id'];
			if ($count_items) {
				$nav['count'] = $this->items_in_category($category['categories_id']);
			}
			$nav['parent_id'] = $categories_parent_id;
			
			if (!$flat && ($max_depth === false || (int)$max_depth < 0 || ($current_depth+1) <= (int)$max_depth)) {
				$nav['sub_nav'] = (array)$this->build_categories_navigation($category['categories_id'], $flat, $max_depth, $count_items, $show_all, ($current_depth+1));
			}
			
			$navigation[] = $nav;
			
			if ($flat && ($max_depth === false || (int)$max_depth < 0 || ($current_depth+1) <= (int)$max_depth)) {
				$navigation = array_merge((array)$navigation, (array)$this->build_categories_navigation($category['categories_id'], $flat, $max_depth, $count_items, $show_all, ($current_depth+1)));
			}
			
		}
		
		return $navigation;
	}
	
	function items_in_category($categories_id) {
		$items_count = 0;
		
		if (!$categories_id) { return $items_count; }
		
		$sql = "SELECT COUNT(*) AS `total`
			FROM `contents_to_categories` AS `ctc`
			JOIN `contents` AS `c` ON (c.`contents_id` = ctc.`contents_id`)
			JOIN `contents_to_sites` AS `cs` ON (cs.`contents_id` = ctc.`contents_id` AND cs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			JOIN `contents_categories` AS `cc` ON (cc.`categories_id` = ctc.`categories_id`)
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = ctc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE ctc.`categories_id` = '" . mysql_real_escape_string($categories_id) . "'
				AND c.`internal_only` = 0
		";
		$res = query($sql,$this->dblink);
		$r = mysql_fetch_assoc($res);
		$items_count += (int)$r['total'];
		
		// get subcats
		$sql = "SELECT cc.`categories_id`
			FROM `contents_categories` AS `cc`
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = cc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE cc.`categories_parent_id` = '" . mysql_real_escape_string($categories_id) . "'
		";
		$res = query($sql, $this->dblink);
		while ($r = mysql_fetch_assoc($res)) {
			$items_count += (int)$this->items_in_category($r['categories_id']);
		}
		
		return $items_count;
	}
	
	function display_categories_nav($arr){
		$pathArray = explode('_', $_REQUEST['path']); 
		$parent_id = $arr[0]['parent_id'];
		
		if (in_array($parent_id, $pathArray) || !$parent_id) { $show = ''; }
		else { $show = 'display:none'; }
		
		echo '<div class="catNav" id="catId' . $parent_id. '" style="' . $show . '">';
		
		foreach((array)$arr as $nav){
			$link = ( !is_array($nav['sub_nav']) || !$parent_id) ? ''  : ' onclick="toggleCat(\'catId' . $nav['id'] . '\'); return false;"' ;
			echo str_repeat('&nbsp;', ($nav['depth']+0));
			echo '<a href="' . DIR_CONTENT . $nav['url'] . '"' . $link .'>';
			//echo '<a href="' . DIR_WS_ROOT . '?path='.urlencode($nav['path']) . '"' . $link .'>';
			echo $nav['name'];
			echo '</a>'."\n";
			if (!empty($nav['sub_nav'])) { 
				$this->display_categories_nav($nav['sub_nav']); 
			}
			echo '<br />';
		}
		
		echo '</div>';
	}
	
	function category_displays_products($categories_id) {
		$sql = "SELECT `displays_items` 
			FROM `contents_categories` 
			WHERE `categories_id` = '" . mysql_real_escape_string($categories_id) . "' 
			LIMIT 1
		";
		$res = query($sql,$this->dblink);
		
		$this->category_displays_products = ( mysql_result($res,0,0) == 1);
		
		return $this->category_displays_products;
	}
	
	function set_contents_list_by_category($categories_id, $sort='', $order='asc') {
		// category actuall was set to display products
		$this->contents_count = 0;
		
		if ( $categories_id && !$this->category_displays_products($categories_id) ) {
			$this->contents_count = 0;
			return false;
		}
			
		// is there a categories id
		if ( $categories_id == '' ) {
			$this->contents_count = 0;
			return false;
		}
		
		// check sort params
		switch (strtolower($sort)) {
			default: 
				$sort = "c.`contents_date`";
				$order = 'DESC';
		}
		
		// get list of content ids
		$sql = "SELECT c.`contents_id`
			FROM `contents` AS `c`
			JOIN `contents_to_categories` AS `ctc` ON (ctc.`contents_id` = c.`contents_id`)
			JOIN `contents_to_sites` AS `cs` ON (cs.`contents_id` = c.`contents_id` AND cs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			JOIN `contents_categories` AS `cc` ON (cc.`categories_id` = ctc.`categories_id`)
			JOIN `contents_categories_to_sites` AS `ccs` ON (ccs.`categories_id` = ctc.`categories_id` AND ccs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE c.`internal_only` = 0
		";
		if ($categories_id) { $sql .= " AND ctc.`categories_id` = '" . mysql_real_escape_string($categories_id) . "'"; }
		
		$res = query($sql,$this->dblink);
		if (!$res) {
			$this->contents_count = 0;
			return false;
		}
		
		$content_ids = array();
		while ($r = mysql_fetch_assoc($res)) { $content_ids[] = $r['contents_id']; }
		
		$sql = "SELECT c.*
			FROM `contents` AS `c`
			JOIN `contents_to_sites` AS `cs` ON (cs.`contents_id` = c.`contents_id` AND cs.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE c.`internal_only` = 0
		";
		if (!empty($content_ids)) { $sql .= " AND c.`contents_id` IN ('" . implode("', '", array_map('mysql_real_escape_string', $content_ids)) . "')"; }
		$sql .= "
			ORDER BY $sort $order
		";
		if (!$categories_id) { $sql .= " LIMIT 15"; }
		
		$res = query($sql,$this->dblink);
		while ( $content = mysql_fetch_assoc($res) ) {
			$this->contents[] = $content;
		}
		
		$this->contents_count = sizeof($this->contents);
	}
	
}