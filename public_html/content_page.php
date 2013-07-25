<? include('includes/application_top.php'); 

$c = new contents($link, SITES_ID);

if ($_REQUEST['content_path']) {
	// parse content_path
	$content_path = htmlentities($_REQUEST['content_path'], ENT_QUOTES, 'UTF-8');
	
	$seo_urlArray = array();
	foreach (explode('/', $content_path) as $val) {
		if (trim($val)) { $seo_urlArray[] = strtolower(trim($val)); }
	}
	
	// if file ends in .php make it content_name
	if (substr($seo_urlArray[count($seo_urlArray)-1], -4) == '.php') {
		$content_name = basename(array_pop($seo_urlArray), '.php');
	}
	
	// if has dirs, try to get category
	if (!empty($seo_urlArray)) {
		if ($category = $c->get_category_byseourl(implode('/', $seo_urlArray))) {
			$_REQUEST['path'] = $category['cpath'];
			$_REQUEST['seo_url'] = $category['seo_url'];
		} elseif (!$content_name) {
			$content_name = array_pop($seo_urlArray);
			if ($category = $c->get_category_byseourl(implode('/', $seo_urlArray))) {
				$_REQUEST['path'] = $category['cpath'];
				$_REQUEST['seo_url'] = $category['seo_url'];
			}
		}
	}
} elseif ($_REQUEST['content_name']) {
	$content_name = htmlentities($_REQUEST['content_name'], ENT_QUOTES, 'UTF-8');
}

if ($content_name) {
	$_REQUEST['content_name'] = $content_name = seo_format($content_name);
	if ($box = $c->get_byname($content_name)) {
		if (!$box['internal_only']) {
			$title = $box['contents_title'];
			$canonical = $url = DIR_CONTENT.$box['contents_slug'].'.php';
			if ($box['members_only']) {
				include('members/members_application_top.php');
				include(DIR_BOXES.'members_header.php');
				include(DIR_BOXES.'listing.content.php');
				include(DIR_BOXES.'members_footer.php');
			} else {
				include(DIR_BOXES.'header.php');
				include(DIR_BOXES.'listing.content.php');
				include(DIR_BOXES.'footer.php');
				exit;
			}
		}
	}
} else {
	if (!empty($category)) {
		$title = $category['categories_name'];
		$canonical = $url = DIR_CONTENT.$category['seo_url'].'/';
	} else {
		$_REQUEST['path'] = 0;
		$title = 'Articles & Information';
		$canonical = $url = DIR_CONTENT;
	}
	include(DIR_BOXES.'header.php');
	include(DIR_BOXES.'listing.content.php');
	include(DIR_BOXES.'footer.php');
	exit;
}

header("Location: ".DIR_WS_ROOT."error.php?error=404");
exit;

?>

