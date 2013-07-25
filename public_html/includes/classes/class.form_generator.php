<?php
// ********************************************************************************
// class :: form_generator
// generate inputs for forms
// return array of form parts START,END,TITLE,INPUTS,HIDDEN_INPUTS,BUTTONS
// ********************************************************************************
// last updated Oct 10/2004
// fixed a bug that wasn't prefilling or validating form values when the inputname[]
// syntax is being used. solution was to map these values from _REQUEST = array
// onto the 1 dimensional this->form_values
// ********************************************************************************
// sample useage of class
// ********************************************************************************
/*
include('../configure.php');
include('../functions/func.database.php');
include('../functions/func.debug.php');
$link = db_pconnect();
$fg = new form_generator();
$fg->set_dblink($link);
$fg->set_form_values($_POST);
$fg->set_form_start();
$fg->set_form_title('Some random form');
$fg->set_form_type(FT_EDIT);
$fg->element(TEXTBOX,'Name:','tb2',30,50);
$fg->element(PASSWORD,'Password:','pwd2',8,8);
foreach(range(1,100) as $val)
	$sb[] = $val;
$fg->element(TEXTAREA,'Description','tb34',3,40);
$fg->element(SELECTBOX,'SB1','sb1',$sb);
//$fg->element(SELECTBOX,'SB2','sb2',$sb);
//$fg->element(SELECTBOX,'SB3','sb3',$sb,false);
//$fg->element(SELECTBOX,'SB4','sb4',$sb);
$fg->element(RADIOGROUP_QUERY,'RG4','rg4','SELECT name,section_id FROM sections','&nbsp;');
$fg->element(SELECTBOX_QUERY,'SB5','sb5','SELECT name,category_id FROM categories');
foreach(range(1,4) as $val)
	$rg[] = $val;
$fg->element(RADIOGROUP,'radiogroup ','rg1',$rg);
//$fg->element(RADIOGROUP,'radiogroup ','rg2',$rg);
//$fg->element(RADIOGROUP,'radiogroup ','rg3',$rg);
$fg->element(CHECKBOX,'Yo','chk1');
$fg->element(CHECKBOX,'yurr','chk2');
$fg->element(HIDDEN,'','hid1','1111111111111111');
$fg->prefill();
?><html><body><? echo $fg->fget(); ?></body></html><?
pre($fg);
*/

class form_generator {
	var $form = array();
	var $prefill = array();
	var $validation = array();
	var $failure_messages = array();
	
	function form_generator() {
		// constants
		$const = 10000;
		define('BUTTON',++$const);
		define('IMAGE',++$const);
		define('CHECKBOX',++$const);
		define('HIDDEN',++$const);
		define('PASSWORD',++$const);
		define('RADIOGROUP',++$const);
		define('RADIOGROUP_QUERY',++$const);
		define('SELECTBOX',++$const);
		define('SELECTBOX_QUERY',++$const);
		define('SELECTBOX_YESNO',++$const);
		define('SUBMIT',++$const);
		define('TEXTAREA',++$const);
		define('TEXTBOX',++$const);
		define('TEXTONLY',++$const);
		define('FORMTITLE',++$const);
		define('DATESELECT',++$const);
		define('DATETIMESELECT',++$const);
		define('CRLFT',"\n   "); // with a tab
		define('CRLF',"\n");
		define('CUSTOM',++$const);
		define('FILESELECT',++$const);

		
		// form types
		define('FT_ADD',++$const);
		define('FT_INSERT',++$const);
		define('FT_EDIT',++$const);
		define('FT_UPDATE',++$const);
		define('FT_UPDATE_NO_DELETE',++$const);		
		define('FT_LOGIN',++$const);
		
		// layout vars
		define('RIGHT',++$const); // 2 columns
		define('LEFT',++$const); // 2 columns
		define('CENTER',++$const); // single column
		
		
		// validation expressions
		define('REG_ALPHANUM','/^\w+[\w\s]+$/i');
		define('REG_NONEMPTY','/^.+$/m');
		define('REG_YESNO','/^(y|n)$/i');
		define('REG_CURRENCY','/^(\d+\.\d\d)|(\.\d\d)|(\d+)$/');
		define('REG_DECIMAL','/^\d+\.\d+$/');
		define('REG_FILENAME','/^[\w\-]*\.[\w]+$/i');
		define('REG_IMAGEFILE','/^[\w\-]*\.(jpeg?|jpg|gif|png)$/i');
		define('REG_INTEGER','/^\d+$/');				
		define('REG_POSTALCODE','/^[a-z]\d[a-z]\s\d[a-z]\d$/i');
		define('REG_EMAIL','/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/');

		// variables
		$this->form_error = false;
		$this->count = 0;
	}

	// ********************************************************************************
	// input building functions
	// ********************************************************************************
	function button($name,$value='Submit',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		return '<input type="button" name="'.$name.'" value="'.$value.'"'.$extra.'/>';
	}
	
	function image($name,$value='Submit',$src='',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		return '<input type="image" src="'.$src.'" name="'.$name.'" value="'.$value.'"'.$extra.'/>';
	}

