<?php

function fb_redirect($REDIRECT, $jsRedirect = false) {
	if ($jsRedirect) {
		// do not use for user client sourced $REDIRECT as its unsafe
		echo 'Redirecting...';
		echo '<br />If your browser does not redirect you to the website within 5 seconds, please <a href="' . $REDIRECT . '">click here to continue</a>.';
		echo '<script type="text/javascript">window.top.location = "' . $REDIRECT . '";</script>';
		exit;
	} else {
		session_write_close();
		header('Location: '.$REDIRECT);
		exit;
	}
}

function fb_sign_request($data, $secret) {
		
	$data['algorithm'] = 'HMAC-SHA256';
	$payload = base64_url_encode(json_encode($data));
	$sig = hash_hmac('sha256', $payload, $secret, $raw = true);
	$encoded_sig = base64_url_encode($sig);
	$signed_request = $encoded_sig . '.' . $payload;
	
	return $signed_request;
}

function fb_parse_signed_request($signed_request, $secret) {
	list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
	
	// decode the data
	$sig = base64_url_decode($encoded_sig);
	$data = json_decode(base64_url_decode($payload), true);
	
	if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
		error_log('Unknown algorithm. Expected HMAC-SHA256');
		return null;
	}

	// check sig
	$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);

	if ($sig !== $expected_sig) {
		error_log('Bad Signed JSON signature!');
		return null;
	}

	return $data;
}

if (!function_exists('base64_url_encode')) {
	function base64_url_encode($input) {
		return strtr(base64_encode($input), '+/', '-_');
	}
}

if (!function_exists('base64_url_decode')) {
	function base64_url_decode($input) {
	  return base64_decode(strtr($input, '-_', '+/'));
	}
}

function fbBatchQuery($facebook, $batchQuery) {
	$results = array();
	
	if (!empty($batchQuery)) {
		
		end($batchQuery); $lastKey = key($batchQuery);
		$batchCount = 0; $batchQuerySet = array();
		
		foreach ($batchQuery as $key => $query) {
			$batchQuerySet[] = $query;
			$batchCount++;
			
			if ($batchCount == 50 || $key == $lastKey) {
				try {
					$fbResult = $facebook->api('?batch=' . urlencode(json_encode($batchQuerySet)), 'POST');
				} catch (FacebookApiException $e) {
					error_log($e);
					//pre($e, 1);
					//if (empty($result)) { return false; }
				}
				
				if (!empty($fbResult)) {
					$results = array_merge((array) $results, (array) objectToArray($fbResult));
				}
				$batchCount = 0; $batchQuerySet = array();
			}
		}
		
		if (!empty($results)) {
			return $results;
		}	
	}
	
	return false;
}

// if facebook canvas, set it for this session
if ($_GET['fb']) { $_SESSION['in_facebook'] = true; }
elseif (isset($_GET['fb']) && $_GET['fb'] == 0) { unset($_SESSION['in_facebook']); }

require_once(DIR_CLASSES.'facebook/facebook.php');
$_FACEBOOK = array();
// facebook check session
//pre($_SESSION);
$facebook = new Facebook(array(
	'appId'  => FB_APP_ID,
	'secret' => FB_APP_SECRET,
));
$_FACEBOOK['uid'] = $facebook->getUser();
$_FACEBOOK['access_token'] = $facebook->getAccessToken();
if (!$_FACEBOOK['uid'] && $_COOKIE[implode('_', array('fb', $facebook->getAppId(), 'access_token'))]) {
	// if facebook session not found but in cookie, try cookie access_token
	$facebook = new Facebook(array(
		'appId'  => FB_APP_ID,
		'secret' => FB_APP_SECRET,
	));
	
	$data = fb_parse_signed_request($_COOKIE[implode('_', array('fb', $facebook->getAppId(), 'access_token'))], FB_APP_SECRET);
	if ($data['access_token']) {
		$facebook->setAccessToken($data['access_token']);
		$_SESSION[implode('_', array('fb', $facebook->getAppId(), 'access_token'))] = $facebook->getAccessToken(); 
		$_FACEBOOK['uid'] = $facebook->getUser();
		$_FACEBOOK['access_token'] = $facebook->getAccessToken();
	}
}
//pre($_FACEBOOK);

