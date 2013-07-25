<?php

class baseView {
	
	protected $_views_base_dir, $_layouts_dir, $_default_layout_name, $_default_view_file_ext;
	protected $_controller_class, $_controller_name, $_controller_action, $_views_dir, $_default_view, $_default_layout;
	protected $_view, $_layout, $_partial, $_current_dir;
	
	public function __construct() {
		
		require_once(DIR_CONTROLLERS . 'errorController.php');
		
		$this->_views_base_dir = DIR_VIEWS;
		$this->_default_view_file_ext = '.phtml';
		$this->_layouts_dir = $this->_views_base_dir . 'layouts/';
		$this->_default_layout_name = 'application';
		$this->_partial = false;
		
		// get default_view
		$controllerSuffix = 'Controller';
		$this->_controller_class = get_class($this);
		if (substr($this->_controller_class, (0 - strlen($controllerSuffix))) == $controllerSuffix) {
			$this->_controller_name = substr($this->_controller_class, 0, (0 - strlen($controllerSuffix)));
		}
		$this->_views_dir = $this->_views_base_dir . $this->_controller_name . '/';
		$this->_default_view = $this->_views_dir . 'index' . $this->_default_view_file_ext;
		
		$this->_default_layout = $this->_layouts_dir . $this->_controller_name . $this->_default_view_file_ext;
		if (!file_exists($this->_default_layout)) {
			$this->_default_layout = $this->_layouts_dir . $this->_default_layout_name . $this->_default_view_file_ext;
		}
		
		$this->_uglifyHTML = true;
		$this->_uglifyHTMLOptions = array(
			'safe_tags' => array(
				'a', 'div', 'p', 'strong', 'em'
			),
		);
	}
	
	protected function render($view = null, array $data = array()) {
		$original_view = $this->_view;
		
		if (is_array($view) && isset($view[0])) { // if $view is a heirarchy of views, loop until we find an existing view
			foreach ($view as $val)	{
				$this->parseViewVar($val);
				if (file_exists($this->_view)) { break; } // if view exists then stop loop
			}
		} else {
			$this->parseViewVar($view);
		}
		$this->_partial = true;

        if (!empty($data)) {
			$this->set($data);
        }

        if (null === $this->_layout) {
            $this->yield();
        } else {
        	$current_dir = $this->_current_dir;
			$this->_current_dir = dirname($this->_layout) . '/';
			
			if ($this->_uglifyHTML) {
				ob_start();
	            require $this->_layout;
				$output = ob_get_contents();
				ob_end_clean();
				$output = preg_replace('!>[\t\n\r\v\f]+<!m', '><', $output);
				$output = preg_replace('!> {2,}<!m', '><', $output);
				$output = preg_replace('!>[\t\n\r\v\f]+!m', '>', $output);
				$output = preg_replace('![\t\n\r\v\f]+<!m', '<', $output);
				$output = preg_replace('!> {2,}!m', '>', $output);
				$output = preg_replace('! {2,}<!m', '<', $output);
				echo $output;
			} else {
				require $this->_layout;
			}
			
			$this->_current_dir = $current_dir;
        }

        // restore state for parent render()
        $this->_view = $original_view;
	}
	
	//Set the view layout
    protected function layout($layout) {
        $this->_layout = $layout;
    }

    //Renders the current view
    protected function yield() {
    	$current_dir = $this->_current_dir;
		$this->_current_dir = dirname($this->_view) . '/';

		if ($this->_uglifyHTML) { 
			ob_start();
			require $this->_view;
			$output = ob_get_contents();
			ob_end_clean();
			$output = preg_replace('!>[\t\n\r\v\f]+<!m', '><', $output);
			$output = preg_replace('!> {2,}<!m', '><', $output);
			$output = preg_replace('!>[\t\n\r\v\f]+!m', '>', $output);
			$output = preg_replace('![\t\n\r\v\f]+<!m', '<', $output);
			$output = preg_replace('!> {2,}!m', '>', $output);
			$output = preg_replace('! {2,}<!m', '<', $output);
			echo $output;
		} else {
			require $this->_view;
		}
		
		$this->_current_dir = $current_dir;
    }
	
	//Sets response properties/helpers
    protected function set($key, $value = null) {
        if (!is_array($key)) {
            return $this->$key = $value;
        }
        foreach ($key as $k => $value) {
            $this->$k = $value;
        }
    }
	
