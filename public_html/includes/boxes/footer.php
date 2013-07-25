<div class="nav">
 	<div class="left"></div>
 	<div class="right"></div>
</div>

</div></div>

<div class="SR">

<!-- Start Search -->
<div class="search">
  <form method="get" action="/search.php" method="q">
   <fieldset>
   <input type="text" value="" name="q" /><button type="submit">Search</button>
   </fieldset>
  </form>
  <br />
<div class="syn">
 <ul>
  <li><a href="/rss.xml"><?PHP echo $h1; ?>The Latest <?PHP echo $main_kw; ?> News</a> (RSS Feed)</li>

 </ul>
</div>
</div>
<!-- End Search -->

<!-- Start About This -->
<div class="about">
<h3>About <?PHP echo $_SITE['site_name']; ?></h3>
<p>By crawling and indexing news sources and blogs all over the internet, we bring you the freshest news on <?=$_SITE['site_name'];?>. You can use the above RSS link to keep upto date with your favorites.</p>
</div>
<!-- End About This Blog -->



<?PHP 


if ($photostream != '') {

	echo '<div class="photostream" style="line-height: 1.5em;"><h3>Photostream</h3><br /><ul>';
	echo $photostream;
	echo '</ul></div>';
}

echo '<div class="categs" style="line-height: 1.5em;">';


echo '<div style="width: 270px !important; line-height: 2.0em;">';


unset($tagArray);
unset($letterSort);

$sql = "SELECT `category`, `num` FROM `feed_data_categories_week` ORDER BY `num` DESC LIMIT 12";
$res = query($sql, $link);

while ($row = mysql_fetch_assoc($res)) {

	$category = $row['category'];
	$cat = $category;

	if ($cat == $category) {
		if (str_replace('+',' ',urlencode($cat)) == $cat) {
			$tagArray[$category] = $row['num'];
		}
	}
}


if (is_array($tagArray)) {
	arsort($tagArray);

	$x = 0;
	foreach ($tagArray as $k=>$v) {

		if ($ignorelist[$k] != 1) {
			$x++;
			if ($x>12)
				break;
			if ($x == 1)
				$max = $v;
			if ($x == 12)
				$min = $v;
			$letterSort[] = $k;		
		}
	}

	if ($min == 0)
		$min = 1;

	$step = $max - $min;
	if ($step == 0)
		$step = 1;
	$step = 1 / $step;
	
	sort($letterSort);
	
	if (is_array($letterSort)) {
		echo '<h3>Top Buzz This Week</h3>';
		foreach ($letterSort as $k=>$v) {
			
			$strength = $tagArray[$v] - $min;
			$size = round(($strength * $step),2) + 1;
			if ($max == $min)
				$size = 1;
			
			$mentions = $tagArray[$v];
			echo '<span style="font-size: '.$size.'em;"><a href="/buzz/'.urlencode(strtolower($v)).'.html" title="'.$mentions.' mentions of '.$v.'">'.ucwords($v).'</a></span> ... ';
		}
	}
}

?>
	</div>
</div>

<div class="recent">
<h3>Latest Articles</h3>
<? include(DIR_BOXES.'menu.content.php'); ?>
</div>

<div class="recent">
<h3>Network</h3>
<a href="http://boracay.com.ph/">Boracay</a> - Philippines<br />
</div>
<span class="share-widget"><fb:like-box href="<?=$_SITE['fb_page_url'];?>" width="292" show_faces="true" stream="true" header="true"></fb:like-box></span>

</div>
<!-- End SideBar1 -->
<!-- Container -->

</div></div>
<!-- End BG -->

<div class="footer">
<p class="copy">&copy; 2006-<?PHP echo date("Y"); ?> <a href="http://www.enthropia.com/">Enthropia Inc</a> | <a href="/about/">About Us</a> | <a href="/contact/">Contact Us</a><br />
<div style="font-size: 10px; margin-top: 10px;">
</div>
</p> 
</div>

