<?php

class membersController extends baseController {
	public function __construct($context) {
		parent::__construct($context);
	}
	
	public function indexAction() {
		$this->_response->redirect(DIR_WS_ROOT . 'members/dashboard/');
	}
	
	public function dashboardAction() {
		membersController::checkLoginStatus(true);
		
		return $this->render(null, array('title' => 'Members Dashboard'));
	}
	
	public function registerAction() {
		switch($this->_request->method()) {
			case 'POST':
				$this->_response->redirect(DIR_WS_ROOT . 'members/profile/');
$this->_response->dump($this->_request->params());
pre($this->_response);
		$sql = "SELECT * FROM `advertisers` WHERE 1";
		$res = query($sql, $this->dblink);
		while ($r = mysql_fetch_assoc($res)) { pre($r); }
		
		$sql = "SELECT `user_ID`, `user_email` FROM `user` WHERE 1 LIMIT 10";
		$res = query($sql, db_connect_topblogs());
		while ($r = mysql_fetch_assoc($res)) { pre($r); }
		
		$sql = "SELECT * FROM `offers` WHERE 1";
		$res = query($sql, $this->dblink);
		while ($r = mysql_fetch_assoc($res)) { pre($r); }
		
		$sql = "SELECT `user_ID`, `user_email` FROM `user` WHERE 1 LIMIT 10";
		$res = query($sql, db_connect_topblogs());
		while ($r = mysql_fetch_assoc($res)) { pre($r); }
			break;

			case 'GET':
			default:
				$registerForm = membersController::registerForm();
				$this->registerForm = $registerForm->get_html();
				
				$registerForm2 = membersController::registerForm2();
				$this->registerForm2 = $registerForm2->get_html();

				$this->render(null, array('title' => 'Register'));
			break;
		}
	}
	
	public function loginAction() {
		switch($this->_request->method()) {
			case 'POST':
				$_SESSION['logged_in'] = true;
				$this->_response->redirect(DIR_WS_ROOT . 'members/dashboard/');
			break;
				
			case 'GET':
			default:
				$loginForm = membersController::loginForm();
				$this->loginForm = $loginForm->get_html();
				
				$this->render(null, array('title' => 'Login'));
			break;
		}
	}
	
	public function forgot_passwordAction() {
		pre('forgot password logic');
	}
	
	public function logoutAction() {
		unset($_SESSION['logged_in']);
		$this->_response->redirect(DIR_WS_ROOT);
	}
	
	public function profileAction() {
		membersController::checkLoginStatus(true);
		switch($this->_request->method()) {
			case 'POST':
				echo $this->_response->markdown('*Profile Saved*');
			break;
				
			case 'GET':
			default:
				$profileForm = membersController::profileForm();
				$this->profileForm = $profileForm->get_html();
				
				$this->render(null, array('title' => 'Profile'));
			break;
		}
	}
	
	public function loginForm() {
		$loginForm = new form_generator();
		$loginForm->set_dblink($this->dblink);
		$loginForm->set_form_display('horizontal');
		$loginForm->set_form_title('Login <span class="label label-info">using your TopBlogs.com.ph account</span>');
		$loginForm->open('', 'POST', DIR_WS_ROOT . 'members/login/');
		$loginForm->element(TEXTBOX, 'login', 'Username / E-Mail', 20, 100, '', 'placeholder="juan@edsamail.com.ph" id="loginBox"');
		$loginForm->element(PASSWORD, 'password', 'Password', 20, 100, '', 'placeholder="mySecretPassword" id="passwordBox"');
		$loginForm->element(CHECKBOX, 'remember', 'Remember Me');
		$loginForm->element(SUBMIT, 'action', 'Login', 'class="btn btn-primary"');
		$loginForm->regexp('login', REG_NONEMPTY, 'Username / E-Mail is required');
		$loginForm->regexp('password', REG_NONEMPTY, 'Password is required');
		$loginForm->close();
		
		return $loginForm;
	}
	
