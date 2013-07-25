<?
include('../application_top.php');

$error = 0;
$msg = array();

if ($_SESSION['new_members_id']) {
	$member = new member();
	$member->init($link, SITES_ID, DB_ENCRYPTION_KEY);
	$member->set_members_id($_SESSION['new_members_id']);
}

if ($member->members_id && !empty($_POST)) {
	
	$values = array();
	
	if (trim($_POST['name'])) {
		if (strrpos(trim($_POST['name']), ' ') !== FALSE) {
			$fullName = trim($_POST['name']);
			$members_firstname = substr($fullName, 0, strrpos($fullName, ' ')+1);
			$members_firstname = trim($members_firstname);
			$members_lastname = substr($fullName, strrpos($fullName, ' '));
			$members_lastname = trim($members_lastname);
		} else {
			$members_firstname = trim($_POST['name']);
			$members_lastname = ''; 
		}
		$values['members_firstname'] = $members_firstname;
		$values['members_lastname'] = $members_lastname; 
	}
	
	if (trim($_POST['cellphone_number'])) {
		$members_cellphone = trim($_POST['cellphone_areacode']).trim($_POST['cellphone_number']);
		$members_cellphone = preg_replace("/[^0-9]/", "", $members_cellphone);
		$values['members_cellphone'] = $members_cellphone;
	}
	
	if ($_POST['city'] || $_POST['city_text']) { 
		if ($_POST['city']) { 
			$sql = "SELECT gc.`city_name`, gr.`region_name` FROM `geo_city_names` AS `gc` 
				LEFT JOIN `geo_region_names` AS `gr` ON (gc.`region_id` = gr.`region_id`)
				WHERE `city_id` = '" . mysql_real_escape_string($_POST['city']) . "' 
				LIMIT 1
			";
			$res = query($sql);
			if ($r = mysql_fetch_assoc($res)) {
				$members_city = $r['city_name'];
				$members_region = $r['region_name'];
			}
		} else {
			
			if (stristr($_POST['city_text'], ',') !== FALSE) {
				$members_city = substr($_POST['city_text'], 0, strrpos($_POST['city_text'], ','));
				$members_region = substr($_POST['city_text'], strrpos($_POST['city_text'], ',') + 1);
			} else {
				$members_city = $_POST['city_text'];
			}
		}
		$values['members_city'] = $members_city;
		$values['members_region'] = $members_region;
	}
	
	if (trim($_POST['gender'])) {
		$members_gender = strtoupper(substr(trim($_POST['gender']), 0, 1));
		$values['members_gender'] = $members_gender;
	}
	
	if (trim($_POST['year'])) {
		$members_dob = trim($_POST['year']).'-00-00';
		$values['members_dob'] = $members_dob;
	}
	
	if (!empty($values)) {
		if ($member->update_members_details($values)) { 
			$msg[] = 'Thank you!';
			if ($values['members_cellphone']) { $msg[] = 'A verification code has been sent to you cellphone'; }
		}
	}
	
	if (trim($_POST['password'])) {
		if (trim($_POST['password']) != trim($_POST['password2'])) {
			$error = 1;
			$msg[] = 'Passwords do not match, please type your password in properly twice';
		} else {
			if ($member->update_members_password('', trim($_POST['password']))) {
				if (empty($values)) { $msg[] = 'Thank you!'; }
			} else {
				$error = 1;
				$msg[] = 'Error saving password. Password may have already been set.';
			}
		}
	}
	
} else { 
	$error = 1; 
	$msg[] = 'Could not find members session. Please refresh the page or log in again.';
}

$return = array(
	'error' => (int)$error,
	'msg' => $msg,
);
$return = json_encode($return);
echo $return;
?>