<!-- FACEBOOK -->
<script type="text/javascript">
  window.fbAsyncInit = function() {
    FB.init({
      appId  : <?=FB_APP_ID;?>,
      status : true, // check login status
      cookie : true, // enable cookies to allow the server to access the session
      xfbml  : false,  // parse XFBML
	  logging: false,
	  oauth: true
    });
	FB.Event.subscribe('edge.create', function(targetUrl) {
		_gaq.push(['_trackSocial', 'facebook', 'like', targetUrl]);
		_gaq.push(['projectX._trackSocial', 'facebook', 'like', targetUrl]);
	});
	FB.Event.subscribe('edge.remove', function(targetUrl) {
		_gaq.push(['_trackSocial', 'facebook', 'unlike', targetUrl]);
		_gaq.push(['projectX._trackSocial', 'facebook', 'unlike', targetUrl]);
	});
	FB.Event.subscribe('message.send', function(targetUrl) {
		_gaq.push(['_trackSocial', 'facebook', 'send', targetUrl]);
		_gaq.push(['projectX._trackSocial', 'facebook', 'send', targetUrl]);
	});
	FB.Event.subscribe('comment.create', function(response) { 
		_gaq.push(['_trackSocial', 'facebook', 'comment', encodeURIComponenet(response.href)]);
		_gaq.push(['projectX._trackSocial', 'facebook', 'comment', encodeURIComponenet(response.href)]);
	});
	
	jQuery(document).ready(function($) {
		var $shareWidgets = $( '.share-widget' );
		$shareWidgets.bind( 'scrollin', { distance: 500 }, function() {
		    var $share = $( this );
		    if (!$share.data( 'initFB' ) && window.FB) {
		        $share.data('initFB', 1);
		        $share.unbind( 'scrollin' );
		        FB.XFBML.parse( $share[0] );
		    }
		});
	});
	
  };
  loadScriptAsync('//connect.facebook.net/en_US/all.js');
</script>
<!-- END FACEBOOK -->

<!-- Load Images on Scroll -->
<script type="text/javascript" src="<?=DIR_JAVASCRIPT;?>jquery.sonar.min.js"></script> 
<script type="text/javascript"> 
jQuery(document).ready(function($) {
	$("img[data-src]").bind("scrollin", {distance:500}, function(){
	    var img = this,
	        $img = $(img);
	    $img.unbind("scrollin");
	    img.src = $img.attr( "data-src" );
	});
});
</script>

<script src="//platform.twitter.com/widgets.js" type="text/javascript"></script>
<script type="text/javascript">
function extractParamFromUri(uri, paramName) {
  if (!uri) {
    return;
  }
  var uri = uri.split('#')[0];  // Remove anchor.
  var parts = uri.split('?');  // Check for query params.
  if (parts.length == 1) {
    return;
  }
  var query = decodeURI(parts[1]);

  // Find url param.
  paramName += '=';
  var params = query.split('&');
  for (var i = 0, param; param = params[i]; ++i) {
    if (param.indexOf(paramName) === 0) {
      return unescape(param.split('=')[1]);
    }
  }
}
twttr.events.bind('tweet', function(event) {
  if (event) {
    var targetUrl;
    if (event.target && event.target.nodeName == 'IFRAME') {
      targetUrl = extractParamFromUri(event.target.src, 'url');
    }
    _gaq.push(['_trackSocial', 'twitter', 'tweet', targetUrl]);
    _gaq.push(['projectX._trackSocial', 'twitter', 'tweet', targetUrl]);
  }
});
</script>
<script type="text/javascript">
	loadScriptAsync('<?=DIR_JAVASCRIPT;?>distilled.FirstTouch.js');
</script>
<script type="text/javascript">
	loadScriptAsync('//apis.google.com/js/plusone.js');
	var s = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	loadScriptAsync(s);
</script>

<? if (($_GET['debugPQP'] == 1 || $_COOKIE['debugPQP'] == 1)) { $PQP->display($linkInfo); } ?>
</body>
</html>