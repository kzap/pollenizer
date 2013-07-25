<?php

class templateController extends baseController {
	public function __construct($context) {
		parent::__construct($context);
	}
	
	function indexAction() {
		pre('indexAction');
	}
	
}
