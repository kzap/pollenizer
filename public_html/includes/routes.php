<?php
require_once(DIR_CLASSES . 'klein.php/klein.php');
require_once(DIR_FUNCTIONS . 'func.routes.php');

addControllerMap(
	array(
		array('browse/?', '#browse'),
		array('', '#index'),
	),
	array('GET', 'POST'),
	'/?',
	'index'
);

addControllerMap(
	array(
		array('/[*:slug].php', '#view'),
		array('/[*:cpath]/?', '#viewCat'),
		array('/?', '#index'),
	),
	'GET', 
	'/articles', 
	'content'
);
/*
addControllerMap(
	array(
		array('/?', 'offers#index'),
		array('/[i:id]/?', 'offers#view'),
		array('/[i:id]/apply/?', 'offers#apply'),
	),
	'GET', 
	'/offers', 
	'offers'
);
*/

// errorController.php
addControllerMap('404', 'error#code404');
addControllerMap('405', 'error#code405');

respond(function ($request, $response) {
    // Handle exceptions => flash the message and redirect to the referrer
    $response->onError(function ($response, $err_msg) {
        $response->flash($err_msg);
        $response->back();
    });
});


// if SCRIPT_NAME is part of the REQUEST_URI, remove it from $uri since we ignore it
if ($_SERVER['SCRIPT_NAME'] == substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['SCRIPT_NAME']))) {
	$dispatchUri = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
} else {
	$dispatchUri = $_SERVER['REQUEST_URI']; 
}
dispatch($dispatchUri);

