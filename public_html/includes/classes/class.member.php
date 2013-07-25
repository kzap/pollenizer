<?php
class member {
	var $members_id;
	
	function member() {
		// do nothing
		$this->members_id = false;
	}
	
	function set_dblink($link) {
		$this->dblink = $link;
	}
	
	function set_remote_dblink($link) {
		$this->remote_dblink = $link;
	}
	
	function set_encryption_key($key) {
		$this->encryption_key = $key;
	}
	
	function set_sites_id($sites_id) {
		$this->sites_id = $sites_id;
	}
	
	function init($link, $sites_id, $encryption_key) {
		$this->set_dblink($link);
		$this->set_sites_id($sites_id);
		$this->set_encryption_key($encryption_key);		
	}
	
	function email_exists($email) {
		$email = trim($email);
		$sql = "SELECT COUNT(*) 
			FROM `members` AS `m`
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "') 
			WHERE LOWER(m.`members_email`) = '" . mysql_real_escape_string(strtolower($email)) . "'
		";
		$res = query($sql, $this->dblink);
		
		if (!$res)
			return false;
		
		if ( mysql_result($res,0,0) > 0 )
			return true;
		
		return false;				  
	}
	
	function username_exists($username) {
		$username = trim($username);
		$sql = "SELECT COUNT(*) 
				  FROM `members` AS `m`
				  JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "') 
				  WHERE LOWER(m.`members_username`) = '" . mysql_real_escape_string(strtolower($username)) . "'
		";
		$res = query($sql, $this->dblink);
		
		if (!$res)
			return false;
		
		if ( mysql_result($res,0,0) > 0 )
			return true;
		
		return false;				  
	}
	
	function email_order_exists($email) {
		global $_SITE;
		
		$email = trim($email);
		$sql = "SELECT t.`txn_id`
			FROM `transactions` AS `t`
			JOIN `sites` AS `s` ON (s.`site_name` = '" . mysql_real_escape_string($_SITE['kraken_sites_name']) . "' AND s.`site_id` = t.`site_id`) 
			WHERE t.`payer_email` = '" . mysql_real_escape_string($email) . "'
				AND UPPER(t.`payment_status`) = 'COMPLETED'
				AND UPPER(t.`delivery_status`) = 'COMPLETED'
			LIMIT 1
		";
		
		$res = query($sql, $this->dblink);
		
		if (!$res)
			return false;
		
		if ( mysql_num_rows($res) )
			return true;
		
		return false;				  
	}
	
	function delete_member($id) {
		if (empty($id))
			return false;

		$success = true;
		
		$tables = array('members', 'members_to_sites');
		
		foreach ($tables as $table) {
			$sql = "DELETE FROM `" . $table . "` 
				WHERE `members_id` = '" . mysql_real_escape_string($id) . "'
			";
			$success = ($success && query($sql, $this->dblink));
		}
		
		return $success;
	}
	
