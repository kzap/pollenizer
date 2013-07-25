<?php

class errorController extends baseController {
	public function __construct($context) {
		parent::__construct($context);
	}
	
	public function code404Action() {
		errorController::render404();
	}
	
	public function code405Action() {
		errorController::renderErrorPage(405);
	}
	
	public function renderErrorPage($code = 404, $exit = true, $message = null) {
		
		// send error header
		if (function_exists('http_response_code')) {
            // Have PHP automatically create our HTTP Status header from our code
            http_response_code($code);
        } else {
            // Manually create the HTTP Status header
            $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
            header("$protocol $code");
        }
		
		$this->render(array(
			'template' => 'error/error',
			'layout' => 'layouts/application',
		), array(
			'title' => 'Error: ' . $code,
			'code' => $code,
			'noShareButtons' => true,
			'noIndex' => true,
			'message' => $message,
			'errorPage' => true,
		));
		if ($exit) { exit; }
	}
	
	public function render404($exit = true) {
		errorController::renderErrorPage(404, $exit);
	}
	
}
