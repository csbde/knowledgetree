<?php
/**
 * $Id: $
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
    		    		
    		$sUrl = KTInit::guessRootUrl();
    		global $default;
			$sRootUrl = ($default->sslEnabled ? 'https' : 'http') .'://'.$_SERVER['HTTP_HOST'].$sUrl;
    		$sCustomErrorPage = $sRootUrl.'/'.$sCustomErrorPage;
 		
    		
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
    	if($oError != null)
    	{
    		//call error handler
    		
    		$aErrorMessage = array ();
			$aErrorMessage['Error_MessageOne'] = $oError->getMessage();
			$aErrorMessage['Error_MessageTwo'] = $oError->getUserInfo();
    		
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
