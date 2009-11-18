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
 
require_once(KT_LIB_DIR . '/validation/errorviewer.inc.php');
require_once(KT_LIB_DIR . '/validation/customerrorviewer.inc.php');
                
class KTCustomErrorCheck
{
    function customErrorInit($error)
	{
		$oCustomViewer =& KTCustomErrorViewer::initCustomErrorViewer();
		        
		//if the custom error messages are set to 'on' in the config file 
		//we check if the error page exists and redirect to it if it does.
		//if either the page doesn't exit or the custom error option is off in the config file
		//we carry out default error reporting
		if ($oCustomViewer->getCustomErrorConfigSetting() == 'on')
		{
			
			$CustomErrorPage = $oCustomViewer->getCustomErrorRedirectPage();
			if ( $CustomErrorPage != '0') //if an error is not returned from getCustomErrorRedirectPage();
			{
				
				$sErrorHandler = $oCustomViewer->getCustomErrorHandlerSetting();
					
				
				if ($sErrorHandler == 'on')
				{
					$oErrorHandler = KTCustomErrorHandler::initCustomErrorHandler();
					$oErrorHandler->logError($error);
				}
				
				//redirect
				$oCustomViewer->doCustomErrorPageRedirect($CustomErrorPage, $error);
				
				
				//exit without errors	
				return true;
		   	}
		   	else
		   	{
		   		return false;
		   	}
		}
		else
		{
			return false;
		}
	}
}
?>