	private function parseViewVar($view = null) {
		// local vars
		$viewFile = null;
		$layoutFile = $this->_layout ? $this->_layout : $this->_default_layout;
		if ($this->_layout === false) { $this->_layout = $layoutFile = null; }
		if ($this->_partial !== false) { $this->_layout = $layoutFile = null; }
		
		if (is_array($view)) { // get view name from array
		
			// parse $view options
			if ($view['uglify'] == false) {
				$this->_uglifyHTML = false;
			}
			
			if ($view['action']) { // if contains action, this is a specific action
				if ($this->_partial !== false && $this->_current_dir) {
					$viewFile = $this->_current_dir . $view['action'];
				} else {
					$viewFile = $this->_views_dir . $view['action'];
				} 
				
			} elseif ($view['template']) { // if template, view is relative to view base dir
				$viewFile = $this->_views_base_dir . $view['template'];
				
			} elseif ($view['file']) { // if file, view is absolute path
				$viewFile = $view['file'];
				// if no layout specified
				if ((!isset($view['layout']) || $view['layout'] == '' || $view['layout'] === false) && (!isset($this->_layout) || $this->_layout == '' || $this->_layout === false)) {
					$layoutFile = null;
				}
				
			} elseif ($view['partial']) { // if its specifically a partial
			
				if (substr($view['partial'], 0, 1) == '/') { // if starts with /, view is absolute path
					$viewFile = $view['partial'];
				
				} elseif (strpos($view['partial'], '/') !== FALSE) { // if view contains a /, view is relative to view base dir
					$viewFile = $this->_views_base_dir . $view['partial'];
					
				} else { // view is specifically named
					if ($this->_partial !== false && $this->_current_dir) {
						$viewFile = $this->_current_dir . $view['partial'];
					} else {
						$viewFile = $this->_views_dir . $view['partial'];
					}
				}
				
				// if no layout specified
				if ((!isset($view['layout']) || $view['layout'] == '' || $view['layout'] === false) && (!isset($this->_layout) || $this->_layout == '' || $this->_layout === false)) {
					$layoutFile = null;
				}
			}
			
			if ($view['layout'] && !is_bool($view['layout'])) { // if layout is specified set it
				$layoutFile = $view['layout'];
			}
		} elseif ($view) { // get view name from var
			
			if (substr($view, 0, 1) == '/') { // if starts with /, view is absolute path
				$viewFile = $view;
				// if no layout specified
				if (!isset($this->_layout) || $this->_layout == '' || $this->_layout === false) {
					$layoutFile = null;
				}
				
			} elseif (strpos($view, '/') !== FALSE) { // if view contains a /, view is relative to view base dir
				$viewFile = $this->_views_base_dir . $view;
				
			} else { // view is specifically named
				if ($this->_partial !== false && $this->_current_dir) {
					$viewFile = $this->_current_dir . $view;
				} else {
					$viewFile = $this->_views_dir . $view;
				}
			}
		}
		
		if (is_null($viewFile)) { 
			// get method called if view not specified
			$debugBacktrace = debug_backtrace(false);
			for ($i = 1; $i < count($debugBacktrace); $i++) {
				if ($debugBacktrace[$i]['class'] == $this->_controller_class) {
					// if backtrace is correct controller class
					if ($i == (count($debugBacktrace) - 1) || !isset($debugBacktrace[$i+1]['class']) || $debugBacktrace[$i+1]['class'] != $this->_controller_class) {
						// if this is last backtrace or next backtrace is different class or has no class
						$actionSuffix = 'Action';
						$this->_controller_action = $debugBacktrace[$i]['function'];
						if (substr($this->_controller_action, (0 - strlen($actionSuffix))) == $actionSuffix) {
							$this->_controller_action = substr($this->_controller_action, 0, (0 - strlen($actionSuffix)));
						}
						if (file_exists($this->_views_dir . $this->_controller_action . $this->_default_view_file_ext)) {
							$viewFile = $this->_views_dir . $this->_controller_action . $this->_default_view_file_ext;
						}
						break;
					}
				}
			}
			
			if (is_null($viewFile)) { // default view if still none
				$viewFile = $this->_default_view;
			}
			
		}
		
		$viewSplit = explode('/', $viewFile);
		if (strpos($viewSplit[count($viewSplit) - 1], '.') === FALSE) {
			// if last part of $viewFile has no . add default extension
			$viewSplit[count($viewSplit) - 1] .= $this->_default_view_file_ext;
		}
		if ($this->_partial !== false && substr($viewSplit[count($viewSplit) - 1], 0, 1) != '_') {
			$viewSplit[count($viewSplit) - 1] = '_' . $viewSplit[count($viewSplit) - 1];
		}
		$viewFile = implode('/', $viewSplit);
		
		if (!is_null($layoutFile)) { // if layout specified
			if (substr($layoutFile, 0, 1) == '/') { // if layout starts with /, layout is absolute path
				$layoutFile = $layoutFile;
			
			} elseif (strpos($layoutFile, '/') !== FALSE) { // if layout contains a /, layout is relative to view base dir
				$layoutFile = $this->_views_base_dir . $layoutFile;
				
			} else { // layout is specifically named
				if ($this->_partial !== false && $this->_current_dir) {
					$layoutFile = $this->_current_dir . $layoutFile;
				} else {
					$layoutFile = $this->_layouts_dir . $layoutFile;
				}
			}
			
			$layoutSplit = explode('/', $layoutFile);
			if (strpos($layoutSplit[count($layoutSplit) - 1], '.') === FALSE) {
				// if last part of $viewFile has no . add default extension
				$layoutSplit[count($layoutSplit) - 1] .= $this->_default_view_file_ext;
			}
			if ($this->_partial !== false && substr($layoutSplit[count($layoutSplit) - 1], 0, 1) != '_') {
				$layoutSplit[count($layoutSplit) - 1] = '_' . $layoutSplit[count($layoutSplit) - 1];
			}
			$layoutFile = implode('/', $layoutSplit);
			
			$this->_layout = $layoutFile;
		}

		$this->_view = $viewFile;
	}
	
}