	public function registerForm() {
		$registerForm = new form_generator();
		$registerForm->set_dblink($this->dblink);
		$registerForm->set_form_display('horizontal');
		$registerForm->set_form_title('Register <span class="label label-info">your Blog on TopBlogs.com.ph if you don\'t have an account yet</span>');
		$registerForm->open('', 'POST', DIR_WS_ROOT . 'members/register/');
		$registerForm->element(TEXTBOX, 'username', 'Username', 20, 100, '', 'placeholder="juan.blogger" id="usernameBox"');
		$registerForm->element(TEXTBOX, 'email', 'E-Mail Address', 20, 100, '', 'placeholder="juan@edsamail.com.ph" id="emailBox"');
		$registerForm->element(PASSWORD, 'password', 'Password', 20, 100, '', 'placeholder="mySuperSecretPassword" id="passwordBox2"');
		$registerForm->element(TEXTBOX, 'url', 'Blog URL', 20, 100, '', 'placeholder="http://www.myBlogUrl.com" id="urlBox"');
		$registerForm->element(HIDDEN, 'type', 'Blogger');
		$registerForm->element(SUBMIT, 'action', 'Register', 'class="btn btn-primary"');
		$registerForm->regexp('email', REG_NONEMPTY, 'E-Mail is required');
		$registerForm->regexp('password', REG_NONEMPTY, 'Password is required');
		$registerForm->regexp('url', REG_NONEMPTY, 'Blog URL is required');
		$registerForm->close();
		
		return $registerForm;
	}

	public function registerForm2() {
		$registerForm2 = new form_generator();
		$registerForm2->set_dblink($this->link);
		$registerForm2->set_form_display('horizontal');
		$registerForm2->set_form_title('Register <span class="label label-info">as an advertiser on TopBlogs.com.ph if you don\'t have an account yet</span>');
		$registerForm2->open('', 'POST', DIR_WS_ROOT . 'members/register/');
		$registerForm2->element(TEXTBOX, 'username', 'Username', 20, 100, '', 'placeholder="my.username" id="usernameBox"');
		$registerForm2->element(TEXTBOX, 'email', 'E-Mail Address', 20, 100, '', 'placeholder="bossing@edsamail.com.ph" id="emailBox"');
		$registerForm2->element(PASSWORD, 'password', 'Password', 20, 100, '', 'placeholder="mySuperSuperSecretPassword" id="passwordBox2"');
		$registerForm2->element(HIDDEN, 'type', 'Advertiser');
		$registerForm2->element(SUBMIT, 'action', 'Register', 'class="btn btn-primary"');
		$registerForm2->regexp('email', REG_NONEMPTY, 'E-Mail is required');
		$registerForm2->regexp('password', REG_NONEMPTY, 'Password is required');
		$registerForm2->close();
		
		return $registerForm2;
	}

	public function profileForm() {
		$loginForm = new form_generator();
		$loginForm->set_dblink($this->dblink);
		$loginForm->set_form_display('horizontal');
		$loginForm->set_form_title('Profile <span class="label label-info">Update your Profile</span>');
		$loginForm->open('', 'POST', DIR_WS_ROOT . 'members/profile/');
		$loginForm->element(TEXTBOX, 'facebook_url', 'Facebook Page', 20, 100, '', 'placeholder="https://www.facebook.com/juan.blogger" id="facebookUrlBox"');
		$loginForm->element(TEXTBOX, 'twitter_handle', 'Twitter Handle', 20, 100, '', 'placeholder="@TopBlogger" id="twitterHandleBox"');
		$loginForm->element(SUBMIT, 'action', 'Save Profile', 'class="btn btn-primary"');
		$loginForm->close();
		
		return $loginForm;
	}

	public function checkLoginStatus($redirectIfFalse = false) {
		if ($_SESSION['logged_in'] === true) { return true; }
		if (!empty($redirectIfFalse)) {
			if ($redirectIfFalse === true) {
				$this->_response->redirect(DIR_WS_ROOT . 'members/login/');
			} else {
				$this->_response->redirect($redirectIfFalse);
			}
		}
		return false;
	}
	
}