if ($_FACEBOOK['uid']) {
	try {
		$_FACEBOOK['me'] = $facebook->api('/me');
	} catch (FacebookApiException $e) {
		error_log($e);
	}
}

if (empty($_FACEBOOK['me'])) {
	// if access_token not working any more, remove from cookie
	setcookie(implode('_', array('fb', $facebook->getAppId(), 'access_token')), false, time()-FB_SESSION_LIFETIME, '/', COOKIE_DOMAIN);
}

if (!$_FACEBOOK['uid']) { unset($_FACEBOOK); }

if ((isset($_SESSION['fb-logout']) && $_SESSION['fb-logout'])) {
	unset($_SESSION[implode('_', array('fb', $facebook->getAppId(), 'access_token'))]);
	unset($_SESSION[implode('_', array('fb', $facebook->getAppId(), 'user_id'))]);
	unset($_SESSION['fb-logout']);
	unset($_FACEBOOK);
}

if ((isset($_SESSION['fb-login']) && $_SESSION['fb-login']) && (isset($_FACEBOOK['me']['id']) && $_FACEBOOK['me']['id'])) {
	unset($_SESSION['fb-login']);
	
	// store access token for forever in a signed_request
	$data = array(
		'access_token' => $facebook->getAccessToken(),
	);
	$signed_request = fb_sign_request($data, FB_APP_SECRET);
	setcookie(implode('_', array('fb', $facebook->getAppId(), 'access_token')), $signed_request, time()+FB_SESSION_LIFETIME, '/', COOKIE_DOMAIN);
	
	$FB_SIGNUP = false;

	require_once(DIR_CLASSES . 'class.member.php');
	$member = new member();
	$member->init($link, SITES_ID, DB_ENCRYPTION_KEY);

	// look for connected id
	$sql = "SELECT mo.`members_id` FROM `members_oauth` AS `mo`
		JOIN `members_to_sites` AS `ms` ON (mo.`members_id` = ms.`members_id` AND ms.`sites_id` = '" . SITES_ID . "')
		WHERE mo.`oauth_provider` = 'facebook' 
			AND mo.`oauth_uid` = '" . mysql_real_escape_string($_FACEBOOK['me']['id']) . "'
		LIMIT 1
	";
	$res = query($sql, $link);
	if ($r = mysql_fetch_assoc($res)) {
		// login member
		$member->set_members_id($r['members_id']);
		$_SESSION['login'] = 1;
		$_SESSION['expires'] = time() + 30*60*60; // 30 minutes
		$_SESSION['members_id'] = $member->get_members_id();
		
		// update oauth key
		$sql = "UPDATE `members_oauth` 
			SET `oauth_username` = '" . mysql_real_escape_string($_FACEBOOK['me']['name']) . "',
				`oauth_token` = '" . mysql_real_escape_string($_FACEBOOK['access_token']) . "'
			WHERE `oauth_provider` = 'facebook' 
				AND `members_id` = '" . mysql_real_escape_string($member->get_members_id()) . "'
			LIMIT 1
		";
		$res = query($sql, $link);
		
	} else {
		// no connected member
		if ($_FACEBOOK['me']['email']) {
			
			// check if email exists
			if (!$member->set_members_id_by_email($_FACEBOOK['me']['email'])) {
				// register user
				$member = new member();
				$member->init($link, SITES_ID, DB_ENCRYPTION_KEY);
				
				$values = array(
					'members_email' => strtolower(trim(filter_var($_FACEBOOK['me']['email'], FILTER_SANITIZE_EMAIL))),
					'members_username' => $_FACEBOOK['me']['username'],
					'members_firstname' => $_FACEBOOK['me']['first_name'],
					'members_lastname' => $_FACEBOOK['me']['last_name'],
					'members_gender' => strtoupper(substr($_FACEBOOK['me']['gender'], 0, 1)),
					'members_dob' => date('Y-m-d', strtotime($_FACEBOOK['me']['birthday'])),
					'account_created' => date('Y-m-d H:i:s'),
					'members_ip_address' => get_ip(),
					'newsletter' => 'on',
				);
				$member->add_new_member($values);
				$member->activate($member->member['activation_key']);
			}
			
			if ($member->get_members_id()) {
				// insert new entry
				$sql = "INSERT INTO `members_oauth` 
					SET `members_id` = '" . mysql_real_escape_string($member->get_members_id()) . "',
						`oauth_provider` = 'facebook',
						`oauth_uid` = '" . mysql_real_escape_string($_FACEBOOK['me']['id']) . "',
						`oauth_username` = '" . mysql_real_escape_string($_FACEBOOK['me']['name']) . "',
						`oauth_token` = '" . mysql_real_escape_string($_FACEBOOK['access_token']) . "'
				";
				$res = query($sql, $link);
			}
			
			// reset members_id
			$member->set_members_id($member->get_members_id());
			
			// do signup fb wall post
			if (!$member->member['oauth']['facebook']['signup_post']) {
				try {
					$permissions = $facebook->api('/' . $_FACEBOOK['me']['id'] . '/permissions');
				} catch (FacebookApiException $e) {
					error_log($e);
				}
				if ($permissions['data'][0]['publish_actions']) {
			
					$signup_messages = array(
						'check it out',
						'just signed up',
						'registered',
						'I registered here',
						'subscribed',
						'I subscribed here',
					);
					$signup_message = $signup_messages[rand(0, (count($signup_messages)-1))];
					$attachment = array(
						'access_token' => $_FACEBOOK['access_token'],
						'message' => $signup_message,
						'name' => $_SITE['site_name'],
						'link' => str_replace('https://', 'http://', DIR_WS_ROOT) . '?utm_source=facebook.com&utm_medium=wall_post&utm_campaign=signup_posts&utm_term=' . urlencode(seo_format($signup_message)) . '&utm_content=' . urlencode($_FACEBOOK['me']['id']),
						'caption' => str_replace('https://', 'http://', DIR_WS_ROOT),
						'description' => 'By crawling and indexing news sources and blogs all over the internet, we bring you the freshest news on ' . $_SITE['site_name'] . '.',
						'privacy' => array(
							'value' => 'EVERYONE',
						),
						'actions' => array(
							'name' => 'Visit ' . $_SITE['site_name'],
							'link' => str_replace('https://', 'http://', DIR_WS_ROOT),
						),
					);
					try {
						$signup_post = $facebook->api('/'.$_FACEBOOK['me']['id'].'/feed', 'POST', $attachment);
					} catch (FacebookApiException $e) {
						error_log($e);
					}
					
					if ($signup_post['id']) {
						$sql = "UPDATE `members_oauth` 
							SET `signup_post` = '" . mysql_real_escape_string($signup_post['id']) . "'
							WHERE `oauth_provider` = 'facebook' 
								AND `members_id` = '" . mysql_real_escape_string($member->get_members_id()) . "' 
							LIMIT 1
						";
						query($sql, $link);
					}
				}
			}
		}
	}
	
	unset($member);
}

