<?
$articles = array();

$c = new contents($link,SITES_ID);

if (isset($_REQUEST['content_name'])) {
	if ($article = $c->get_byname($_REQUEST['content_name'])) { $articles[] = $article; }
} elseif (isset($_REQUEST['path'])) {
	$path = explode('_', $_REQUEST['path']);
	$category_id = array_pop($path);
	$c->set_contents_list_by_category($category_id);
	foreach ((array)$c->contents as $content) { $articles[] = $content; }
}

foreach ((array)$articles as $article) {
	echo '<!--Start Post-->';
	
	echo '<div class="content-'.$article['contents_id'].' post hentry" style="margin-bottom: 40px;"><div class="p-head">';
	
	echo '<h2>';
	if ($article['image'] != '' && !$_REQUEST['content_name']) {
		if ($photostream_done['c'.$article['contents_id']] == 0 && $photostream_count < 4) {
			$photostream_done['c'.$article['contents_id']] = 1;
			$photostream_count++;
			$photostream .= '<li>
				<a href="'.DIR_CONTENT.($_REQUEST['seo_url'] ? $_REQUEST['seo_url'].'/' : '').$article['contents_slug'].'.php" title="'.htmlspecialchars($article['contents_title']).'">
					<img src="'.DIR_IMAGES.'spacer.gif" data-src="'.DIR_IMAGES_CDN.'content_images/'.$article['contents_id'].'-'.$article['image'].'" />
				</a>
			</li>';
		}
		echo '<img src="'.DIR_IMAGES.'spacer.gif" data-src="'.DIR_IMAGES_CDN.'content_images/'.$article['contents_id'].'-'.$article['image'].'" style="float:left; margin-right: 12px; border: 1px black solid;">';
	}
	echo '<a href="'.DIR_CONTENT.($_REQUEST['seo_url'] ? $_REQUEST['seo_url'].'/' : '').$article['contents_slug'].'.php">'.htmlspecialchars($article['contents_title']).'</a></h2>';
	echo '<p class="p-cat share-widget" style="margin-bottom:5px;">';
	echo 'By: <a href="'.DIR_WS_ROOT.'" rel="category tag">'.$_SITE['site_name'].'</a>';
	echo '&nbsp;<fb:like href="'.DIR_CONTENT.$article['contents_slug'].'.php" send="true" layout="button_count" show_faces="false" font="verdana"></fb:like>';
	echo '&nbsp;<a href="http://twitter.com/share"'.($_SITE['twitter_user'] ? ' via="'.htmlspecialchars($_SITE['twitter_user']).'"' : '').' data-url="'.htmlspecialchars(DIR_CONTENT.$article['contents_slug'].'.php').'" data-text="'.htmlspecialchars($article['contents_title']).'" data-counturl="'.htmlspecialchars(DIR_CONTENT.$article['contents_slug'].'.php').'" class="twitter-share-button">Tweet</a>';
	echo '&nbsp;<g:plusone href="'.DIR_CONTENT.$article['contents_slug'].'.php"></g:plusone>';
	echo '</p>';
	
	echo '<small class="p-time">';
	echo '<strong class="day">'.date("j", strtotime($article['contents_date'])).'</strong>';
	echo '<strong class="month">'.date("M", strtotime($article['contents_date'])).'</strong>';
	echo '<strong class="year">'.date("Y", strtotime($article['contents_date'])).'</strong>';
	echo '</small>';
	
	echo '<div class="p-con"><p>';
	if ($article['image'] != '' && !$_REQUEST['content_name']) {
		echo '<br clear="both;">';
	}
	
	if (!$_REQUEST['content_name']) {
		$content = substr(strip_tags($article['contents_text']), 0, 500);
		$content .= ' ';
		$last = strripos($content,'. ')+1;
		if ($last > 5) { $content = substr($content,0,$last); }
	} else { $content = $article['contents_text']; }
	
	echo $content;
	if (!$_REQUEST['content_name']) {
		echo '<small> <a href="'.DIR_CONTENT.($_REQUEST['seo_url'] ? $_REQUEST['seo_url'].'/' : '').$article['contents_slug'].'.php" title="'.htmlspecialchars($article['contents_title']).'">more ...</a></small>';
	}
	echo '</p>';
	if ($_REQUEST['content_name']) {
		echo '<fb:comments href="'.DIR_CONTENT.$article['contents_slug'].'.php" num_posts="1" width="500"></fb:comments>';
	}
	echo '<div class="clear"></div></div>';
	
	echo "\n";
	
/*		
	$sql = "SELECT `category` FROM `feed_index_categories` WHERE `uuid` = '" . mysql_real_escape_string($row['uuid']) . "'";
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
*/			
	echo '</div></div>';
}
?>
