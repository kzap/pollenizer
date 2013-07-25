<?php

session_set_cookie_params ( time()+9999999 , "/" , ".shakedownsports.com" );
session_start();
header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');      //IE6 Cookies

$m_time = explode(" ",microtime());
$m_time = $m_time[0] + $m_time[1];
$loadstart = $m_time;					//this is used for timerx.
$loadsubsequent = $m_time;

//Make sure it was called inside a file.
if ($x_IN_FILE != 1)
  exit;

$mySQL_Address = '10.176.195.151';
$mySQL_User = "sd_sports";
$mySQL_Password = "DFBNUh876f%^Tb0utygff";
$databaseName = "shakedownsports";

//Connect to MySQL and database
$databaseConnection = mysql_connect($mySQL_Address,$mySQL_User,$mySQL_Password) or die ("Unable to connect to MySQL server.");
$db = mysql_select_db($databaseName) or die ("Unable to select requested database.");

//AWS access info  
$aws_accesskey = 'AKIAJHEYKC2YS3RNM5NQ';
$aws_secretkey = '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';

//Variables

$typepad_key = '52616e646f6d49563d2f9c3dfb74f2c663e7f1892d25bea6578d446775d78e560ac79f8c984180e29dfbd9bdebb382f2';
$i_state_ID=3;
$currentDate = date("Y-m-d");
$currentTime = date("H:i:s");
$base_name = 'ShakedownSports.com';
$base_url = 'http://www.shakedownsports.com/';
$main_email = 'sol@shakedownsports.com';
$fullreply = "From: \"ShakedownSports.com\" <sol@shakedownsports.com>" . "\n" . 'X-Mailer: PHP/' . phpversion() . "\n" . "Return-Path: bounce@shakedownsports.com\n\n";
$noreply = "From: \"ShakedownSports.com\" <noreply@shakedownsports.com>" . "\n" . 'X-Mailer: PHP/' . phpversion() . "\n" . "Return-Path: bounce@shakedownsports.com\n\n";
$currentDate = date("Y-m-d");

$main_name = 'ShakedownSports.com';
$main_url = 'http://www.shakedownsports.com/';
$main_domain = 'ShakedownSports.com';
$just_url = 'shakedownsports.com';

$pwd = '/home/shakedownsports.com/public_html/';


//===================== end vars.

$robots['google'] = 'google';
$robots['yahoo'] = 'slurp';
$robots['msn'] = 'msnbot';
$robots['ask'] = 'teoma';

$sport['nba'] = 'NBA';
$sport['nfl'] = 'NFL';
$sport['mlb'] = 'MLB';
$sport['nhl'] = 'NHL';
$sport['nascar'] = 'NASCAR';

$sports['nba'] = 'NBA';
$sports['nfl'] = 'NFL';
$sports['mlb'] = 'MLB';
$sports['nhl'] = 'NHL';
$sports['national basketball association'] = 'NBA';
$sports['national football league'] = 'NFL';
$sports['major league baseball'] = 'MLB';
$sports['national hockey league'] = 'NHL';

$sports['american league'] = 'MLB';
$sports['american league central'] = 'MLB';
$sports['american league east'] = 'MLB';
$sports['american league west'] = 'MLB';

$sports['national league'] = 'MLB';
$sports['national league central'] = 'MLB';
$sports['national league east'] = 'MLB';
$sports['national league west'] = 'MLB';

$sports['american hockey league'] = 'NHL';
$sports['western hockey league'] = 'NHL';
$sports['eastern hockey league'] = 'NHL';

//National Hockey League = wtf?
$sports['central division'] = 'MLB';

$sports['afc south'] = 'NFL';
$sports['afc east'] = 'NFL';
$sports['afc north'] = 'NFL';
$sports['afc west'] = 'NFL';

$sports['nfc south'] = 'NFL';
$sports['nfc east'] = 'NFL';
$sports['nfc north'] = 'NFL';
$sports['nfc west'] = 'NFL';

$sports['southwest division'] = 'NBA';

	
	
	
	$ignorelist['entertainment'] = 1;
	$ignorelist['blogging'] = 1;
	$ignorelist['music'] = 1;
	$ignorelist['movies'] = 1;
	$ignorelist['news'] = 1;
	$ignorelist['sporting news'] = 1;
	$ignorelist['philippines'] = 1;
	$ignorelist['personal'] = 1;
	$ignorelist['politics'] = 1;
	$ignorelist['sports'] = 1;
	$ignorelist['technology'] = 1;
	$ignorelist['internet'] = 1;
	$ignorelist['travel'] = 1;
	$ignorelist['blog'] = 1;
	$ignorelist['movie'] = 1;
	$ignorelist['health'] = 1;
	$ignorelist['video'] = 1;
	$ignorelist['videos'] = 1;
	$ignorelist['humor'] = 1;
	$ignorelist['business'] = 1;
	$ignorelist['column'] = 1;
	$ignorelist['contest'] = 1;
	$ignorelist['features'] = 1;
	$ignorelist['frontpage'] = 1;
	$ignorelist['history'] = 1;
	$ignorelist['main'] = 1;
	$ignorelist['opinions'] = 1;
	$ignorelist['sporting news'] = 1;
	$ignorelist['youtube'] = 1;
	$ignorelist['uncategorized'] = 1;
	$ignorelist['general'] = 1;
	$ignorelist['front page'] = 1;
	$ignorelist['bloglockers'] = 1;	//used by some specific sports network.
	$ignorelist['top'] = 1;
	$ignorelist['steppin up'] = 1;
	$ignorelist['the draft report'] = 1;
	$ignorelist['blast from the past'] = 1;
	$ignorelist['editorial'] = 1;
	$ignorelist['must-see sports stories'] = 1;
	$ignorelist['opinion'] = 1;
	$ignorelist['links'] = 1;
	$ignorelist['featured'] = 1;
	$ignorelist['breaking news'] = 1;
	$ignorelist['latest news'] = 1;
	$ignorelist['all'] = 1;
	$ignorelist['need to step up'] = 1;
	$ignorelist['needs to step up'] = 1;
	$ignorelist['sports impact'] = 1;
	$ignorelist['blogs'] = 1;




