			
		</div>
	</div>
</div>

<div class="topbar" data-scrollspy="scrollspy">
    <div class="fill">
        <div class="container" data-dropdown="dropdown">
        	<div class="alert-message info fade in" data-alert="alert">
        		<a class="close" href="#">&times;</a>
        		<p><strong>Notice:</strong> This is not the official website for this campaign, only a fan site. Click <a href="http://itsmorefuninthephilippines.com/" target="_blank">here</a> to visit the official website.</p>
      		</div>
            <h3><a href="<?=$_SITE['homepage_url'];?>"><?=$_SITE['site_name'];?></a></h3>
            <ul>
                <li class="active"><a href="<?=$_SITE['homepage_url'];?>">Home</a></li>
                <li><a href="#">Contact</a></li>
                
          </ul>
          <form action="">
            <input type="text" style="width: 100px;" placeholder="Search" />
          </form>
          <ul class="nav secondary-nav">
<? /*
          	<li class="menu">
              <a href="#" class="menu">Dropdown 1</a>
              <ul class="menu-dropdown">
                <li><a href="#">Secondary link</a></li>
                <li><a href="#">Something else here</a></li>
                <li class="divider"></li>
                <li><a href="#">Another link</a></li>
              </ul>
            </li>
 * 
 */
?>
          	<?
            if ($member->members_id) {
				echo '<li><a href="'.DIR_WS_ROOT.'members/" title="My Account">My Account</a></li>';
				if (!empty($_FACEBOOK['me'])) {
					echo '<li><a href="'.DIR_WS_ROOT.'?action=fb-logout&nextloc='.urlencode('members/logout.php').'" title="Log Out">Log Out</a></li>';
				} else {
					echo '<li><a href="'.DIR_WS_ROOT.'members/logout.php" title="Log Out">Log Out</a></li>';
				}
			} else { 
            	//echo '<li><a href="'.DIR_WS_ROOT.'register.php" title="Register">Register</a></li>';
				//echo '<li><a href="'.DIR_WS_ROOT.'members/login.php" title="Login">Login</a></li>';
				//echo '<li><a href="'.$_SERVER['PHP_SELF'].'?action=fb-login" title="Login using Facebook"><img src="'.DIR_IMAGES.'fb-login.gif" border="0" alt="Login using Facebook" style="margin-top: 10px; vertical-align: text-bottom;" /></a>';
				echo '<li><a href="http://itsmorefuninthephilippinespics.com" title="Pics from Twitter" target="_blank">Twitter Pics</a></li>';
				echo '<li><a href="http://pinterest.com/antondiaz/itsmorefuninthephilippines/" title="Pintrest Pinboard" target="_blank">Pinboard</a></li>';
				echo '<li><a href="http://itsmorefuninthephilippines.com" title="Official Website" target="_blank">Official Website</a></li>';
			}
			?>
          </ul>
        </div>
    </div>
</div>

<script src="//autobahn.tablesorter.com/jquery.tablesorter.min.js"></script>
<script src="//twitter.github.com/bootstrap/1.4.0/bootstrap-alerts.js"></script>
<script src="//twitter.github.com/bootstrap/1.4.0/bootstrap-buttons.js"></script>
<script src="//twitter.github.com/bootstrap/1.4.0/bootstrap-dropdown.js"></script>
<script src="//twitter.github.com/bootstrap/1.4.0/bootstrap-twipsy.js"></script>
<script src="//twitter.github.com/bootstrap/1.4.0/bootstrap-scrollspy.js"></script>

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