	function checkbox($name,$checked=false,$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
			
		if($checked)
			return '<input type="checkbox" name="'.$name.'" checked' . $extra . '/>';
		else
			return '<input type="checkbox" name="'.$name.'"' . $extra . '/>';					
	}

	function hidden($name,$current_value='',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		return '<input type="hidden" name="'.$name.'" value="'.$current_value.'"'.$extra.'/>';
	}
	
	function password($name,$size,$maxlen,$current_value='',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		if ($current_value != '') {
			$current_value = stripslashes($current_value);
			$current_value = htmlentities($current_value);
		}

		return '<input type="password" name="'.$name.'" size="'.$size.'" maxlength="'.$maxlen.'" value="'.$current_value.'"'.$extra.'/>';
	}
		
	function radiogroup($name,$values,$radio_separator='<br/>') {
		
		if ($radio_separator == '') { $radio_separator = '<br/>'; }
			
		// values is either a 1- or 2-dimensional array
		// 2d array is array[i][0] = display_value, array[i][1] = radio_value
		
		// 2 dimensional array
		if (is_array($values[0])) {
			for ($i=0; $i < sizeof($values); $i++) {
				$rg[] = '<input type="radio" name="'.$name.'" value="'.$values[$i][1].'"/>&nbsp;'.$values[$i][0];
			}
				
		// 1 dimensional array
		} else {
			for ($i=0; $i < sizeof($values); $i++) {
				$rg[] = '<input type="radio" name="'.$name.'" value="'.$values[$i].'"/>&nbsp;'.$values[$i];
			}
		}
		
		if (strstr($this->display, 'bootstrap') !== FALSE) {
			switch ($radio_separator) {
				case RADIOGROUP:
					$rg = '<label class="radio">' . @implode('</label><label class="radio">', $rg) . '</label>'; 
				break;
				case RADIOGROUP_INLINE:
					$rg = '<label class="radio inline">' . @implode('</label><label class="radio inline">', $rg) . '</label>';
				break;
			}
		} else {
			$rg = @implode($radio_separator,$rg);
		}
		
		return $rg;
	}

	function radiogroup_query($name,$sql,$radio_separator='<br/>') {
		$res = query($sql,$this->dblink);
		
		if (!$res)
			return false;
		
		$values = array();
		while ($r = mysql_fetch_row($res))
			$values[] = $r;

		return $this->radiogroup($name,$values,$radio_separator);
	}
	
	function date_selector($prefix) {
		$this_year = date('Y',time());
		$month_names  = array('January','February','March','April','May','June','July','August','September','October','November','December');
		foreach($month_names as $month)
			$month_values[] = array($month,str_pad(++$count,2,'0',STR_PAD_LEFT));
		for($i=1; $i <=31; $i++) 
			$days[] = str_pad($i,2,'0',STR_PAD_LEFT);
		$ds[] = $this->selectbox($prefix.'[year]',range($this_year-5,$this_year+1),true);
		$ds[] = $this->selectbox($prefix.'[month]',$month_values,true);
		$ds[] = $this->selectbox($prefix.'[day]',$days,true);
		return @implode('&nbsp;',$ds);
	}
	
	function datetime_selector($prefix) {
		$this_year = date('Y',time());
		$month_names  = array('January','February','March','April','May','June','July','August','September','October','November','December');
		foreach($month_names as $month)
			$month_values[] = array($month,str_pad(++$count,2,'0',STR_PAD_LEFT));
		for($i=1; $i <=31; $i++) 
			$days[] = str_pad($i,2,'0',STR_PAD_LEFT);
		for($i=0; $i <=23; $i++) 
			$hours[] = str_pad($i,2,'0',STR_PAD_LEFT);
		for($i=0; $i <=59; $i++) 
			$minutes[] = str_pad($i,2,'0',STR_PAD_LEFT);
		for($i=0; $i <=59; $i++) 
			$seconds[] = str_pad($i,2,'0',STR_PAD_LEFT);
		$ds[] = $this->selectbox($prefix.'[year]',range($this_year-5,$this_year+1),true);
		$ds[] = $this->selectbox($prefix.'[month]',$month_values,true);
		$ds[] = $this->selectbox($prefix.'[day]',$days,true);
		$ds[] = $this->selectbox($prefix.'[hour]',$hours,true);
		$ds[] = $this->selectbox($prefix.'[minute]',$minutes,true);
		$ds[] = $this->selectbox($prefix.'[second]',$seconds,true);
		return @implode('&nbsp;',$ds);
	}
	
	function selectbox($name,$values,$blank_option,$current_value='',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		// values is either a 1- or 2-dimensional array
		// 2d array is array[i][0] = display_value, array[i][1] = select_value
		
		// start select box
		$sb = '<select name="'.$name.'"' . $extra . '>';
		
		// add a dummy option		
		if ($blank_option) {
			$sb .= '<option value=""></option>';
		}
		
		$sb .= $this->selectbox_options($values);
		
		// end select box
		$sb .= '</select>';
		
		// select a value in the box
		foreach ((array)$current_value as $current_val) {
			if (trim($current_val) != '') {
				$sb = $this->select_value($sb,$current_val);
			}
		}
		
		return $sb;
	}
	
