<?php
/**
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009, 2010 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */
session_start();
require_once("thirdparty/getsatisfaction/FastPass.php");
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/users/User.inc");

/**
 * The Get Satisfaction API registers a given user onto the getsatisfaction support
 * community platform.
 */
class GetSatisfactionDispatcher extends KTStandardDispatcher {

    /**
     * 
     * @var String $key 
     */
    private $key;
    private $secret;
    private $email;
    private $name;
    private $uid;
    private $isSecure = false;
    private $additionalFields = array();    	    
	private $objUser;
	
	public function __construct()
	{
		parent::KTStandardDispatcher();
		
		$this->objUser = new User();
		$this->objUser = $this->objUser->get($_SESSION['userID']);
		
		$this->key = 'wwhjh26psiyx';
		$this->secret = 'idegmf014t9r6mnjf1ynfs0lo9xdkxs4';
		$this->name = $this->objUser->getUserName();
		$this->email = $this->objUser->getEmail();
		$this->email = ($this->email != '')? $this->email : $this->name . '@knowledgetree.com';
		$this->uid = $_SESSION['userID'];
		$this->isSecure = false;
		$this->additionalFields =  array();
		
	}

    function do_main()
    {
    	redirect($this->getSupportUrl());
    }

    /**
     * This method returns the get satisfaction url to redirect to
     *
     */
    private function getSatisfactionUrl()
    {
        $fastPassUrl = '';
        $message = '';
        
    	try
    	{
    	    
    	    //Validating parameters
    	    $isValid = true;
    	    if (is_null($this->key)) {
    	        $message = 'The key supplied was invalid: ['.$this->key.']';
    	        $isValid = false;
    	    }
    	    
    	    if (is_null($this->secret)) {
    	        $message = 'The secret supplied was invalid: ['.$this->secret.']';
    	        $isValid = false;
    	    }

    	    if (is_null($this->email)) {
    	        $message = 'The email supplied was invalid: ['.$this->email.']';
    	        $isValid = false;
    	    }

    	    if (is_null($this->name)) {
    	        $message = 'The name supplied was invalid: ['.$this->name.']';
    	        $isValid = false;
    	    }
    	    
    	    if (is_null($this->uid)) {
    	        $message = 'The uid supplied was invalid: ['.$this->uid.']';
    	        $isValid = false;
    	    }
    	    
    	    if ($isValid) {
	            $fastPassUrl = FastPass::url($this->key, $this->secret, $this->email, $this->name, $this->uid, $this->isSecure, $this->additionalFields);
    	    } else {
    	        Throw New Exception($message);
    	    }
    	    
    	}
    	catch(Exception $e)
    	{
    		$this->errorRedirectTo('control', _kt('Could not retrieve 1st "get satisfaction" url.') . $e->getMessage());
    	}
    	
    	return $fastPassUrl;
    }    

    /**
     * This returns the support url that takes the user to the support infrustructure
     * landing page at getsatisfaction.com/knowledgetree
     */
    private function getSupportUrl()
    {
        $getSatisfactionUrl = $this->getSatisfactionUrl();
        $supportUrl = '';
        
    	try
    	{
            $ch = curl_init($getSatisfactionUrl);
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close ($ch);
    	    
    	    $requestBody = $result;
            
            $res = preg_match_all('/GSFN.company_url.*\=.*".*"/isU', $requestBody, $matches);

            if ($res) {
                //var_dump($matches[0][0]);
                $supportUrl = str_replace('GSFN.company_url', '', $matches[0][0]);
                $supportUrl = str_replace(' ', '', $supportUrl);
                $supportUrl = str_replace('="', '', $supportUrl);
                $supportUrl = str_replace(';&', '', $supportUrl);
                $supportUrl = str_replace('"', '', $supportUrl);
            } else {
    	        Throw New Exception("Couldn't Find Support URL in GetSatisfaction API Response.");
    	    }
    	}
    	catch(Exception $e)
    	{
    		$this->errorRedirectTo('control', _kt('Could not retrieve support url.') . $e->getMessage());
    	}
    	
    	return $supportUrl;
    }        
    
}

$oDispatcher = new GetSatisfactionDispatcher();
$oDispatcher->dispatch();

?>
