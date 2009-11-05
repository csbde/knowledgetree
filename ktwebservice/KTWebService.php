<?php
/**
 * Framework for a rest server
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

require_once('../config/dmsDefaults.php');
require_once('../ktapi/ktapi.inc.php');

/**
 * Base class for encoding the request response
 *
 * @author KnowledgeTree Team
 * @package KTWebService
 * @version Version 0.9
 */
class EncoderBase
{
    /**
     * Constructor for the base encoder
     *
	 * @author KnowledgeTree Team
	 * @access public
     */
    public function __construct()
    {
    }

    /**
     * Gets the encoder based on type - XML or JSON
     *
	 * @author KnowledgeTree Team
	 * @access public
	 * @static
     * @param string $type The type of encoder - xml|json
     * @return EncoderBase
     */
    public static function getEncoder($type)
    {
        $encoder = ucwords(strtolower($type)) . 'Encoder';
        return new $encoder();
    }

    /**
     * Performs the encoding
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param mixed $input The response to be encoded
     * @return string
     */
    public function encode($input)
    {
        return $input;
    }

    /**
     * Returns the headers associated with the encoding
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @return array
     */
    public function getHeaders()
    {
        return array();
    }
}

/**
 * Encodes the response output using json
 *
 * @author KnowledgeTree Team
 * @package KTWebService
 * @version Version 0.9
 */
class JsonEncoder extends EncoderBase
{
    /**
     * Serialises the response using json
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param mixed $input The response to be serialised
     * @return string The JSON string
     */
    public function encode($input)
    {
        $input = array('response' => $input);
        return json_encode($input);
    }
}

/**
 * Encodes the response output in XML
 *
 * @author KnowledgeTree Team
 * @package KTWebService
 * @version Version 0.9
 */
class XmlEncoder extends EncoderBase
{
    /**
     * Formats the output as XML
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param array $input The response to be formatted
     * @return string The XML
     */
    public function encode($input)
    {
        if(is_string($input)){
            $input = array($input);
        }

        if(!is_array($input)){
            // throw exception
            return false;
        }

        $xml = '<?xml version="1.0"  encoding="utf-8" ?>'."\n";
        $xml .= '<response>'."\n";
//        $xml .= '<response status="ok">'."\n";

        $xml .= XmlEncoder::createXmlFromArray($input);
        $xml .= '</response>'."\n";
        return $xml;
    }

    /**
     * Creates an XML string from an array
     *
	 * @author KnowledgeTree Team
	 * @access private
     * @param array $input The array to be formatted
     * @return string The XML
     */
    private static function createXmlFromArray($input)
    {
        $xml = '';
        foreach ($input as $key => $value) {

            if(is_numeric($key)){
                $key = 'item';
            }

            if(is_array($value)){
                $value = XmlEncoder::createXmlFromArray($value);
            }

            $xml .= "<$key>$value</$key>";
        }
        return $xml;
    }

    /**
     * Returns the xml headers
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @return array The headers as an array
     */
    public function getHeaders()
    {
        $headers = array();
        $headers['Content-type'] = 'text/xml';
        return $headers;
    }
}

/**
 * Base class for the request response
 *
 * @author KnowledgeTree Team
 * @package KTWebService
 * @version Version 0.9
 */
class ResponseBase
{
    /**
     * The formatting to be used in the response
     *
     * @access protected
     * @var string xml|json
     */
    protected $responseType;

    /**
     * Create the response and set the type.
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param string $type xml|json
     */
    public function __construct($type = 'xml')
    {
        $this->responseType = $type;
    }

    /**
     * Dispatch function for calling the appropriate request method
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param string $method The request method - get|post|put|delete
     * @param array $args The request arguments / parameters
     * @return bool FALSE on fail
     */
    public function _dispatch($method, $args)
    {
        $dispatch_method = "_{$method}";
        if(!is_callable(array($this, $dispatch_method))) {
            $this->_respondError("501 Not Implemented");
            return false;
        }

        $this->{$dispatch_method}($args);
    }

    /**
     * returns the protocol used in the request
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @return string
     */
    protected function _getProtocol()
    {
        $protocol = "HTTP/1.1";
        if(isset($_SERVER['SERVER_PROTOCOL'])) {
            $protocol = $_SERVER['SERVER_PROTOCOL'];
        }
        return $protocol;
    }

