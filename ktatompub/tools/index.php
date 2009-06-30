<?php


define('KT_APP_BASE_URI',"http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?/');
define('KT_APP_SYSTEM_URI',"http://".$_SERVER['HTTP_HOST']);

// Define whether to use in debug mode for viewing generated structures
define('KT_APP_WEB_OUTPUT',false); 



/**
 * Includes
 */
include_once('../../ktapi/ktapi.inc.php');
include_once('../lib/KTAPPHelper.inc.php');						//Containing helper bridge functions to KtAPI
include_once('../lib/KTAPDoc.inc.php');							//Containing the parent class allowing easy XML manipulation
include_once('../lib/KTAPPServiceDoc.inc.php');					//Containing the servicedoc class allowing easy ServiceDocument generation
include_once('../lib/KTAPPFeed.inc.php');							//Containing the response feed class allowing easy atom feed generation
include_once('../auth.php');										//Containing the authentication protocols





class KTAPP{
	private $services=array();
	private $errors=array();
	private $output='';
	private $header='200';
	
	private $headerLibrary=array(
		200		=>'OK',
		201		=>'Created',
		204		=>'No Content'
	);
	
	public function __construct(){
		$this->registerService('servicedocument',array($this,'serviceDocument'));
	}
	
	public function switchBoard(){
		$reqMethod=trim(strtoupper($_SERVER['REQUEST_METHOD']));
		$queryArray=split('/',trim($_SERVER['QUERY_STRING'],'/'));
		$service=strtolower(trim($queryArray[0]));
		$requestParams=array_slice($queryArray,1);
		$this->queryArray=$queryArray;
		$this->serviceName=$service;
		$service=$this->getRegisteredService($service);
		if(is_array($service)){
			if($service['fileName'])require_once($service['fileName']);
			if(is_array($service['serviceFunc'])){
				$service['serviceFunc'][0]->$service['serviceFunc'][1]($reqMethod,$requestParams);
			}else{
				$this->output=$service['serviceFunc']($reqMethod,$requestParams);
			}
		}else{
			echo 'service  not found'			;
		}
	}

	public function registerService($workspace=NULL,$serviceName=NULL,$serviceFunctionName=NULL,$fileName=NULL){
		$serviceRecord=array(
			'fileName'		=>$fileName,
			'serviceFunc'	=>$serviceFunctionName
		);
		$this->services[$workspace][$serviceName]=$serviceRecord;
	}
	
	public function getRegisteredService($serviceName=NULL){
		$serviceName=strtolower(trim($serviceName));
		if(isset($this->services[$serviceName]))return $this->services[$serviceName];
		return false;
	}
	
	public function serviceDocument(){
		$service=new KTAPPServiceDoc(KT_APP_BASE_URI);
		
		//Creating the Default Workspace for use with standard atomPub Clients
		$ws=$service->newWorkspace('DMS');
		
		foreach($this->services as $serviceName=>$serviceInstance){
			$col=$service->newCollection(KT_APP_BASE_URI.$serviceName.'/','',$ws);
		}
		
/*
		$col=$service->newCollection(KT_APP_BASE_URI.'fulltree/','Full Document Tree',$ws);
		$col=$service->newCollection(KT_APP_BASE_URI.'folder/','Folder Detail',$ws);
		$col=$service->newCollection(KT_APP_BASE_URI.'document/','Document Detail',$ws);
		$col=$service->newCollection(KT_APP_BASE_URI.'mimetypes/','Supported Mime Types',$ws);
*/
	
		$this->output=$service->getAPPdoc();
	}

	public function render(){
		//ob_end_clean();
		header('Content-type: text/xml');
		echo $this->output;
	}
}


function KTAPP_service_folder($reqMethod,$reqParams){
	//Create a new response feed
	$feed=new KTAPPFeed(KT_APP_BASE_URI);

	//Invoke the KtAPI to get detail about the referenced document
	$folderDetail=KTAPPHelper::getFolderDetail($reqParams[0]?$reqParams[0]:1);

	//Create the atom response feed
	$entry=$feed->newEntry();
	foreach($folderDetail as $property=>$value){
		$feed->newField($property,$value,$entry);
	}

	//Generate and set the output
	return $feed->getAPPdoc();
}


function KTAPP_service_fullTree($reqMethod,$reqParams){
	//Create a new response feed
	$feed=new KTAPPFeed(KT_APP_BASE_URI);
	
	//Invoke the KtAPI to get detail about the referenced document
	$tree=KTAPPHelper::getFullTree();

	//Create the atom response feed
	foreach($tree as $item){
		$id=$item['id'];
		$entry=$feed->newEntry();
		$feed->newField('id',$id,$entry);
		foreach($item as $property=>$value){
			$feed->newField($property,$value,$entry);
		}
	}


	//Generate and set the output
	return $feed->getAPPdoc();
}

function KTAPP_service_document($reqMethod,$reqParams){
	
	switch ($reqMethod){
		case 'GET':
			break;
		case 'PUT':
			break;
		case 'POST':
			break;
		case 'DELETE':
			break;
	}
	//Create a new response feed
	$feed=new KTAPPFeed(KT_APP_BASE_URI);

	//Invoke the KtAPI to get detail about the referenced document
	$docDetail=KTAPPHelper::getDocumentDetail($reqParams[0]);
	
	//Create the atom response feed
	$entry=$feed->newEntry();
	foreach($docDetail['results'] as $property=>$value){
		$feed->newField($property,$value,$entry);
	}
	//Add a downloaduri field manually
	$feed->newField('downloaduri',urlencode(KT_APP_SYSTEM_URI.'/action.php?kt_path_info=ktcore.actions.document.view&fDocumentId='.$docDetail['results']['document_id']),$entry);
	
	//Generate and set the output
	return $feed->getAPPdoc();
}


$APP=new KTAPP();
$APP->registerService('fulltree','KTAPP_service_fullTree','fasdfasdfasdfasdfa');
$APP->registerService('fulltree',array($obj,'KTAPP_service_fullTree'));
$APP->registerService('folder','KTAPP_service_folder');
$APP->registerService('document','KTAPP_service_document');
$APP->switchBoard();
$APP->render();
//echo '<pre>'.print_r($APP,true).'</pre>';

?>