<?php
require_once('../application_top.php');
$results = array();

switch(strtoupper($_REQUEST['action'])) {
	case 'ADD_SOURCE':
		if ($_REQUEST['events_id']) {
			
			if ($_REQUEST['fb_album_id']) {
				try {
					$fbResult = $facebook->api('/' . (int) $_REQUEST['fb_album_id']);
				} catch (FacebookApiException $e) {
					error_log($e);
				}
				
				if (!empty($fbResult)) {
					$fb_album_count = (int) $fbResult['count'];
				}
			}
			
			if ($_REQUEST['sources_id']) {
				$sql = "UPDATE `events_photos_sources` SET ";
				if ($_REQUEST['fb_album_id']) { $sql .= "`fb_album_id` = '" . mysql_real_escape_string($_REQUEST['fb_album_id']) . "', "; }
				if ($_REQUEST['fb_photo_id']) { $sql .= "`fb_photo_id` = '" . mysql_real_escape_string($_REQUEST['fb_photo_id']) . "', "; }
				if ($_REQUEST['url']) { $sql .= "`url` = '" . mysql_real_escape_string($_REQUEST['url']) . "', "; }
				if ($fb_album_count) { $sql .= "`photo_count` = '" . mysql_real_escape_string($fb_album_count) . "', "; }
				$sql = substr($sql, 0, -2);
				$sql .= " WHERE `sources_id` = '" . mysql_real_escape_string($_REQUEST['sources_id']) . "'";
				if (query($sql, $link)) {
					$results['status'] = 'success';
					$results['sources_id'] = $_REQUEST['sources_id'];
				}
				
			} elseif ($_REQUEST['fb_album_id'] || $_REQUEST['fb_photo_id'] || $_REQUEST['url']) {
				
				$sql = "INSERT INTO `events_photos_sources` SET ";
				$sql .= "`events_id` = '" . mysql_real_escape_string($_REQUEST['events_id']) . "', ";
				if ($_REQUEST['fb_album_id']) { $sql .= "`fb_album_id` = '" . mysql_real_escape_string($_REQUEST['fb_album_id']) . "', "; }
				if ($_REQUEST['fb_photo_id']) { $sql .= "`fb_photo_id` = '" . mysql_real_escape_string($_REQUEST['fb_photo_id']) . "', "; }
				if ($_REQUEST['url']) { $sql .= "`url` = '" . mysql_real_escape_string($_REQUEST['url']) . "', "; }
				if ($fb_album_count) { $sql .= "`photo_count` = '" . mysql_real_escape_string($fb_album_count) . "', "; }
				$sql = substr($sql, 0, -2);
				if (query($sql, $link)) {
					$results['status'] = 'success';
					$results['sources_id'] = (int) mysql_insert_id($link);
				}	
				
			}
			
		}
	break;
		
	case 'REMOVE_SOURCE':
		if ($_REQUEST['sources_id']) {
			$sql = "DELETE
				FROM `events_photos_sources`
				WHERE `sources_id` = '" . mysql_real_escape_string($_REQUEST['sources_id']) . "'
				LIMIT 1
			";
			if (query($sql, $link)) {
				$results['status'] = 'success';
			}
		}
	break;
}

echo json_encode($results);