if ((isset($_SESSION['fb-connect']) && $_SESSION['fb-connect']) && (isset($_FACEBOOK['me']['id']) && $_FACEBOOK['me']['id'])) {
	unset($_SESSION['fb-connect']);
	
	// check if logged in
	if ($_SESSION['login'] == 1 && time() <= $_SESSION['expires']) {
		$member = new member();
		$member->init($link, SITES_ID, DB_ENCRYPTION_KEY);
		$member->set_members_id($_SESSION['members_id']);
		if ($member->member['oauth']['facebook']['oauth_uid'] != $_FACEBOOK['me']['id']) { // if not matching, connect
			$REDIRECT = DIR_WS_ROOT . 'members/facebook_connect.php?action=connect&nextloc=' . urlencode($_REQUEST['fb-nextloc']);
			fb_redirect($REDIRECT);
		}
	} else {
		// send to login page first if not logged in
		$REDIRECT = DIR_WS_ROOT.'members/login.php?nextloc=' . urlencode('members/facebook_connect.php?action=connect&nextloc='.urlencode($_REQUEST['fb-nextloc']));
		fb_redirect($REDIRECT);
	}
}

if ($_REQUEST['fb-nextloc']) {
	$nextLoc = $_REQUEST['fb-nextloc'];
	
	if (stristr($nextLoc, 'http://') !== FALSE || stristr($nextLoc, 'https://') !== FALSE) {
		// redirect is an absolute URI, check if allowed domains
		$pUrl = parse_url($nextLoc);
		$REDIRECT =	$nextLoc;
	} else {
		$REDIRECT = DIR_WS_ROOT . $nextLoc;
	}
	fb_redirect($REDIRECT);
}

