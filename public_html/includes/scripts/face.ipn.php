<?php
require_once('../application_top.php');

if (!function_exists('viewArray')) {
	function viewArray($arr) {
	   $output .= '<table cellpadding="0" cellspacing="0" border="1">';
	   foreach ($arr as $key1 => $elem1) {
	       $output .= '<tr>';
	       $output .= '<td>'.$key1.'&nbsp;</td>';
	       if (is_array($elem1)) { $output .= extArray($elem1); }
	       else { $output .= '<td>'.$elem1.'&nbsp;</td>'; }
	       $output .= '</tr>';
	   }
	   $output .= '</table>';
	   return $output;
	}
}
if (!function_exists('extArray')) {
	function extArray($arr) {
	   $output .= '<td>';
	   $output .= '<table cellpadding="0" cellspacing="0" border="1">';
	   foreach ($arr as $key => $elem) {
	       $output .= '<tr>';
	       $output .= '<td>'.$key.'&nbsp;</td>';
	       if (is_array($elem)) { $output .= extArray($elem); }
	       else { $output .= '<td>'.htmlspecialchars($elem).'&nbsp;</td>'; }
	       $output .= '</tr>';
	   }
	   $output .= '</table>';
	   $output .= '</td>';
	   return $output;
	}
}

mail(
	'andre@enthropia.com', 
	'Face.com Callback', 
	'<html><body>'.viewArray($_POST).viewArray($_SERVER)."\r\n".'</body></html>', 
	"MIME-Version: 1.0\n" . 
		"Content-type: text/html; charset=iso-8859-1
	"
);

