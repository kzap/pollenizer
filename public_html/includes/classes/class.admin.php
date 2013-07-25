<?php
class admin extends PasswordHash {
	
	public $_bad_user;
	public $_acc_disabled = false;
	// Base-2 logarithm of the iteration count used for password stretching
	public $hash_cost_log2 = 8;
	// Do we require the hashes to be portable to older systems (less secure)?
	public $hash_portable = FALSE;
	public $errors = array();
	
	function admin($dblink, $sites_id) {
		$this->dblink = $dblink;
		$this->sites_id = $sites_id;
		$this->PasswordHash($this->hash_cost_log2, $this->hash_portable);
		
		if (defined('ADMIN_CODE_EXPIRE')) { $this->code_expire = ADMIN_CODE_EXPIRE; }
		if (!$this->code_expire) { $this->code_expire = 15*60; }
		
		if (defined('ADMIN_FORGOT_EXPIRE')) { $this->forgot_expire = ADMIN_FORGOT_EXPIRE; }
		if (!$this->forgot_expire) { $this->forgot_expire = 15*60; }
	}
	
	function setdblink($link) {
		$this->dblink = $link;
	}
	
	function logs(){
		$get   = @array_map('map_entities', $_GET);
		$post  = @array_map_r('map_entities', $_POST);

		foreach( $post as $key => $post_val ){
			// dont log passwords
			if (in_array($key, array('password','password_confirm','temp_password'))) { $post_val = 'HIDDEN'; }
			if (is_string($post_val)) { $post[$key] = substr(trim($post_val), 0, 100); }
		}
		
		$get = array_map('trim', $get);
		
		$sSql = "INSERT INTO `adminpanel_logs` 
			SET `sites_id` = '" . mysql_real_escape_string($this->sites_id) . "', 
				`userid` = '" . mysql_real_escape_string($_SESSION['adm']['id']) . "', 
				`page` = '" . mysql_real_escape_string($_SERVER['PHP_SELF']) . "', 
				`get` = '" . mysql_real_escape_string( serialize( $get ) ) . "', 
				`post` = '" . mysql_real_escape_string( serialize( $post ) ). "', 
				`dateandtime` = '" . date('Y-m-d H:i:s',time()) . "',
				`ip_address` = '" . mysql_real_escape_string(get_ip()) . "'
		";
		query($sSql, $this->dblink);
	}

	function deleteLog($id){
		$sSql = "DELETE FROM `adminpanel_logs` 
			WHERE `logid` = '" . mysql_real_escape_string($id) . "'
		";
		query($sSql, $this->dblink);
	}
	
	function passwordStrike($id){
		if (!$id) { return false; }
		
		$sql = "SELECT a.`strike` 
			FROM `adminpanel_users` AS `a`
			WHERE a.`userid` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		if ($r = mysql_fetch_assoc($res)) {
			
			$update = array(
				'userid' => $id,
				'strike' => (int)(++$r['strike']),
				'strike_date' => date('Y-m-d H:i:s'),
			);
			return $this->update($update);
		}
		
		return false;
		
	}
	
	function checkStrikes($id){
		if (!$id) { return false; }
		
		$minStrikes = 3;
		
		$sql = "SELECT a.`strike`, a.`strike_date`
			FROM `adminpanel_users` AS `a`
			WHERE a.`userid` = '" . mysql_real_escape_string($id) . "'
				AND a.`strike` > " . $minStrikes . "
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		if ($r = mysql_fetch_assoc($res)) {
			$strike_date = strtotime($r['strike_date']);
			$strikes = $r['strike'] - $minStrikes;
			$clear_time = $strike_date + ($strikes * 30);
			if (time() < $clear_time) {
				$timeLeft = $clear_time - time();
				$this->errors[] = 'Please wait '.sec_to_time($timeLeft).' before logging in again';
				return true;
			}
		}
		
		return false;
		
	}
	
	function authenticate ($values, $twostep = true) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		
		if (!$values['login']) { 
			$this->errors[] = 'Username / E-Mail Missing.';
			return false;
		}
		if (!$values['password']) { $values['password'] = pwqgen(); } // fake pass to simulate login if they are trying to check for valid users
		
