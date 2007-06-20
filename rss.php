<?php
/**
 * $Id$
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
 */

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR .'/authentication/DBAuthenticator.inc');
require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

// widget includes.
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

require_once(KT_LIB_DIR . '/mime.inc.php');

// workaround to get http authentication working in cgi mode
$altinfo = KTUtil::arrayGet( $_SERVER, 'kt_auth', KTUtil::arrayGet( $_SERVER, 'REDIRECT_kt_auth'));
if ( !empty( $altinfo) && !isset( $_SERVER['PHP_AUTH_USER'])) {
    $val = $altinfo;
    $pieces = explode( ' ', $val);   // bad.
    if ( $pieces[0] == 'Basic') {
        $chunk = $pieces[1];
        $decoded = base64_decode( $chunk);
        $credential_info = explode( ':', $decoded);
        if ( count( $credential_info) == 2) {
            $_SERVER['PHP_AUTH_USER'] = $credential_info[0];
            $_SERVER['PHP_AUTH_PW'] = $credential_info[1];
            $_SERVER["AUTH_TYPE"] = 'Basic';
        }
    }
}

if (!validateUser($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
   header('WWW-Authenticate: Basic realm="KnowledgeTree DMS"');
   header('HTTP/1.0 401 Unauthorized');
   echo 'This RSS feed requires authentication. Please enter your username and password.';
   exit;
} else {
	$user = DBAuthenticator::getUser($_SERVER['PHP_AUTH_USER'], array('id'=>'id',));
	$id =  $user[$_SERVER['PHP_AUTH_USER']]['id'];
	
	
	if(KTUtil::arrayGet($_REQUEST, 'docId')){ // if a docId parameter is passed
		// get document id from http request object
		$iDocumentId = KTUtil::arrayGet($_REQUEST, 'docId');
		
		if(KTrss::validateDocumentPermissions($id, $iDocumentId)){ // if document passes validation check
			// get document info
			$aDocumentInfo[] = KTrss::getOneDocument($iDocumentId, $id);
			
			if($aDocumentInfo){			
				// create rss xml for document
				$documentFeed = KTrss::arrayToXML($aDocumentInfo);
			}else{
				// create rss xml for the error
				$error = KTrss::errorToXML(_kt('This document has returned a empty response'));
			}	
		}else{
			// create rss xml for error
			$error = KTrss::errorToXML(_kt('You are either not authorised to view details on this document or it does not exist.' .
					' Please visit http://' .$_SERVER['HTTP_HOST'].'/'.$GLOBALS['KTRootUrl'].'/ to browse for a valid document.'));
		}
		if(isset($error)){ // if an error exist, output...else out the result
			echo $error;
		}else{
			echo $documentFeed;
		}
   	}elseif(KTUtil::arrayGet($_REQUEST, 'folderId')){ // if a folderId parameter is passed
   		// get folder id from http request object
   		$iFolderId = KTUtil::arrayGet($_REQUEST, 'folderId');

		if(KTrss::validateFolderPermissions($id, $iFolderId)){ // if folder passes validation check
			// get folder info
			$aFolderInfo[] = KTrss::getOneFolder($iFolderId);
			
			if($aFolderInfo){
				// create rss xml for folder
				$folderFeed = KTrss::arrayToXML($aFolderInfo);
			}else{
				// create rss xml for error
				$error = KTrss::errorToXML(_kt('This document has returned a empty response'));
			}
		}else{
			// create rss xml for error
			$error = KTrss::errorToXML(_kt('You are either not authorised to view details on this folder or it does not exist.' .
					' Please visit http://' .$_SERVER['HTTP_HOST'].'/'.$GLOBALS['KTRootUrl'].'/ to browse for a valid folder.'));
		}
		if(isset($error)){ // if an error exist, output...else out the result
			echo $error;
		}else{
			echo $folderFeed;
		}
   	}else{ // else do normal rss parsing
   		// get full list of subscribed documents and folders
	   	$aFullList = array_merge(KTrss::getDocuments($id), KTrss::getFolders($id));
	   	$internalFeed = KTrss::arrayToXML($aFullList);
	   	echo $internalFeed;
   	}
}

// Validate user credentials
function validateUser($username, $password){
	return DBAuthenticator::checkPassword($username, $password);
}
?>
