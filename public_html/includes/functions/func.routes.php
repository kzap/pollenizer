<?php

function addControllerMap($controllerMapCollection, $method = array('GET', 'POST'), $route = '*', $controllerMap = null) {
	global $__controllerMaps, $link;
	
	// parse arguments
	$args = func_get_args();
	switch (count($args)) {
		case 1:
			$controllerMap = $controllerMapCollection;
			$controllerMapCollection = $method = $route = null;
		break;
		
		case 2:
			$controllerMap = $method;
			$route = $controllerMapCollection;
			$controllerMapCollection = $method = null;
		break;
		
		case 3:
			$controllerMap = $route;
			$route = $method;
			$method = $controllerMapCollection;
			$controllerMapCollection = null;
		break;
		
		case 4:
		default:
		break;
	}
	
	// parse $controllerMap
	if (is_array($controllerMap)) {
		if (isset($controllerMap['controller'])) {}
		elseif (isset($controllerMap['c'])) { $controllerMap['controller'] = $controllerMap['c']; unset($controllerMap['c']); }
	} elseif (is_string($controllerMap)) {
		$tempControllerMap = explode('#', $controllerMap);
		$controllerMap = array();
		if (isset($tempControllerMap[0])) { $controllerMap['controller'] = $tempControllerMap[0]; }
		if (isset($tempControllerMap[1])) { $controllerMap['action'] = $tempControllerMap[1]; }
	}
	
	// if has collection
	if (!empty($controllerMapCollection)) {
		foreach($controllerMapCollection as $collection) {
			if (!is_array($collection)) { continue; }
			// set subVars
			$subMethod = $method;
			$subRoute = $route;
			$subControllerMap = $collection[count($collection)-1];
			switch (count($collection)) {
				case 2:
					$subRoute .= $collection[0];
				break;
				
				case 3:
					$subRoute .= $collection[1];
					$subMethod = $collection[0];
				break;
			}
			
			// parse $controllerMap
			if (is_array($subControllerMap)) {
				if (isset($subControllerMap['controller'])) {
				} elseif (isset($subControllerMap['c'])) {
					$subControllerMap['controller'] = $subControllerMap['c']; 
					unset($subControllerMap['c']);
				}
			} elseif (is_string($subControllerMap)) {
				$tempSubControllerMap = explode('#', $subControllerMap);
				$subControllerMap = array();
				if (isset($tempSubControllerMap[0]) && strlen($tempSubControllerMap[0])) {
					$subControllerMap['controller'] = $tempSubControllerMap[0];
				}
				if (isset($tempSubControllerMap[1]) && strlen($tempSubControllerMap[1])) {
					$subControllerMap['action'] = $tempSubControllerMap[1];
				}
			}
			// attach default controller and action
			$subControllerMap = array_merge((array) $controllerMap, (array) $subControllerMap);
			addControllerMap($subMethod, $subRoute, $subControllerMap);
		}

	} else {
		
		$controllerCallback = function($request, $response, $app) use ($controllerMap, $link) {
			//DEFAULTS
			$controllerPath = DIR_CONTROLLERS;
			$controllerFileExt = '.php';
			$controllerSuffix = 'Controller';
			$actionSuffix = 'Action';
			$defaultController = 'index';
			$defaultAction = 'index';
			
			$controller = trim($controllerMap['controller']);
        	$action  = trim($controllerMap['action']);
			if (!$controller) { $controller = $defaultController; }
			if (!$action) { $action = $defaultAction; }
			
			$context = new stdClass();
			$context->_request = $request;
			$context->_response = $response;
			$context->_app = $app;
			$context->dblink = $link;
			$args = array();
			foreach($request->params() as $key => $val) {
				if (is_int($key) && $key > 0) { $args[] = $val; }
			}
			
			if( '' === $controller )
    	        throw new classNotSpecifiedException('Class Name not specified');

	        if( '' === $action )
        	    throw new methodNotSpecifiedException('Method Name not specified');

	
	        //Because the class could have been matched as a dynamic element,
	        // it would mean that the value in $class is untrusted. Therefore,
	        // it may only contain alphanumeric characters. Anything not matching
	        // the regexp is considered potentially harmful.
	        $controller = str_replace('\\', '', $controller);
	        preg_match('/^[a-zA-Z0-9_]+$/', $controller, $matches);
	        if( count($matches) !== 1 )
	            throw new badClassNameException('Disallowed characters in class name ' . $controller);
	
	        //Apply the suffix
	        $file_name = $controllerPath . $controller . $controllerSuffix . $controllerFileExt;
	        $class = $controller . $controllerSuffix;
			$method = $action . $actionSuffix;
	        
	        //At this point, we are relatively assured that the file name is safe
	        // to check for it's existence and require in.
	        if (FALSE === file_exists($file_name)) {
	            throw new classFileNotFoundException('Class file not found' . $file_name);
	        } else {
	            require_once($file_name);
			}
	
	        //Check for the class class
	        if( FALSE === class_exists($class) )
	            throw new classNameNotFoundException('class not found ' . $class);
	
	        //Check for the method
	        if( FALSE === method_exists($class, $method))
	            throw new classMethodNotFoundException('method not found ' . $method);
			
			$obj = new $class($context);
			
			call_user_func_array(array($obj, $method), $args);
			exit;
		};

		// no collection so do record
		respond($method, $route, $controllerCallback);
	}
}
