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
	
	public function __construct(){
	}
	
	public function switchBoard(){
		$reqMethod=trim(strtoupper($_SERVER['REQUEST_METHOD']));
		$queryArray=split('/',trim($_SERVER['QUERY_STRING'],'/'));
		$rawRequest=@file_get_contents('php://input');

		$workspace=strtolower(trim($queryArray[0]));
		$serviceName=strtolower(trim($queryArray[1]));
		$requestParams=array_slice($queryArray,2);
		$this->queryArray=$queryArray;
		$this->serviceName=$service;
		
		if($workspace=='servicedocument'){
			$this->serviceDocument();
			return;
		}
		
		$service=$this->getRegisteredService($workspace,$serviceName);
		
		if(is_array($service)){
			if($service['fileName'])require_once($service['fileName']);
			if(is_array($service['serviceFunc'])){
				$service['serviceFunc'][0]->$service['serviceFunc'][1]($reqMethod,$requestParams,$rawRequest);
			}else{
				$this->output=$service['serviceFunc']($reqMethod,$requestParams,$rawRequest);
			}
		}else{
			echo "Could not find service:{$service['serviceFunc']} in $workspace"; //TODO: ERROR HERE
		}
	}

	public function registerService($workspace=NULL,$serviceName=NULL,$serviceFunctionName=NULL,$title=NULL,$filename=NULL){
		$workspace=strtolower(trim($workspace));
		$serviceName=strtolower(trim($serviceName));
		
		$serviceRecord=array(
			'fileName'		=>$fileName,
			'serviceFunc'	=>$serviceFunctionName,
			'title'			=>$title
		);
		
		$this->services[$workspace][$serviceName]=$serviceRecord;
	}
	
	public function getRegisteredService($workspace,$serviceName=NULL){
		$serviceName=strtolower(trim($serviceName));
		if(isset($this->services[$workspace][$serviceName]))return $this->services[$workspace][$serviceName];
		return false;
	}
	
	public function serviceDocument(){
		$service=new KTAPPServiceDoc(KT_APP_BASE_URI);
		
		foreach($this->services as $workspace=>$collection){
			//Creating the Default Workspace for use with standard atomPub Clients
			$ws=$service->newWorkspace($workspace);
			
			foreach($collection as $serviceName=>$serviceInstance){
				$col=$service->newCollection(KT_APP_BASE_URI.$workspace.'/'.$serviceName.'/',$serviceInstance['title'],$ws);
			}
		}
		
		$this->output=$service->getAPPdoc();
	}

	public function render(){
		ob_end_clean();
		header('Content-type: text/xml');
		echo $this->output;
	}
}


function KTAPP_service_folder($reqMethod,$reqParams){
	//print_r(array($reqMethod,$reqParams));
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

/**
 * 		200		=>'OK',
		201		=>'Created',
		204		=>'No Content'

 *
 */

class ktAPP_Service{
	const STATUS_OK					='200 OK';
	const STATUS_NOT_FOUND			='204 No Content';
	const STATUS_NOT_ALLOWED		='204 Not Allowed';
	const STATUS_NOT_AUTHENTICATED	='204 Not Authenticated';
	const STATUS_CREATED			='201 Created';
	const STATUS_UPDATED			='200 Updated';
	
	public $responseFeed=NULL;
	public $responseHeader=NULL;
	
	
	public function __construct($method,$params,$content){
		$this->method=$method;
		$this->params=$params;
		$this->rawContent=$content;
	}
	
	public function GET_action(){
		
	}
	
	public function PUT_action(){
		
	}
	
	public function POST_action(){
		
	}
	
	public function DELETE_action(){
		
	}
	
	
	private function setStatus($status=NULL){
		
	}
}


$APP=new KTAPP();
$APP->registerService('DMS','fulltree','KTAPP_service_fullTree','Full Document Tree');
$APP->registerService('DMS','folder','KTAPP_service_folder','Folder Detail');
$APP->registerService('DMS','document','KTAPP_service_document','Document Detail');
//echo '<pre>'.print_r($APP,true).'</pre>';
$APP->switchBoard();
$APP->render();

?>