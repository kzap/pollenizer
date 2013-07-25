<?php 

switch (SITES_ID) {
	case 18:
		// new test site
		require_once(DIR_BOXES . 'page.index.v2.php');
	break;
	
	case 24:
		require_once(DIR_BOXES . 'page.index.v3.php');
	break;
		
	default:

$url = DIR_WS_ROOT;
$h1 = $_SITE['site_name'];
include(DIR_BOXES . 'header.php');

$photostream = '';
$photostream_done = Array();
$photostream_count = 0;

$sql = "SELECT fi.`uuid` 
	FROM `feed_index_site` AS `fi` 
	JOIN `sources_to_sites` AS `ss` ON (ss.`sources_id` = fi.`sources_id` AND ss.`sites_id` = '" . mysql_real_escape_string(SITES_ID) . "')
	ORDER BY fi.`ts` DESC 
	LIMIT 15";
$res = query($sql, $link);
while ($rowX = mysql_fetch_assoc($res)) {
	
	$sql = "SELECT * 
		FROM `feed_datastore` 
		WHERE `uuid` = '" . mysql_real_escape_string($rowX['uuid']) . "' 
		LIMIT 1
	";
	$res2 = query($sql, $link);
	$row = mysql_fetch_assoc($res2);
	
	$data = unserialize($row['data']);
	$title = strip_tags($data['title']);
	
	$sql = "SELECT * 
		FROM `sources` AS `s`
		JOIN `sources_to_sites` AS `ss` ON (ss.`sources_id` = s.`sources_id` AND ss.`sites_id` = '" . mysql_real_escape_string(SITES_ID) . "')
		WHERE s.`sources_id` = '" . mysql_real_escape_string($data['sources_id']) . "' 
		LIMIT 1
	";
	$res2 = query($sql, $link);
	$rowX2 = mysql_fetch_assoc($res2);
	
	$titles_shown[] = $title;
	
	echo '<!--Start Post-->';
	
	echo '<div class="post-'.$rowX2['sources_id'].' post hentry" style="margin-bottom: 40px;"><div class="p-head">';
	
	echo '<h2>';
	if ($data['image'] != '') {
	
		$sources_id = $data['sources_id'];
		if ($photostream_done[$sources_id] == 0 && $photostream_count < 4) {
			$photostream_done[$sources_id] = 1;
			$photostream_count++;
			$photostream .= '<li><a href="'.DIR_WS_ROOT.'sources/'.$rowX2['slug'].'.html" title="'.$rowX2['title'].'"><img src="'.DIR_IMAGES.'spacer.gif" data-src="'.DIR_IMAGES_CDN.'post_images/'.$rowX['uuid'].'.'.$data['image'].'" /></a></li>';
		}
	
		echo '<img src="'.DIR_IMAGES.'spacer.gif" data-src="'.DIR_IMAGES_CDN.'post_images/'.$rowX['uuid'].'.'.$data['image'].'" style="float:left; margin-right: 12px; border: 1px black solid;">';
	}
	echo '<a href="'.DIR_WS_ROOT.'visitstory/'.$row['uuid'].'" rel="nofollow" target="_blank">'.$title.'</a></h2>';
	echo '<p class="p-cat share-widget" style="margin-bottom:5px;">';
	echo 'By: <a href="'.DIR_WS_ROOT.'sources/'.$rowX2['slug'].'.html" title="View all posts by '.$rowX2['name'].'" rel="category tag">'.$rowX2['name'].'</a>';
	//echo '&nbsp;<fb:like href="'.$data['permalink'].'" send="true" layout="button_count" show_faces="false" font="verdana"></fb:like>';
	echo '&nbsp;<fb:like href="'.DIR_WS_ROOT.'visitstory/'.$row['uuid'].'" send="true" layout="button_count" show_faces="false" font="verdana"></fb:like>';
//	echo '&nbsp;<iframe src="http://www.facebook.com/plugins/like.php?app_id='.FB_APP_ID.'&amp;href='.urlencode(DIR_WS_ROOT.'visitstory/'.$row['uuid']).'&amp;send=false&amp;layout=button_count&amp;width=100&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font=verdana&amp;height=35" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100px; height:35px;" allowTransparency="true"></iframe>';
	echo '&nbsp;<a href="http://twitter.com/share"'.($_SITE['twitter_user'] ? ' via="'.htmlspecialchars($_SITE['twitter_user']).'"' : '').' data-url="'.htmlspecialchars(DIR_WS_ROOT.'visitstory/'.$row['uuid']).'" data-text="'.htmlspecialchars($title).'" data-counturl="'.htmlspecialchars($data['permalink']).'" class="twitter-share-button">Tweet</a>';
	echo '&nbsp;<g:plusone href="'.DIR_WS_ROOT.'visitstory/'.$row['uuid'].'"></g:plusone>';
	echo '</p>';
	
	echo '<small class="p-time">';
	echo '<strong class="day">'.date("j",$row['ts']).'</strong>';
	echo '<strong class="month">'.date("M",$row['ts']).'</strong>';
	echo '<strong class="year">'.date("Y",$row['ts']).'</strong>';
	echo '</small>';
	
		
		
	//$data['content'] = substr($data['content'],0,500);
	
	$content = strip_tags($data['content']);
	$content .= ' ';
	$last = strripos($content,'. ')+1;
	if ($last > 5)
		$content = substr($content,0,$last);
	
	
	
	echo '<div class="p-con"><p>';
	if ($data['image'] != '') {
		echo '<br clear="both;">';
	}
	echo nl2br($content);
	echo '<small> <a href="'.DIR_WS_ROOT.'visitstory/'.$row['uuid'].'" rel="nofollow" target="_blank">more</a></small>';
	echo '</p>';
	echo '<div class="clear"></div></div>';
	
	//echo nl2br(strip_tags($data['content']));
	
	echo "\n";
	
	
	$sql = "SELECT `category` 
		FROM `feed_index_categories` 
		WHERE `uuid` = '" . mysql_real_escape_string($row['uuid']) . "'
	";
	$res2 = query($sql, $link);
	$references = '';
	$count = 0;
	while ($rowX2 = mysql_fetch_assoc($res2)) {
	
		$c = strtolower($rowX2['category']);
		
		if (str_replace('+',' ',urlencode($c)) == $c) {
			$references .= '<a href="'.DIR_WS_ROOT.'buzz/'.urlencode(strtolower($c)).'.html">'.$c.'</a>, ';
		}
	}

	if ($references != '') {
		$references = substr($references,0,-2);
		echo '<div class="p-det"><ul><li class="p-det-tag">Mentions: ';
		echo $references;
		echo '</li></ul></div>';
	}
		
	echo '</div></div>';
}

include(DIR_BOXES . 'footer.php');

	break;
}