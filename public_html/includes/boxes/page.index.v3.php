<?php
include(DIR_BOXES . 'header.v3.php'); 

switch (SITES_ID) {
	case 24:
		echo '<div class="alert alert-info" align="center"><h2>redirecting to </h2><h1><a href="http://rescueph.com">RescuePH.com</a></h1></div>';
		echo '<meta http-equiv="refresh" content="5;URL=\'http://rescueph.com\'">';
		echo '<script type="text/javascript">
		jQuery(document).ready(function($) {
			window.location = "http://rescueph.com";
		});
		</script>';
	break;
}

include(DIR_BOXES . 'footer.v3.php');