	function selectbox_options($options = array()) {
		$sb = '';
		
		foreach ($options as $key => $val) {
			if (is_array($val)) {
				if (isset($val['options'])) {
					$sb .= $this->selectbox_optgroup($val);
				} elseif (isset($val['label']) && isset($val['value'])) {
					$sb .= '<option value="' . $val['value'] . '">' . $val['label'] . '</option>';
				} elseif (isset($val['value'])) {
					$sb .= '<option value="' . $val['value'] . '">' . $val['value'] . '</option>';
				} elseif (isset($val[1])) {
					$sb .= '<option value="' . $val[1] . '">' . $val[0] . '</option>';
				} else {
					$sb .= '<option value="' . $val[0] . '">' . $val[0] . '</option>';
				}
			} else {
				$sb .= '<option value="' . $val . '">' . $val . '</option>';
			}
		}
		
		return $sb;
	}
	
	function selectbox_optgroup($optgroup = array()) {
		$sb = '';
		
		$sb .= '<optgroup';
		if ($optgroup['label']) { $sb .= ' label="' . $optgroup['label'] . '"'; }
		$sb .= '>';
		if (!empty($optgroup['options'])) {
			$sb .= $this->selectbox_options($optgroup['options']);
		}
		$sb .= '</optgroup>';
		
		return $sb;
	}
	
	function selectbox_query($name,$sql,$blank_option=true,$current_value='',$extra='') {
			
		$res = query($sql,$this->dblink);
		
		if (!$res)
			return false;
		
		$values = array();
		while ($r = mysql_fetch_row($res))
			$values[] = $r;

		return $this->selectbox($name,$values,$blank_option,$current_value,$extra);
	}

	function submit($name,$value='Submit',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		if ($value=='')
			$value = 'Submit';
			
		return '<input type="submit" name="'.$name.'" value="'.$value.'" '.$extra.'/>';
	}
		
	function textarea($name,$rows,$cols,$current_value='',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra; }
		
		if ($current_value != '') {
			$current_value = stripslashes($current_value);
			$current_value = htmlentities($current_value);
		}
				
