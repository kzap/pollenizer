<?

class textbroker {
	function textbroker($link,$sites_id) {
		include_once(DIR_CLASSES . 'nusoap/lib/nusoap.php');
		$this->set_dblink($link);
		$this->set_sites_id($sites_id);
		$this->success_msgs = array();
		$this->error_msgs = array();
		// do nothing
	}
	
	function set_sites_id($sites_id) {
		$this->sites_id = $sites_id;
	}
	
	function set_dblink($link) {
		$this->dblink = $link;
	}
	
	function set_budget_id($id) {
		$this->budget_id = $id;
	}
	
	function set_budget_key($key) {
		$this->budget_key = $key;
	}
	
	function set_budget_password($password) {
		$this->budget_password = $password;
	}
	
	function set_api_url($url) {
		$this->api_url = $url;
	}
	
	function soap($operation, $params = array(), $endpoint = '') {
		$response = false;
		
		if (!$endpoint) {
			switch ($operation) {
				case 'changeWorkTime':
				case 'changeWordsCount':
					$endpoint = $this->api_url.'budgetChangeService.php';
				break;
				
				case 'getCategories':
				case 'create':
				case 'getStatus':
				case 'pickUp':
				case 'delete':
				case 'preview':
				case 'accept':
				case 'revise':
				case 'reject':
					$endpoint = $this->api_url.'budgetOrderService.php';
				break;
				
				case 'getUsage':
				case 'isInSandbox':
				case 'getName':
				case 'getActualPeriodData':
					$endpoint = $this->api_url.'budgetCheckService.php';
				break;
				
				case 'doLogin':
				default:
					$endpoint = $this->api_url.'loginService.php';
				break;
			}
		}
		
		// default params
		$salt = rand(0, 10000);
		$defaultParams = array(
			$salt,
			md5($salt . $this->budget_password),
			$this->budget_key,
		);
		
		$params = array_merge($defaultParams, (array)$params);
		
		$client = new nusoap_client($endpoint, false, false, false, false, false, 0, 999999999999999999);
		$client->soap_defencoding = 'UTF-8';
		$client->decode_utf8 = false;
		if ($client->getError() && $this->debug) { pre('error = '.$client->getError()); }
		else {
			$response = $client->call($operation, $params);
			if ($this->debug) { pre('response = '.(int)$response); }
		}
		
		if ($this->debug) {
			echo '<h2>Request</h2>';
			echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
			echo '<h2>Response</h2>';
			echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
			echo '<h2>Debug</h2>';
			echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
		}
		
		return $response;
		/*
$options = array(
	'location' => $tb->api_url.'loginService.php',
	'uri' => $tb->api_url,
);
$client = new SoapClient(null, $options);
$salt = rand(0, 10000);
try {
	$response = $client->doLogin($salt, md5($salt . $tb->budget_password), $tb->budget_key);
	pre('response = '.$response);
}
	catch (SoapFault $result) {
	pre($result);
} 
*/
	}
	
	function prepare_feed($feed_row) {
		$feed = trim($feed_row['content_feeds_content']);
		
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
		$content = array(
			'title' => stripslashes($feed_row['content_feeds_title']),
			'content' => $feed,
			'members_only' => $feed_row['members_only'],
			'internal_only' => $feed_row['internal_only'],
		);
		
		return $content;
	}
	
