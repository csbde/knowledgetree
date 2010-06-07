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
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");

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
		$this->uid = MD5($_SERVER['HTTP_HOST']) . '-' . $_SESSION['userID'];
		$this->isSecure = false;
		$this->additionalFields =  array();
		
	}

    function do_main()
    {
    	return $this->renderGetSatisfactionRedirect();
    }

    /**
     * This method returns the get satisfaction script to include
     *
     */
    private function getSatisfactionScript()
    {
        global $default;
        
        $fastPassScript = '';
        $message = '';
        
    	try
    	{
    	    if ($this->validateInput($message)) {
    	            	        
    	        $default->log->info('KEY : ' . '[' . $this->key . '] SECRET [' . $this->secret .']  EMAIL ['. $this->email  .']  NAME ['. $this->name . ']  UID ['. $this->uid . ']  IS_SECURE ['. $this->isSecure . ']  ADDITIONAL OPTIONS ['. var_export($this->additionalFields, true));
	            $fastPassScript = FastPass::script($this->key, $this->secret, $this->email, $this->name, $this->uid, $this->isSecure, $this->additionalFields);
	            $default->log->info("Support: FastPass Script : [" . $fastPassScript . "]");
    	    } else {
    	        Throw New Exception($message);
    	    }
    	    
    	}
    	catch(Exception $e)
    	{
    		$this->errorRedirectTo('control', _kt('Could not retrieve 1st "get satisfaction" script.') . $e->getMessage());
    	}
    	
    	return $fastPassScript;
    }    

    /**
     * This method returns the get satisfaction url to redirect to
     *
     */
    private function getSatisfactionUrl()
    {
        global $default;
        
        $fastPassUrl = '';
        $message = '';
        
    	try
    	{
    	    if ($this->validateInput($message)) {
	            $fastPassUrl = FastPass::url($this->key, $this->secret, $this->email, $this->name, $this->uid, $this->isSecure, $this->additionalFields);
	            $default->log->info("Support: FastPass Url : [" . $fastPassUrl . "]");
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
     * Returns true/false based on weather or not the arguments, required by getsatisfaction are valid.
     */
    private function validateInput(&$message) {
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
	    
	    return $isValid;
    }    
    
    /**
     * This returns the support url that takes the user to the support infrustructure
     * landing page at getsatisfaction.com/knowledgetree
     */
    private function getSupportUrl()
    {
        global $default;
        
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
            $default->log->info("Support: FastPassScript Contents : [" . $requestBody . "]");
    	    
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

    /**
     * This returns the support url that takes the user to the support infrustructure
     * landing page at getsatisfaction.com/knowledgetree
     */
    private function getScript($url)
    {
        global $default;
        
    	try
    	{
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $result = curl_exec($ch);
            $info = curl_getinfo($ch);
            curl_close ($ch);

    	    $requestBody = $result;
            //$default->log->info("Support: Get Script Contents : [" . $requestBody . "]");

            return $requestBody;
    	}
    	catch(Exception $e)
    	{
    		$this->errorRedirectTo('control', _kt('Could not retrieve support url.') . $e->getMessage());
    	}
    	
    	return false;
    }    
    
    /**
     * This method will render the dynamic javascript, set the cookies and redirect the user to
     * the getsatisfaction page.
     * 
     * @depricated Uses the smarty template.
     */
    private function renderGetSatisfactionRedirect__() {
    	$url = $this->getSatisfactionUrl();

        $this->aBreadcrumbs = array(array('name' => _kt("Support")));
    	
		//Adding the required Get Satisfaction script.
		$sJavascript = $this->getScript($url);
		$this->oPage->requireJSStandalone($sJavascript);
		
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/support.getsatisfaction');
		
		try {
    		$script = $this->getSatisfactionScript();
            $script = trim($script);
    		if ($script == '') {
    	        Throw New Exception("Error retrieving javascript from getsatisfaction.com");
    	    }
    	}
    	catch(Exception $e)
    	{
    		$this->errorRedirectTo('control', _kt('Support: Couldn\'t load support page.') . $e->getMessage());
    	}
    	
        $aTemplateData = array(
              "script" => $script,
              'url' => $url
        );
        
        return $oTemplate->render($aTemplateData);
        
    }
    
    
    /**
     * This method will render the dynamic javascript, set the cookies and redirect the user to
     * the getsatisfaction page.
     */
    private function renderGetSatisfactionRedirect() {
    	$url = $this->getSatisfactionUrl();

		//Adding the required Get Satisfaction script.
		$sJavascript = $this->getScript($url);
		
    	print '<html>';
		print '<title>Knowledgetree Support</title>';
		print '<head>';
		print '<script type="text/javascript" src="thirdpartyjs/jquery/jquery-1.3.2.js"> </script>
               <script type="text/javascript" src="thirdpartyjs/jquery/jquery_noconflict.js"> </script>';
		
		print "<script type='text/javascript'> $sJavascript </script>";
		print '</head>';
		
		try {
    		$script = $this->getSatisfactionScript();
            $script = trim($script);
    		if ($script == '') {
    	        Throw New Exception("Error retrieving javascript from getsatisfaction.com");
    	    }
    	}
    	
    	catch(Exception $e)
    	{
    		$this->errorRedirectTo('control', _kt('Support: Couldn\'t load support page.') . $e->getMessage());
    	}
    	
    	print '<body>';
    	print "$script";
    	//print '<a href="GSFN.goto_gsfn();" name="Support"> Support Page </a>';
    	print '<script type="text/javascript">
            		jQuery(document).ready(function() {
            			GSFN.goto_gsfn();
            		});
			   </script>';
    	print '</body>';
    	print '</html>';
    	
        exit;        
    }    
    
}

$oDispatcher = new GetSatisfactionDispatcher();
$oDispatcher->dispatch();

?>