		//check username / email
		$sql = "SELECT a.* 
			FROM `adminpanel_users` AS `a`
			LEFT JOIN `adminpanel_users_to_sites` AS `as` ON (`as`.`userid` = a.`userid`) 
			WHERE 1
		";
		$sql .= " AND (`as`.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'";
		if (ADMIN_MAIN) {
			$adminMain = explode(',', trim(ADMIN_MAIN));
			if (!empty($adminMain)) {
				$sql .= " OR a.`userid` IN ('" . implode("', '", array_map('mysql_real_escape_string', $adminMain)) . "')";
			}
		}
		$sql .= ")";
		$sql .=	" AND (a.`username` = '" . $values['login'] . "' OR a.`email` = '" . $values['login'] . "') 
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($r = mysql_fetch_assoc($res)) {
			
			$this->validateUser($r['userid']);
			if ($this->checkStrikes($r['userid'])) {
				// have to wait
				return false;
			}
			
			// check password
			if ($this->CheckPassword($values['password'], $r['password'])) {
				// password is valid
				// update user password and hash
				$update = array(
					'userid' => $r['userid'],
					'password' => $values['password'],
				);
				if (!$twostep) {
					$update = array_merge($update, 
						array(
							'strike' => 0,
							'lastlogin' => date('Y-m-d H:i:s'),
							'lastloginip' => get_ip(),
							'reset_key' => '',
						)
					);
				}
				if ($this->update($update)) {
					return $r['userid'];
				}
			} else {
				// password wrong
				$this->passwordStrike($r['userid']);
				$this->errors[] = 'Username or Password Invalid. <a href="'.$_SERVER['PHP_SELF'].'?action=reset">Click Here To Reset Password</a>';
			}
		} else {
			// no user found, simulate fake user check to waste time
			$this->CheckPassword($values['password'], $this->HashPassword(pwqgen()));
			$this->errors[] = 'Username or Password Invalid. <a href="'.$_SERVER['PHP_SELF'].'?action=reset">Click Here To Reset Password</a>';
		}
		
