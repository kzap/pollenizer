<?php
if (SITES_ID == 2) {
	if (stristr($_SERVER['PHP_SELF'], 'photofinder.php') !== FALSE) {
		echo '<blockquote>Comments, questions, suggestions? E-mail <a href="mailto:contact@running.ph">contact@running.ph</a></blockquote>';
		echo '<a href="http://face.com/" title="Face Recognition by face.com">
			<img src="http://static.face.com/badges/badge_3_light_bg.png" border="0" alt="Face Recognition by face.com">
			</a>
		';
	}
}
?>
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
<script type="text/javascript" src="<?=DIR_JAVASCRIPT;?>jquery.sonar.js"></script>
<script type="text/javascript"> 
jQuery(document).ready(function($) {
	$("img[data-src]").on("scrollin", {distance:500}, function(){
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