	function add_new_member($values) {
		$values = array_map('trim', $values);
		$values = array_map('mysql_real_escape_string', $values);
		
		if ( $values['members_username'] && $this->username_exists($values['members_username']) )
			return false;
		
		if ( $this->email_exists($values['members_email']) )
			return false;
		
		$this->new_activation_key();
					
		$sql = "INSERT INTO `members` 
			SET `members_username` = '" . $values['members_username'] . "',
				`members_email` = '" . $values['members_email'] . "',
				`members_password` = ENCODE('" . $values['members_password'] . "', '" . $this->encryption_key . "'),
				`members_firstname` = '" . $values['members_firstname'] . "',
				`members_lastname` = '" . $values['members_lastname'] . "',
				`members_address01` = '" . $values['members_address01'] . "',
				`members_address02` = '" . $values['members_address02'] . "',
				`members_city` = '" . $values['members_city'] . "',
				`members_region` = '" . $values['members_region'] . "',
				`members_zip_code` = '" . $values['members_zip_code'] . "',
				`members_country_code` = '" . $values['members_country_code'] . "',
				`members_phone` = '" . $values['members_phone'] . "',
				`members_cellphone` = '" . $values['members_cellphone'] . "',
				`members_gender` = '" . $values['members_gender'] . "',
				`members_dob` = '" . $values['members_dob'] . "',
				`newsletter` = '" . ($values['newsletter'] == 'on') . "',
				`account_created` = NOW(),
				`account_activated` = '0',
				`activation_key` = '" . $this->member['activation_key'] . "',
				`members_ip_address` = '" . ($values['members_ip_address'] ? $values['members_ip_address'] : mysql_real_escape_string(get_ip())) . "'
		";
		$res = query($sql, $this->dblink);
	//	pre($sql);
		if (!$res)
			return false;
		
		$this->set_members_id(mysql_insert_id($this->dblink));
		
		$sql = "INSERT INTO `members_to_sites` 
			SET `members_id` = '" . mysql_real_escape_string($this->members_id) . "', 
				`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "'
		";
		$res = query($sql, $this->dblink);
		return $res;
	}

	function new_activation_key() {
		$key1 = crypt(microtime(),substr(microtime(),0,2));
		$key1 = substr($key1,2,strlen($key1));
		$key1 = strtoupper($key1);
		$key1 = preg_replace("/[^\w]*/","",$key1);
		
		$key2 = crypt($key1,substr(microtime(),0,2));
		$key2 = strtoupper($key2);
		$key2 = preg_replace("/[^\w]*/","",$key2);
		
		$key = $key1.$key2;		
		
		// check for duplicates
		$sql = "SELECT COUNT(*) 
			FROM `members` 
			WHERE `activation_key` = '" . mysql_real_escape_string($key) . "'
		";
		$res = query($sql, $this->dblink);
		if (mysql_result($res,0,0) > 0)
			$this->new_activation_key();
		else {
			$this->member['activation_key'] = $key;
		}
	}

	function get_password() {
		if ( !$this->members_id ) 
			return false;
		$sql = "SELECT DECODE(`members_password`, '" . mysql_real_escape_string($this->encryption_key) . "') 
			FROM `members` 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if (!$res)
			return false;
		
		return mysql_result($res,0,0);
	}
	
	function password_retrieval_email() {
		if ( !$this->members_id)
			return false;
		
		// get password
		$msg = 'Your current password is : ' . $this->get_password();
		return $msg;
	}
	
	function password_reset_email() {
		global $_SITE;
		if ( !$this->members_id)
			return false;
			
		$this->new_reset_key();
		$sql = "UPDATE `members` 
			SET `reset_key` = '" . mysql_real_escape_string($this->member['reset_key']) . "', 
				`reset_generated` = NOW() 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "'
		";
		$res = query($sql, $this->dblink);
		if (!$res) { return false; }
		
		// get password
		$msg = 'Hello,

To reset your password, please click on this link:

'.$_SITE['homepage_url'].'members/password_retrieval.php?action=reset&k='.$this->member['reset_key'].'

If you did not ask for a password reset, please ignore this email.';
		return $msg;
	}
	
	function password_reset_complete_email() {
		if ( !$this->members_id)
			return false;
			
		$sql = "UPDATE `members` 
			SET `reset_key` = '' 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "'
		";
		$res = query($sql, $this->dblink);
		if (!$res) { return false; }
		
		$this->new_reset_key();
		
		$sql = "UPDATE `members` 
			SET `members_password` = ENCODE('" . mysql_real_escape_string($this->member['reset_key']) . "', '" . mysql_real_escape_string($this->encryption_key) . "') 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		if (!$res) { return false; }
		
		// get password
		$msg = 'Your new password is: ' . $this->member['reset_key'];
		return $msg;
	}
	
	function new_reset_key() {
		$key1 = crypt(microtime(),substr(microtime(),0,2));
		$key1 = substr($key1,2,strlen($key1));
		$key1 = strtoupper($key1);
		$key1 = preg_replace("/[^\w]*/","",$key1);
		
		$key2 = crypt($key1,substr(microtime(),0,2));
		$key2 = strtoupper($key2);
		$key2 = preg_replace("/[^\w]*/","",$key2);
		
		$key = $key1.$key2;		
		
		// check for duplicates
		$sql = "SELECT COUNT(*) 
			FROM `members` 
			WHERE `reset_key` = '" . mysql_real_escape_string($key) . "'
		";
		$res = query($sql, $this->dblink);
		if (mysql_result($res,0,0) > 0)
			$this->new_reset_key();
		else {
			$this->member['reset_key'] = $key;
		}
	}
	
	function claim_email($claim_email) {
		global $_SITE;
		if (!trim($claim_email) || stristr($claim_email, '@') === FALSE) { return false; }
			
		$key = $this->new_claim_key();
		if (!$key) { return false; }
		
		$sql = "SELECT m.`members_id` 
			FROM `members` AS `m`
			LEFT JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE m.`members_email` = '" . mysql_real_escape_string($claim_email) . "'
		";
		$res = query($sql, $this->dblink);
		if ($row = mysql_fetch_assoc($res)) {
			$sql = "UPDATE `members` AS `m`
				SET m.`claim_key` = '" . mysql_real_escape_string($key) . "', 
					m.`claim_generated` = NOW(), 
					m.`claiming_id` = '" . mysql_real_escape_string($this->members_id) . "' 
				WHERE m.`members_id` = '" . mysql_real_escape_string($row['members_id']) . "'
			";
			$res = query($sql, $this->dblink);
			if (!$res) { return false; }
		} else { return false; }
		
		// get password
		$msg = 'Hello,

'.$this->member['members_firstname'].' '.$this->member['members_lastname'].' with IP of '.get_ip().' is attemping to claim the email address ('.$claim_email.') to the account of ('.$this->member['members_email'].')
		
To confirm and verify this email account, please click on this link:

'.$_SITE['homepage_url'].'members/emails.php?action=claim&k='.$key.'

If you did not ask for a confirmation e-mail, please contact our support staff immediately here: 

'.$_SITE['homepage_url'].'support/';
		return $msg;
	}
	
	function new_claim_key() {
		$key1 = crypt(microtime(),substr(microtime(),0,2));
		$key1 = substr($key1,2,strlen($key1));
		$key1 = strtoupper($key1);
		$key1 = preg_replace("/[^\w]*/","",$key1);
		
		$key2 = crypt($key1,substr(microtime(),0,2));
		$key2 = strtoupper($key2);
		$key2 = preg_replace("/[^\w]*/","",$key2);
		
		$key = $key1.$key2;		
		
		// check for duplicates
		$sql = "SELECT COUNT(*) 
			FROM `members` 
			WHERE `claim_key` = '" . mysql_real_escape_string($key) . "'
		";
		$res = query($sql, $this->dblink);
		if (mysql_result($res,0,0) > 0) {
			$key = $this->new_claim_key();
		}
		
		return $key;
	}
	
	function claim($claim_key) {
		global $_SITE;
		
		$sql = "SELECT * 
			FROM `members` 
			WHERE `claim_key` = '" . mysql_real_escape_string($claim_key) . "' 
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		if ($row = mysql_fetch_assoc($res)) {
			$old_parent_id = $row['members_parent_id'] ? $row['members_parent_id'] : $row['members_id'];
			$claiming_id = $row['claiming_id'];
			$claim_email = $row['members_email'];
			$members_id = $row['members_id'];
			
			$this->set_members_id($old_parent_id);
			$old_emails = $this->member['emails'];
			
			$sql = "UPDATE `members` 
				SET `account_activated` = '1', 
					`members_parent_id` = `claiming_id`, 
					`claim_key` = '', 
					`claiming_id` = 0 
				WHERE `members_id` = '" . mysql_real_escape_string($members_id) . "' 
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			
			if (!$res) {
				return false;
			} elseif ( mysql_affected_rows($this->dblink) == 1 ) {
				$sql = "UPDATE `members` 
					SET `members_parent_id` = 0 
					WHERE `members_parent_id` = '" . mysql_real_escape_string($members_id) . "'
				";
				query($sql, $this->dblink);
			
				require_once(DIR_CLASSES . 'PHPMailer/class.phpmailer.php');
				
				$this->set_members_id($claiming_id);
				
				$mail = new PHPMailer();
				foreach ($this->member['emails'] as $email) { $mail->AddAddress($email); }
				$mail->From = $_SITE['email_support'];
				$mail->FromName = $_SITE['organization_name'];
				$mail->Subject = $_SITE['organization_name'].': Account Confirmed';
				$mail->Body = 'Hello,

Congratulations you have successfully claimed a new email address: ('.$claim_email.').

If you did not attempt to confirm any new email address, please contact our support staff immediately here: 

'.$_SITE['homepage_url'].'support/';
				$mail->Send();
				
				if (!empty($old_emails) && count($old_emails) >= 2) { // if claimed from another account owner
					$this->set_members_id($claiming_id);
					$mail = new PHPMailer();
					foreach ($old_emails as $email) { 
						if ($email != $claim_email) { $mail->AddAddress($email); }
					}
					$mail->From = $_SITE['email_support'];
					$mail->FromName = $_SITE['organization_name'];
					$mail->Subject = $_SITE['organization_name'].': Account Confirmed';
					$mail->Body = 'Hello,

'.$this->member['members_firstname'].' '.$this->member['members_lastname'].' with IP of '.get_ip().' has successfully claimed the email address ('.$claim_email.') to the account of ('.$this->member['members_email'].') and it is no longer under your account.

If you do not have any knowledge of such email claim, please contact our support staff immediately here: 

'.$_SITE['homepage_url'].'support/';
					$mail->Send();
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	function account_activation_email($sites_name, $activation_page) {
		if ($this->member['activation_key'] == '')
			$this->new_activation_key(); // ie, create new activation key
			
		$activation_link = $activation_page . "?action=confirm&activation_key=" . urlencode($this->member['activation_key']);
		
		$msg = "Thank you for registering".($this->member['members_email'] ? ' '.$this->member['members_email'] : '')." with " . $sites_name . ".\n\n"
					. "To activate your account click the link below."
					. "\n\n"
					. "\t" . $activation_page . "?mk=" . $this->member['activation_key'] . "&a=1&rf=" . time()
					. "\n\n\n"
					. "This e-mail was requested by " . get_ip() . " on " . date('F j, Y, g:i a') . " by signing up at " . DIR_WS_ROOT
					. "\n\n"
					. "\t" . $activation_page . "?mk=" . $this->member['activation_key'] . "&a=0&rf=" . time()
					. "\n\n"
					;
		return $msg;		
	}
	
	function account_activation_email_html($sites_name, $activation_page) {
		if ($this->member['activation_key'] == '')
			$this->new_activation_key(); // ie, create new activation key
		
		$activation_link = $activation_page . "?action=confirm&activation_key=" . urlencode($this->member['activation_key']);
		
		$msg = 'Thank you for registering'.($this->member['members_email'] ? ' <strong>'.$this->member['members_email'].'</strong>' : '').' with ' . $sites_name . '<br /><br />
			To activate your account, <a href="'.$activation_link.'">click here</a> or copy and paste the link below into your browser:<br /><br />
			'.$activation_link.'<br />
			<br />
			<br />
			This e-mail was requested by <strong>' . get_ip() . '</strong> on <strong>' . date('F j, Y, g:i a') . '</strong> by signing up at <strong>' . DIR_WS_ROOT . '</strong>.<br />
			<br />
			If you did not request this e-mail, please ignore it and we will not send any further e-mails.<br />
			<br />
			Thank you!
		';
		return $msg;		
	}
	
	function activate($activation_key) {
		$sql = "UPDATE `members` 
			SET `account_activated` = '1', 
				`activation_key` = '' 
			WHERE `activation_key` = '" . mysql_real_escape_string($activation_key) . "' 
			LIMIT 1
		";
		
		$res = query($sql, $this->dblink);
		
		if (!$res) {
			return false;
		} elseif ( mysql_affected_rows($this->dblink) == 1 ) {
			return true;
		}
		
		return false;
	}
	
	function deactivate($activation_key) {
		$sql = "UPDATE `members` 
			SET `account_activated` = '0', 
				`activation_key` = '' 
			WHERE `activation_key` = '" . mysql_real_escape_string($activation_key) . "' 
			LIMIT 1
		";
		$res = query($sql, $this->dblink);

		if (!$res) {
			return false;
		} elseif ( mysql_affected_rows($this->dblink) == 1 ) {
			return true;
		}

		return false;
	}
	
	function cellphone_activation_message() {
		if ( !$this->members_id)
			return false;
			
		if ($this->member['cellphone_key'] == '')
			$this->new_cellphone_key(); // ie, create new cellphone key
		
		$sql = "UPDATE `members` SET `cellphone_key` = '" . mysql_real_escape_string($this->member['cellphone_key']) . "' WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "'";
		$res = query($sql, $this->dblink);
		if (!$res) { return false; }
		
		$msg = 'Thank you for registering with Awesome.ph! To validate your cellphone, 
			please go to HTTP://V.AWESOME.PH/ in your web browser and enter the code  '.$this->member['cellphone_key'].'  in the box and click SUBMIT. 
			If you did not request this message, please ignore. Thank you';
		return $msg;		
	}
	
	function activate_cellphone($cellphone_key) {
		$sql = "UPDATE `members` SET `cellphone_activated` = '1', `cellphone_key` = '' WHERE `cellphone_key` = '" . mysql_real_escape_string($cellphone_key) . "' LIMIT 1";
		
		$res = query($sql, $this->dblink);
		
		if (!$res) {
			return false;
		} elseif ( mysql_affected_rows($this->dblink) == 1 ) {
			return true;
		}
		
		return false;
	}
	
	function new_cellphone_key() {
		$key1 = crypt(microtime(),substr(microtime(),0,2));
		$key1 = substr($key1,2,strlen($key1));
		$key1 = strtoupper($key1);
		$key1 = preg_replace("/[^\w]*/","",$key1);
		
		$key2 = crypt($key1,substr(microtime(),0,2));
		$key2 = strtoupper($key2);
		$key2 = preg_replace("/[^\w]*/","",$key2);
		
		$key = $key1.$key2;
		$key = strtoupper(substr($key, 0, 7));
		
		// check for duplicates
		$sql = "SELECT COUNT(*) FROM `members` WHERE `cellphone_key` = '" . mysql_real_escape_string($key) . "'";
		$res = query($sql, $this->dblink);
		if (mysql_result($res,0,0) > 0)
			$this->new_cellphone_key();
		else {
			$this->member['cellphone_key'] = $key;
		}
	}
	
	function set_members_details() {
		if (!$this->members_id)
			return false;
		
		$sql = "SELECT m.*
			FROM `members` AS `m` 
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE m.`members_id` = '" . mysql_real_escape_string($this->get_members_id()) . "'
		";
		$res = query($sql, $this->dblink);

		if ($row = mysql_fetch_assoc($res)) {
			$this->member = $row;
			if ($row['members_username']) { $this->member['usernames'][] = $row['members_username']; }
			$this->member['emails'][] = $row['members_email'];
			$this->member['ids'][] = $row['members_id'];
			
			$sql = "SELECT m.`members_username`, m.`members_email` 
				FROM `members` AS `m`
				JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE m.`members_parent_id` = '" . mysql_real_escape_string($this->get_members_id()) . "'
			";
			$res = query($sql, $this->dblink);
			while ($row = mysql_fetch_assoc($res)) { 
				if ($row['members_username']) { $this->member['usernames'][] = $row['members_username']; }
				$this->member['emails'][] = $row['members_email'];
				$this->member['ids'][] = $row['members_id']; 
			}
			
			$sql = "SELECT mo.* 
				FROM `members_oauth` AS `mo`
				JOIN `members_to_sites` AS `ms` ON (mo.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "') 
				WHERE mo.`members_id` = '" . mysql_real_escape_string($this->get_members_id()) . "'
			";
			$res = query($sql, $this->dblink);
			while ($row = mysql_fetch_assoc($res)) { 
				$this->member['oauth'][$row['oauth_provider']] = $row;
			}
			
			return true;
		}

		return false;		
	}

	function flip_bit_field($fieldname) {
		$sql = "SELECT `" . $fieldname . "` 
			FROM `members` 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "'
		";
		$res = query($sql, $this->dblink);
		$old_value = mysql_result($res,0,0);
		
		$new_value = (int)!$old_value;
		$sql = "UPDATE `members` 
			SET `" . $fieldname . "` = '" . mysql_real_escape_string($new_value) . "' 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
			LIMIT 1
		";
		return query($sql, $this->dblink);		
	}
	
	function update_members_details($values) {
		if (!$this->members_id && !empty($values)) 
			return false;
			
		$sql = "UPDATE `members` SET ";
		if (isset($values['members_firstname'])) { $sql .= "`members_firstname` = '" . mysql_real_escape_string($values['members_firstname']) . "', "; }
		if (isset($values['members_lastname'])) { $sql .= "`members_lastname` = '" . mysql_real_escape_string($values['members_lastname']) . "', "; }
		if (isset($values['members_address01'])) { $sql .= "`members_address01` = '" . mysql_real_escape_string($values['members_address01']) . "', "; }
		if (isset($values['members_address02'])) { $sql .= "`members_address02` = '" . mysql_real_escape_string($values['members_address02']) . "', "; }
		if (isset($values['members_city'])) { $sql .= "`members_city` = '" . mysql_real_escape_string($values['members_city']) . "', "; }
		if (isset($values['members_region'])) { $sql .= "`members_region` = '" . mysql_real_escape_string($values['members_region']) . "', "; }
		if (isset($values['members_zip_code'])) { $sql .= "`members_zip_code` = '" . mysql_real_escape_string($values['members_zip_code']) . "', "; }
		if (isset($values['members_country_code'])) { $sql .= "`members_country_code` = '" . mysql_real_escape_string($values['members_country_code']) . "', "; }
		if (isset($values['members_phone'])) { $sql .= "`members_phone` = '" . mysql_real_escape_string($values['members_phone']) . "', "; }
		if (isset($values['members_cellphone'])) { $sql .= "`members_cellphone` = '" . mysql_real_escape_string($values['members_cellphone']) . "', "; }
		if (isset($values['members_gender'])) { $sql .= "`members_gender` = '" . mysql_real_escape_string($values['members_gender']) . "', "; }
		if (isset($values['members_dob'])) { $sql .= "`members_dob` = '" . mysql_real_escape_string($values['members_dob']) . "', "; }
		if (isset($values['newsletter'])) { $sql .= "`newsletter` = '" . ($values['newsletter'] == 'on' ? 1 : 0) . "', "; }
		$sql = substr($sql, 0, -2);
		$sql .= "WHERE `members_id` = '" . $this->members_id . "'
			LIMIT 1
		";
		return query($sql, $this->dblink);		
	}
	
	function admin_update_members_details($values) {
		if (!$values['members_id']) 
			return false;
			
		$sql = "UPDATE `members` SET ";
		if (isset($values['members_username'])) { $sql .= "`members_username` = '" . mysql_real_escape_string($values['members_username']) . "', "; }
		if (isset($values['members_email'])) { $sql .= "`members_email` = '" . mysql_real_escape_string($values['members_email']) . "', "; }
		if (isset($values['members_firstname'])) { $sql .= "`members_firstname` = '" . mysql_real_escape_string($values['members_firstname']) . "', "; }
		if (isset($values['members_lastname'])) { $sql .= "`members_lastname` = '" . mysql_real_escape_string($values['members_lastname']) . "', "; }
		if (isset($values['members_address01'])) { $sql .= "`members_address01` = '" . mysql_real_escape_string($values['members_address01']) . "', "; }
		if (isset($values['members_address02'])) { $sql .= "`members_address02` = '" . mysql_real_escape_string($values['members_address02']) . "', "; }
		if (isset($values['members_city'])) { $sql .= "`members_city` = '" . mysql_real_escape_string($values['members_city']) . "', "; }
		if (isset($values['members_region'])) { $sql .= "`members_region` = '" . mysql_real_escape_string($values['members_region']) . "', "; }
		if (isset($values['members_zip_code'])) { $sql .= "`members_zip_code` = '" . mysql_real_escape_string($values['members_zip_code']) . "', "; }
		if (isset($values['members_country_code'])) { $sql .= "`members_country_code` = '" . mysql_real_escape_string($values['members_country_code']) . "', "; }
		if (isset($values['members_phone'])) { $sql .= "`members_phone` = '" . mysql_real_escape_string($values['members_phone']) . "', "; }
		if (isset($values['members_cellphone'])) { $sql .= "`members_cellphone` = '" . mysql_real_escape_string($values['members_cellphone']) . "', "; }
		if (isset($values['newsletter'])) { $sql .= "`newsletter` = '" . ($values['newsletter'] == 'on' ? 1 : 0) . "', "; }
		if (isset($values['user_code'])) { $sql .= "`user_code` = '" . mysql_real_escape_string($values['user_code']) . "', "; }
		$sql = substr($sql, 0, -2);
		$sql .= " WHERE `members_id` = '" . $values['clientusers_id'] . "'
			LIMIT 1
		";
		
		return query($sql, $this->dblink);
	}	
	
	function update_members_password($old_password, $new_password) {
		$new_password = trim($new_password);
		if ($new_password == '')
			return false;
		
		$sql = "SELECT COUNT(*) 
			FROM `members` AS `m`
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE m.`members_id` = '" . mysql_real_escape_string($this->members_id) . "'
				AND DECODE(m.`members_password`, '" . mysql_real_escape_string($this->encryption_key) . "') = '" . mysql_real_escape_string($old_password) . "' 
		";
		
		$res = query($sql, $this->dblink);
		$success = ((int)mysql_result($res,0,0) == 1);
		if (!$success) { return false; }
		
		$sql = "UPDATE `members` 
			SET `members_password` = ENCODE('" . mysql_real_escape_string($new_password) . "', '" . mysql_real_escape_string($this->encryption_key) . "') 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
			LIMIT 1
		";
		return query($sql, $this->dblink);
	}
	
	function set_members_password($new_password) {
		$new_password = trim($new_password);
		if ($new_password == '')
			return false;
/*		
		$sql = "SELECT COUNT(*) FROM `members` AS `m`
			WHERE m.`members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
		";
		
		$res = query($sql, $this->dblink);
		$success = ((int)mysql_result($res,0,0) == 1);
		if (!$success) { return false; }
*/
		$sql = "UPDATE `members` SET `members_password` = ENCODE('" . mysql_real_escape_string($new_password) . "', '" . $this->encryption_key . "') WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "' LIMIT 1";
		return query($sql, $this->dblink);
	}
	
