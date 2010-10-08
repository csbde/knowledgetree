<?php
//include_once('../../lib/session/Session.inc');
include_once('../../ktapi/ktapi.inc.php');
//include_once('../../config/dmsDefaults.php');
error_reporting(E_ERROR);

define('COMMS_DEBUG',true);
define('COMMS_TIMEOUT',60*3); //3 minutes

set_time_limit(COMMS_TIMEOUT);	//Be careful altering this inside the services area - it should never be set to 0 as that could cause runaway processes

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
function error_handler($errno,$errstr=null,$errfile=null,$errline=null){
	$e=new ErrorException($errstr,0,$errno,$errfile,$errline);
	print_r($e);
	if($GLOBALS['RET']){
		$GLOBALS['RET']->addError($e->getmessage());
		$GLOBALS['RET']->setDebug('Exception::',$e);
		echo $GLOBALS['RET']->getJson();
		exit;
	};
//	if($GLOBALS['RET']){
//		$GLOBALS['RET']->addError($errfile?$errstr:$e->getmessage());
//		$GLOBALS['RET']->setDebug($errfile?'ERR':'EXC',$errfile?(array('error_number'=>$e,'error_string'=>$errstr,'error_file'=>$errfile,'error_line'=>$errline)):$e);
//		echo $GLOBALS['RET']->getJson();
//		exit;
//	};
}

function exception_handler($e){
	if($GLOBALS['RET']){
		$GLOBALS['RET']->addError($e->getmessage());
		$GLOBALS['RET']->setDebug('Exception::',$e);
		echo $GLOBALS['RET']->getJson();
		exit;
	};
}

/**
 * Set the error & exception handlers
 */
$old_error_handler=set_error_handler('error_handler',E_ERROR);
$old_exception_handler=set_exception_handler('exception_handler');


/**
 * Load additional generic libaries
 */


//Interpret the Json Object that was passed
include_once('jsonWrapper.php');
include_once('webajaxhandler.php');
include_once('serviceHelper.php');
include_once('client_service.php');
include_once('clienttools_syslog.php');


// Creating the object that will be returned;
$RET=new jsonResponseObject();
if($_GET['datasource'])$RET->isDataSource=true;

//Instantiate base classes
$KT = new KTAPI();
$KT->get(3);// Set it to Use Web Version 3

//Pick up the session
$session=KTAPI_UserSession::getCurrentBrowserSession($KT);
if(PEAR::isError($session)){
	$RET->addError("Not Logged In");
	echo $RET->getJson();
	exit;
}
$KT->start_system_session($session->user->getUserName());


//Instantiate the ajax handler
$handler=new webAjaxHandler($RET,$KT);

?>