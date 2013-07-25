<?
function errorbox($msg,$redirect='') {

	if (is_array($msg)) {
		$msg = implode('.<br/><img src="' . DIR_IMAGES . 'x.gif" align="absmiddle" border="0"/>',$msg);
	}
	

	$box = '
	<p>
	<table cellspacing="1" cellpadding="0" border="0" width="90%" class="errorbox">
	<tr>
		<td class="errorbox-messages"><img src="' . DIR_IMAGES . 'x.gif" align="absmiddle" border="0"/>'.$msg.'.</td>
	</tr>
	</table>
	</p>
	';

	if ($redirect != '')
		$box .= "<script>function redirectme() { document.location = '".$redirect."'; } setTimeout('redirectme()',1700);</script>";	

	return $box;
}

function successbox($msg,$redirect='') {
	if (is_array($msg)) {
		$msg = implode(".<br/>\n",$msg);
	}

	$box = '
	<p>
	<table cellspacing="1" cellpadding="0" border="0" width="90%" class="successbox">
	<tr>
		<td class="successbox-messages">'.$msg.'.</td>
	</tr>
	</table>
	</p>
	';

	if ($redirect != '')
		$box .= "<script>function redirectme() { document.location = '".$redirect."'; } setTimeout('redirectme()',1700);</script>";	
	
	return $box;
}

?>