	function set_members_username($username) {
		$username = trim($username);
		if ($username == '')
			return false;
		
		$sql = "SELECT COUNT(*) 
			FROM `members` AS `m`
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE m.`members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
			  	AND m.`members_username` = ''
			  	AND m.`account_activated` = '1'
		";
		$res = query($sql, $this->dblink);
		$success = ((int)mysql_result($res,0,0) == 1);
		if (!$success) { return false; }
		$sql = "UPDATE `members` 
			SET `members_username` = '" . mysql_real_escape_string($username) . "' 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
			LIMIT 1
		";
		return query($sql, $this->dblink);
	}
	
	function set_members_id_by_email($email) {
		$email = trim($email);
		
		$sql = "SELECT m.`members_id`, m.`members_parent_id` 
			FROM `members` AS `m`
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE m.`members_email` = '" . mysql_real_escape_string($email) . "'
		";
		$res = query($sql, $this->dblink);
		
		if ($r = mysql_fetch_assoc($res)) {
			$members_id = $r['members_parent_id'] ? $r['members_parent_id'] : $r['members_id'];
			$this->set_members_id($members_id);
			return true;
		} 
		
		return false;			
	}
	
	function set_members_id_by_login($login) {
		$login = trim($login);
		
		$sql = "SELECT m.`members_id`, m.`members_parent_id` 
			FROM `members` AS `m`
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			WHERE m.`members_email` = '" . mysql_real_escape_string($login) . "'
			LIMIT 1
		";
		$res = query($sql, $this->dblink);
		
		if (!mysql_num_rows($res)) {
			$sql = "SELECT m.`members_id`, m.`members_parent_id` 
				FROM `members` AS `m`
				JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE m.`members_username` = '" . mysql_real_escape_string($login) . "'
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
		}
		
		if ($r = mysql_fetch_assoc($res)) {
			$members_id = $r['members_parent_id'] ? $r['members_parent_id'] : $r['members_id'];
			$this->set_members_id($members_id);
			return true;
		} 
		
		return false;			
	}

	function set_members_id($id) {
		if ($id > 0)
			$this->members_id = $id;

		return $this->set_members_details();		
	}
		
	function get_members_id()	{
		if ($this->members_id > 0)
			return $this->members_id;
		return false;
	}
	
	function get_members_details() {
		return $this->member;
	}
	
	function authenticate($login, $password) {
		if (trim($login) && trim($password)) {
			
			$sql = "SELECT m.*
				FROM `members` AS `m`
				JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
				WHERE (LOWER(m.`members_email`) = '" . mysql_real_escape_string(strtolower($login)) . "' 
					OR LOWER(m.`members_username`) = '" . mysql_real_escape_string(strtolower($login)) . "'
					)
					AND m.`account_activated` = '1'
				LIMIT 1
			";
			$res = query($sql, $this->dblink);
			
			if ($row = mysql_fetch_assoc($res)) {
				
				$members_id = $row['members_parent_id'] ? $row['members_parent_id'] : $row['members_id'];
				$sql = "SELECT * 
					FROM `members` AS `m`
					JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
					WHERE m.`members_id` = '" . mysql_real_escape_string($members_id) . "'
						AND DECODE(m.`members_password`, '" . mysql_real_escape_string($this->encryption_key) ."') = '" . mysql_real_escape_string($password) . "' 
						AND m.`account_activated` = '1' 
					LIMIT 1
				"; 
				$res = query($sql, $this->dblink);
				
				$success = (mysql_num_rows($res) == 1);
				
				if ($success && $row = mysql_fetch_assoc($res)) { 
					$this->set_members_id($row['members_id']); 
					$sql = "UPDATE `members` 
						SET `members_ip_address` = '" . mysql_real_escape_string(get_ip()) . "' 
						WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "' 
						LIMIT 1
					";
					query($sql, $this->dblink);
				}
			
			}
			
		}
		
		return $success;			
	}

	function is_activated() {
		$sql = "SELECT `account_activated` 
			FROM `members` 
			WHERE `members_id` = '" . mysql_real_escape_string($this->members_id) . "'
		";
		$res = query($sql, $this->dblink);
		
		return (mysql_result($res,0,0) == 1);		
	}
	
	function exists() {
		return ($this->members_id > 0);
	}
	
	function quick_stats() {
		// total count
		$sql = "SELECT COUNT(*) AS `count` 
			FROM `members` AS `m`
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
		";

		$res = query($sql, $this->dblink);
		$m['count'] = mysql_result($res,0,0);
		
		// activated
		$sql = "SELECT `account_activated`, COUNT(*) AS `count` 
			FROM `members` AS `m`
			JOIN `members_to_sites` AS `ms` ON (m.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . mysql_real_escape_string($this->sites_id) . "')
			GROUP BY `account_activated`
		";
		$res = query($sql, $this->dblink);
		
		while( $r = mysql_fetch_assoc($res) )
			if( $r['account_activated'] )
				$m['activated'] = $r['count'];
			else
				$m['inactive'] = $r['count'];
	
		return $m;	
	}
}
