<?
include('../application_top.php');

$cities = array();
if ($_REQUEST['q']) {
	$city_name = ltrim(utf8_decode($_REQUEST['q']));
	if (stristr($city_name, ',')) {
		$rpos = strrpos($city_name, ',');
		$region_name = ltrim(substr($city_name, $rpos+1));
		$city_name = substr($city_name, 0, $rpos);
	}
    $sql = "SELECT c.`city_id`, c.`city_name`, r.`region_id`, r.`region_name`
        FROM `geo_city_names` AS `c`
        LEFT JOIN `geo_region_names` AS `r` ON (c.`region_id` = r.`region_id`)
        WHERE c.`country_code` = 'PH'
            AND c.`city_name` LIKE '" . mysql_real_escape_string($city_name) . "%'
	";
	if ($region_name) { $sql .= " AND r.`region_name` LIKE '". mysql_real_escape_string($region_name) . "%'"; }
	$sql .= " ORDER BY c.`city_name`, r.`region_name`";
    $res = query($sql);
    while ($r = mysql_fetch_assoc($res)) {
        $cities[] = array_map('utf8_encode', $r);
    }
     
    $return = $cities;
    echo json_encode($return);
}

?>