$robots['google'] = 'google';
$robots['yahoo'] = 'slurp';
$robots['msn'] = 'msnbot';
$robots['ask'] = 'teoma';



//Check if user is logged in fine.
if ($_SESSION['loggedin'] == 1) {			//make sure login is okay.

	$loggedin = 1;
	
	$email = quote_smart($_SESSION['email']);
	if (strlen($email) < 1) 
		$loggedin = 0;
	
	$sql_query = "SELECT email, user_ID, pw, salt FROM `user` WHERE email='$email'";
	$results = mysql_query($sql_query);
	if (!mysql_num_rows($results))
		$loggedin = 0;
	
	$row = mysql_fetch_assoc($results);
	if ($row['pw'] != $_SESSION['pw'])
		$loggedin = 0;
	
	$_SESSION['user_ID'] = (int)$_SESSION['user_ID'];	//just in case.
} else {
	
	if ($_COOKIE['u'] != '') {
		$u = base64_decode($_COOKIE['u']);
		$p = base64_decode($_COOKIE['p']);
		
		$email = quote_smart($u);
		$sql_query = "SELECT email, user_ID, pw, salt FROM `user` WHERE email='$email'";
		$results = mysql_query($sql_query);
		if (mysql_num_rows($results)) {
			$row = mysql_fetch_assoc($results);
			if ($row['pw'] == $p) {
				$_SESSION['pw'] = $p;
				$_SESSION['user_ID'] = $row['user_ID'];
				$_SESSION['email'] = $u;
				$_SESSION['loggedin'] = 1;
				$loggedin = 1;
			}
		}
	}
}

//Get rid of all magic quotes
if (get_magic_quotes_gpc())
{
	if (!empty($_GET))    { $_GET    = strip_magic_quotes($_GET);    }
	if (!empty($_POST))   { $_POST   = strip_magic_quotes($_POST);   }
	if (!empty($_COOKIE)) { $_COOKIE = strip_magic_quotes($_COOKIE); }
}






function registerHTML($error='') {

	global $redirect;
	
	if (strlen($error) > 1)
		echo '<ul>' . $error . '</ul><br />';
	
	echo '<form method="post" action="/register.php" style="display:inline;">';
	echo '<input type="hidden" name="action" value="add2" />';
	echo '<input type="hidden" name="redir" value="'.$redirect.'">';
	echo '<table cellspacing="5">';
	echo '<tr><td style="padding:5px;"><b>Username: </b></td><td><input style="width: 200px;padding:5px;" type="text" name="username" value="'.$_POST['username'].'" /></td></tr>';
	echo '<tr><td style="padding:5px;"><b>Email: </b></td><td><input style="width: 200px;padding:5px;" type="text" name="email" value="'.$_POST['email'].'" /></td></tr>';
	echo '<tr><td style="padding:5px;"><b>Password: </b></td><td><input style="width: 200px;padding:5px;" type="password" name="pwx" value=""/></td></tr>';
	echo '<tr><td></td><td><input type="submit" style="padding:5px;" value="Register &raquo;"></td></tr>';
	echo '</table>';
	echo '</form>';

	echo '<br /><br />We made the registration form as simple as humanly possible.';
	
}

function get_salt($len = 3) {
        $str = null;
        for ($i=1; $i<=$len; $i++) $str .= chr(rand(1,2) == 1 ? rand(97,122) : rand(65,90));
        return $str;
}


function validate_email($email) {
        // Create the syntactical validation regular expression
        $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";

        // Validate the syntax
        if (eregi($regexp, $email))
                return 1;

        return 0;
}


function loginHTML($error='',$message='') {
	
	global $redirect;
	
	echo $message;

	if (strlen($error) > 1)
		echo '<ul>' . $error . '</ul><br />';
	
	echo '<form method="post" action="/login.php" style="display:inline;">';
	echo '<input type="hidden" name="action" value="l2">';
	echo '<input type="hidden" name="redir" value="'.$redirect.'">';
	echo '<table cellspacing="5">';
	echo '<tr><td style="padding:5px;"><b>Username or Email: </b></td><td><input style="width: 200px;padding:5px;" type="text" name="email" value="'.$_POST['email'].'" /></td></tr>';
	echo '<tr><td style="padding:5px;"><b>Password: </b></td><td><input style="width: 200px;padding:5px;" type="password" name="pwx" value="" /></td></tr>';
	echo '<tr><td style="padding:5px;"></td><td><input style="padding:5px;" type="submit" value="Login &raquo;" /></td></tr>';
	echo '<tr><td colspan="2"><br /></td></tr>';
	echo '<tr><td colspan="2"><small><a href="/forgot.php">Forgot your password?</a></small></td></tr>';
	echo '<tr><td colspan="2"><small><a href="/register.php?redir='.$redirect.'">Register a new Account</a></small></td></tr>';
	echo '</table>';
	echo '</form>';
}


function quickRedirect($url,$message='') {
	
	global $main_url,$main_name;
	
	if ($url == 'http://' || $url == 'http:///') {
		$url = $main_url;
	}
	
	if ($message == '')
		$message = '<p>You are being redirected.</p>';
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?PHP echo $main_name; ?> Redirect</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><link rel="stylesheet" type="text/css" href="/compositio/style.css" />

<meta http-equiv="refresh" content="1;URL=<?PHP echo $url; ?>">
</head><body>
<br /><br /><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><center><h1>ShakedownSports.com</h1><br /><br /><?PHP echo $message; ?><br /><p><a href="<?PHP echo $url; ?>">Please click here if you do not wish to wait</a></p></center>
</body></html>
<?PHP
}






