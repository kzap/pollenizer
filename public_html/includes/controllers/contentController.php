<?php

class contentController extends baseController {
	public function __construct($context) {
		parent::__construct($context);
		$this->c = new contents($this->dblink, SITES_ID);
	}
	
	public function indexAction() {
		global $_SITE;
		
		$_REQUEST['path'] = 0;
		$title = 'Articles & Information';
		$url = DIR_CONTENT;
		
		return $this->render('index', array(
			'title' => $title, 
			'h1' => $title, 
			'url' => $url,
			'canonical' => $url,
		));
	}
	
	public function viewCatAction($cpath) {
		
		// parse content_path
		$content_path = htmlentities($cpath, ENT_QUOTES, 'UTF-8');
	
		$seo_urlArray = array();
		foreach (explode('/', $content_path) as $val) {
			if (trim($val)) { $seo_urlArray[] = strtolower(trim($val)); }
		}
	
		// if has dirs, try to get category
		if (!empty($seo_urlArray)) {
			if ($category = $this->c->get_category_byseourl(implode('/', $seo_urlArray))) {
				$_REQUEST['path'] = $category['cpath'];
				$_REQUEST['seo_url'] = $category['seo_url'];
			}
		}
		
		if (!empty($category)) {
			$title = $category['categories_name'];
			$url = DIR_CONTENT.$category['seo_url'].'/';
			
			return $this->render('index', array(
				'title' => $title, 
				'h1' => $title, 
				'url' => $url,
				'canonical' => $url,
			));
		}

		// 404
		$this->render404();
	}
	
	public function viewAction($slug) {
		
		$content_path = htmlentities($slug, ENT_QUOTES, 'UTF-8');
		
		$seo_urlArray = array();
		foreach (explode('/', $content_path) as $val) {
			if (trim($val)) { $seo_urlArray[] = strtolower(trim($val)); }
		}
		
		$content_name = array_pop($seo_urlArray);
		
		$_REQUEST['content_name'] = $content_name = seo_format($content_name);
		if ($box = $this->c->get_byname($content_name)) {
			if (!$box['internal_only']) {
					
				if ($box['members_only']) {
					include(DIR_ROOT . 'members/members_application_top.php');
				}
				
				$title = $box['contents_title'];
				$url = DIR_CONTENT . $box['contents_slug'].'.php';
				
				return $this->render('index', array(
					'title' => $title, 
					'h1' => $title, 
					'url' => $url,
					'canonical' => $url,
				));
				
			}
			exit;
		}
		
		// 404
		$this->render404();
	}
	
}
