<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="https://www.facebook.com/2008/fbml">
<head>
	<title><?=($title?htmlspecialchars($title).' - ':'').htmlspecialchars($_SITE['site_name']);?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<? if ($meta != '') echo $meta; ?>
	
	<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
	
	<link rel="stylesheet" href="http://twitter.github.com/bootstrap/1.4.0/bootstrap.min.css">
	<link href="http://twitter.github.com/bootstrap/assets/css/docs.css" rel="stylesheet">
	<!-- Le fav and touch icons -->
    <link rel="shortcut icon" type="image/x-icon" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    <link rel="apple-touch-icon" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    <link rel="apple-touch-icon" sizes="72x72" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    <link rel="apple-touch-icon" sizes="114x114" href="<?=DIR_TEMPLATES;?>compositio/favicon.ico">
    
	<link rel="index" title="<?=htmlspecialchars($_SITE['site_name']);?>" href="<?=htmlspecialchars($_SITE['homepage_url']);?>" />
	<link rel="canonical" href="<?=$_SITE['canonical'];?>" />
	<meta property="og:title" content="<?=$title ? $title . ' - ' : $_SITE['site_name'];?>" />
	<meta property="og:description" content="<?=$description ? $description : 'Create your own It\'s More Fun in the Philippines Image easily, even use all the free images on Flickr. Easy to use Meme Generator.'; ?>" />
	<meta property="og:site_name" content="<?=$_SITE['site_name'];?>" />
	<meta property="og:url" content="<?=$_SITE['canonical'];?>" />
	<meta property="og:type" content="website" />
	<meta property="fb:app_id" content="<?=FB_APP_ID;?>" />
	<meta property="fb:page_id" content="<?=FB_PAGE_ID;?>" />
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
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

    <header class="jumbotron masthead" id="overview">
      <div class="inner">
        <div class="container">
        	<br /><br />
			<h1>It's More FUN In The Philippines!</h1>
          <p class="lead">
Why is it more fun in the Philippines?
          </p>
        </div><!-- /container -->
      </div>
    </header>

<div class="container">
	<div class="row"><div class="span16">&nbsp;</div></div>
	<div class="row">
		<div class="span16">
<?
echo '<div class="share-widget">';
	echo '&nbsp;<fb:like href="'.$_SITE['canonical'].'" send="true" layout="button_count" show_faces="true" action="recommend" font="verdana" height="100"></fb:like>';
	echo '&nbsp;<a href="http://twitter.com/share"'.($_SITE['twitter_user'] ? ' via="'.htmlspecialchars($_SITE['twitter_user']).'"' : '').' data-url="'.htmlspecialchars($_SITE['canonical']).'" data-text="'.htmlspecialchars($article['contents_title']).'" data-counturl="'.htmlspecialchars($_SITE['canonical']).'" class="twitter-share-button">Tweet</a>';
	echo '&nbsp;<g:plusone href="'.$_SITE['canonical'].'"></g:plusone>';
echo '</div>';
?>