function getIP() {
	if (getenv('HTTP_CLIENT_IP')) {
	$ip = getenv('HTTP_CLIENT_IP');
	}
	elseif (getenv('HTTP_X_FORWARDED_FOR')) {
	$ip = getenv('HTTP_X_FORWARDED_FOR');
	}
	elseif (getenv('HTTP_X_FORWARDED')) {
	$ip = getenv('HTTP_X_FORWARDED');
	}
	elseif (getenv('HTTP_FORWARDED_FOR')) {
	$ip = getenv('HTTP_FORWARDED_FOR');
	}
	elseif (getenv('HTTP_FORWARDED')) {
	$ip = getenv('HTTP_FORWARDED');
	}
	else {
	$ip = $_SERVER['REMOTE_ADDR'];
	}
	
	$ip = ip2long($ip);
	
	return $ip;
}

// Quote variable to make safe
function quote_smart($value) {
	
	$value = trim($value);
	
   // Assumes Stripslashes - we utf-8 it
   return htmlspecialchars($value,ENT_QUOTES, 'UTF-8',false);

}

//Used to go from utf-8 ---> simple.
function quote_decode ($str) {
   $x = strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES)));
   return str_replace('&#039;',"'",$x);
}


//Does not allow the browser to cache anything
function noCache()
{
	header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');      //IE6 Cookies
	header("Expires: Sun 10 Feb 1983 05:00:00 GMT");                     // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");        // always modified
	header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");                          // HTTP/1.0
}



//Quick code to print out the values and keys of an array
function printArray($arr,$arrayName="")
{
	
	if ($arrayName != "") {
		echo "<br /> PRINTING ARRAY --[$arrayName]: <br />";
	}
	else {
		echo '<br /> PRINTING ARRAY: <br />';
	}
		
	foreach($arr as $k => $v) {
	    echo '[\'' . $k . '\'] => .' . $v . '.<br />' . "\n";
	}

	if ($arrayName != "") {
		echo "END ARRAY --[$arrayName] <br />";
	}
	else {
		echo 'END ARRAY <br />';
	}
}

//Querys the database with a string - used to update databse [does NOT return values]
function queryDatabase($sql_query)
{
        global $mysqlerror;

        if (@mysql_query($sql_query)) {
                //Successfull - do not display anything
        }
        else {
			echo 'Error occured with the database.';
			if ($mysqlerror) {
				echo "<br><br>\n\n Query: $sql_query <br><br>";
				echo "\nError:".mysql_error();
				echo "\n\n";
			}
        }
}

function addSlashesArray($arr)
{

	foreach ($arr as $k => $v)
	{

		if (is_array($v)) {
			$arr[$k] = addSlashesArray($v);
		}
		else {
			$arr[$k] = addslashes($v);
		}
	}
	return $arr;
}


//This gets rid of the damn 'magic quotes'
function strip_magic_quotes($arr)
{
	foreach ($arr as $k => $v)
	{
		if (is_array($v)) {
			$arr[$k] = strip_magic_quotes($v);
		}
		else {
			$arr[$k] = stripslashes($v);
		}
	}
	return $arr;
}




function htmlheader3($title, $h1, $breadcrumb='',$meta='',$showad=1, $cat='', $url='/') {
	
	global $loggedin;
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?PHP echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?PHP if ($meta != '') echo $meta; ?>
<link rel="stylesheet" type="text/css" href="/compositio/style.css" />
<script type="text/javascript" src="/compositio/javascript/tabs.js"> </script>
<link rel="shortcut icon" href="/compositio/favicon.ico" type="image/x-icon" />
<link rel='index' title='Shakedown Sports' href='http://www.shakedownsports.com' />
</head>
<body>

<!-- Start BG -->
<div id="bg">

<div style="width:980px;text-align:right;margin-bottom:10px;">
	<?PHP
	echo '<b>';
	if ($loggedin) {
		echo '<a href="/user.php">My User CP</a> | <a href="/logout.php">Logout</a>';
	} else {
		echo '<a href="/register.php">Register</a> | <a href="/login.php">Login</a>';
	}
	echo '</b>';
	?>
</div>

<div id="bg-all">


<div class="menu">
   <ul>

	<li class="<?PHP if ($cat == 'home') { echo 'current_page_item'; } else { echo 'page_item'; } ?> page-item-1"><a href="/"><span>Shakedown Sports Home</span></a></li>
	<li class="<?PHP if ($cat == 'nfl') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-2"><a href="/nfl/" title="NFL News &amp; Rumors"><span>NFL</span></a></li>
	<li class="<?PHP if ($cat == 'mlb') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-3"><a href="/mlb/" title="MLB News &amp; Rumors"><span>MLB</span></a></li>
	<li class="<?PHP if ($cat == 'nba') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-4"><a href="/nba/" title="NBA News &amp; Rumors"><span>NBA</span></a></li>
	<li class="<?PHP if ($cat == 'nhl') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-5"><a href="/nhl/" title="NHL News &amp; Rumors"><span>NHL</span></a></li>
	<li class="<?PHP if ($cat == 'nascar') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-6"><a href="/nascar/" title="NASCAR News &amp; Rumors"><span>NASCAR</span></a></li>
	<li class="<?PHP if ($cat == 'buzz') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-4"><a href="/buzz/" title="Latest Trending Topics"><span>Buzz</span></a></li>
	<li class="<?PHP if ($cat == 'sources') { echo 'current_page_item'; } else { echo 'page_item'; } ?>  page-item-4"><a href="/sources/" title="Sports Sources"><span>Sources</span></a></li>
  </ul>

  
</div>
 

<!-- Start Container -->
<div class="container">

<!-- Start Header -->
<?PHP if ($cat == 'home') { echo '<div class="logo2">'; } else { echo '<div class="logo">'; } ?>
 <div class="txt">
 

 
 <h1><a href="<?PHP echo $url; ?>"><?PHP echo $h1; ?></a></h1>

 <?PHP 
 switch ($cat) {
	case 'sources':
	case 'buzz':
 	case 'story':
	case 'empty':
 		break;
 	case 'home':
 		echo '<p class="desc">Your ticket to the latest sports news &amp; rumors from across the web</p>';
 		break;
 	default:
 		echo '<p class="desc">Your ticket to the latest '.$h1.' News &amp; Rumors</p>';
 		break;
 }
?>
</div></div>
<!-- END Header -->

<?PHP
}




