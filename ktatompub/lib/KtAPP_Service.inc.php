<?php
class ktAPP_Service{
	const STATUS_OK					='200 OK';
	const STATUS_NOT_FOUND			='204 No Content';
	const STATUS_NOT_ALLOWED		='204 Not Allowed';
	const STATUS_NOT_AUTHENTICATED	='204 Not Authenticated';
	const STATUS_CREATED			='201 Created';
	const STATUS_UPDATED			='200 Updated';
	const STATUS_NOT_MODIFIED		='304 Not Modified';
	const STATUS_PRECONDITION_FAILED='412 Precondition Failed';			//Could not update document because another a newer version exist on the server than the one you are trying to update

	public $responseFeed=NULL;
	public $responseHeader=NULL;
	public $method='';
	public $params='';
	public $rawContent='';
	public $parsedXMLContent='';
	public $headers=array();

	public function __construct($method,$params,$content){
		$this->method=$method;
		$this->params=$params;
		$this->rawContent=$content;
		$this->parsedXMLContent=json_decode(json_encode(@simplexml_load_string($this->rawContent)),true);
		$this->setStatus(self::STATUS_OK);
		$this->responseFeed=new KTAPPFeed(KT_APP_BASE_URI);
		switch(strtoupper($this->method)){
			case 'GET':		$this->GET_action();break;
			case 'PUT':		$this->PUT_action();break;
			case 'POST':	$this->POST_action();break;
			case 'DELETE':	$this->DELETE_action();break;
		}
	}

	public function GET_action(){
		$this->setStatus(ktAPP_Service::STATUS_OK);
	}

	public function PUT_action(){
		$this->setStatus(ktAPP_Service::STATUS_NOT_FOUND );
	}

	public function POST_action(){
		$this->setStatus(ktAPP_Service::STATUS_NOT_FOUND );
	}

	public function DELETE_action(){
		$this->setStatus(ktAPP_Service::STATUS_NOT_FOUND );
	}


	public function render(){
		return $this->responseFeed->render();
	}

	private function parseHeaders(){

	}

	private function setStatus($status=NULL){
		header("HTTP/1.1 ".$status);
	}

	private function setEtag($etagValue=NULL){
		if($etagValue)header('ETag: '.$etagValue);
	}


}
?>