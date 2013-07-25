<?php
require_once('../application_top.php');
require_once(DIR_INCLUDES . 'configure.face.php');
require_once(DIR_CLASSES . 'facebook/facebook.php');
require_once(DIR_CLASSES . 'face/FaceRestClient.php');

$face_api = new FaceRestClient(FACE_API_KEY, FACE_API_SECRET);
if ($_FACEBOOK['me']['id']) {
	$face_api->setFBUser($_FACEBOOK['me']['id'], $_FACEBOOK['access_token']);
}

$results = array();

switch(strtoupper($_REQUEST['action'])) {
	case 'SAVE':
		if ($_REQUEST['uid'] && $_REQUEST['tid']) {
			if (stristr($_REQUEST['uid'], '@facebook.com') === FALSE) { $_REQUEST['uid'] .= '@facebook.com'; }
			
			$oldTag = $_REQUEST['tid'];
			
			// first try to remove existing saved tag if there is one:
			$faceResults = $face_api->tags_remove(
				$_REQUEST['tid']
			);
			$faceResults = objectToArray($faceResults);
			
			if ($faceResults['status'] == 'success') { // if previous tag was removed, get new temp tag
				$_REQUEST['tid'] = $faceResults['removed_tags'][0]['detected_tid'];
			}
			
			$faceResults = $face_api->tags_save(
				$_REQUEST['tid'],
				$_REQUEST['uid']
			);
			$faceResults = objectToArray($faceResults);
			
			if ($faceResults['status'] == 'success') {
				if (!in_array($_REQUEST['uid'], (array) $_SESSION['uids'])) { $_SESSION['uids'][] = $_REQUEST['uid']; }
				$results['status'] = 'success';
				$results['newTid'] = $faceResults['saved_tags'][0]['tid'];
				
				$newTag = $results['newTid'];
				if (substr($newTag, 0, 4) == 'TEMP') { $newTag = substr($newTag, 0, -4); }
				if (substr($oldTag, 0, 4) == 'TEMP') { $oldTag = substr($oldTag, 0, -4); }
				$sql = "UPDATE `face_sprite_cache` 
					SET `tag_id` = '" . mysql_real_escape_string($newTag) . "'
					WHERE `tag_id` = '" . mysql_real_escape_string($oldTag) . "'
				";
				query($sql, $link);	
			}
			//$results['raw'] = $faceResults;
		}
	break;
		
	case 'REMOVE':
		if ($_REQUEST['tid']) {
			$oldTag = $_REQUEST['tid'];
			
			$faceResults = $face_api->tags_remove(
				$_REQUEST['tid']
			);
			$faceResults = objectToArray($faceResults);

			if ($faceResults['status'] == 'success') {
				$results['status'] = 'success';
				$results['newTid'] = $faceResults['removed_tags'][0]['detected_tid'];
				
				$newTag = $results['newTid'];
				if (substr($newTag, 0, 4) == 'TEMP') { $newTag = substr($newTag, 0, -4); }
				if (substr($oldTag, 0, 4) == 'TEMP') { $oldTag = substr($oldTag, 0, -4); }
				$sql = "UPDATE `face_sprite_cache` 
					SET `tag_id` = '" . mysql_real_escape_string($newTag) . "'
					WHERE `tag_id` = '" . mysql_real_escape_string($oldTag) . "'
				";
				query($sql, $link);
			}
			//$results['raw'] = $faceResults;
		}
	break;
}

echo json_encode($results);
