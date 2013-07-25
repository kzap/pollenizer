<?php

class baseController extends baseView {
	
	public function __construct($context) {
		parent::__construct();
		foreach ($context as $key => $value) {
			$this->$key = $value;
		}
	}
	
	public function indexAction() {
		echo 'This is the default action';
	}
	
}
