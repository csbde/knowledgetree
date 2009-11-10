<?php
/**
 * $Id$
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

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR .'/authentication/DBAuthenticator.inc');
require_once(KT_LIB_DIR .'/authentication/authenticationutil.inc.php');
require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');

// Workaround for mod_auth when running php cgi
if(!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['HTTP_AUTHORIZATION'])){
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
}

// workaround to get http authentication working in cgi mode
// * older workaround - doesn't seem to be working now *
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

    if(OS_WINDOWS){
    	$sReferrer = $_SERVER['HTTP_USER_AGENT'];
    	// Check if this is IE 6
    	if(strstr($sReferrer, 'MSIE 6.0')){
    		header('Content-Type: application/rss+xml; charset=utf-8;');
		    header('Content-Disposition: inline; filename="rss.xml"');
		    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    	}
    }

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
        $aFullList = kt_array_merge(KTrss::getDocuments($id), KTrss::getFolders($id));
        $internalFeed = KTrss::arrayToXML($aFullList);
        echo $internalFeed;
    }
}

// Validate user credentials
function validateUser($username, $password){
    //return DBAuthenticator::checkPassword($username, $password);
    $oUser =& User::getByUsername($username);
    if(PEAR::isError($oUser) || is_a($oUser, 'KTEntityNoObjects')){
        return false;
    }

    return KTAuthenticationUtil::checkPassword($oUser, $password);
}
?>
