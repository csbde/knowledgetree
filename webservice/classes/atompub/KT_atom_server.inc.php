<?php
class KT_atom_server{
	protected $services=array();
	protected $errors=array();
	protected $output='';
	protected $queryArray=array();
	protected $serviceName='';
	protected $method='';
	protected $workspace='';


	public function __construct(){
	}

	public function execute(){
		$reqMethod=trim(strtoupper($_SERVER['REQUEST_METHOD']));
		$queryArray=split('/',trim($_SERVER['QUERY_STRING'],'/'));
		$rawRequest=@file_get_contents('php://input');

		$workspace=strtolower(trim($queryArray[0]));
		$serviceName=strtolower(trim($queryArray[1]));
		$requestParams=array_slice($queryArray,2);
		$this->queryArray=$queryArray;
		$this->serviceName=$service;
		$this->method=$reqMethod;
		$this->workspace=$workspace;

		if($workspace=='servicedocument'){
			$this->serviceDocument();
			return;
		}

		$service=$this->getRegisteredService($workspace,$serviceName);
		if(is_array($service)){
			$serviceClass=$service['serviceClass'];
			echo 'made it';
			$serviceObject=new $serviceClass($reqMethod,$requestParams,$rawRequest);
			$this->output=$serviceObject->render();
		}else{
//            $this->serviceDocument();
//            return;
			$serviceObject=new KT_atom_service($requestParams,$rawRequest);
			$serviceObject->setStatus(KT_atom_service::STATUS_NOT_FOUND);
			$this->output=$serviceObject->render();
		}
	}

	public function registerService($workspace=NULL,$serviceName=NULL,$serviceClass=NULL,$title=NULL){
		$workspace=strtolower(trim($workspace));
		$serviceName=strtolower(trim($serviceName));

		$serviceRecord=array(
			'fileName'		=>$fileName,
			'serviceClass'	=>$serviceClass,
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
		$service=new KT_atom_serviceDoc(KT_APP_BASE_URI);

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

?>