		return '<textarea name="'.$name.'" rows="'.$rows.'" cols="'.$cols.'"'.$extra.'>'.$current_value.'</textarea>';		
	}
	
	function textbox($name,$size,$maxlen,$current_value='',$extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		if ($current_value != '') {
			$current_value = stripslashes($current_value);
			$current_value = htmlentities($current_value);
		}
			
		return '<input type="text" name="'.$name.'" size="'.$size.'" maxlength="'.$maxlen.'" value="'.$current_value.'"'.$extra.'/>';
	}
	
	function fileselect($name, $extra='') {
		if ($extra != '') { $extra = ' ' . $extra . ' '; }
		
		return '<input type="file" name="'.$name.'"'.$extra.'/>';
	}

	// ********************************************************************************
	// input manipulation functions
	// ********************************************************************************	
	function check_value($str,$value) {
		$str = str_replace(' checked="checked"','',$str);
		$str = str_replace('value="'.$value.'"','value="'.$value.'" checked="checked"',$str);
		return $str;
	}
		
	function select_value($str,$value) {
		$str = str_replace(' selected="selected"','',$str);
		if (!is_array($value)) { $value = array($value); }
		foreach ($value as $val) {
			$str = str_replace('value="'.$val.'"','value="'.$val.'" selected="selected"',$str);
		}
		return $str;
	}

	// ********************************************************************************
	// prefill input data functions
	// ********************************************************************************		
	function prefill($values=false) {
		// must have something to evaluate
		if (!sizeof($this->form_values) && !$values) {
//			echo('ERROR: function prefill() : no prefill values specified');
			return false;
		} else {
			$this->set_form_values($values);
		}
		
		for($i=0; $i < sizeof($this->form); $i++) {
			$html = $this->form[$i]['input_html'];
			$data = $this->form_values[$this->form[$i]['input_name']];
			
			if (isset($this->form_values[$this->form[$i]['input_name']])) {
				switch($this->form[$i]['type']) {
					case CHECKBOX:
						if ($data == 'on' || $data == 1)
							$html = str_replace('/>','checked />',$html);
					break;
					
					case HIDDEN:
					case PASSWORD:
					case TEXTBOX:
						//$data = stripslashes($data);
						$data = htmlentities($data);								
						//$html = str_replace('value=""',"value=\"$data\"",$html);
						$html = preg_replace('/value="[^"]*"/i', 'value="'.$data.'"', $html);
					break;
					
					case RADIOGROUP:	
					case RADIOGROUP_QUERY:
						$html = $this->check_value($html,$data);
					break;
					
					case DATESELECT:
						if (is_array($data)) {
							list($year,$month,$day) = explode('</select>',$html); // split the date selector
							$year = $this->select_value($year,$data['year']);
							$month = $this->select_value($month,$data['month']);
							$day = $this->select_value($day,$data['day']);
							$html = implode('</select>',array($year,$month,$day));						
						} else {
							list($year,$month,$day) = explode('</select>',$html); // split the date selector
							$data = explode('-',$data);
							$year = $this->select_value($year,$data[0]);
							$month = $this->select_value($month,$data[1]);
							$day = $this->select_value($day,$data[2]);
							$html = implode('</select>',array($year,$month,$day));						
						}
					break;
					
					case DATETIMESELECT:
						if (is_array($data)) {
							list($year,$month,$day,$hour,$minute,$second) = explode('</select>',$html); // split the date selector
							$year = $this->select_value($year,$data['year']);
							$month = $this->select_value($month,$data['month']);
							$day = $this->select_value($day,$data['day']);
							$hour = $this->select_value($hour,$data['hour']);
							$minute = $this->select_value($minute,$data['minute']);
							$second = $this->select_value($second,$data['second']);
							$html = implode('</select>',array($year,$month,$day,$hour,$minute,$second));						
						} else {
							list($year,$month,$day,$hour,$minute,$second) = explode('</select>',$html); // split the date selector
							list($data_date,$data_time) = explode(' ',$data);
							$data_date = explode('-',$data_date);
							$data_time = explode(':',$data_time);
							$year = $this->select_value($year,$data_date[0]);
							$month = $this->select_value($month,$data_date[1]);
							$day = $this->select_value($day,$data_date[2]);
							$hour = $this->select_value($hour,$data_time[0]);
							$minute = $this->select_value($minute,$data_time[1]);
							$second = $this->select_value($second,$data_time[2]);
							$html = implode('</select>',array($year,$month,$day,$hour,$minute,$second));						
						}
					break;
					
					case SELECTBOX:
					case SELECTBOX_QUERY:
					case SELECTBOX_YESNO:
						$html = $this->select_value($html,$data);
					break;
	
					
					case TEXTAREA:
						//$data = stripslashes($data);
						$data = htmlentities($data);
						$html = str_replace('></textarea>','>'.$data.'</textarea>',$html);
					break;
					
					case SUBMIT:
					case BUTTON:				
					case TEXTONLY:
						// do nothing
					break;
				}
				
				$this->form[$i]['input_html']	= $html;
				//pre(htmlentities($html));
			}
		}
		
	}
	

	// ********************************************************************************
	// form definition and form definition helper functions
	// ********************************************************************************		
	function add_text_only($input_type, $text) {
		$this->form[$this->count++] = array(
				'type' => $input_type,
				'text' => $text
				);
		return $text;
	}
	
	function add_form_input($input_type,$input_name,$display_text,$input_html) {
		$input = array( 
					'type'  => $input_type,
					'input_name' => $input_name,
					'text'  => $display_text, 
					'input_html' => $input_html,
					);
					
		$this->form[$this->count++] = $input;
		
		return $input_html;
	}
	
	// element definition by user
	// function requires the element type followed by all the necessary arguments
	// required to define a particular element
	// general definition for a form input
	// 0 = element_type (const)
	// 1 = input_name (name="")
	// 2 = text to display in form column 1
	// 3 ... variables to define input displayed in form column 2
	function element($element_type) {
		
		// get the argument list
		$args     = func_get_args();
		$argcount = func_num_args();
		
		// perform actions for each element type
		switch($element_type) {
			case BUTTON:
				// 1 = input name
				// 2 = input value
				// 3 = extra (ie, onclick etc)
				return $this->add_form_input($args[0],$args[1],'',$this->button($args[1],$args[2],$args[3]));
			break;
			
			case IMAGE:
				// 1 = input name
				// 2 = input value
				// 3 = extra (ie, onclick etc)
				return $this->add_form_input($args[0],$args[1],'',$this->image($args[1],$args[2],$args[3],$args[4]));
			break;
			
			case CHECKBOX:
			case CHECKBOX_INLINE:
				// 1 = input name
				// 2 = display text
				// 3 = checked [true|false]
//				if ($args[3] == '1') { $args['3'] = true;}
				 return $this->add_form_input($args[0],$args[1],$args[2],$this->checkbox($args[1],$args[3]));
			break;
			
			case DATESELECT:
				// 1 = input name
				// 2 = display text
				return $this->add_form_input($args[0],$args[1],$args[2],$this->date_selector($args[1]));
			break;
			
			case DATETIMESELECT:
				// 1 = input name
				// 2 = display text
				return $this->add_form_input($args[0],$args[1],$args[2],$this->datetime_selector($args[1]));
			break;
			
			case HIDDEN:
				// 1 = input name
				// 2 = input value
				// 3 = extra 
				return $this->add_form_input($args[0],$args[1],'',$this->hidden($args[1],$args[2],$args[3]));
			break;
			
			case PASSWORD:
				// 1 = input_name
				// 2 = display text
				// 3 = size
				// 4 = maxlength
				// 5 = input value
				// 6 = extra
				return $this->add_form_input($args[0],$args[1],$args[2],$this->password($args[1],$args[3],$args[4],$args[5],$args[6]));
			break;
			
			case RADIOGROUP:
			case RADIOGROUP_INLINE:
				// 1 = input name
				// 2 = display text
				// 3 = values (1 or 2 dim array)
				// 4 = radio separator (defaults to <br/>)
				if (strstr($this->display, 'bootstrap') !== FALSE) { $args[4] = $element_type; }
				return $this->add_form_input($args[0],$args[1],$args[2],$this->radiogroup($args[1],$args[3],$args[4]));			
			break;
			
			case RADIOGROUP_QUERY:
			case RADIOGROUP_INLINE_QUERY:
				// 1 = input name
				// 2 = display text
				// 3 = sql query
				// 4 = radio separator (defaults to <br/>)
				if (strstr($this->display, 'bootstrap') !== FALSE) { $args[4] = $element_type; }
				return $this->add_form_input($args[0],$args[1],$args[2],$this->radiogroup_query($args[1],$args[3],$args[4]));	
			break;
			
			case SELECTBOX:
				// 1 = input name
				// 2 = display text
				// 3 = select values (1 or 2 dim array)
				// 4 = blank option [true|false] defaults to true
				// 5 = selected option value
				// 6 = extra
				return $this->add_form_input($args[0],$args[1],$args[2],$this->selectbox($args[1],$args[3],$args[4],$args[5],$args[6]));
			break;
			
			case SELECTBOX_QUERY:
				// 1 = input name
				// 2 = display text
				// 3 = sql query
				// 4 = blank option [true|false] defaults to true
				// 5 = selected option value
				// 6 = extra
				return $this->add_form_input($args[0],$args[1],$args[2],$this->selectbox_query($args[1],$args[3],$args[4],$args[5],$args[6]));		
			break;

			case SELECTBOX_YESNO:
				// 1 = input name
				// 2 = display text
				// 3 = blank option [true|false] defaults to true
				// 4 = selected option value
				// 5 = extra 
				return $this->add_form_input($args[0],$args[1],$args[2],$this->selectbox($args[1],$this->array_yesno(),$args[3],$args[4],$args[5]));
			break;
			
			case SUBMIT:
				// 1 = input name
				// 2 = input value
				// 3 = extra options
				return $this->add_form_input($args[0],$args[1],'',$this->submit($args[1],$args[2],$args[3]));
			break;
			
			case TEXTAREA:
				// 1 = input_name
				// 2 = display_text
				// 3 = rows
				// 4 = columns
				// 5 = input value
				// 6 = extra
				return $this->add_form_input($args[0],$args[1],$args[2],$this->textarea($args[1],$args[3],$args[4],$args[5],$args[6]));
			break;
			
			case TEXTONLY:
			case FORMTITLE:
				// 1 = display_text
				return $this->add_text_only($args[0], $args[1]);
			break;
			
			case TEXTBOX:
				// 1 = input_name
				// 2 = display_text
				// 3 = size
				// 4 = maxlength
				// 5 = input value
				// 6 = extra
				return $this->add_form_input($args[0],$args[1],$args[2],$this->textbox($args[1],$args[3],$args[4],$args[5],$args[6]));
			break;			
			
			case CUSTOM:
				// 1 = input_name
				// 2 = display_text
				return $this->add_form_input($args[0],'',$args[1],$args[2]);
			break;
			
			case FILESELECT:
				// 1 = input_name
				// 2 = display_text
				// 3 = extra
				return $this->add_form_input($args[0],$args[1],$args[2],$this->fileselect($args[1],$args[3]));
			break;
		}		
	}
	

	// ********************************************************************************	
	// form retrieval functions
	// ********************************************************************************		
	// unformatted array
	function get() {
		return $this->form;
	}
	
	function set_submit_align($align) {
		$this->submit_alignment = $align;		
	}
	
	function draw_html() {
		echo $this->get_html();
	}
	
	// formatted html
	function get_html() {
		
		switch ($this->display) {
			case 'bootstrap-horizontal':
			case 'bootstrap-inline':
			case 'boostrap-search':
			case 'boostrap':
				$html = $this->get_html_bootstrap();
			break;
			
			case 'table':
			default:
				$html = $this->get_html_table();
		}
		
		return $html;	
	}
	
	// formatted html - TABLE format
	function get_html_bootstrap() {
		
		// assume form open() was called
		$html = $this->form[0];
		if ($this->form['TITLE']) { $html .= '<legend>' . $this->form['TITLE'] . '</legend>'; }
		
		for ($i=1; $i < sizeof($this->form); $i++) {

			switch($this->form[$i]['type']) {
				case BUTTON:
				case IMAGE:
				case DATESELECT:
				case DATETIMESELECT:
				case PASSWORD:
				case SELECTBOX:
				case SELECTBOX_QUERY:
				case SELECTBOX_YESNO:
				case TEXTAREA:
				case TEXTBOX:
				case FILESELECT:
				case CUSTOM:
					switch ($this->display) {
						default:
							$html .= $this->draw_row_bootstrap($this->form[$i]['text'],$this->form[$i]['input_html']);	
					}
					
				break;				
				
				case CHECKBOX:
				case CHECKBOX_INLINE:
					if ($this->form[$i]['text']) {
						$this->form[$i]['input_html'] .= '&nbsp;' . $this->form[$i]['text'];
					}
					if ($this->form[$i]['type'] == CHECKBOX_INLINE) {	
						$this->form[$i]['input_html'] = '<label class="checkbox inline">' . $this->form[$i]['input_html'] . '</label>';
					} else {
						$this->form[$i]['input_html'] = '<label class="checkbox">' . $this->form[$i]['input_html'] . '</label>';
					}
					$html .= $this->draw_row_bootstrap($this->form[$i]['input_html']);
				break;
				
				case RADIOGROUP:
				case RADIOGROUP_QUERY:
				case RADIOGROUP_INLINE:
				case RADIOGROUP_INLINE_QUERY:
					$html .= $this->draw_row_bootstrap($this->form[$i]['input_html']);
				break;
				
				case TEXTONLY:
					switch ($this->display) {
						default:
							$html .= $this->draw_row_bootstrap($this->form[$i]['text']);	
					}
				break;

				case FORMTITLE:
					$html .= CRLF . '<legend>' . $this->form[$i]['text'] . '</legend>';
				break;

				case HIDDEN:
					$html .= CRLF . $this->form[$i]['input_html'];
				break;				

				case SUBMIT:
					$submit_buttons[] = $this->form[$i]['input_html'];
				break;				
			}			
		}


		// submit buttons
		if (sizeof($submit_buttons)) {
			$html .= '<div class="form-actions">';
			switch($this->submit_alignment) {
				default:
					$html .= CRLF . @implode('&nbsp;', $submit_buttons);
				break;
			}
			$html .= '</div>';
		}

		// footer
		$html .= $this->close() . CRLF;
			
				 
		return $html;	
	}
	
	function draw_row_bootstrap($args) {
		$args = func_get_args();
		
		$labelFor = '';
		if ($args[1] && stripos($args[1], 'id=') !== FALSE) {
			if (preg_match('/id="([^"]*)"/i', $args[1], $match)) {
				$labelFor = $match[1];
			}
		}
		
		if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '<div class="control-group">'; }
		
		if ( sizeof($args) == 1) {
			if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '<div class="controls">'; }
			$html .= CRLF  . $args[0];
			if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '</div>'; }
		
		} elseif ( sizeof($args) == 2) {
			$html .= CRLF  . '<label';
			if ($labelFor) { $html .= ' for="' . $labelFor . '"'; }
			if ($this->display == 'bootstrap-horizontal') { $html .= ' class="control-label"'; }
			$html .= '>' . $args[0] . '</label>';
			if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '<div class="controls">'; }
			$html .= CRLF  . $args[1];
			if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '</div>'; }
					 
		} elseif( sizeof($args > 2) ) {
			$html .= CRLF  . '<label';
			if ($labelFor) { $html .= ' for="' . $labelFor . '"'; }
			if ($this->display == 'bootstrap-horizontal') { $html .= ' class="control-label"'; }
			$html .= '>' . $args[0] . '</label>';
			
			if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '<div class="controls">'; }
			for ($i=1; $i < sizeof($args); $i++) { 
				$html .= CRLF  . $args[$i];
			}
			if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '</div>'; }
		}
		
		if ($this->display == 'bootstrap-horizontal') { $html .= CRLF . '</div>'; }
	
		return $html;
	}
	
	// formatted html - TABLE format
	function get_html_table() {
		
		// assume form open() was called
		$html  = $this->form[0]
		       . $this->open_table()
				 ;
		
		for ($i=1; $i < sizeof($this->form); $i++) {

			switch($this->form[$i]['type']) {
				case BUTTON:
				case IMAGE:
				case CHECKBOX:
				case DATESELECT:
				case DATETIMESELECT:
				case PASSWORD:
				case RADIOGROUP:
				case RADIOGROUP_QUERY:
				case SELECTBOX:
				case SELECTBOX_QUERY:
				case SELECTBOX_YESNO:
				case TEXTAREA:
				case TEXTBOX:
				case FILESELECT:
				case CUSTOM:
					$html .= $this->draw_row($this->form[$i]['text'],$this->form[$i]['input_html']);
				break;				

				case TEXTONLY:
					$html .= $this->draw_row($this->form[$i]['text']);
				break;

				case FORMTITLE:
					$html .= str_replace('form-field-input','form-title',$this->draw_row($this->form[$i]['text']));
				break;

				case HIDDEN:
					$html .= CRLF . $this->form[$i]['input_html'];
				break;				

				case SUBMIT:
					$submit_buttons[] = $this->form[$i]['input_html'];
				break;				
			}			
		}


		// submit buttons
		if (sizeof($submit_buttons)) {

			switch($this->submit_alignment) {
				case LEFT: 
					$html .= CRLF . '<tr>'							 
							 . CRLFT . '<td colspan="2" class="form-submit">'.@implode('&nbsp;',$submit_buttons).'</td>'
							 . CRLF . '</tr>'
							 ;
				break;
						
				default:
					$html .= CRLF . '<tr>'
							 . CRLFT . '<td class="form-submit">&nbsp;</td>'
							 . CRLFT . '<td class="form-submit">'.@implode('&nbsp;',$submit_buttons).'</td>'
							 . CRLF . '</tr>'
							 ;
				break;
			}
		}

		// footer
		$html .= $this->close_table() . CRLF . $this->close() . CRLF;
			
				 
		return $html;	
	}
	
	function draw_spacer($colspan=2) {
		return '<tr><td background="' . DIR_IMAGES . 'spacer.gif" height="2" colspan="'. $colspan . '">&nbsp;</td></tr>';
	}
	
	function draw_row($args) {
		$args = func_get_args();
	
		if ( sizeof($args) == 1) {
			$html .= CRLF  . '<tr>'
					 . CRLFT . '<td class="form-field-input" colspan="2">' . $args[0] . '</td>'
					 . CRLF  . '</tr>'
					 ;				
		
		} elseif ( sizeof($args) == 2) {
			$html .= CRLF  . '<tr>'
					 . CRLFT . '<td class="form-field-name" nowrap width="20%">' . $args[0] . '</td>'
					 . CRLFT . '<td class="form-field-input" width="80%">' . $args[1] . '</td>'
					 . CRLF  .'</tr>'
					 ;
					 
		} elseif( sizeof($args > 2) ) {
			$html .= CRLF . '<tr>'
					 . CRLFT . '<td class="form-field-input">' . $args[0] . '</td>'
					 ;
			
			for($i=1; $i < sizeof($args); $i++) 
				$html .= CRLFT . '<td class="form-field-input">' . $args[$i] . '</td>';
			
			$html .= CRLF .'</tr>';		
		}
	
		return $html;
	}

	// ********************************************************************************
	// object defintion and form definition functions
	// ********************************************************************************		
	function set_dblink($link) {
		$this->dblink = $link;
	}

	// used with prefill
	// used with validation
	function set_form_values($arr) {
		if (empty($arr)) 			
			return false;
			
		foreach($arr as $key => $value) {
			
			// for arrays			
			if (is_array($value)) {
				foreach($value as $key2 => $value2) 
					if (!is_array($value2)) {
						$this->form_values[$key][$key2] = $value2;
					}
			// not an array
			} else {
				$this->form_values[$key] = (string) $value;
			}
		}
		
		return (sizeof($this->form_values) > 0);
	}
	
	function set_form_title($title) {
		$this->form['TITLE'] = $title;
	}
	
	function set_form_type($formtype) {
		$this->formtype = $formtype;
		
		switch ($formtype) {
			case FT_ADD:
			case FT_INSERT:
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Save'));				
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Cancel'));				
			break;
			
			case FT_EDIT:
			case FT_UPDATE:
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Save Changes'));				
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Delete','onClick="return confirm(\'Are you sure?\');"'));
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Cancel'));				
			break;

			case FT_UPDATE_NO_DELETE:
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Save Changes'));				
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Cancel'));				
			break;
			
			case FT_LOGIN:
				$this->add_form_input(SUBMIT,'action','',$this->submit('action','Login'));				
			break;
			
		}
	}
	
	function open_table() {
		return CRLF . '<table border="0" cellspacing="1" cellpadding="0" width="98%" class="form-table">' ;
	}
	
	function close_table() {
		return  CRLF .'</table>';
	}
	
	function open($name='form001', $method='POST', $action='', $extra='') {
		if ($action=='') {
			$action = $_SERVER['PHP_SELF'];
		}
		
		if ($this->display == 'bootstrap-horizontal') {
			$bootstrapClass = 'form-horizontal';
		} elseif ($this->display == 'bootstrap-inline') {
			$bootstrapClass = 'form-inline';
		} elseif ($this->display == 'bootstrap-search') {
			$bootstrapClass = 'form-search';
		}
		if ($bootstrapClass) {
			if (stripos($extra, 'class=') !== FALSE) {
				$extra = preg_replace('/class="([^"]*)"/i', 'class="$1 ' . $bootstrapClass . '"', $extra);
			} else {
				$extra = (strlen($extra) ? $extra . ' ' : '') . 'class="' . $bootstrapClass . '"';
			}
		}
			
		if ($extra != '') { $extra = ' ' . $extra; }
			
		$html = '<form name="'.$name.'" method="'.$method.'" action="'.$action.'"'.$extra.'>';
		$this->form[$this->count++] = $html;
		return $html;
	}
	
	function close() {		
		//$this->form[$this->count++] = '</form>';
		return '</form>';
	}
	
	function set_form_display($type = 'table') {
		switch (strtoupper($type)) {
			case 'HORIZONTAL':
			case 'BOOTSTRAP-HORIZONTAL':
				$this->display = 'bootstrap-horizontal';
			break;
			
			case 'INLINE':
			case 'BOOTSTRAP-INLINE':
				$this->display = 'bootstrap-inline';
			break;
			
			case 'SEARCH':
			case 'BOOTSTRAP-SEARCH':
				$this->display = 'bootstrap-search';
			break;
			
			case 'BOOTSTRAP':
				$this->display = 'boostrap';
			break;
			
			default:
				$this->display = 'table';
		}
	}
	

	// ********************************************************************************	
	// form validation functions
	// ********************************************************************************		
	function regexp($input_name,$pattern,$failure_message) {
		$this->validation[] = array('input_name' => $input_name, 
		                            'pattern' => $pattern, 
											 'message' => $failure_message
											 );
	}
	
	function add_error($message) {
		$this->form_error = true;
		$this->failure_messages[] = $message;
	}
	
	function validation_errors() {
		return $this->failure_messages;
	}
	
	function validate($values=false) {	
		// must have something to evaluate
		if (!sizeof($this->form_values) && !$values) {
//			echo('ERROR: function validate() : no form values to validate');
			return false;
		} else {
			$this->set_form_values($values);
		}
			
		// cycle through the form vars
		for($i=0; $i < sizeof($this->validation); $i++) {
			
			// check the type of key			
			preg_match('/(\w+)(\[(\w+)\])?/',$this->validation[$i]['input_name'],$match);

			// to account for the PHP method of [] as naming a variable
			if (sizeof($match) == 4)
				$value = $this->form_values[$match[1]][$match[3]];
			else
				$value = $this->form_values[$match[1]];

			// checkt he validation expression against value
			if ( !preg_match($this->validation[$i]['pattern'],$value) ) {
				$this->form_error = true;
				$this->failure_messages[] = $this->validation[$i]['message'];
			}		
		}
				
		// return result
		return !$this->form_error;
	}	
	
	function validate_dateselector($name,$msg_prefix,$required=false) {
		$dateok = checkdate($this->form_values[$name]['month'],$this->form_values[$name]['day'],$this->form_values[$name]['year']);
		
		// required and empty - tell user it's required
		if ($required && (empty($this->form_values[$name]['month']) && empty($this->form_values[$name]['day']) && empty($this->form_values[$name]['year']))) {
			$this->form_error = true;
			$this->failure_messages[] = $msg_prefix . ' is required';
			return false;

		// not required and all empty - so ok
		} elseif (!$required && (empty($this->form_values[$name]['month']) && empty($this->form_values[$name]['day']) && empty($this->form_values[$name]['year']))) {						
			return true;

		// not required or required but, just a bad date
		} elseif (!$dateok) {
			$this->form_error = true;
			$this->failure_messages[] = $msg_prefix . ' is not a valid date';
			return false;
		} 
	}
	
	function validate_datetimeselector($name,$msg_prefix,$required=false) {
		$dateok = $this->isValidDateTime($this->form_values[$name]['year'].'-'.$this->form_values[$name]['month'].'-'.$this->form_values[$name]['day'].' '.$this->form_values[$name]['hour'].':'.$this->form_values[$name]['minute'].':'.$this->form_values[$name]['second']);
		
		// required and empty - tell user it's required
		if ($required && (empty($this->form_values[$name]['month']) && empty($this->form_values[$name]['day']) && empty($this->form_values[$name]['year']) && empty($this->form_values[$name]['hour']) && empty($this->form_values[$name]['minute']) && empty($this->form_values[$name]['second']))) {
			$this->form_error = true;
			$this->failure_messages[] = $msg_prefix . ' is required';
			return false;

		// not required and all empty - so ok
		} elseif (!$required && (empty($this->form_values[$name]['month']) && empty($this->form_values[$name]['day']) && empty($this->form_values[$name]['year']) && empty($this->form_values[$name]['hour']) && empty($this->form_values[$name]['minute']) && empty($this->form_values[$name]['second']))) {						
			return true;

		// not required or required but, just a bad date
		} elseif (!$dateok) {
			$this->form_error = true;
			$this->failure_messages[] = $msg_prefix . ' is not a valid date';
			return false;
		} 
	}
	
	// ********************************************************************************
	// formatting functions for incoming form values
	// ********************************************************************************		
	function fmt_on2bit($value) {
		if($value == 'on') 
			$value = 1;
		else
			$value = 0;

		return $value;
	}

	function fmt_bit2on($value) {
		if($value == '1') 
			$value = 'on';
		else
			$value = '';

		return $value;
	}
			
	function fmt_empty2null($value,$quoted=true) {
		if ($value == '')
			return 'NULL';
		else {
			if ($quoted) 
				return "'" . $value . "'";
			else
				return $value;
		}
	}
	
	function array_yesno() {
		$yesno[] = array('Yes',1);
		$yesno[] = array('No',0);		
		return $yesno;
	}
	
	function to_date($input_name) {
		if ($this->form_values[$input_name]['year'] != '' && $this->form_values[$input_name]['month'] != '' && $this->form_values[$input_name]['day'] != '')
			return $this->form_values[$input_name]['year'] .'-'. $this->form_values[$input_name]['month'] .'-'. $this->form_values[$input_name]['day'];

		return '';
	}
	
	function to_datetime($input_name) {
		if ($this->form_values[$input_name]['year'] != '' && $this->form_values[$input_name]['month'] != '' && $this->form_values[$input_name]['day'] != '' && $this->form_values[$input_name]['hour'] != '' && $this->form_values[$input_name]['minute'] != '' && $this->form_values[$input_name]['second'] != '')
			return $this->form_values[$input_name]['year'] .'-'. $this->form_values[$input_name]['month'] .'-'. $this->form_values[$input_name]['day'] .' '. $this->form_values[$input_name]['hour'] .':'. $this->form_values[$input_name]['minute'] .':'. $this->form_values[$input_name]['second'];

		return '';
	}
	
	function isValidDateTime($dateTime) {
		if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches)) {
			if (checkdate($matches[2], $matches[3], $matches[1])) {
				return true;
			}
    	}
		
    	return false;
	}
	
}
