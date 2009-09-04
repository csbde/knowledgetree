<?php
class KT_atom_service{
	const STATUS_OK					 = '200 OK';
	const STATUS_NOT_FOUND			 = '204 No Content';
	const STATUS_NO_CONTENT			 = '204 No Content';
	const STATUS_NOT_ALLOWED		 = '204 Not Allowed';
	const STATUS_CREATED			 = '201 Created';
	const STATUS_UPDATED			 = '200 Updated';
	const STATUS_NOT_MODIFIED		 = '304 Not Modified';			// For use with ETag & If-None-Match headers.
	const STATUS_BAD_REQUEST         = '400 Bad Request';           // Client issued a wrongly constructed request
	const STATUS_NOT_AUTHENTICATED	 = '401 Not Authenticated';
	const STATUS_PRECONDITION_FAILED = '412 Precondition Failed';   // Could not update document because another a newer version exist on the server than the one you are trying to update
	const STATUS_SERVER_ERROR        = '500 Internal Server Error';	// Server encountered an error processing the request

	/*
		Array(
		    100 => 'Continue',
		    101 => 'Switching Protocols',
		    200 => 'OK',
		    201 => 'Created',
		    202 => 'Accepted',
		    203 => 'Non-Authoritative Information',
		    204 => 'No Content',
		    205 => 'Reset Content',
		    206 => 'Partial Content',
		    300 => 'Multiple Choices',
		    301 => 'Moved Permanently',
		    302 => 'Found',
		    303 => 'See Other',
		    304 => 'Not Modified',
		    305 => 'Use Proxy',
		    306 => '(Unused)',
		    307 => 'Temporary Redirect',
		    400 => 'Bad Request',
		    401 => 'Unauthorized',
		    402 => 'Payment Required',
		    403 => 'Forbidden',
		    404 => 'Not Found',
		    405 => 'Method Not Allowed',
		    406 => 'Not Acceptable',
		    407 => 'Proxy Authentication Required',
		    408 => 'Request Timeout',
		    409 => 'Conflict',
		    410 => 'Gone',
		    411 => 'Length Required',
		    412 => 'Precondition Failed',
		    413 => 'Request Entity Too Large',
		    414 => 'Request-URI Too Long',
		    415 => 'Unsupported Media Type',
		    416 => 'Requested Range Not Satisfiable',
		    417 => 'Expectation Failed',
		    500 => 'Internal Server Error',
		    501 => 'Not Implemented',
		    502 => 'Bad Gateway',
		    503 => 'Service Unavailable',
		    504 => 'Gateway Timeout',
		    505 => 'HTTP Version Not Supported'
		);


	*/

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
		$this->parseHeaders();
		$this->parsedXMLContent=$this->xml2array($this->rawContent);
		$this->setStatus(self::STATUS_OK);
		$this->responseFeed=new KT_atom_responseFeed(KT_APP_BASE_URI);
		switch(strtoupper($this->method)){
			case 'GET':		$this->GET_action();break;
			case 'PUT':		$this->PUT_action();break;
			case 'POST':	$this->POST_action();break;
			case 'DELETE':	$this->DELETE_action();break;
			default:		$this->UNSUPPORTED_action();break;
		}
	}

	public function GET_action(){
		$this->setStatus(KT_atom_service::STATUS_OK);
	}

	public function PUT_action(){
		$this->setStatus(KT_atom_service::STATUS_NOT_FOUND );
	}

	public function POST_action(){
		$this->setStatus(KT_atom_service::STATUS_NOT_FOUND );
	}

	public function DELETE_action(){
		$this->setStatus(KT_atom_service::STATUS_NOT_FOUND );
	}

	public function UNSUPPORTED_action(){
		$this->setStatus(KT_atom_service::STATUS_NOT_FOUND );
	}

    public function getHeaders()
    {
        return $this->headers;
    }

	public function render(){
		return $this->responseFeed->render();
	}

	protected function xml2array($xml)
    {
        if (trim($xml) == '') return array();

    	if(class_exists('XMLns2array'))
        {
			$array=XMLns2array::parse($xml);
		}
        else
        {
			$array=json_decode(json_encode(@simplexml_load_string($xml)), true);
		}
		return $array;
	}

	protected function parseHeaders(){
		$headers=null;
		if(function_exists('http_get_request_headers')){			//Try to use pcre_http library if it exists
			$headers=http_get_request_headers();
		}else{
			if(function_exists('apache_request_headers')){			//If not: try to use apache specific headers
				$headers=apache_request_headers();
			}else{													//If not: not supported - empty headers
				$headers=array();
			}
		}
		$this->headers=$headers;
	}

	public function setStatus($status=NULL){
		header("HTTP/1.1 ".$status);
	}

	public function setEtag($etagValue=NULL){
		if($etagValue)header('ETag: '.$etagValue);
	}

}
?>