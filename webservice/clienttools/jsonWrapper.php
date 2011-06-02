<?php

class jsonContentException extends Exception {

    const INPUT_ERROR = 100001;

}

//=-=-=-=-=-=-=-=-=-=//

class jsonResponseObject {

    protected $title = '';
    protected $errors = array();
    protected $status = array('session_id' => '', 'random_token' => '');
    protected $data = array();
    protected $log = array();
    protected $request = array();
    protected $debug = array();

    public $additional = array();
    public $isDataSource = false;
    public $location = '';
    public $includeDebug = true;

    public $response = array(
        'requestName' => '',
        'errors' => array('hadErrors' => 0, 'errors' => array()),
        'status' => array('session_id' => '', 'random_token' => ''),
        'data' => array(),
        'request' => array(),
        'debug' => array(),
        'log' => array()
    );

    public function addError($message = null, $code = null)
    {
        $this->errors[md5($message)] = array('code' => $code, 'message' => $message);
        $user = isset($this->request['auth']['user']) ? $this->request['auth']['user'] : '';
        Clienttools_Syslog::logError($user, $this->location,array('code' => $code, 'message' => $message), '');
    }

    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    public function setStatus($varName = null, $value = null)
    {
        $this->status[$varName] = $value;
    }

    public function setData($varName = null, $value = null)
    {
        $this->data[$varName] = $value;
    }

    public function getData($varname = null)
    {
        if ($varname == null) {
            return $this->data;
        }
        else {
            return isset($this->data[$varname]) ? $this->data[$varname] : null;
        }
    }

    public function overwriteData($value = null)
    {
        $this->data = $value;
    }

    public function setDebug($varName = null, $value = null)
    {
        $this->debug[$varName] = $value;
        $user = isset($this->request['auth']['user'])?$this->request['auth']['user']:'';
        Clienttools_Syslog::logInfo($user, $this->location, $varName, $value);
    }

    public function addDebug($varName = null, $value = null)
    {
        $this->setDebug($varName, $value);
    }

    public function setRequest($request = null)
    {
        $this->request = $request;
    }


    public function setResponse($value = null)
    {
        $this->overwriteData($value);
    }

    public function setTitle($title = null)
    {
        $title = (string)$title;
        $this->title = $title;
    }

    public function log($str)
    {
        $this->log[] = '['.date('h:i:s').'] '.$str;
        $user = isset($this->request['auth']['user'])?$this->request['auth']['user']:'';
        Clienttools_Syslog::logTrace($user, $this->location, $str);
    }

    public function getJson()
    {
        $response = array_merge(array(
            'requestName' => $this->title,
            'errors' => array('hadErrors' => (count($this->errors) > 0) ? 1 : 0, 'errors' => $this->errors),
            'status' => $this->status,
            'data' => $this->data,
            'request' => $this->request,
            'debug' => $this->debug,
            'log' => $this->log
        ), $this->additional);

        if (!$this->includeDebug) { unset($response['debug']); }

        if ($this->isDataSource) {
            $response = json_encode($response['data']);
        }
        else {
            $response = json_encode($response);
        }

        return $response;
    }

}

//=-=-=-=-=-=-=-=-=-=//

class jsonWrapper {

    public $raw = '';
    public $jsonArray = array();
    public $packaged = false;

    public function __construct($content = null)
    {
        $this->raw = $content;
        $content = @json_decode($content, true);
        if (!is_array($content)) {
            throw new jsonContentException('Invalid JSON input', jsonContentException::INPUT_ERROR);
        }

        if (!is_array($content['request']['parameters'])) {
            $content['request']['parameters'] = array();
        }

        // check for a request package vs single request
        if(isset($content['request']['parameters']['name'])) {
        	if (is_array($content['request']['parameters']['name'])) {
            	$this->packaged = true;
        	}
        }

        $this->jsonArray = $content;
    }

    public function getVersion()
    {
        $version = $this->jsonArray['auth']['version'];
        $version = "{$version['major']}.{$version['minor']}.{$version['revision']}";

        return $version;
    }

}

?>