function htmlfooter3($showad='') {
?>





</div></div>
<!-- End BG -->

<div class="footer">
<p class="copy">&copy; 2007-<?PHP echo date("Y"); ?> ShakedownSports.com. <a href="/about/">About Us</a> | <a href="/contact/">Contact Us</a><br />
<div style="font-size: 10px; margin-top: 10px;">
Some information from <a href="http://www.freebase.com/" rel="nofollow">Freebase</a>, licensed under <a href="http://creativecommons.org/licenses/by/2.5/" rel="nofollow">CC-BY</a>.
</div>
</p> 
</div>


<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-9422959-7");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>


<?PHP	
	
}




function relatedItems($val,$altword='') {

	$ts = time() - (86400*5);
	$ts_check = time() - (60 * 60);
	$d = date("Y-m-d",$ts);
	
	//$sql_query = "TRUNCATE TABLE `feed_related` ";
	//queryDatabase($sql_query);

	$sql_query = "SELECT txt FROM `feed_related` WHERE val='$val' AND ts > $ts_check";		//Let it get cached for 60 minutes.
	$results = mysql_query($sql_query);
	
	if (mysql_num_rows($results)) {
		$row = mysql_fetch_assoc($results);
		$array = unserialize($row['txt']);
		return $array;
	}

	
	$alt = '';
	if ($altword != '')
		$alt = "OR a.category = '$altword'";
	
	$sql_query_related = "SELECT b.uuid,b.category FROM `feed_index_categories` a, `feed_index_categories` b WHERE (a.category = '$val' $alt) AND a.d >='$d' AND a.uuid=b.uuid";
	
	
	//if ($_SESSION['user_ID'] == 14130) {
	//	echo $sql_query_related;
	//}
	
	
	$resultsX = mysql_query($sql_query_related);
	$x = 0;
	$kinds = 0;
	
	
	if (mysql_num_rows($resultsX) == 0) {	//go further back in time - 1.5 months
		$ts = time() - (86400 * 45);
		$d = date("Y-m-d",$ts);
		$sql_query_related = "SELECT b.uuid,b.category FROM `feed_index_categories` a, `feed_index_categories` b WHERE (a.category = '$val' $alt) AND a.d >='$d' AND a.uuid=b.uuid";
		$resultsX = mysql_query($sql_query_related);
	}

	
	$mentions = Array();
	$mentions_uuid = Array();
	
	while ($rowX = mysql_fetch_assoc($resultsX)) {
	
		$cat = $rowX['category'];
		

		$sql_queryZ = "SELECT sportsleague,name FROM `meta_data` WHERE name='$cat' AND is_team=0";
		//echo $sql_queryZ. '<br>';
		$resultsZ = mysql_query($sql_queryZ);
		if (mysql_num_rows($resultsZ)) {
			$rowZ = mysql_fetch_assoc($resultsZ);
					
			if (strtolower($rowZ['sportsleague']) == strtolower($val)) {
				$cat = $rowZ['name'];
				$mentions[$cat]++;
				$mentions_uuid[$cat][] = $rowX['uuid'];
			}
		}
	}
	
	/*
	if ($_SESSION['user_ID'] == 14130) {
		arsort($mentions);
		printArray($mentions);
		$x = 0;
		foreach ($mentions as $k=>$v) {
			$x++;
			if ($x < 10) {
				echo '<b>'.$k.'</b>';
				foreach ($mentions_uuid[$k] as $k2=>$v2) {
					echo '<a href="/visitstory/'.$v2.'">'.$v2.'</a><br />';
				}
			}
		}
	}
	*/

	foreach ($mentions as $k=>$v) {

		$valx = $k;

		unset($sources);
		$sources = Array();
		$uuids = '';
		foreach ($mentions_uuid[$valx] as $k2=>$v2) {
			$uuids .= "'$v2', ";
		}
		$uuids = substr($uuids,0,-2);

		$sql_queryQ = "SELECT weblog_ID FROM `feed_index_site` WHERE uuid IN ($uuids)";
		$resultsQ = mysql_query($sql_queryQ);
		while ($rowQ = mysql_fetch_assoc($resultsQ)) {
			$source_ID = $rowQ['weblog_ID'];
			$sources[$source_ID]++;
		}

		
		$num_count = count($sources);
		//echo "$k was mentioned in $num_count posts<br>";
		
		$unique_mentions[$k] = $num_count;
	}

	arsort($unique_mentions);
	
	//if ($_SESSION['user_ID'] == 14130) {
	//	printArray($unique_mentions);
	//}
	
	$unique_mentions = array_slice($unique_mentions, 0, 5,true);	//get only teh top results.
	
	$ts = time();
	$txt = addslashes(serialize($unique_mentions));
	$sql_query = "INSERT INTO `feed_related` (val,ts,txt) VALUES ('$val','$ts','$txt') ON DUPLICATE KEY UPDATE ts=$ts,txt='$txt'";
	queryDatabase($sql_query);
	
	
	return $unique_mentions;
}



