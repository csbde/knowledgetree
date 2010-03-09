<?php
include_once('../../ktapi/ktapi.inc.php');
error_reporting(E_ERROR);

define('COMMS_DEBUG',true);

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
include_once('ajaxhandler.php');
include_once('serviceHelper.php');
include_once('client_service.php');
include_once('clienttools_syslog.php');


Clienttools_Syslog::logTrace('--','--','======================================================================== TRANSACTION START');

//Instantiate base classes
$KT = new KTAPI(3);
$RET=new jsonResponseObject();
if($_GET['datasource'])$RET->isDataSource=true;

$noAuthRequests=array(
	'auth.ping',
	//'auth.japiLogin',
	'kt.get_all_client_policies',
	'kt.get_languages',
	'kt.switchlang',
	'kt.get_max_fileupload_size',
	'kt.get_all_explorer_policies'
);

if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
           $path = KTPluginUtil::getPluginPath('ktdms.wintools');
           require_once($path . 'baobabkeyutil.inc.php');
}



$handler=new ajaxHandler($RET,$KT,$noAuthRequests);




//Determine the requested comms version & Load related libraries


/**
 * Reset the error & exception handlers
 */
//set_exception_handler($old_exception_handler);
//set_error_handler($old_error_handler,E_ALL);
?>