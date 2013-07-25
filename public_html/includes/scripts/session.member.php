<?php

// if assume
if ($_REQUEST['action'] == 'assume_login' && $_REQUEST['members_id']) {
	// fetch member details
	require_once(DIR_CLASSES . 'class.member.php');
	$member = new member();
	$member->init($link, SITES_ID, DB_ENCRYPTION_KEY);
	if ($member->set_members_id($_REQUEST['members_id'])) {
		pre($member);
		$_SESSION['login'] = 1;
		$_SESSION['expires'] = time() + 30*60*60; // 30 minutes
		$_SESSION['members_id'] = $member->get_members_id();
		
		if ($member->member['oauth']['facebook']['oauth_token']) {
			$_SESSION[implode('_', array('fb', FB_APP_ID, 'access_token'))] = $member->member['oauth']['facebook']['oauth_token'];
			$_SESSION[implode('_', array('fb', FB_APP_ID, 'user_id'))] = $member->member['oauth']['facebook']['oauth_uid'];
		}
	}
}

if ($_SESSION['members_id'] && $_SESSION['login'] == 1 && time() <= $_SESSION['expires']) {
	// extend login timeout 
	$_SESSION['expires'] = time() + 30*60*60;
	
	// fetch member details
	require_once(DIR_CLASSES . 'class.member.php');
	$member = new member();
	$member->init($link, SITES_ID, DB_ENCRYPTION_KEY);
	$member->set_members_id($_SESSION['members_id']);
} else { $_SESSION['members_id'] = 0; }