function timerx() {

	global $loadstart, $loadsubsequent;
	
	$m_time = explode(" ",microtime());
	$m_time = $m_time[0] + $m_time[1];
	$loadend = $m_time;
	$loadtotal = ($loadend - $loadstart);
	$loadsub = $loadend - $loadsubsequent;
	echo "<p><small><em>Generated here in ". round($loadtotal,3) ." s from start, ".round($loadsub,3)."s subsequently</em></small></p>";

	$loadsubsequent = $m_time;
}


function getData($name,$num=0,$possible_id='',$force_update=0,$debug=0) {

	$num++;
	if ($num > 3)
		return 0;

	$mysqlerror = 1;

	$name = to7bit($name,"UTF-8");
	
	$n_original = $name;
	
	$name = quote_smart($name);
	
	$sql_query = "SELECT sportsleague,freebase_id,meta_ID,needs_update FROM `meta_data` WHERE name LIKE '$name'";
	$results = mysql_query($sql_query);
	$row = mysql_fetch_assoc($results);
	
	
	if ($force_update != 1) {
		if ($row['meta_ID'] > 0 && $row['needs_update'] == 0)	//this means it doesnt need an update.
			return 0;
	}
	
	$name = quote_decode($name);
	
	
	$meta_ID = (int)$row['meta_ID'];

	$name = trim($name);
	$name = strtolower($name);
	$name = str_replace(' ','_',$name);
	
	if ($possible_id != '')
		$name = $possible_id;
	
	if ($row['freebase_id'] != '')
		$name = $row['freebase_id'];
	
	$ts = time();
	
	

	$string = '[{ "id": "/en/'.$name.'", "/people/person/nationality": [], "/people/person/date_of_birth": null, "/people/person/gender": null, "/people/person/height_meters": null, "/people/person/weight_kg": null, "/basketball/basketball_player/team": { "team": { "name": null }, "number": null, "position": { }, "optional": true }, "/ice_hockey/hockey_player/current_team": { "team": { "name": null }, "number": null, "position": { }, "optional": true }, "/baseball/baseball_player/current_team": { "team": { "name": null }, "number": null, "position": { }, "optional": true }, "/american_football/football_player/current_team": { "team": { "name": null }, "number": null, "position": { }, "optional": true }, "/sports/pro_athlete/career_start": null, "/sports/pro_athlete/career_end": null, "/sports/sports_award_winner/awards": [{ "award": { "name": null }, "season": { "name": null }, "optional": true }], "/common/topic/article": [{ "id": null, "optional": true }], "/common/topic/image": [{ "id": null, "optional": true }], "type": [], "/basketball/basketball_team/roster": [{ "player": { }, "optional": true }], "/ice_hockey/hockey_team/current_roster": [{ "player": { }, "optional": true }], "/baseball/baseball_team/current_roster": [{ "player": { }, "optional": true }], "/american_football/football_team/current_roster": [{ "player": { }, "optional": true }], "name": null }]';
	
	if ($debug)
		echo "<BR><BR><BR>$string<BR><BR><BR>";
	
	$simplequery = json_decode($string);

	$queryarray = array('q1'=>array('query'=>$simplequery));
	$jsonquerystr = json_encode($queryarray);

	$apiendpoint = "http://api.freebase.com/api/service/mqlread?queries";
	$ch = curl_init();
	//curl_setopt($ch, CURLOPT_HTTPHEADER, 'Content-type: application/x-www-form-urlencoded;charset=UTF-8'); 
	curl_setopt($ch, CURLOPT_URL, "$apiendpoint=$jsonquerystr");
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$jsonresultstr = curl_exec($ch);
	curl_close($ch); 

	$resultarray = json_decode($jsonresultstr, true);

	$data = $resultarray['q1']['result'][0];


	$sport = '';
	$is_team = 0;

	if (is_array($data['type'])) {
		if (in_array('/sports/sports_team',$data['type'])) {
			
			//Now we see what kind of team
			if (in_array('/basketball/basketball_team',$data['type'])) {
				$is_team = 1;
				$sport = 'basketball';
			}
			if (in_array('/baseball/baseball_team',$data['type'])) {
				$is_team = 1;
				$sport = 'baseball';
			}
			if (in_array('/ice_hockey/hockey_team',$data['type'])) {
				$is_team = 1;
				$sport = 'hockey';
			}
			if (in_array('/american_football/football_team',$data['type'])) {
				$is_team = 1;
				$sport = 'football';
			}
			
		}
	}
	
	if ($sport == '') {
		if (is_array($data['/basketball/basketball_player/team'])) {
			$sport = 'basketball';
		}
		if (is_array($data['/ice_hockey/hockey_player/current_team'])) {
			$sport = 'hockey';
		}
		if (is_array($data['/baseball/baseball_player/current_team'])) {
			$sport = 'baseball';
		}
		if (is_array($data['/american_football/football_player/current_team'])) {
			$sport = 'football';
		}
	}

	
	echo " plays: $sport [is team: $is_team] ";

	if ($sport == '') {
	
		//try alternative?
		$n2 = $n_original;
		$n2 = str_replace("'",'',$n2);
		$n2 = str_replace('.',' ',$n2);
		$n2 = str_replace('  ',' ',$n2);
		$n2 = str_replace('  ',' ',$n2);
		$n2 = str_replace(' ','_',$n2);
		$n2 = strtolower($n2);
		
		if ($n2 != $name) {
			echo "FAILED. Trying $n2 vs $name ... ";
			$z = getData($n_original,$num,$n2,$force_update);
		}
		
		if ($z == -1) {
			$n_original = addslashes($n_original);
			$sql_query = "SELECT nmeta_ID FROM `meta_data_fail` WHERE name='$n_original'";
			$results = mysql_query($sql_query);
			$ts = time();
			if (mysql_num_rows($results) == 0) {
				$sql_query = "INSERT INTO `meta_data_fail` SET name='$n_original',ts=$ts";
			} else {
				$row = mysql_fetch_assoc($results);
				$sql_query = "UPDATE `meta_data_fail` ts=$ts WHERE nmeta_ID=$row[nmeta_ID]";
			}
			queryDatabase($sql_query);
		}
		
		return -1;
	}

		
	$sportstrings['basketball'] = '/basketball/basketball_player/team';
	$sportstrings['baseball'] = '/baseball/baseball_player/current_team';
	$sportstrings['football'] = '/american_football/football_player/current_team';
	$sportstrings['hockey'] = '/ice_hockey/hockey_player/current_team';

	$sport_string = $sportstrings[$sport];

	if ($is_team) {

		
		$sport_string = str_replace('team','roster',$sport_string);
		$sport_string = str_replace('player','team',$sport_string);
		
		echo "\n\n$name IS TEAM. Doing Roster ... \n";
		
		$rosters = Array();
	
		foreach ($data[$sport_string] as $k=>$v) {
			$n = $v['player']['name'];

			$n = to7bit($n,"UTF-8");
			echo "Doing Player $n ... ";
			$result = getData($n,$num,'',$force_update);
			switch ($result) {
				case -1:
					echo '[could not locate]';
					break;
				case 0:
					echo '[already updated]';
					$rosters[] = $n;
					break;
				case 1:
					echo '[new]';
					$rosters[] = $n;
					break;
			}
			echo "\n";
		}
		
		echo "\n\n DONE ROSTER \n\n";
		
		$rosters = serialize($rosters);
		$rosters = quote_smart($rosters);
		
		$name = $data['name'];
		$name = to7bit($name,"UTF-8");
		$name = quote_smart($name);
		$freebase_id = str_replace('/en/','',$data['id']);
		$freebase_id = quote_smart($freebase_id);

		$images = Array();
		if (is_array($data['/common/topic/image'])) {
			foreach ($data['/common/topic/image'] as $k=>$v) {
				$aguid = md5($v['id']);
				
				$tempfn = tempnam('','');
				
				$remote_file = 'http://www.freebase.com/api/trans/raw'.$v['id'];
				$x = copy($remote_file,$tempfn);
				$done = resampleImageFeedx($tempfn,$tempfn);

				if ($done != '') {
					global $s3;
					$filename = 'fb_images/'.$aguid.'.'.$done;	//The $done value we get back from resample IS the extension!
					$s3->putObjectFile($tempfn, "shakedownsports.com", $filename, S3::ACL_PUBLIC_READ);	//Put it into S3
				}
				unlink($tempfn);
					
				$images[] = $aguid.'.'.$done;
				
			}
			$img = serialize($images);
		}
		
		if (strlen($data['/common/topic/article'][0]['id']) > 1) {
			$desc_url = 'http://www.freebase.com/api/trans/raw/'.$data['/common/topic/article'][0]['id'];
			$desc = file_get_contents($desc_url);
			$desc = quote_smart($desc);
		}

		global $sport;
		foreach ($sport as $k=>$v) {
			$x = strpos(strtolower($desc),strtolower($v));
			if ($x > 0)
				$this_sport= $k;
		}
		
		
		if ($meta_ID > 0) {
			$sql_query = "UPDATE `meta_data` SET sportsleague='$this_sport',freebase_id='$freebase_id',sport='$sport',is_team=1,freebase_images='$img',freebase_desc='$desc',name='$name',ts=$ts,needs_update=0,roster='$rosters' WHERE meta_ID=$meta_ID";
		} else {
			$sql_query = "INSERT INTO `meta_data` SET sportsleague='$this_sport',freebase_id='$freebase_id',sport='$sport',is_team=1,freebase_images='$img',freebase_desc='$desc',name='$name',ts=$ts,needs_update=0,roster='$rosters'";
		}
		echo "......................$sql_query...................";
		queryDatabase($sql_query);
		
		return 1;

	} else {

		$name = $data['name'];
		echo "...$name...";
		$name = to7bit($name,"UTF-8");
		echo "...$name...";
		$name = quote_smart($name);
		$nationality = quote_smart($data['/people/person/nationality'][0]);
		$dob = quote_smart($data['/people/person/date_of_birth']);
		$gender = $data['/people/person/gender'];
		$gender = substr($gender,0,1);
		$height = (float)$data['/people/person/height_meters'];
		$weight = (float)$data['/people/person/weight_kg'];
		$team_name = quote_smart($data[$sport_string]['team']['name']);
		$team_number = quote_smart($data[$sport_string]['number']);
		$team_position = quote_smart($data[$sport_string]['position']['name']);
		$career_start = (int)$data['/sports/pro_athlete/career_start'];
		$career_end = (int)$data['/sports/pro_athlete/career_end'];
		$freebase_id = str_replace('/en/','',$data['id']);
		$freebase_id = quote_smart($freebase_id);

		$images = Array();
		if (is_array($data['/common/topic/image'])) {
			foreach ($data['/common/topic/image'] as $k=>$v) {
				$aguid = md5($v['id']);
				
				$tempfn = tempnam('','');
				
				$remote_file = 'http://www.freebase.com/api/trans/raw'.$v['id'];
				$x = copy($remote_file,$tempfn);
				$done = resampleImageFeedx($tempfn,$tempfn);

				if ($done != '') {
					global $s3;
					$filename = 'fb_images/'.$aguid.'.'.$done;	//The $done value we get back from resample IS the extension!
					$s3->putObjectFile($tempfn, "shakedownsports.com", $filename, S3::ACL_PUBLIC_READ);	//Put it into S3
				}
				unlink($tempfn);
					
				$images[] = $aguid.'.'.$done;
				
			}
			$img = serialize($images);
		}
		
		if (strlen($data['/common/topic/article'][0]['id']) > 1) {
			$desc_url = 'http://www.freebase.com/api/trans/raw/'.$data['/common/topic/article'][0]['id'];
			$desc = file_get_contents($desc_url);
			$desc = quote_smart($desc);
		}

		global $sport;
		foreach ($sport as $k=>$v) {
			$x = strpos(strtolower($desc),strtolower($v));
			if ($x > 0)
				$this_sport= $k;
		}		
		
		if ($meta_ID > 0) {
			$sql_query = "UPDATE `meta_data` SET sportsleague='$this_sport',freebase_id='$freebase_id',sport='$sport',name='$name',nationality='$nationality',dob='$dob',gender='$gender',height = $height,weight = $weight,team_name = '$team_name',team_number = '$team_number',team_position='$team_position',career_start=$career_start,career_end=$career_end,freebase_images='$img',freebase_desc='$desc',ts=$ts,needs_update=0 WHERE meta_ID=$meta_ID";
		} else {
			$sql_query = "INSERT INTO `meta_data` SET sportsleague='$this_sport',freebase_id='$freebase_id',sport='$sport',name='$name',nationality='$nationality',dob='$dob',gender='$gender',height = $height,weight = $weight,team_name = '$team_name',team_number = '$team_number',team_position='$team_position',career_start=$career_start,career_end=$career_end,freebase_images='$img',freebase_desc='$desc',ts=$ts";
		}
		queryDatabase($sql_query);
		return 1;
	}



}

