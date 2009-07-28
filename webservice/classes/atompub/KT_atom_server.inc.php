<?php
class KT_atom_server {

    public $output='';
	protected $services=array();
	protected $workspaceDetail=array();
	protected $errors=array();
	protected $queryArray=array();
	protected $serviceName='';
	protected $method='';
	protected $workspace='';
    protected $serviceObject = null;
    protected $renderBody=true;


	public function __construct(){
	}

    protected function hook_beforeDocCreate($doc){return true;}
    protected function hook_beforeDocRender($doc){return true;}

	/**
	 * Run the server switchboard - find the correct service class to instantiate, execute & render that class with the passed parameteres
	 *
	 */
	public function execute(){
		$reqMethod=trim(strtoupper($_SERVER['REQUEST_METHOD']));
		$queryArray=split('/',trim($_SERVER['QUERY_STRING'],'/'));
		$rawRequest=@file_get_contents('php://input');

        $workspace=strtolower(trim($queryArray[0]));
		$serviceName=strtolower(trim($queryArray[1]));
		$requestParams=array_slice($queryArray,2);
		$this->queryArray=$queryArray;
		$this->serviceName=$serviceName;
		$this->method=$reqMethod;
		$this->workspace=$workspace;

        if($workspace=='servicedocument'){
			$this->serviceDocument();
			return;
		}

		$service=$this->getRegisteredService($workspace,$serviceName);
		if(is_array($service)){
			$serviceClass=$service['serviceClass'];
			$serviceObject=new $serviceClass($reqMethod,$requestParams,$rawRequest);
            if($this->hook_beforeDocRender($serviceObject))	$this->output=$serviceObject->render();
		}else{
			$serviceObject=new KT_atom_service($requestParams,$rawRequest);
			$serviceObject->setStatus(KT_atom_service::STATUS_NOT_FOUND);
            if($this->hook_beforeDocRender($serviceObject))	$this->output=$serviceObject->render();
		}
		$this->serviceObject=$serviceObject;
        return $serviceObject;
	}


	public function registerService($workspaceCode=NULL,$serviceName=NULL,$serviceClass=NULL,$title=NULL){
		$workspaceCode=strtolower(trim($workspaceCode));
		$serviceName=strtolower(trim($serviceName));

		$serviceRecord=array(
			'fileName'		=>$fileName,
			'serviceClass'	=>$serviceClass,
			'title'			=>$title
		);

		$this->services[$workspaceCode][$serviceName]=$serviceRecord;
	}

	public function addWorkspaceTag($workspaceCode=NULL,$TagName=NULL,$tagValue=NULL){
		$workspaceCode=strtolower(trim($workspaceCode));
		if(!isset($this->workspaceDetail[$workspaceCode]))$this->workspaceDetail[$workspaceCode]=array();
		$this->workspaceDetail[$workspaceCode][$TagName]=$tagValue;
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
			$ws=$service->newWorkspace();

			$hadDetail=false;
			if(isset($this->workspaceDetail[$workspace]))if(is_array($this->workspaceDetail[$workspace])){
				foreach ($this->workspaceDetail[$workspace] as $wsTag=>$wsValue){
					$ws->appendChild($service->newElement($wsTag,$wsValue));
					$hadDetail=true;
				}
			}
			if(!$hadDetail){
				$ws->appendChild($service->newElement('atom:title',$workspace));
			}

			foreach($collection as $serviceName=>$serviceInstance){
				$col=$service->newCollection(KT_APP_BASE_URI.$workspace.'/'.$serviceName.'/',$serviceInstance['title'],$ws);
			}
		}

		$this->output=$service->getAPPdoc();
	}

	public function render(){
		ob_end_clean();
		header('Content-type: text/xml');
		if($this->renderBody)echo $this->output;
	}

	public function setNoContent($flag=false){
		$this->renderBody=$flag?false:true;
	}

}

?>