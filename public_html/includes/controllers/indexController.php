<?php

class indexController extends baseController {
	public function __construct($context) {
		parent::__construct($context);
	}
	
	public function indexAction() {
		global $_SITE;
		
		$form = new form_generator();
		$form->set_dblink($this->dblink);
		$form->open('form001', 'POST', '/browse');
		$form->element(FORMTITLE, '<h2>Enter Flickr Keywords</h2>');
		$form->element(TEXTBOX, 'keyword', 'Keyword', 50, 1000);
		//$form->element(TEXTBOX, 'flickr_imageid', 'Flickr Image ID (Option 2)<br /><small>(<a href="/thePhilippines/action/browse/">Need an image? Browse for one on Flickr here</a>)</small>', 50, 100);
		//$form->element(SELECTBOX, 'flickr_size', 'Flickr Image Size', $sizesArray, 1);
		$form->element(SUBMIT, 'action', 'Search', 'class="btn info"');
		$form->close();
		$formHtml = $form->get_html();

		return $this->render(null, array(
			'form' => $formHtml,
			'title' => $_SITE['site_name'], 
			'h1' => $_SITE['site_name'], 
			'url' => DIR_WS_ROOT
		));
	}

	public function browseAction($keyword = NULL) {
		global $_SITE;

		if ($this->_request->param('keyword')) {
			$keyword = explode(' ', $this->_request->param('keyword'));

			$flickr = new Zend_Service_Flickr($_SITE['flickr_api_key']);
			$tags = array((array) $keyword);

			if ($_REQUEST['tags']) {
				$new_tags = explode(',', trim($_REQUEST['tags'], ','));
				foreach ($new_tags as $tag) {
					if (trim($tag)) { $tags[] = $tag; }
				}
			}
			$options = array(
				'per_page' => ($_REQUEST['per_page'] ? $_REQUEST['per_page'] : 10),
				'page' => ($_REQUEST['page'] ? $_REQUEST['page'] : 'random'),
				'sort' => 'relevance',
				'license' => '7,4',
				'tag_mode' => ($_REQUEST['tag_mode'] ? $_REQUEST['tag_mode'] : 'or'),
				'text' => ($_REQUEST['text'] ? 'philippines '.$_REQUEST['text'] : ''),
			);
			$size = (in_array(ucfirst(strtolower($_REQUEST['size'])), array('Square', 'Thumbnail', 'Small', 'Medium', 'Large', 'Original')) ? ucfirst(strtolower($_REQUEST['size'])) : 'Small');
			if ($options['per_page'] > 100) { $options['per_page'] = 100; } // lets not get banned from flickr shall we
			if ($_REQUEST['debug']) { pre($tags); pre($options); }
		
		
			if ($options['page'] == 'random') {
				//echo '<p><a href="/browse/" class="btn large info">See More Random Flickr Images</a></p>';
				// for random first figure out how many in total result set
				unset($options['page']);
				
				// set search to max 1
				$per_page = $options['per_page'];
				$options['per_page'] = 1;
				// do search
				$searchResults = $flickr->tagSearch($tags, $options);
				if ($searchResults->totalResultsAvailable) {
					// get total results
					$totalResultsAvailable = $searchResults->totalResultsAvailable;
					// API max is 4000 even if there are more results...
					if ($totalResultsAvailable > 4000) { $totalResultsAvailable = 4000; }
				}

				$results = array();

				for ($i = 0; $i < $per_page; $i++) {
					$options['page'] = rand(1, $totalResultsAvailable);
					$searchResults = $flickr->tagSearch($tags, $options);
					foreach ($searchResults as $result) {
						if ($result->$size) {
							$results[] = $result;
						}
					}
				}

				
			} else {
				// regular search
				$results = $flickr->tagSearch($tags, $options);
/*
				echo '<ul class="thumbnails">';
				foreach ($results as $result) {
					if ($result->$size) {
						if ($_REQUEST['debug']) { pre($result); }
						if ($_REQUEST['debug']) { pre($result->$size); }
						echo '<div class="thumbnail">';
						echo '<a href="/thePhilippines/action/create_your_own/flickr_imageid/'.$result->id.'"><img src="'.$result->$size->uri.'" /></a>';
						//echo '<a href="'.substr($result->$size->clickUri, 0, strpos($result->$size->clickUri, 'sizes/')).'" target="blank"" target="_blank"">View on Flickr</a>';
						echo '</div>';
					}
				}
				echo '</ul>';
*/
			}
			
			$this->render(null, array(
				'results' => $results,
				'options' => $options,
				'size' => $size,
			));
		} else {
			echo 'no keywords entered';
		}
	}
	
}
