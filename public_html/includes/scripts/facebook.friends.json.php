<?php
require_once('../application_top.php');

// get friends
$stashArgs = array('facebook', $_FACEBOOK['me']['id'], '/' . $_FACEBOOK['me']['id'] . '/friends');
$stash = StashManager::getCache('Slow Cache', SITES_ID, implode('|', array_map('trim', array_map('strval', $stashArgs))));

$friends = $stash->get();
if ($stash->isMiss()) {
	$friends = array();
	try {
		$friends = $facebook->api('/' . $_FACEBOOK['me']['id'] . '/friends', 'GET');
	} catch (FacebookApiException $e) {
		error_log($e);
	}
	
	if (!empty($friends['data'])) {
		$stash->store($friends, 3600);
	}
}

$searchResults = array();

if (!empty($friends['data'])) {
	// create arrays
	$friendsList = array();
	$friendsListNames = array();
	
	// insert friends into arrays
	foreach ($friends['data'] as $friend) {
		$friendsList[$friend['id']] = array(
			'label' => $friend['name'],
			'value' => substr($friend['name'], 0, (strpos($friend['name'], ' ') ? strpos($friend['name'], ' ') : strlen($friend['name']))),
			'uid' => $friend['id'],
		);
		$friendsListNames[$friend['id']] = $friend['name'];
	}
	
	// sort arrays
	array_multisort($friendsListNames, SORT_ASC, $friendsList);
		
	$count = 0;
	if (trim($_REQUEST['term'])) {
		
		// put your name first always if a valid result
		if (stristr($_FACEBOOK['me']['name'], trim($_REQUEST['term'])) !== FALSE) {
			$searchResults[] = array(
				'label' => $_FACEBOOK['me']['name'],
				'value' => substr($_FACEBOOK['me']['name'], 0, (strpos($_FACEBOOK['me']['name'], ' ') ? strpos($_FACEBOOK['me']['name'], ' ') : strlen($_FACEBOOK['me']['name']))),
				'uid' => $_FACEBOOK['me']['id'],
			);
			$count++;
		}
		
		foreach ($friendsListNames as $id => $name) {
			if ($id != $_FACEBOOK['me']['id']) {
				if (stristr($name, trim($_REQUEST['term'])) !== FALSE) {
					$searchResults[] = $friendsList[$id];
					if ($count++ > 10) { break; }
				}
			}
		}
		
	} else {
		
		// put your name first always
		$searchResults[] = array(
			'label' => $_FACEBOOK['me']['name'],
			'value' => substr($_FACEBOOK['me']['name'], 0, (strpos($_FACEBOOK['me']['name'], ' ') ? strpos($_FACEBOOK['me']['name'], ' ') : strlen($_FACEBOOK['me']['name']))),
			'uid' => $_FACEBOOK['me']['id'],
		);
		$count++;
		
		foreach ($friendsList as $id => $friend) {
			if ($id != $_FACEBOOK['me']['id']) {
				$searchResults[] = $friend;
				if ($count++ > 10) { break; }
			}
		}
	}
}

echo json_encode($searchResults);
