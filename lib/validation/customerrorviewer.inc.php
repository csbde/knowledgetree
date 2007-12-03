<?php
/**
 * 
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */
 require_once(KT_LIB_DIR.'/validation/customerrorhandler.php');
 
 class KTCustomErrorViewer
 {
 	// {{{ initCustomErrorViewer()
 	function initCustomErrorViewer()
 	{
 		return $oCustomViewer =& new KTCustomErrorViewer; 
 	}
 	//}}}
 	
 	//{{{ getCustomErrorConfigSetting()
 	function getCustomErrorConfigSetting()
 	{
 		$oKTConfig =& KTConfig::getSingleton();
        $sCustomErrorCheck = $oKTConfig->get('CustomErrorMessages/customerrormessages');
        return $sCustomErrorCheck;
 	}	
 	//}}}	
 	
 	//{{{ getCustomErrorHandlerSetting()
 	function getCustomErrorHandlerSetting()
 	{
 		$oKTConfig =& KTConfig::getSingleton();
        $sCustomErrorCheck = $oKTConfig->get('CustomErrorMessages/customerrorhandler');
        return $sCustomErrorCheck;
 	}	
 	//}}}	
 	
 	// {{{ getCustomErrorRedirectPage()
    function getCustomErrorRedirectPage ()
    {
    	$oKTErrorConfig =& KTConfig::getSingleton();
    	$sCustomErrorPage = $oKTErrorConfig->get('CustomErrorMessages/customerrorpagepath');
    	
    	//if a filname is specified in the config.ini file make it into a url
    	if (substr($sCustomErrorPage, 0, 4) != 'http')
    	{
    		    		
    		$sCustomErrorPage = 'http://'.$_SERVER['HTTP_HOST'].'/'.$sCustomErrorPage;
    		
    	}

    	//checking if file exists  
    	//curl options will return the page header, we can then check for an http error  	
    	$CurlSession = curl_init();
    	curl_setopt($CurlSession, CURLOPT_URL, $sCustomErrorPage);
   		curl_setopt($CurlSession, CURLOPT_HEADER, true);
   		curl_setopt($CurlSession, CURLOPT_NOBODY, true);
   		curl_setopt($CurlSession, CURLOPT_RETURNTRANSFER, true);
   		$data = curl_exec($CurlSession);
   		curl_close($CurlSession);
        preg_match("/HTTP\/1\.[1|0]\s(\d{3})/",$data,$matches);

       	//checking for http error - if the retunr code isn't 200 then we have an error
       	//on an error we return 0 
        if ($matches[1] != '200')
        {
         	//if file does not exist return error code of 0
            return '0';
        }
        else
        {
        	//if file exists return error page address
        	return $sCustomErrorPage;
        	
        }
	}
    // }}}
    
    // {{{ customErrorPageRedirect()
    function doCustomErrorPageRedirect($CustomErrorPage, $oError = null)
    {
    	$sErrorMessage = '';
    	if($oError != null)
    	{
    		//call error handler
    		
    		$aErrorMessage = array ();
			$aErrorMessage['Error_MessageOne'] = $oError->getMessage();
			$aErrorMessage['Error_MessageTwo'] = $oError->getUserInfo();
    		//echo '<pre>';
    		//print_r($aErrorMessage);
    		//echo '</pre>';
    		//exit;
    		$customErrorHandler = KTCustomErrorHandler::initCustomErrorHandler();
    		$customErrorHandler->logError($oError);
    	}
    	
    	$ErrorPageCurlSession = curl_init($CustomErrorPage);
	//curl_setopt($ErrorPageCurlSession, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ErrorPageCurlSession, CURLOPT_POST, true);
		curl_setopt($ErrorPageCurlSession, CURLOPT_POSTFIELDS, $aErrorMessage);
    	$ErrorPageSent = curl_exec($ErrorPageCurlSession);
   	curl_close($ErrorPageCurlSession);
	
    }
 	// }}}
 }
 ?>