	function get($id) {
		$sql = "SELECT *
			FROM `textbroker` 
			WHERE `id` = '" . mysql_real_escape_string($id) ."'
			LIMIT 1
		";
		$res = mysql_query($sql, $this->dblink);
		
		if ( mysql_num_rows($res) == 1 ) {
			return mysql_fetch_assoc($res);
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
		$id = false;
		
		$sql = "INSERT INTO `textbroker` SET 
			`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "', 
			`categories_id` = '" . $values['categories_id'] . "', 
			`title` = '" . $values['title'] . "', 
			`description` = '" . $values['description'] . "', 
			`min` = '" . $values['min'] . "', 
			`max` = '" . $values['max'] . "', 
			`stars` = '" . $values['stars'] . "', 
			`deadline` = '" . $values['deadline'] . "'
		";
		
		$success = ($success && query($sql,$this->dblink));
		$id = mysql_insert_id($this->dblink);
		
		if ($values['submit_live']) {
			$success = ($success && $this->tb_create($id));
		}
		
		return $id;
	}
	
	function update($id, $values) {
		if (!$id || empty($values))
			return false;
		
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
	
		$sql = "UPDATE `textbroker` SET ";
		$sql_fields = '';
		if (isset($values['categories_id'])) { $sql_fields .= "`categories_id` = '" . $values['categories_id'] . "', "; }
		if (isset($values['title'])) { $sql_fields .= "`title` = '" . $values['title'] . "', "; }
		if (isset($values['description'])) { $sql_fields .= "`description` = '" . $values['description'] . "', "; }
		if (isset($values['min'])) { $sql_fields .= "`min` = '" . $values['min'] . "', "; }
		if (isset($values['max'])) { $sql_fields .= "`max` = '" . $values['max'] . "', "; }
		if (isset($values['stars'])) { $sql_fields .= "`stars` = '" . $values['stars'] . "', "; }
		if (isset($values['deadline'])) { $sql_fields .= "`deadline` = '" . $values['deadline'] . "', "; }
		if (isset($values['budget_order_id'])) { $sql_fields .= "`budget_order_id` = '" . $values['budget_order_id'] . "', "; }
		if (isset($values['budget_order_type'])) { $sql_fields .= "`budget_order_type` = '" . $values['budget_order_type'] . "', "; }
		if (isset($values['budget_order_status'])) { $sql_fields .= "`budget_order_status` = '" . $values['budget_order_status'] . "', "; }
		if (isset($values['budget_order_status_id'])) { $sql_fields .= "`budget_order_status_id` = '" . $values['budget_order_status_id'] . "', "; }
		if (isset($values['count_words'])) { $sql_fields .= "`count_words` = '" . $values['count_words'] . "', "; }
		if (isset($values['author'])) { $sql_fields .= "`author` = '" . $values['author'] . "', "; }
		if (isset($values['author_title'])) { $sql_fields .= "`author_title` = '" . $values['author_title'] . "', "; }
		if (isset($values['text'])) { $sql_fields .= "`text` = '" . $values['text'] . "', "; }
		if (isset($values['classification'])) { $sql_fields .= "`classification` = '" . $values['classification'] . "', "; }
		
		if (!$sql_fields) { return false; }
		$sql_fields = substr($sql_fields, 0, -2);
		$sql .= $sql_fields . " WHERE `id` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		
		$success = ($success && query($sql,$this->dblink));
		
		if ($values['submit_live']) {
			$success = ($success && $this->tb_create($id));
		}
		
		return $success;
	}

	function delete($id) {
		if (!$id) {
			$this->error_msgs[] = 'ID missing';
			return false;
		}
		
		if ($article = $this->get($id)) {
			if ($article['budget_order_id']/* && $article['budget_order_status_id'] != 7*/) {
				if ($this->tb_delete($id)) {
					return true;
				}
			} else {
				// dont delete if has budget_order_id
				$sql = "DELETE FROM `textbroker` WHERE `id` = '" . mysql_real_escape_string($id) . "' LIMIT 1";
				return query($sql,$this->dblink);
			}
		}
		
		return false;
	}
	
	function tb_create($id) {
		if (!$id) {
			$this->error_msgs[] = 'ID missing';
			return false;
		}
		
		// submit to text broker
		if ($article = $this->get($id)) {
			if ($article['budget_order_id']) { 
				$this->error_msgs[] = $id.' - Already has a Budget Order ID: '.$article['budget_order_id'];
				return false;
			}
			
			$params = array(
				$article['categories_id'],
				$article['title'],
				$article['description'],
				$article['min'],
				$article['max'],
				$article['stars'],
				$article['deadline'],
			);
			$result = $this->soap('create', $params);
			if ($result['error'] == null) {
				$values = array('budget_order_id' => $result['budget_order_id']);
				if ($this->update($id, $values)) {
					$this->success_msgs[] = 'Budget Order ID updated to '.$result['budget_order_id'];
					return $this->tb_update_status($id);
				} else { $this->error_msgs[] = mysql_error($this->dblink); }
			} else { $this->error_msgs[] = 'SOAP Error: '.$result['error']; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function tb_update_status($id) {
		if (!$id) {
			$this->error_msgs[] = 'ID missing';
			return false;
		}
		
		// submit to text broker
		if ($article = $this->get($id)) {
			if (!$article['budget_order_id']) { 
				$this->error_msgs[] = $id.' - Has no Budget Order ID';
				return false;
			}
			$params = array($article['budget_order_id']);
			$result = $this->soap('getStatus', $params);
			if ($result['error'] == null) {
				$values = array(
					'budget_order_status' => $result['budget_order_status'],
					'budget_order_status_id' => $result['budget_order_status_id'],
					'budget_order_type' => $result['budget_order_type'],
				);
				if ($this->update($id, $values)) {
					$this->success_msgs[] = 'Status Information Updated to '.$result['budget_order_status_id'].' - '.$result['budget_order_status'];
					
					if ($result['budget_order_status_id'] == 4) { return $this->tb_update_preview($id); }
					elseif ($result['budget_order_status_id'] == 5) { return $this->tb_pickup($id); }
					else { return true; }
				} else { $this->error_msgs[] = mysql_error($this->dblink); }
			} else { $this->error_msgs[] = 'SOAP Error: '.$result['error']; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function tb_update_preview($id) {
		if (!$id) {
			$this->error_msgs[] = 'ID missing';
			return false;
		}
		
		if ($article = $this->get($id)) {	
			// success - values retrieved
			if ($article['budget_order_id']) {
				$params = array($article['budget_order_id']);
				$result = $this->soap('preview', $params);
				if ($result['error'] == null) {
					$values = array(
						'count_words' => $result['count_words'],
						'author' => $result['author'],
						'author_title' => $result['title'],
						'text' => iconv("UTF-8", "CP1252", $result['text']),
						'classification' => $result['classification'],
					);
					if ($this->update($id, $values)) {
						$this->success_msgs[] = 'Preview Article Updated: '.$result['title'].' - '.$result['author'];
						return true;
						
					// failure - database error
					} else { $this->error_msgs[] = mysql_error($this->dblink); }
				} else { $this->error_msgs[] = 'SOAP Error: '.$result['error'];	}
			} else { $this->error_msgs[] = 'No Textbroker Order ID Found'; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function tb_accept($id, $values) {
		if (!$id || empty($values)) {
			$this->error_msgs[] = 'ID or Values missing';
			return false;
		}
		
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		
		if ($article = $this->get($id)) {	
			// success - values retrieved
			if ($article['budget_order_id']) {
				$params = array(
					$article['budget_order_id'],
					$values['accept_rating'],
					$values['message'],
				);
				$result = $this->soap('accept', $params);
				if ($result['error'] == null) {
					$values = array(
						'count_words' => $result['count_words'],
						'author' => $result['author'],
						'author_title' => $result['title'],
						'text' => iconv("UTF-8", "CP1252", $result['text']),
					);
					if ($this->update($id, $values)) {
						$this->success_msgs[] = 'Article Accepted: '.$result['title'].' - '.$result['author'];
						
						return $this->tb_update_status($id);
						
					// failure - database error
					} else { $this->error_msgs[] = mysql_error($this->dblink); }
				} else { $this->error_msgs[] = 'SOAP Error: '.$result['error'];	}
			} else { $this->error_msgs[] = 'No Textbroker Order ID Found'; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function tb_revise($id, $values) {
		if (!$id || empty($values)) {
			$this->error_msgs[] = 'ID or Values missing';
			return false;
		}
		
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		
		if ($article = $this->get($id)) {	
			// success - values retrieved
			if ($article['budget_order_id']) {
				$params = array(
					$article['budget_order_id'],
					$values['message'],
				);
				$result = $this->soap('revise', $params);
				if ($result['error'] == null) {
					$this->success_msgs[] = 'Textbroker Article Revision Requested: '.$article['title'].' - '.$article['author'];
					
					return $this->tb_update_status($id);
				} else { $this->error_msgs[] = 'SOAP Error: '.$result['error'];	}
			} else { $this->error_msgs[] = 'No Textbroker Order ID Found'; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function tb_pickup($id) {
		if (!$id) {
			$this->error_msgs[] = 'ID  missing';
			return false;
		}
		
		if ($article = $this->get($id)) {	
			// success - values retrieved
			if ($article['budget_order_id']) {
				$params = array(
					$article['budget_order_id'],
				);
				$result = $this->soap('pickUp', $params);
				if ($result['error'] == null) {
					$values = array(
						'count_words' => $result['count_words'],
						'author' => $result['author'],
						'author_title' => $result['title'],
						'text' => iconv("UTF-8", "CP1252", $result['text']),
					);
					if ($this->update($id, $values)) {
						$this->success_msgs[] = 'Article Picked Up: '.$result['title'].' - '.$result['author'];
						
						return $this->tb_update_status($id);
						
					// failure - database error
					} else { $this->error_msgs[] = mysql_error($this->dblink); }
				} else { $this->error_msgs[] = 'SOAP Error: '.$result['error'];	}
			} else { $this->error_msgs[] = 'No Textbroker Order ID Found'; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function tb_reject($id) {
		if (!$id) {
			$this->error_msgs[] = 'ID  missing';
			return false;
		}
		
		if ($article = $this->get($id)) {	
			// success - values retrieved
			if ($article['budget_order_id']) {
				$params = array(
					$article['budget_order_id'],
				);
				$result = $this->soap('reject', $params);
				if ($result['error'] == null) {
					$this->success_msgs[] = 'Textbroker Article Rejected: '.$article['title'].' - '.$article['author'];
						
					return $this->tb_update_status($id);
				} else { $this->error_msgs[] = 'SOAP Error: '.$result['error'];	}
			} else { $this->error_msgs[] = 'No Textbroker Order ID Found'; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function tb_delete($id) {
		if (!$id) {
			$this->error_msgs[] = 'ID  missing';
			return false;
		}
		
		if ($article = $this->get($id)) {	
			// success - values retrieved
			if ($article['budget_order_id']) {
				$params = array(
					$article['budget_order_id'],
				);
				$result = $this->soap('delete', $params);
				if ($result['error'] == null) {
					$this->success_msgs[] = 'Textbroker Article Deleted: '.$article['title'].' - '.$article['author'];
						
					return $this->tb_update_status($id);
				} else { $this->error_msgs[] = 'SOAP Error: '.$result['error'];	}
			} else { $this->error_msgs[] = 'No Textbroker Order ID Found'; }
		} else { $this->error_msgs[] = mysql_error($this->dblink); }
		
		return false;
	}
	
	function update_slug($id, $slug='') {
		if (!$id) { return false; }
		
		$slug = trim($slug);
		
		if (!$slug) { // get name from id
			$sql = "SELECT `content_feeds_title` FROM `sites_content_feeds` WHERE `content_feeds_id` = '" . mysql_real_escape_string($id) . "' LIMIT 1";
			$res = query($sql, $this->dblink);
			if ($r = mysql_fetch_assoc($res)) {
				$slug = trim($r['content_feeds_title']);
			} else { return false; }
		}
		
		$NOT_FOUND = false; $i = 0;
		while (!$NOT_FOUND) {
			$slug_name = seo_format($slug);
			if (++$i > 1) { $slug_name .= '-'.$i; }
			$sql = "SELECT `content_feeds_id` FROM `sites_content_feeds`
				WHERE `content_feeds_name` = '" . mysql_real_escape_string($slug_name) . "' 
					AND `content_feeds_id` != '" . mysql_real_escape_string($id) . "' 
					AND `sites_id` = '" . mysql_real_escape_string($this->sites_id) . "' 
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			if (!mysql_num_rows($res)) { $NOT_FOUND = true; }
		}
		
		if ($slug_name) {
			$sql = "UPDATE `sites_content_feeds` SET `content_feeds_name` = '" . mysql_real_escape_string($slug_name) . "' WHERE `content_feeds_id` = '" . mysql_real_escape_string($id) . "' LIMIT 1";
			$success = query($sql, $this->dblink);
			return $success;
		}
		
	}
}
?>