function resampleImageFeedx($src, $dst)
{
	list($width, $height, $type) = getimagesize($src);
	
	if (!$src = imagecreatefromstring(file_get_contents($src)))
		return '';

	$fixed_width = 120;
	$fixed_height = 90;
	$ratioFixed = $fixed_width/$fixed_height;
	
	$min_width = 0.75 * $fixed_width;
	$min_height = 0.75 * $fixed_height;
	
	if ($width < $min_width OR $height < $min_height) {
		return '';
	}
	
	$new_height = $fixed_height;
	$new_width = $fixed_width;
	if ($width > $fixed_width || $height > $fixed_height) {
		$ratio = $width / $height;
		if ($ratio > $ratioFixed) {
			$x = $width / $fixed_width;
			$new_width = $fixed_width;
			$new_height = $height / $x;
		} else {
			$x = $height / $fixed_height;
			$new_height = $fixed_height;
			$new_width = $width / $x;
		}
	}
	
	$new_height = (int)$new_height;
	$new_width = (int)$new_width;
	
	//The ratio part above is currently NOT used.
	$new_height = $fixed_height;
	$new_width = $fixed_width;
	
	if (!$img = imagecreatetruecolor($new_width, $new_height))
		return '';
		
	if (!imagecopyresampled($img, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height))
		return '';

	ob_start();
	switch ($type)
	{
		case 1:
			$ext = 'gif';
			if (!imagegif($img))
				return '';
			break;
		case 2:
			$ext = 'jpg';
			if (!imagejpeg($img))
				return '';
			break;
		case 3:
			$ext = 'png';
			if (!imagepng($img))
				return '';
			break;
		default:
			return '';
	}		

	$filedata = ob_get_clean();

	if (!$fp = fopen($dst, 'w'))
		return '';
	if (!fwrite($fp, $filedata))
		return '';
	fclose($fp);

	return $ext;
}

