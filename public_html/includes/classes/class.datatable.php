<?
//error_reporting(E_ALL & ~E_WARNING);


class datatable {
	var $errors = array();
	
	function datatable() {
		$this->cnt = 0;
		$this->sortable = 0;
		$this->id = 'myTable';
		$this->tableClass = 'datalist';
		$this->markRow = true;
		
		define('CRLFT',"\n   "); // with a tab
		define('CRLF',"\n");
		$this->column_count=1;
		
	}

	function set_column_count($value) {
		$this->column_count = $value;
	}
	
	function colspan($html_row, $head=0) {
		if ($head) {
			$td_count = substr_count($html_row,'<th');
			//pre($td_count);	
			
			if ($td_count < $this->column_count) {
				$tmp = explode('<th',$html_row);
				$tmp[$td_count] = ' colspan="' . ($this->column_count - $td_count + 1) . '"' . $tmp[$td_count];
				$html_row = implode('<th', $tmp);			
			}
		} else {
			$td_count = substr_count($html_row,'<td');
			//pre($td_count);	
			
			if ($td_count < $this->column_count) {
				$tmp = explode('<td',$html_row);
				$tmp[$td_count] = ' colspan="' . ($this->column_count - $td_count + 1) . '"' . $tmp[$td_count];
				$html_row = implode('<td', $tmp);			
			}
		}
		
		return $html_row;
	}
	
	function open() {
		return CRLF . '<p><table border="0" cellspacing="1" cellpadding="0" width="98%" class="'.$this->tableClass.($this->sortable? ' tablesorter':'').'" id="'.$this->id.'">';
	}
	
	function close() {
		if ($this->sortable && $this->id) {
			$jsSort = CRLF . '<script type="text/javascript"><!--
				$(document).ready(function() { $("#'.$this->id.'").tablesorter('.($this->sorter_params ? '{'.$this->sorter_params.'}' : '').'); });
			//--></script>';
		}
		return CRLF . '</tbody></table></p>'.$jsSort;
	}
	
	function heading() {
		$data = func_get_args();

		$html = CRLF  . '<thead><tr>' 
				 . CRLFT;
		foreach ((array) $data as $val) {
			$html .= '<th class="'.$this->tableClass.'-heading"'.(is_array($val) && $val[1] ? ' '.$val[1] : '').'>';
			if (is_array($val)) { $html .= $val[0]; } else { $html .= $val; }
			$html .= '</th>' . CRLFT;
		}
		$html .= CRLF . '</tr></thead>';
		
		if ($this->cnt == 0) { $html .= '<tbody>'; }
		
		return $this->colspan($html, 1);
	}
	
	function subheading() {
		$data = func_get_args();
		
		@array_map('stripslashes', $data);
		@array_map('trim', $data);
				
		foreach ($data as $val) {
			if ( is_numeric($val) ) {
				$html .= CRLFT . '<td class="'.$this->tableClass.'-subheading" bgcolor="#FFFFFF" align="right">' . $val . '</td>';
			} else {
				$html .= CRLFT . '<td class="'.$this->tableClass.'-subheading" bgcolor="#FFFFFF"'.(is_array($val) && $val[1] ? ' '.$val[1] : '').'>';
				if (is_array($val)) { $html .= $val[0]; } else { $html .= $val; }
				$html .= '</td>';		
			}
		}
		
		$html .= CRLF . '</tr>';
		
		return $this->colspan($html);
	}
	
	function row() {
		
		$data = func_get_args();
		
		$this->cnt++;
		
		$html .= CRLF . '<tr';
		
		if ($this->markRow) {
			$html .= ' onmousedown="colorRow(this, ' . $this->cnt. ', \'click\', \'#FFFFFF\', \'#CCFFCC\', \'#FFCC99\');" '
				 . 'onmouseover="colorRow(this, ' . $this->cnt . ', \'over\', \'#FFFFFF\', \'#CCFFCC\', \'#FFCC99\');" '
				 . 'onmouseout="colorRow(this, ' . $this->cnt . ', \'out\', \'#FFFFFF\', \'#CCFFCC\', \'#FFCC99\');"'
			;
		}
		
		$html .= '>';
				 
		@array_map('stripslashes', $data);
		@array_map('trim', $data);
				
		foreach ($data as $val) {
			if ( is_numeric($val) ) {
				$html .= CRLFT . '<td class="'.$this->tableClass.'-item" bgcolor="#FFFFFF" align="right">' . $val . '</td>';
			} else {
				$html .= CRLFT . '<td class="'.$this->tableClass.'-item" bgcolor="#FFFFFF"'.(is_array($val) && $val[1] ? ' '.$val[1] : '').'>';
				if (is_array($val)) { $html .= $val[0]; } else { $html .= $val; }
				$html .= '</td>';		
			}
		}
		
		$html .= CRLF . '</tr>';
		
		
		return $this->colspan($html);				
	}

	function simple_row() {
		$data = func_get_args();
			
		$html .= CRLF . '<tr>';
				 
		array_map('stripslashes', $data);
		array_map('trim', $data);
				
		foreach ($data as $val) {
			$html .= CRLFT . '<td class="'.$this->tableClass.'-item" bgcolor="#FFFFFF"'.(is_array($val) && $val[1] ? ' '.$val[1] : '').'>';
			if (is_array($val)) { $html .= $val[0]; } else { $html .= $val; }
			$html .= '</td>';		
		}
		
		$html .= CRLF . '</tr>';
		
		return $this->colspan($html);
	}	
}


?>