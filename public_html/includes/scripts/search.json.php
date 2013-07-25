<?php
require_once('../application_top.php');

$return = array();

if (trim($_REQUEST['term'])) {

	$sql = "SELECT p.`products_name` 
		FROM `products` AS `p`
		JOIN `products_to_categories` as `pc` ON (p.`products_id` = pc.`products_id`)
		JOIN `categories` AS `c` ON (c.`categories_id` = pc.`categories_id`)
		JOIN `products_prices` as `pp` ON (p.`products_id` = pp.`products_id` AND pp.`sites_id` = '" . mysql_real_escape_string(SITES_ID) . "')
		JOIN `products_to_sites` as `ps` ON (p.`products_id` = ps.`products_id` AND ps.`sites_id` = '" . mysql_real_escape_string(SITES_ID) . "')
		WHERE p.`products_name` LIKE '%" . mysql_real_escape_string(trim($_REQUEST['term'])) . "%' 
			AND ps.`sites_visibility` = 1 
			AND p.`products_visibility` = 1 
			AND pp.`products_price` > 0 
			AND c.`categories_visibility` = 1
		GROUP BY p.`products_name`
		LIMIT 10
	";
	
	$result = query($sql, $link);
	if( mysql_num_rows($result) > 0 ){
		while($row=mysql_fetch_assoc($result)){
			$rec[] = $row['products_name'];
		}
		
		if ($_REQUEST['opensearch']) {
			$return = array($_REQUEST['term'], $rec);
		} else {
			$return = $rec;
		}
	}
}

$return = json_encode($return);
echo $return;