		return false;
	}
	
	function get($id){
		$sql = "SELECT a.`userid`, a.`username`, a.`email`, a.`name`, a.`cellphone`, a.`status`, a.`strike`, a.`session_timeout`
			FROM `adminpanel_users` AS `a`
			LEFT JOIN `adminpanel_users_to_sites` AS `as` ON (`as`.`userid` = a.`userid`) 
			WHERE 1
		";
		$sql .= " AND (`as`.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'";
		if (ADMIN_MAIN) {
			$adminMain = explode(',', trim(ADMIN_MAIN));
			if (!empty($adminMain)) {
				$sql .= " OR a.`userid` IN ('" . implode("', '", array_map('mysql_real_escape_string', $adminMain)) . "')";
			}
		}
		$sql .= ")";
		$sql .=	" AND a.`userid` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		return mysql_fetch_assoc($res);
	}
	
	function insert($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		$errors = array();
		
		if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Invalid E-Mail'; 
		}
		if ($this->checkEmail($values['email'])) { 
			$errors[] = 'Please use another e-mail';
		}
		if ($values['username'] && $this->checkUsername($values['username'])) { 
			$errors[] = 'Invalid Username. Please use another username';
		}
		
		$pwqcheck = pwqcheck($values['password'], '', $values['username']);
		if ($pwqcheck !== 'OK') { 
			$errors[] = $pwqcheck; 
		}
		
		if (!empty($errors)) { 
			$this->errors = array_merge((array)$this->errors, (array)$errors);
			return false; 
		}
		
		$sql = "INSERT INTO `adminpanel_users` 
			SET `username` = '" . mysql_real_escape_string($values['username']) . "', 
				`email` = '" . mysql_real_escape_string($values['email']) . "', 
				`name` = '" . mysql_real_escape_string($values['name']) . "', 
				`password` = '" . mysql_real_escape_string($this->HashPassword($values['password'])) . "', 
				`cellphone` = '" . mysql_real_escape_string($values['cellphone']) . "', 
				`status` = '" . mysql_real_escape_string($values['status']) . "', 
				`pass_date` = NOW(), 
				`strike` = '0', 
				`lastlogin` = NOW(), 
				`lastloginip` = '" . mysql_real_escape_string(get_ip()) . "', 
				`session_timeout` = '" . mysql_real_escape_string($values['session_timeout']) . "'
		";
		
		$success = ($success && query($sql, $this->dblink));
		if ($success) {
			$values['userid'] = mysql_insert_id($this->dblink);
			
			$success = ($success && $this->add_to_site($values['userid'], $this->sites_id));
			$success = ($success && $this->updateHash($values['userid']));
		} else {
			$this->errors[] = 'Failed to insert user into database';
		}
		
		return $success;
	}
	
	function update($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		$success = true;
		$errors = array();
		
		if (!$values['userid']) { 
			$errors[] = 'User ID Missing';
		}
		if (isset($values['email']) && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Invalid E-Mail';
		}
		if (isset($values['email']) && $this->checkEmail($values['email'], $values['userid'])) { 
			$errors[] = 'Please use another e-mail';
		}
		if (isset($values['username']) && $this->checkUsername($values['username'], $values['userid'])) { 
			$errors[] = 'Invalid Username. Please use another username';
		}
		
		if (isset($values['password']) && strlen($values['password'])) {
			$pwqcheck = pwqcheck($values['password'], '', $values['username']);
			if ($pwqcheck !== 'OK') { $errors[] = $pwqcheck; }
			// update password date
			if (!isset($values['pass_date'])) { $values['pass_date'] = date('Y-m-d H:i:s'); }
		}
		
		if (!empty($errors)) { 
			$this->errors = array_merge((array)$this->errors, (array)$errors);
			return false; 
		}
		
		$sql = "UPDATE `adminpanel_users` SET ";
		if (isset($values['username'])) { $sql .= "`username` = '" . mysql_real_escape_string($values['username']) . "', "; }
		if (isset($values['email'])) { $sql .= "`email` = '" . mysql_real_escape_string($values['email']) . "', "; }
		if (isset($values['name'])) { $sql .= "`name` = '" . mysql_real_escape_string($values['name']) . "', "; }
		if (isset($values['password']) && strlen($values['password'])) { $sql .= "`password` = '" . mysql_real_escape_string($this->HashPassword($values['password'])) . "', "; }
		if (isset($values['cellphone'])) { $sql .= "`cellphone` = '" . mysql_real_escape_string($values['cellphone']) . "', "; }
		if (isset($values['status'])) { $sql .= "`status` = '" . mysql_real_escape_string($values['status']) . "', "; }
		if (isset($values['code'])) { $sql .= "`code` = '" . mysql_real_escape_string($this->HashPassword($values['code'])) . "', "; }
		if (isset($values['code_date'])) { $sql .= "`code_date` = '" . mysql_real_escape_string($values['code_date']) . "', "; }
		if (isset($values['alterego'])) { $sql .= "`alterego` = '" . mysql_real_escape_string($values['alterego']) . "', "; }
		if (isset($values['pass_date'])) { $sql .= "`pass_date` = '" . mysql_real_escape_string($values['pass_date']) . "', "; }
		if (isset($values['strike'])) { $sql .= "`strike` = '" . mysql_real_escape_string($values['strike']) . "', "; }
		if (isset($values['strike_date'])) { $sql .= "`strike_date` = '" . mysql_real_escape_string($values['strike_date']) . "', "; }
		if (isset($values['lastlogin'])) { $sql .= "`lastlogin` = '" . mysql_real_escape_string($values['lastlogin']) . "', "; }
		if (isset($values['lastloginip'])) { $sql .= "`lastloginip` = '" . mysql_real_escape_string($values['lastloginip']) . "', "; }
		if (isset($values['session_timeout'])) { $sql .= "`session_timeout` = '" . mysql_real_escape_string($values['session_timeout']) . "', "; }
		if (isset($values['reset_key'])) { $sql .= "`reset_key` = '" . mysql_real_escape_string($values['reset_key']) . "', "; }
		if (isset($values['reset_date'])) { $sql .= "`reset_date` = '" . mysql_real_escape_string($values['reset_date']) . "', "; }
		if (isset($values['reset_count'])) { $sql .= "`reset_count` = '" . mysql_real_escape_string($values['reset_count']) . "', "; }
		$sql = substr($sql, 0, -2);
		$sql .= "WHERE `userid` = '" . $values['userid'] . "'
			LIMIT 1
		";
		
		$success = ($success && query($sql, $this->dblink));
		if ($success) {
			$success = ($success && $this->updateHash($values['userid']));
		} else { $this->errors[] = 'Failed to update user into database'; }
		
		return $success;
	}
	
	function add_to_site($id, $sites_id) {
		if (!$id || !$sites_id) 
			return false;
		$success = true;
		
		$sql = "INSERT INTO `adminpanel_users_to_sites` 
			SET `userid` = '" . mysql_real_escape_string($id) . "', 
				`sites_id` = '" . mysql_real_escape_string($sites_id) . "'
		";
		
		$success = ($success && query($sql,$this->dblink));
		$success = ($success && $this->updateHash($id));
		
		return $success;
	}
	
	function delete_from_site($id,$sites_id) {
		if (!$id || !$sites_id) 
			return false;
		$success = true;
		
		if ($sites_id == $this->sites_id) { return false; }
			
		$sql = "DELETE FROM `adminpanel_users_to_sites` 
			WHERE `userid` = '" . mysql_real_escape_string($id) . "' 
				AND `sites_id` = '" . mysql_real_escape_string($sites_id) . "' 
				AND `sites_id` != '" . mysql_real_escape_string($this->sites_id) . "' 
			LIMIT 1
		";
		
		$success = ($success && query($sql,$this->dblink));
		$success = ($success && $this->updateHash($id));
		
		return $success;
	}
	
	function delete_from_allsites($id) {
		if (!$id) 
			return false;
		$success = true;
		
		$sql = "DELETE FROM `adminpanel_users_to_sites` 
			WHERE `userid` = '" . mysql_real_escape_string($id) . "' 
				AND `sites_id` != '" . mysql_real_escape_string($this->sites_id) . "'
		";
		
		$success = ($success && query($sql,$this->dblink));
		$success = ($success && $this->updateHash($id));
		
		return $success;
	}
	
	function checkEmail ($email, $id = null) {
		$sql = "SELECT 1 
			FROM `adminpanel_users`
			WHERE `email` = '" . mysql_real_escape_string($email) . "'
		";
		if ($id) { $sql .= " AND `userid` != '" . mysql_real_escape_string($id) . "'"; }
		
		$res = query($sql, $this->dblink);
		
		return mysql_num_rows($res);
	}
	
	function checkUsername ($username, $id = null) {
		$sql = "SELECT 1 
			FROM `adminpanel_users`
			WHERE `username` = '" . mysql_real_escape_string($username) . "'
		";
		if ($id) { $sql .= " AND `userid` != '" . mysql_real_escape_string($id) . "'"; }
		
		$res = query($sql, $this->dblink);
		
		return mysql_num_rows($res);
	}
	
	function createHash($id) {
		if (!$id) { return false; }
		
		$sql = "SELECT *
			FROM `adminpanel_users`
			WHERE `userid` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($r = mysql_fetch_assoc($res)) {
			
			$sql = "SELECT `sites_id`
				FROM `adminpanel_users_to_sites`
				WHERE `userid` = '" . mysql_real_escape_string($id) . "'
			";
			$res2 = query($sql, $this->dblink);
			$sites = array();
			while ($r2 = mysql_fetch_assoc($res2)) { $sites[] = $r2['sites_id']; }
			$sites = implode(',', $sites);
			
			$hash = array(
				$r['userid'],
				$r['username'],
				$r['email'],
				$r['password'],
				$r['cellphone'],
				$r['status'],
				$r['strike'],
				$r['reset_key'],
				$sites,
			);
			$hash = implode('|', $hash);
			
			return $hash;
		} else { $this->errors[] = 'User ID Not Found'; }
		
		return false;
	}
	
	function updateHash($id) {
		if (!$id) { return false; }
		
		if ($hash = $this->createHash($id)) {
			$sql = "UPDATE `adminpanel_users` 
				SET `hash` = '" . mysql_real_escape_string($this->HashPassword($hash)) . "'
				WHERE `userid` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1
			";
			if ($res = query($sql, $this->dblink)) {
				return true;
			} else { $this->errors[] = 'Failed to update hash in database'; }
		}
		
		return false;
	}
	
	function checkHash($id) {
		if (!$id) { return false; }
		
		$sql = "SELECT `hash`
			FROM `adminpanel_users`
			WHERE `userid` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($r = mysql_fetch_assoc($res)) {
			$hash = $this->createHash($id);
			if ($this->CheckPassword($hash, $r['hash'])) {
				return true;
			}
		}
		
		return false;
	}
	
	// validate user or else log out
	function validateUser($id) {
		if ($id) {
			$sql = "SELECT *
				FROM `adminpanel_users`
				WHERE `userid` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			
			if ($r = mysql_fetch_assoc($res)) {
//if (in_array($r['userid'], explode(',', ADMIN_MAIN))) { return true; } // bypass check if in trouble				
				if ($r['status'] != 1) { // check user status
					if ($r['status'] == 0) { $this->errors[] = 'User is not activated'; }
					elseif ($r['status'] == 2) { $this->errors[] = 'User has been locked out'; }
				} elseif (!$this->checkHash($r['userid'])) { // check hash of user
					// lock user
					$this->errors[] = 'User Data is Corrupted';
					$update = array(
						'userid' => $r['userid'],
						'status' => 2,
					);
					$this->update($update);
				} elseif (!$_SESSION['adm']['logged_in']) {
					return true;
				} elseif ($_SESSION['adm']['logged_in']) {
					// has logged in session, validate session
					
					// check if session not expired
					$session_expiry = $r['session_timeout'] ? $r['session_timeout'] : ADMIN_EXPIRE;
					if (time() > ($_SESSION['adm']['lastactivity'] + $session_expiry)) {
						$this->errors[] = 'Session Expired';
					} else {
						// if session is ok
						return true;
					}
				}
			} else { $this->errors[] = 'User Does Not Exist'; }
		} else { $this->errors[] = 'User Session Error'; }
		
		unset($_SESSION['adm']);
		echo errorbox($this->errors);
		@session_write_close();
		exit;
	}
	
	function do2Step($id) {
		global $_SITE;
		
		// get user
		if ($user = $this->get($id)) { 
			if (filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
				// create 2 step code
				$code = strtoupper(randomString(5));
				$update = array(
					'userid' => $id,
					'code' => $code,
					'code_date' => date('Y-m-d H:i:s'),
				);
				if ($this->update($update)) {
					// code was updated in database
					$sent = mail($user['email'], $_SITE['organization_name'].' Verification Code', $code, 'From: '.$_SITE['email_support'].' <'.$_SITE['email_support'].'>');
					if ($sent) { return true; }
					else { $this->errors[] = 'Error Sending Verification Code'; }
				}
			} else {
				$this->errors[] = 'Email not valid';
			}
		} else {
			$this->errors[] = 'User Not Found'; 
		}
		
		return false;
		
	}
	
	function verify2Step($id, $code) {
		
		// get user code
		$sql = "SELECT `code`, `code_date`
			FROM `adminpanel_users`
			WHERE `userid` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($r = mysql_fetch_assoc($res)) { 
			// verification code is only good for CODE_EXPIRE minutes
			if (time() > (strtotime($r['code_date']) + ($this->code_expire))) {
				$this->errors[] = 'Verification Code Expired. Please login again';
				return false;
			}
			
			if ($this->CheckPassword(trim($code), $r['code'])) {
				// reset code/strike variables and store login info
				$update = array(
					'userid' => $id,
					'code' => strtoupper(randomString(5)),
					'strike' => 0,
					'lastlogin' => date('Y-m-d H:i:s'),
					'lastloginip' => get_ip(),
					'reset_key' => '',
				);
				if ($this->update($update)) {
					return true;
				}
			} else {
				$this->passwordStrike($id);
				$this->errors[] = 'Verification Code Failed';
			}
		} else {
			$this->errors[] = 'User Not Found'; 
		}
		
		return false;
		
	}
	
	function updatePassword($password, $id) { 
		
		return false;
	}
		
	function delete($id){
		$success = true;
		
		$sql = "DELETE FROM `adminpanel_users` 
			WHERE `userid` = '" . mysql_real_escape_string($id) . "'
			LIMIT 1
		";
		$success = $success && query($sql, $this->dblink);
		
		return $success;
	}
		
	function isMainAdmin($id=false){
		$id = ( !$id ) ? $_SESSION['adm']['id'] : $id;
		
		if (!$id) { return false; }
		
		if (in_array($id, explode(',', ADMIN_MAIN))) { return true; }
		
		return false;
	}
	
	function reset_password ($login) {
		global $_SITE;
		
		if (!$login) { 
			$this->errors[] = 'Username / E-Mail Missing.';
			return false;
		}
		
		//check username / email
		$sql = "SELECT a.* 
			FROM `adminpanel_users` AS `a`
			LEFT JOIN `adminpanel_users_to_sites` AS `as` ON (`as`.`userid` = a.`userid`) 
			WHERE 1
		";
		$sql .= " AND (`as`.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'";
		if (ADMIN_MAIN) {
			$adminMain = explode(',', trim(ADMIN_MAIN));
			if (!empty($adminMain)) {
				$sql .= " OR a.`userid` IN ('" . implode("', '", array_map('mysql_real_escape_string', $adminMain)) . "')";
			}
		}
		$sql .= ")";
		$sql .=	" AND (a.`username` = '" . mysql_real_escape_string($login) . "' 
				OR a.`email` = '" . mysql_real_escape_string($login) . "') 
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if ($r = mysql_fetch_assoc($res)) {
			
			$this->validateUser($r['userid']);
			
			if ($r['reset_count'] >= 5 && (time() - strtotime($r['reset_date'])) < (60*60*24) ) {
				// reset of 5 reached per 24 hours
				$this->errors[] = 'Max of 5 Password Resets Per 24 Hours Reached';
				return false;
			} elseif ($r['reset_count'] >= 5 && (time() - strtotime($r['reset_date'])) >= (60*60*24) ) {
				// 24 hour reset passed, reset count
				$update = array(
					'userid' => $r['userid'],
					'reset_count' => 0,
				);
				$this->update($update);
			}
			
			// generate reset key
			$reset_code = strtoupper(randomString(rand(15,25)));
			$update = array(
				'userid' => $r['userid'],
				'reset_key' => $reset_code,
				'reset_date' => date('Y-m-d H:i:s'),
				'reset_count' => ++$r['reset_count'],
			);
			if ($this->update($update)) { 
				// send email
				$content = $_SITE['homepage_url'].'adminpanel/login.php?action=resetpw&code='.urlencode($reset_code).' requested by '.get_ip().' at '.date('Y-m-d H:i:s').'.';

				require_once(DIR_CLASSES . 'PHPMailer/class.phpmailer.php');
				$mail = new PHPMailer();
				$mail->AddAddress($r['email']);
				$mail->From = $_SITE['email_support'];
				$mail->FromName = $_SITE['organization_name'];
				$mail->Subject = $_SITE['organization_name'].' Password Reset Link';
				$mail->Body = $content;
				
				if ($mail->Send()) {
					return true;
				} else {
					$this->errors[] = 'Error Sending Reset Password Link'; 
				}
			} else {
				$this->errors[] = 'Database error'; 
			}
		} else {
			// no user found, simulate fake user check to waste time
			$this->CheckPassword(pwqgen(), $this->HashPassword(pwqgen()));
			$this->errors[] = 'Username or Email Invalid';
		}
		
		return false;
	}
	
	function reset_password2($code) {
		global $_SITE;
		if (!trim($code)) { return false; }
		
		$sql = "SELECT a.`userid`, a.`email`, a.`reset_date`
			FROM `adminpanel_users` AS `a`
			LEFT JOIN `adminpanel_users_to_sites` AS `as` ON (`as`.`userid` = a.`userid`) 
			WHERE 1
		";
		$sql .= " AND (`as`.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'";
		if (ADMIN_MAIN) {
			$adminMain = explode(',', trim(ADMIN_MAIN));
			if (!empty($adminMain)) {
				$sql .= " OR a.`userid` IN ('" . implode("', '", array_map('mysql_real_escape_string', $adminMain)) . "')";
			}
		}
		$sql .= ")";
		$sql .=	" AND a.`reset_key` = '" . mysql_real_escape_string($code) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		if ($r = mysql_fetch_assoc($res)) {
			
			// check reset time
			if ((strtotime($r['reset_date']) + ($this->forgot_expire)) < time()) { 
				$this->errors[] = 'Password Reset Expired'; 
				return false;
			}
			
			$this->validateUser($r['userid']);
			// valid so reset password
			$new_password = pwqgen();
			
			$update = array(
				'userid' => $r['userid'],
				'password' => $new_password,
				'pass_date' => date('Y-m-d H:i:s'),
				'reset_key' => '',
				'reset_count' => 0,
			);
			
			if ($this->update($update)) { 
				// send email
				$content = 'New Password: '.$new_password."\n";
				$content .= ' requested by '.get_ip().' at '.date('Y-m-d H:i:s').'.';
				
				require_once(DIR_CLASSES . 'PHPMailer/class.phpmailer.php');
				$mail = new PHPMailer();
				$mail->AddAddress($r['email']);
				$mail->From = $_SITE['email_support'];
				$mail->FromName = $_SITE['organization_name'];
				$mail->Subject = $_SITE['organization_name'].' New Password';
				$mail->Body = $content;
				
				if ($mail->Send()) {
					return true; 
				} else {
					$this->errors[] = 'Error Sending New Password'; 
				}
			} else {
				$this->errors[] = 'Database error'; 
			}
		} else {
			$this->errors[] = 'Invalid Password Reset';
		}
		
		return false;
	}
	
}
