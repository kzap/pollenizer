<?php

function configuration_input($config_id,$config_key,$possible_values) {
	
	$possible_values = trim($possible_values);
	$inputname = strtolower($config_key);
		
	// a normal text input - nonspecific
	if ($possible_values == '') {
		return '<input type="text" size="50" maxlength="255" name="configid_'.$config_id.'" />';		

	// a query
	} elseif( preg_match('/SELECT/i',$possible_values) ) {
		// run a query to build selectbox
		global $link;
		$sql = $possible_values;
		$res = query($sql,$link);
		while ($value = mysql_fetch_row($res) ) {
			if (sizeof($value) == 1)
				$options .= '<option value="' . $value[0] . '">' . $value[0] . '</option>';
			elseif (sizeof($value) == 2)
				$options .= '<option value="' . $value[0] . '">' . $value[1] . '</option>';
			else
				$zero_options = 1;
		}
		
		// selectbox
		if (!$zero_options) {
			return '<select name="configid_' . $config_id . '"><option value=""></option>' . $options . '</select>';		
		} else {
			return false;
		}
		
	// a select box
	} else {
		$values = explode(',',$possible_values);
		$select = '<select name="configid_'.$config_id.'"><option value=""></option>';
		foreach($values as $value) {
			$select .= '<option value="'.$value.'">'.$value.'</option>';
		}
		$select .= '</select>';
		
		return $select;
	}	
}
