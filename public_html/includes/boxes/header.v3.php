<!DOCTYPE html>
<html lang="en">
<head>
	<title><?=($title ? htmlspecialchars($title) . ' - ' : '') . htmlspecialchars($_SITE['site_name']);?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<?php
	if ($meta != '') {
		echo $meta;
	} else {
		echo '<meta name="description" content="' . ($description ? $description : 'About ' . $_SITE['site_name'] . ': By crawling and indexing news sources and blogs all over the internet, we bring you the freshest news on ' . $_SITE['site_name'] . '.') . '" />';
	}
	?>
	
	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	
	<link href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
	
	<!-- Le fav and touch icons -->
    <link rel="shortcut icon" type="image/x-icon" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    <link rel="apple-touch-icon" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    <link rel="apple-touch-icon" sizes="72x72" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    <link rel="apple-touch-icon" sizes="114x114" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    
	<link rel="index" title="<?=htmlspecialchars($_SITE['site_name']);?>" href="<?=htmlspecialchars($_SITE['homepage_url']);?>" />
	<link rel="canonical" href="<?=$_SITE['canonical'];?>" />
	<meta property="og:title" content="<?=$title ? $title . ' - ' : $_SITE['site_name'];?>" />
	<meta property="og:description" content="<?=$description ? $description : 'About ' . $_SITE['site_name'] . ': By crawling and indexing news sources and blogs all over the internet, we bring you the freshest news on ' . $_SITE['site_name'] . '.'; ?>" />
	<meta property="og:site_name" content="<?=$_SITE['site_name'];?>" />
	<meta property="og:url" content="<?=$_SITE['canonical'];?>" />
	<meta property="og:type" content="website" />
	<meta property="fb:app_id" content="<?=FB_APP_ID;?>" />
	<meta property="fb:page_id" content="<?=FB_PAGE_ID;?>" />
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
	<link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/cupertino/jquery-ui.css" rel="stylesheet" />
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

<div class="well" style="background-color: #FFF;">
<?php
if (SITES_ID == 2) {
	if (stristr($_SERVER['PHP_SELF'], 'photofinder.php') !== FALSE
		|| stristr($_SERVER['PHP_SELF'], 'photos.php') !== FALSE) {
		echo '<div class="page-header">';
		if (!checkFbPerms()) { echo '<a href="?action=fb-face-login" class="btn btn-primary"><i class="icon-user"></i>&nbsp;Login Using Facebook</a>'; }
		else { echo '<a href="?action=fb-logout" class="btn btn-danger"><i class="icon-off"></i>&nbsp;Logout</a>'; }
		echo '<br /><br />';
		echo '<h1>Photo Finder <small>Find your Photo Anywhere</small></h1>';
		echo '</div>';
		
		echo '<ul class="nav nav-pills">';
		if (!$_REQUEST['action'] && stristr($_SERVER['PHP_SELF'], 'photofinder.php') !== FALSE) { echo '<li class="active">'; } else { echo '<li>'; }
		echo '<a href="' . DIR_WS_ROOT . 'photofinder.php"><i class="icon-list-alt"></i>&nbsp;Instructions</a></li>';
		if (in_array($_REQUEST['action'], array('Tag Photos', 'Process Tagged Images')) && stristr($_SERVER['PHP_SELF'], 'photofinder.php') !== FALSE) { echo '<li class="active">'; } else { echo '<li>'; }
		echo '<a href="' . DIR_WS_ROOT . 'photofinder.php?action=' . urlencode('Tag Photos') . '"><i class="icon-tags"></i>&nbsp;Tag Your Photos</a></li>';
		if (in_array($_REQUEST['action'], array('Search Photos')) && stristr($_SERVER['PHP_SELF'], 'photofinder.php') !== FALSE) { echo '<li class="active">'; } else { echo '<li>'; }
		echo '<a href="' . DIR_WS_ROOT . 'photofinder.php?action=' . urlencode('Search Photos') . '"><i class="icon-search"></i>&nbsp;Find Photos</a></li>';
		if (stristr($_SERVER['PHP_SELF'], 'photos.php') !== FALSE) { echo '<li class="active">'; } else { echo '<li>'; }
		echo '<a href="' . DIR_WS_ROOT . 'photos.php"><i class="icon-camera"></i>&nbsp;Browse Running Event Photos</a></li>';
		echo '</ul>';
	}
}
