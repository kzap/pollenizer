<?
$c = new contents($link, SITES_ID);
if (empty($allCats)) { $allCats = $c->build_categories_navigation(); }
$c->display_categories_nav($allCats);
?>
