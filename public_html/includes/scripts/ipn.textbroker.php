<?php
include('../application_top.php');

include(DIR_CLASSES . 'class.textbroker.php');
$tb = new textbroker($link, SITES_ID);
$tb->set_budget_id(TB_BUDGET_ID);
$tb->set_budget_key(TB_BUDGET_KEY);
$tb->set_budget_password(TB_BUDGET_PASSWORD);
$tb->set_api_url(TB_API_URL);

if ($_REQUEST['order_id']) {
	$sql = "SELECT t.`id` FROM `textbroker` AS `t` WHERE t.`budget_order_id` = '" . mysql_real_escape_string($_REQUEST['order_id']) . "' LIMIT 1";
	$res = query($sql, $link);
	if ($r = mysql_fetch_assoc($res)) {
		$tb->tb_update_status($r['id']);
	}
}

$response = print_r($tb->success_msgs, 1).print_r($tb->error_msgs, 1).print_r($_REQUEST, 1).print_r($_SERVER, 1);

if ($fp = fopen(DIR_ROOT.'temp/'.basename(__FILE__, '.php').'-'.time().'.txt', 'w')) {
	fwrite($fp, $response);
	fclose($fp);
}
//echo mail('andre@enthropia.com', 'Textbroker IPN', $response);
//pre($response, 1);
?>