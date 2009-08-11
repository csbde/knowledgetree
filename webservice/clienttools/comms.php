<?php
include_once('../../ktapi/ktapi.inc.php');
require_once("../../config/dmsDefaults.php");
error_reporting(E_ERROR&~E_WARNING);

/**
 * Intercept Errors and Exceptions and provide a json response in return.
 * TODO: Make the json response 1. an object of its own and 2. versionable.
 *
 * @param unknown_type $e
 * @param unknown_type $errstr
 * @param unknown_type $errfile
 * @param unknown_type $errline
 * 
 * return json Error Response
 */
function error_handler($e,$errstr=null,$errfile=null,$errline=null){
	if($GLOBALS['RET']){
		$GLOBALS['RET']->addError($errfile?$errstr:$e->getmessage());
		$GLOBALS['RET']->setDebug('',$errfile?(array('error_number'=>$e,'error_string'=>$errstr,'error_file'=>$errfile,'error_line'=>$errline)):$e);
		echo $GLOBALS['RET']->getJson();
		exit;
	};
}

/**
 * Set the error & exception handlers
 */
$old_exception_handler=set_exception_handler('error_handler');
$old_error_handler=set_error_handler('error_handler',E_ALL);



/**
 * Load additional generic libaries
 */
require_once("../../config/dmsDefaults.php");


//Interpret the Json Object that was passed
include_once('jsonWrapper.php');
include_once('ajaxhandler.php');

//Instantiate base classes
$RET=new jsonResponseObject();
$handler=new ajaxHandler($RET);




//Determine the requested comms version & Load related libraries


/**
 * Reset the error & exception handlers
 */
set_exception_handler($old_exception_handler);
set_error_handler($old_error_handler,E_ALL);
?>