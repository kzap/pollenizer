<?
ob_start();

include(DIR_BOXES.'header.email.php');
?>
<?=$box['content'];?>
<?
include(DIR_BOXES.'footer.email.php');

$contents = ob_get_contents();
ob_end_clean();
return str_replace('https://', 'http://', $contents);
?>