function to7bit($text,$from_enc) {
    
	$text = mb_convert_encoding($text,'HTML-ENTITIES',$from_enc);
	//Two manual ones - 
	$text = str_replace('&#381;','Z',$text);
	$text = str_replace('&#363;','u',$text);
    
	$text = preg_replace(
        array('/&szlig;/','/&(..)lig;/',
             '/&([aouAOU])uml;/','/&(.)[^;]*;/'),
        array('ss',"$1","$1".'e',"$1"),
        $text);
    return $text;
} 

function deleteSite($id,$admin=0) {
	
	$sql_query = "SELECT user_ID,url FROM `weblog` WHERE weblog_ID=$id";
	$results = mysql_query($sql_query);
	$row = mysql_fetch_assoc($results);
	
	if ($admin) {
		$url = $row['url'];
		$ts = time();
		
		if (strlen($url) > 1) {
			$sql_query = "INSERT INTO `rejected` SET val='$url', ts=$ts";
			queryDatabase($sql_query);
		}
		
		$sql_queryX = "SELECT email FROM `user` WHERE user_ID=$row[user_ID]";
		$resultsX = mysql_query($sql_queryX);
		
		if (mysql_num_rows($resultsX)) {
			$rowX = mysql_fetch_assoc($resultsX);
		}
		
		if (strlen($rowX['email']) > 1) {
			$sql_query = "INSERT INTO `rejected` SET val='$rowX[email]', ts=$ts";
			queryDatabase($sql_query);
		}
	}
	
	//Remove all feed posts.
	$sql_queryX = "SELECT uuid FROM `feed_index_site` WHERE weblog_ID=$id";
	$resultsX = mysql_query($sql_queryX);
	while ($rowX = mysql_fetch_assoc($resultsX)) {
		$uuid = $rowX['uuid'];
		$sql_queryX = "DELETE FROM `feed_datastore` WHERE uuid='$uuid'";
		queryDatabase($sql_queryX);
		$sql_queryX = "DELETE FROM `feed_index_categories` WHERE uuid='$uuid'";
		queryDatabase($sql_queryX);
		$sql_queryX = "DELETE FROM `feed_datastore_posts_v2` WHERE uuid='$uuid'";
		queryDatabase($sql_queryX);
		$sql_queryX = "DELETE FROM `sports_hits_out` WHERE uuid='$uuid'";
		queryDatabase($sql_queryX);
	}
	$sql_queryX = "DELETE FROM `feed_index_site` WHERE weblog_ID=$id";
	queryDatabase($sql_queryX);
	
	//Delete clicks.
	$sql_queryX = "DELETE FROM `sports_hits_out` WHERE source_ID=$id";
	queryDatabase($sql_queryX);

	//Make sure we now delete it!
	$sql_query = "DELETE FROM `weblog` WHERE weblog_ID=$id";
	queryDatabase($sql_query);		
}


