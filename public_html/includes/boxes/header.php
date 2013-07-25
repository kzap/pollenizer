<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml">
<head>
	<title><?=($title ? htmlspecialchars($title).' - ': '' ) . htmlspecialchars($_SITE['site_name']);?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<?PHP if ($meta != '') echo $meta; ?>
	<link rel="stylesheet" type="text/css" href="<?=DIR_TEMPLATES;?>compositio/style.css" />
	<link rel="shortcut icon" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico" type="image/x-icon" />
	<link rel="index" title="<?=htmlspecialchars($_SITE['site_name']);?>" href="<?=htmlspecialchars($_SITE['homepage_url']);?>" />
	<link rel="canonical" href="<?=$_SITE['canonical'];?>" />
	<meta property="og:title" content="<?=$title ? $title . ' - ' : $_SITE['site_name'];?>" />
	<meta property="og:description" content="<?=$description ? $description : 'About ' . $_SITE['site_name'] . ': By crawling and indexing news sources and blogs all over the internet, we bring you the freshest news on ' . $_SITE['site_name'] . '.'; ?>" />
	<meta property="og:site_name" content="<?=$_SITE['site_name'];?>" />
	<meta property="og:url" content="<?=$_SITE['canonical'];?>" />
	<meta property="og:type" content="website" />
	<meta property="fb:app_id" content="<?=FB_APP_ID;?>" />
	<meta property="fb:page_id" content="<?=FB_PAGE_ID;?>" />
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
	<script type="text/javascript" src="<?=DIR_JAVASCRIPT;?>loadScriptAsync.js"></script>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(
			['_setAccount', '<?=$_SITE['ga_id'];?>'],
			['_setDomainName', 'none'],
			['_setAllowLinker', true],
			[function() {var pageTracker = _gaq._getAsyncTracker(); distilledFirstTouch(pageTracker);}],
			['_trackPageview'],
			['_trackPageLoadTime'],
			['projectX._setAccount', 'UA-872660-39'],
			['projectX._setDomainName', 'none'],
			['projectX._setAllowLinker', true],
			['projectX._trackPageview'],
			['projectX._trackPageLoadTime']
		);
	</script>
</head>
<body>
<div id="fb-root"></div>

<!-- Start BG -->
<div id="bg">

<div style="width:980px;text-align:right;margin-bottom:10px;">
</div>

<div id="bg-all">


<div class="menu">
   <ul>

	<li class="<?PHP if ($cat == 'home') { echo 'current_page_item'; } else { echo 'page_item'; } ?> page-item-1"><a href="<?=DIR_WS_ROOT;?>"><span><?PHP echo $main_name; ?> Home</span></a></li>
	<li class="<?PHP if ($cat == 'content') { echo 'current_page_item'; } else { echo 'page_item'; } ?> page-item-1"><a href="<?=DIR_CONTENT;?>"><span>Articles</span></a></li>
	<li class="<?PHP if ($cat == 'buzz') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-4"><a href="<?=DIR_WS_ROOT;?>buzz/" title="Latest Trending Topics"><span><?PHP echo $main_kw; ?> Buzz</span></a></li>
	<li class="<?PHP if ($cat == 'sources') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-4"><a href="<?=DIR_WS_ROOT;?>sources/" title="<?PHP echo $main_kw; ?> Sources"><span>Sources</span></a></li>
<?php
	if (SITES_ID == 2) {
		echo '<li class="page_item"><a href="' . DIR_WS_ROOT . 'photos.php">Photos</a></li>';
		echo '<li class="page_item"><a href="' . DIR_WS_ROOT . 'photofinder.php">Photo Finder</a><sup style="color: red;">Beta</sup></li>';
	}
	if ($member->members_id) {
		//echo '<li class="page_item"><a href="' . DIR_WS_ROOT . 'members/">My Account</a></li>';
		if (!empty($_FACEBOOK['me'])) {
			echo '<li class="page_item"><a href="' . DIR_WS_ROOT . '?action=fb-logout&nextloc=' . urlencode('members/logout.php') . '">Log Out</a></li>';
		} else {
			//echo '<li class="page_item"><a href="' . DIR_WS_ROOT . 'members/logout.php">Log Out</a></li>';
		}
	} else {
		//echo '<li class="page_item"><a href="' . DIR_WS_ROOT . 'register.php" title="Register"><span>Register</span></a></li>';
		//echo '<li class="page_item"><a href="' . DIR_WS_ROOT . 'members/login.php" title="Login"><span>Login</span></a></li>';
		echo '<li class="page_item"><a href="' . $_SERVER['PHP_SELF'].  '?action=fb-login" title="Login using Facebook"><img src="' . DIR_IMAGES . 'fb-login.gif" border="0" alt="Login using Facebook" style="vertical-align: text-bottom;" /></a></li>';
	}
?>
  </ul>
  
</div>
 

<!-- Start Container -->
<div class="container">

<!-- Start Header -->
<?PHP if ($cat == 'home') { echo '<div class="logo2">'; } else { echo '<div class="logo">'; } ?>
 <div class="txt share-widget">
 
 <h1>
 <a href="<?PHP echo $url; ?>"><?=($h1 ? $h1 : $title); ?></a></h1>

 <?PHP 
 switch ($cat) {
	case 'movietimes':
		global $city_name;
		if ($city_name == '')
			$city_name = 'all of New Zealand';
		echo '<p class="desc">Movie times for '.$city_name.'</p>';
		break;
	case 'sources':
	case 'buzz':
 		break;
 	case 'home':
 		echo '<p class="desc">The latest '.$main_kw.' news, &amp; updates, and movie times</p>';
 		break;
 	default:
 		break;
 }
 
?>

<?
echo '&nbsp;<fb:like href="'.$_SITE['canonical'].'" send="true" layout="button_count" show_faces="true" action="recommend" font="verdana" height="100"></fb:like>';
echo '&nbsp;<a href="http://twitter.com/share"'.($_SITE['twitter_user'] ? ' via="'.htmlspecialchars($_SITE['twitter_user']).'"' : '').' data-url="'.htmlspecialchars($_SITE['canonical']).'" data-text="'.htmlspecialchars($article['contents_title']).'" data-counturl="'.htmlspecialchars($_SITE['canonical']).'" class="twitter-share-button">Tweet</a>';
echo '&nbsp;<g:plusone href="'.$_SITE['canonical'].'"></g:plusone>';
?>

</div></div>
<!-- END Header -->

<div class="SL">