    /**
     * Sets the error header
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @param string $error
     */
    protected function _respondError($error)
    {
        $protocol = $this->_getProtocol();
        header("$protocol $error");
    }

    /**
     * Sets the response headers and body
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @param string $body The body of the response
     * @param array $headers The headers for the response
     */
    protected function _respond($body, $headers = array())
    {
        foreach($headers AS $header => $value) {
            header("{$header}: {$value}");
        }
        echo $body;
    }
}

/**
 * Request response class - takes the input request and creates the response
 *
 * @author KnowledgeTree Team
 * @package KTWebService
 * @version Version 0.9
 */
class Response extends ResponseBase
{
    protected $output;
    protected $headers;

    protected $error;
    protected $error_code;

    protected $class;
    protected $method;

    protected $ktapi;

    /**
     * Creates the response with the given type
     *
	 * @author KnowledgeTree Team
	 * @access public
     * @param string $type xml|json
     */
    public function __construct($type)
    {
        parent::__construct($type);

        $this->class = 'KTAPI';
    }

    /**
     * Parses the arguments for the method and parameters.
     * Uses reflection to determine the order of parameters and then runs the requested method.
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @param array $args The request arguments
     * @return array The result of the method
     */
    protected function callMethod($args)
    {
        $method = (isset($args['method'])) ? $args['method'] : '';
        $session_id = (isset($args['session_id'])) ? $args['session_id'] : NULL;
        unset($args['method']);
        unset($args['session_id']);

        // Get the available methods in KTAPI
        $reflect = new ReflectionClass($this->class);
        $methods = $reflect->getMethods();

        // Check that the method exists
        $exists = false;
        foreach ($methods as $var){
            if($var->getName() == $method){
                $exists = true;
                break;
            }
        }

        if(!$exists){
            $this->error = 'Method does not exist in the API: '.$method;
            $this->error_code = 404;
            return false;
        }

        $this->method = $method;

        // Get method parameters
        $reflectMethod = new ReflectionMethod($this->class, $method);
        $methodParams = $reflectMethod->getParameters();

        $orderedParams = array();
        // map parameters to supplied arguments and determine order
        foreach ($methodParams as $parameter){
            $param = isset($args[$parameter->getName()]) ? $args[$parameter->getName()] : '';

            if(empty($param)) {
                if(!$parameter->isOptional()){
                    $this->error = 'Missing required parameter: '.$parameter->getName();
                    return false;
                }
                $param = $parameter->getDefaultValue();
            }

            $orderedParams[$parameter->getPosition()] = $param;
        }

        // instantiate KTAPI and invoke method
        $ktapi = $this->get_ktapi($session_id);

        if(PEAR::isError($ktapi)){
            $this->error = 'API could not be authenticated: '.$ktapi->getMessage();
            $this->error_code = 404;
            return false;
        }

        $result = $reflectMethod->invokeArgs($ktapi, $orderedParams);

        return $result;
    }

    /**
     * Instantiate KTAPI and get the active session, if the session id is supplied
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @param string $session_id
     * @return KTAPI
     */
    protected function &get_ktapi($session_id = null)
    {
    	if (!is_null($this->ktapi))
    	{
    		return $this->ktapi;
    	}

    	$kt = new KTAPI();

    	// if the session id has been passed through - get the active session.
    	if(!empty($session_id)){
        	$session = $kt->get_active_session($session_id, null);

        	if (PEAR::isError($session))
        	{
        	    // return error / exception
                return $session;
        	}
    	}
    	$this->ktapi = $kt;
    	return $kt;
    }

    /**
     * Not sure what to do here yet
     * @todo
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @param unknown_type $input
     * @return unknown
     */
    protected function flattenInput($input)
    {
        //echo gettype($input);

        if(is_array($input)){
            return $input;
        }

        if(is_string($input)){
            return array($input);
        }

        return $input;
    }