function getLinkData($site_ID) {
	
	global $sport;
	
	$sql_query = "SELECT * FROM `weblog` WHERE weblog_ID=$site_ID";
	$results = mysql_query($sql_query);
	$row = mysql_fetch_assoc($results);
	
	if ($row['claimed'] == 1 && $row['linking_to_us'] == 1) {
		$data['url'] = '';
		$data['text'] = '';
		return $data;
	}
	
	srand((double)microtime()*1000000); 
	$num = rand(0,100);
	
	$data['url'] = '';
	$data['text'] = '';
	
	if (strlen($row['url']) < 5) {	//this means the blog was deleted
		$data['url'] = 'http://www.shakedownsports.com/';
		$data['text'] = 'Shakedown Sports News &amp; Rumors';
		return $data;		
	}


	
	$num_teams = 0;
	$num_leagues = 0;
	$num_players = 0;
	
	$team_name = '';
	$player_name = '';
	$league_name = '';
	
	$tags[] = $row['tag1'];
	$tags[] = $row['tag2'];
	$tags[] = $row['tag3'];
	$tags[] = $row['tag4'];
	$tags[] = $row['tag5'];
	$tags[] = $row['tag6'];
	$tags[] = $row['tag7'];
	$tags[] = $row['tag8'];
	$tags[] = $row['tag9'];
	
	foreach ($tags as $k=>$v) {
		if (strlen($v) > 1) {
			$tag = strtolower($v);
			
			if ($sport[$tag] != '') {
				$num_leagues++;
				$league_name = $sport[$tag];
			}
			
			$sql_query = "SELECT name,is_team FROM `meta_data` WHERE name='$tag'";
			$results = mysql_query($sql_query);
			if (mysql_num_rows($results)) {
			
				$row = mysql_fetch_assoc($results);
			
				if ($row['is_team'] == 1) {
					$num_teams++;
					$team_name = $row['name'];
				} else {
					$num_players++;
					$player_name = $row['name'];
				}
			}
		}
	}
	
	
	
	//echo "num teams: $num_teams num leagues: $num_leagues<br>";
	//echo "team: $team_name league: $league_name player: $player_name";
	
	if ($num_teams == 0 && $num_leagues == 0 && $num_players == 0) {
		$data['url'] = 'http://www.shakedownsports.com/';
		$data['text'] = 'Shakedown Sports News &amp; Rumors';
		return $data;
	}
	
	if ($num_teams == 1) {
		$data['url'] = 'http://www.shakedownsports.com/'.urlencode(strtolower(quote_decode($team_name))).'/';
		$data['text'] = $team_name.' Ticket - Sports News &amp; Rumors';
		return $data;
	}

	if ($num_leagues == 1) {
		$data['url'] = 'http://www.shakedownsports.com/'.urlencode(strtolower(quote_decode($league_name))).'/';
		$data['text'] = $league_name.' Ticket - Sports News &amp; Rumors';
		return $data;
	}
	
	if ($num_players == 1) {
		$data['url'] = 'http://www.shakedownsports.com/people/'.urlencode(strtolower(quote_decode($player_name))).'.html';
		$data['text'] = $player_name.' Ticket - Sports News &amp; Rumors';
		return $data;
	}
	
	$data['url'] = 'http://www.shakedownsports.com/';
	$data['text'] = 'Shakedown Sports News &amp; Rumors';
	return $data;
	
}


function blogger($linktext) {
	
	global $main_url, $main_domain, $just_url,$typepad_key;
	
?>
<br />
<form method="post" action="http://www.blogger.com/add-widget" style="display: inline;">
<input type="hidden" name="infoUrl" value="<?PHP echo $main_url; ?>"/>
<input type="hidden" name="logoUrl" value="<?PHP echo $main_url; ?>logo.gif"/>
<input type="hidden" name="widget.title" value="<?PHP echo $main_domain; ?>"/>
<textarea name="widget.content" style="display:none;"><?PHP echo htmlspecialchars($linktext); ?></textarea>
<input type="hidden" name="widget.template" value="&lt;data:content/&gt;" />
<input type="submit" name="go" value="Use Blogger? Click Here" style="font-size: 10px;padding:4px;" />
</form>

<form action="https://www.typepad.com/t/app/weblog/design/widgets" method="post" style="display: inline;">
  <input type="hidden" name="service_key" value="<?PHP echo $typepad_key; ?>" />
  <input type="hidden" name="service_name" value="<?PHP echo $main_domain; ?>" />
  <input type="hidden" name="service_url" value="<?PHP echo $main_url; ?>" />
  <input type="hidden" name="long_name" value="<?PHP echo $main_domain; ?>" />
  <input type="hidden" name="short_name" value="<?PHP echo $just_url; ?>" />
  <input type="hidden" name="content" value="<?PHP echo htmlspecialchars($linktext); ?>" />
  <input type="hidden" name="return_url" value="<?PHP echo $main_url; ?>user.php" />
  <input type="submit" name="submit" value="Use TypePad? Click Here" style="font-size: 10px;padding:4px;" />
</form>


<?PHP
}
