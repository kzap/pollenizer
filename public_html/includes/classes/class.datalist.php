<?php
//error_reporting(E_ALL & ~E_WARNING);


class datalist {
	var $errors = array();
	
	function datalist($link) {
		$this->set_dblink($link);
		$this->sortable = 0;
		$this->id = 'myTable';
		
		// constants
		define('CRLFT',"\n   "); // with a tab
		define('CRLF',"\n");
		
	}

	function set_edit_link($href,$keyfield) {
		$this->edit['show'] = true;
		$this->edit['href'] = $href;
		$this->edit['keyfield'] = $keyfield;
	}
	
	function set_delete_link($href,$keyfield) {
		$this->delete['show'] = true;
		$this->delete['href'] = $href;
		$this->delete['keyfield'] = $keyfield;
	}
	
	function set_view_link($href,$keyfield) {
		$this->view['show'] = true;
		$this->view['href'] = $href;
		$this->view['keyfield'] = $keyfield;
	}
	
	function set_copy_link($href,$keyfield) {
		$this->copy['show'] = true;
		$this->copy['href'] = $href;
		$this->copy['keyfield'] = $keyfield;
	}
	
	function set_insert_link($href) {
		$this->insert['show'] = true;
		$this->insert['href'] = $href;
	}
		
	function set_headings() {
		$this->headings = func_get_args();
	}
	
	// ********************************************************************************
	// database related functions
	// ********************************************************************************	
	function set_dblink($link) {
		$this->dblink = $link;
	}
	
	function set_sql($sql) {
		$this->sql = $sql;
	}
	
	// ********************************************************************************
	// display functions
	// ********************************************************************************
	function set_pagetitle($pagetitle) {
		$this->pagetitle = $pagetitle;
	}
	
	function draw_html() {
		// get list
		$res = query($this->sql);		
		if (!$res) { return false; }
			
		// number of columns
		$colcount = mysql_num_fields($res);
		if ($this->edit['show'] && !$this->action_column) 	{ $colcount++; array_unshift($this->headings,''); $this->action_column = true; }
		if ($this->delete['show'] && !$this->action_column) { $colcount++; array_unshift($this->headings,''); $this->action_column = true; }
		if ($this->view['show'] && !$this->action_column) { $colcount++; array_unshift($this->headings,''); $this->action_column = true; }
		if ($this->copy['show'] && !$this->action_column) { $colcount++; array_unshift($this->headings,''); $this->action_column = true; }
		
		// formstart
		$html .= CRLF . '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
				
		// page title
		$html .= CRLF . '<font class="datalist-title">' . $this->pagetitle . '</font><br/>';
		
		// list container
		$html .= CRLF . '<table border="0" cellspacing="1" cellpadding="0" width="98%" class="datalist'.($this->sortable? ' tablesorter':'').'" id="'.$this->id.'">';
		
		
		// list headings
		if ($this->insert['show'])
			$insert = '<a href="' . $this->insert['href'] . '" title="Insert Record"><img src="' . DIR_IMAGES . 'insert.png" border="0" alt="Insert Record" align="absmiddle"/></a>';
			
		$html .= '<thead><tr>' . CRLFT . '<th class="datalist-heading" align="center">' . $insert 
				 . @implode('</th>' . CRLFT . '<th class="datalist-heading">',$this->headings)
				 . '</th>' . CRLF . '</tr></thead><tbody>';
				 
		// list sort row
		
		
		// list
		$rowcount = 0; // for the marking of rows
		while ( $this->row = mysql_fetch_assoc($res) ) {	
			unset($edit);
			unset($delete);
			
			$html .= CRLF . '<tr '
			       . 'onmousedown="colorRow(this, ' . ++$rowcount . ', \'click\', \'#FFFFFF\', \'#CCFFCC\', \'#FFCC99\');" '
			       . 'onmouseover="colorRow(this, ' . $rowcount . ', \'over\', \'#FFFFFF\', \'#CCFFCC\', \'#FFCC99\');" '
					 . 'onmouseout="colorRow(this, ' . $rowcount . ', \'out\', \'#FFFFFF\', \'#CCFFCC\', \'#FFCC99\');">'
					 ;
			
			// view button
			if ($this->view['show']) {
				$view = CRLFT . '&nbsp;<a href="' . $this->view['href'] . $this->row[$this->view['keyfield']] . '" title="View Record"><img src="' . DIR_IMAGES . 'search.png" alt="View Record" border="0" align="absmiddle"/></a>&nbsp;';
			}
			
			// copy button
			if ($this->copy['show']) {
				$copy = CRLFT . '&nbsp;<a href="' . $this->copy['href'] . $this->row[$this->copy['keyfield']] . '" onClick="return confirm(\'Are you sure?\');" title="Copy Record"><img src="' . DIR_IMAGES . 'copy.png" alt="Copy Record" border="0" align="absmiddle"/></a>&nbsp;';
			}
			
			// edit button
			if ($this->edit['show']) {
				$edit = CRLFT . '&nbsp;<a href="' . $this->edit['href'] . $this->row[$this->edit['keyfield']] . '" title="Edit Record"><img src="' . DIR_IMAGES . 'edit.png" alt="Edit Record" border="0" align="absmiddle"/></a>&nbsp;';
			}
			
			// delete button
			if ($this->delete['show']) {
				$delete = CRLFT . '<a href="' . $this->delete['href'] . $this->row[$this->delete['keyfield']] . '" onClick="return confirm(\'Are you sure?\');" title="Delete Record"><img src="'. DIR_IMAGES . 'delete.png" border="0" align="absmiddle" alt="Delete Record"/></a>&nbsp;';
			}
			
			// add edit and delete links
			if (!empty($view) || !empty($edit) || !empty($delete)) {
				$html  .= CRLFT . '<td class="datalist-item" bgcolor="#FFFFFF" align="center" nowrap>'
						 . $view
						 . $edit
						 . $copy
						 . $delete
						 . '</td>'
						 ;
			}
						
			array_walk($this->row,'_stripslashes');
			array_walk($this->row,'_htmlentities');
			array_walk($this->row,'_trim');
					
			$html .= CRLFT . '<td class="datalist-item" bgcolor="#FFFFFF">'
			       . @implode('</td>' . CRLFT . '<td class="datalist-item" bgColor="#FFFFFF">',$this->row)
					 . '</td>'
					 . CRLF . '</tr>'
					 ;
		}
		
		// end list container
		$html .= CRLF . '</tbody></table>';
		$html .= CRLF . '</form>';
		if ($this->sortable && $this->id) {
			$html .= CRLF . '<script type="text/javascript"><!--
				$(document).ready(function() { $("#'.$this->id.'").tablesorter('.($this->sorter_params ? '{'.$this->sorter_params.'}' : '').'); });
			//--></script>';
		}
		
		return $html;
	}
	
}