    /**
     * Implements the GET. Returns a result for the given method.
     *
	 * @author KnowledgeTree Team
	 * @access protected
     * @param array $args The request arguments
     * @return bool FALSE if error
     */
    protected function _get($args)
    {
        $result = $this->callMethod($args);

        // if an error occurred, initiate the error response
        if($result === false){
            return false;
        }

        $result = $this->flattenInput($result);

        $encoder = EncoderBase::getEncoder($this->responseType);
        $this->output = $encoder->encode($result);
        $this->headers = $encoder->getHeaders();
    }

    /**
     * Implements the POST. Posts a set of parameters to a given method.
     *
     * @todo
	 * @author KnowledgeTree Team
	 * @access protected
     * @param array $args
     */
    protected function _post($args)
    {
        $result = $this->callMethod($args);

        // if an error occurred, initiate the error response
        if($result === false){
            return false;
        }

        $result = $this->flattenInput($result);

        $encoder = EncoderBase::getEncoder($this->responseType);
        $this->output = $encoder->encode($result);
        $this->headers = $encoder->getHeaders();
    }

    /**
     * Implements the PUT. Puts / creates a new resource.
     *
     * @todo
	 * @author KnowledgeTree Team
	 * @access protected
     * @param array $args
     */
    protected function _put($args)
    {
        $result = $this->callMethod($args);

        // if an error occurred, initiate the error response
        if($result === false){
            return false;
        }

        $result = $this->flattenInput($result);

        $encoder = EncoderBase::getEncoder($this->responseType);
        $this->output = $encoder->encode($result);
        $this->headers = $encoder->getHeaders();
    }

    /**
     * Implements the DELETE. Deletes a resource
     *
     * @todo
	 * @author KnowledgeTree Team
	 * @access protected
     * @param array $args
     */
    protected function _delete($args)
    {
        $result = $this->callMethod($args);

        // if an error occurred, initiate the error response
        if($result === false){
            return false;
        }

        $result = $this->flattenInput($result);

        $encoder = EncoderBase::getEncoder($this->responseType);
        $this->output = $encoder->encode($result);
        $this->headers = $encoder->getHeaders();
    }

    /**
     * Generates the response output / error output
     *
	 * @author KnowledgeTree Team
	 * @access public
     */
    public function output()
    {
        if(!empty($this->error)){
            $response = array('message' => $this->error, 'status_code' => 1);
            $encoder = EncoderBase::getEncoder($this->responseType);
            $this->output = $encoder->encode($response);
            $this->headers = $encoder->getHeaders();

            $this->_respondError($this->error_code);
        }

        $this->_respond($this->output, $this->headers);
    }
}

/**
 * Webservice class - parses the request and initiates the response.
 *
 * @author KnowledgeTree Team
 * @package KTWebService
 * @version Version 0.9
 */
class WebService
{
    /**
     * Handles the output
     *
     * @access private
     * @var Response object
     */
    private $response;

    /**
     * The requested method or function
     *
     * @access private
     * @var string
     */
    private $method;

    /**
     * The request arguments and parameters
     *
     * @access private
     * @var array
     */
    private $arguments;

    /**
     * Constructor for setting up the webservice
     *
	 * @author KnowledgeTree Team
	 * @access public
     */
    public function WebService()
    {
        // determine the method of request
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        // get the parameters / arguments based on the method
        $this->getArguments();

        // Check the response type - xml / json
        $responseType = (isset($this->arguments['type'])) ? $this->arguments['type'] : 'xml';
        unset($this->arguments['type']);

        // Set the output handler
        $this->response = new response($responseType);
    }

    /**
     * Gets the arguments / parameters from the request
     *
	 * @author KnowledgeTree Team
	 * @access private
     */
    private function getArguments()
    {
        $arguments = array();
        switch ($this->method){
            case 'put':
            case 'delete':
                parse_str(file_get_contents('php://input'), $arguments);
                break;
            case 'post':
                $arguments = $_POST;
                break;
            case 'get':
            default:
                $arguments = $_GET;
        }
        $this->arguments = $arguments;
    }

    /**
     * Dispatches the request
     *
	 * @author KnowledgeTree Team
	 * @access public
     */
    public function handle()
    {
        $this->response->_dispatch($this->method, $this->arguments);
        $this->response->output();
    }
}

// Instantiate the webservice
$ws = new WebService();
$ws->handle();

exit();

?>