if (empty($_FACEBOOK['me']) && stristr($_SERVER['HTTP_REFERER'], 'apps.facebook.com') !== FALSE) {
	// user is from facebook but not logged into our app, send to login
	//$_REQUEST['action'] = 'fb-login';
}
if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'fb-login')) {
	$redirectUri = $_REQUEST['nextloc'] ? DIR_WS_ROOT . '?fb-nextloc=' . urlencode($_REQUEST['nextloc']) : ($_SERVER['HTTP_REFERER'] ? DIR_WS_ROOT . '?fb-nextloc=' . urlencode($_SERVER['HTTP_REFERER']) : DIR_WS_ROOT);
	
	$REDIRECT = $facebook->getLoginUrl(array(
		'scope' => FB_PERMISSIONS, 
		'redirect_uri' => $redirectUri,
	));
	$_SESSION['fb-login'] = 1;
	fb_redirect($REDIRECT, $_SESSION['in_facebook']);
}

if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'fb-connect')) {
	$redirectUri = $_REQUEST['nextloc'] ? DIR_WS_ROOT . '?fb-nextloc=' . urlencode($_REQUEST['nextloc']) : ($_SERVER['HTTP_REFERER'] ? DIR_WS_ROOT . '?fb-nextloc=' . urlencode($_SERVER['HTTP_REFERER']) : DIR_WS_ROOT);
	$REDIRECT = $facebook->getLoginUrl(array(
		'scope' => FB_PERMISSIONS,
		'redirect_uri' => $redirectUri,
	));
	if ($_FACEBOOK['me']['id']) { // if already logged in, log out first
		$REDIRECT = $facebook->getLogoutUrl(array(
			'next' => $REDIRECT,
		));
	}
	$_SESSION['fb-connect'] = 1;
	fb_redirect($REDIRECT, $_SESSION['in_facebook']);
}

if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'fb-logout')) {
	$redirectUri = $_REQUEST['nextloc'] ? DIR_WS_ROOT . '?fb-nextloc=' . urlencode($_REQUEST['nextloc']) : ($_SERVER['HTTP_REFERER'] ? DIR_WS_ROOT . '?fb-nextloc=' . urlencode($_SERVER['HTTP_REFERER']) : DIR_WS_ROOT);
	$REDIRECT = $facebook->getLogoutUrl(array(
		'next' => $redirectUri,
	));
	$_SESSION['fb-logout'] = 1;
	setcookie(implode('_', array('fb', $facebook->getAppId(), 'access_token')), false, time()-FB_SESSION_LIFETIME, '/', COOKIE_DOMAIN);
	fb_redirect($REDIRECT, $_SESSION['in_facebook']);
}