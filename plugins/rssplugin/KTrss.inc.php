<?php
/*
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
 *
 */

// boilerplate.
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");

// document related includes
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentType.inc");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentFieldLink.inc");
require_once(KT_LIB_DIR . "/documentmanagement/documentmetadataversion.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/documentcontentversion.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
require_once(KT_LIB_DIR . "/security/Permission.inc");

require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

class KTrss{
    // Gets a listing of external feeds for user
    function getExternalFeedsList($iUserId){
    	$sQuery = "SELECT id, url, title FROM plugin_rss WHERE user_id = ?";
        $aParams = array($iUserId);
        $aFeeds = DBUtil::getResultArray(array($sQuery, $aParams));

        if (PEAR::isError($aFeeds)) {
            // XXX: log error
            return false;
        }
        if ($aFeeds) {
            return $aFeeds;
        }
    }

    // Gets full listing of data of documents and folders subscribed to
    function getInternalFeed($iUserId){
    	$documents = KTrss::getDocuments($iUserId);
    	$folders = KTrss::getFolders($iUserId);

    	if (is_null($documents)) $documents=array();
    	if (is_null($folders)) $folders=array();

    	$response = '';
    	$aFullList = kt_array_merge($documents,$folders );
    	if(!empty($aFullList)){
    		$internalFeed = KTrss::arrayToXML($aFullList);
    		$response = rss2arrayBlock($internalFeed);
    	}
    	return $response;
    }

    // Get the data for the document or folder
    function getExternalInternalFeed($sFeed, $iUserId){
        $aRss = array();
        $pos = strpos($sFeed, 'docId');

        if($pos === false){
            $pos = strpos($sFeed, 'folderId');
            $folderId = substr($sFeed, $pos+9);
            $aRss[] = KTrss::getOneFolder($folderId);
        }else{
            $docId = substr($sFeed, $pos+6);
            $aRss[] = KTrss::getOneDocument($docId, $iUserId);
        }

    	if($aRss){
    		$internalFeed = KTrss::arrayToXML($aRss);
    		$response = rss2arrayBlock($internalFeed);
    	}
    	return $response;
    }

    // Get list of document subscriptions
    function getDocumentList($iUserId){
    	$sQuery = "SELECT document_id as id FROM document_subscriptions WHERE user_id = ?";
        $aParams = array($iUserId);
        $aDocumentList = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($aDocumentList)) {
            // XXX: log error
            return false;
        }
        if($aDocumentList){
            return $aDocumentList;
        }
    }

    // Get list of folder subscriptions
    function getFolderList($iUserId){
        $sQuery = "SELECT folder_id as id FROM folder_subscriptions WHERE user_id = ?";
        $aParams = array($iUserId);
        $aFolderList = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($aFolderList)) {
            // XXX: log error
            return false;
        }
        if ($aFolderList) {
            return $aFolderList;
        }
    }

    // Get data for all documents subscribed to
    function getDocuments($iUserId){
    	$aDList = KTrss::getDocumentList($iUserId);
    	if($aDList){
	    	foreach($aDList as $document_id){
		        $document = KTrss::getOneDocument($document_id, $iUserId);
		        if($document){
		        	$aDocuments[] = $document;
		        }
	    	}
    	}
    	if (PEAR::isError($aDocuments)) {
            // XXX: log error
            return false;
        }
        if ($aDocuments) {
            return $aDocuments;
        }
    }

    // Get data for all folders subscribed to
    function getFolders($iUserId){
    	$aFList = KTrss::getFolderList($iUserId);

    	if($aFList){
	    	foreach($aFList as $folder_id){
		        $folder = KTrss::getOneFolder($folder_id);
		        if($folder){
		        	$aFolders[] = $folder;
		        }
	    	}
    	}

    	if (PEAR::isError($aFolders)) {
            // XXX: log error
            return false;
        }
        if ($aFolders){
            return $aFolders;
        }
    }

    function getChildrenFolderTransactions($iParentFolderId, $depth = '1'){
        $aParams = array($iParentFolderId);

        if($depth == '1'){
            // Get direct child folder id's
            $sQuery = "SELECT id FROM folders WHERE parent_id = ?";
        }else{
            // Get all child folders
            if($iParentFolderId == 1){
                $sQuery = "SELECT id FROM folders WHERE parent_folder_ids LIKE '?' OR parent_folder_ids LIKE '?,%'";
            }
	    	$sQuery = "SELECT id FROM folders WHERE parent_folder_ids LIKE '%,?' OR parent_folder_ids LIKE '%,?,%'";
	    	$aParams[] = $iParentFolderId;
    	}

        $aFolderList = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($aFolderList)) {
            // XXX: log error
            return false;
        }
        return $aFolderList;
    }

    function getChildrenDocumentTransactions($iParentFolderId, $depth = '1'){
    	$aParams = array($iParentFolderId);

        if($depth == '1'){
            // Get direct child document id's
            $sQuery = "SELECT id FROM documents WHERE folder_id = ?";
        }else{
            // Get all documents in child folders
            if($iParentFolderId == 1){
                $sQuery = "SELECT id FROM documents WHERE parent_folder_ids LIKE '?' OR parent_folder_ids LIKE '?,%'";
            }
	    	$sQuery = "SELECT id FROM documents WHERE parent_folder_ids LIKE '%,?' OR parent_folder_ids LIKE '%,?,%'";
	    	$aParams[] = $iParentFolderId;
    	}

        $aDocumentList = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        if (PEAR::isError($aDocumentList)) {
            // XXX: log error
            return false;
        }

        if ($aDocumentList) {
            $aDocumentTransactions = KTrss::getDocumentTransactions($aDocumentList);
        }
        if ($aDocumentTransactions){
            return $aDocumentTransactions;
        }
    }

    // get information on document
    function getOneDocument($iDocumentId, $iUserId){
        $aDData = KTrss::getDocumentData($iUserId, $iDocumentId);
        $aDTransactions = KTrss::getDocumentTransactions(array($iDocumentId));

        if($aDData){
        	$aDData['itemType'] = 'document';

    		// create mime info
			$aMimeInfo = KTrss::getMimeTypeInfo($iUserId, $iDocumentId);
			$aDData['mimeTypeFName'] = $aMimeInfo['typeFName'];
			$aDData['mimeTypeIcon'] = $aMimeInfo['typeIcon'];

        	$aDocument[] = $aDData;
        	$aDocument[] = $aDTransactions;
        }
    	if (PEAR::isError($aDData)) {
            return false;
        }
        if ($aDocument){
            return $aDocument;
        }
    }

    // get information for folder
    function getOneFolder($iFolderId){
    	$aFolder = array();
    	$aFData = KTrss::getFolderData($iFolderId);

    	if (PEAR::isError($aFData)) {
            return false;
        }

    	// Get child folder ids
    	$aFolderIds = KTrss::getChildrenFolderTransactions($iFolderId);

    	// Get folder transactions
    	$aFolderIds[] = $iFolderId;
    	$aFTransactions = KTrss::getFolderTransactions($aFolderIds);

    	if(PEAR::isError($aFTransactions)){
    	    return false;
    	}

    	// Get child document transactions
    	$aDocTransactions = KTrss::getChildrenDocumentTransactions($iFolderId);

    	if(!empty($aDocTransactions)){
            $aFTransactions = array_merge($aFTransactions, $aDocTransactions);

            // Sort the child folder and document transactions by date and reduce to 4
            $code = 'if (strtotime($a[datetime]) == strtotime($b[datetime])){
    	        return 0;
    	    }
    	    return (strtotime($a[datetime]) > strtotime($b[datetime])) ? -1 : 1;';

    		$compare = create_function('$a,$b', $code);

            usort($aFTransactions, $compare);
            $aFTransactions = array_slice($aFTransactions, 0, 4);
    	}

        if($aFData){
        	$aFData['itemType'] = 'folder';

    		// create mime info
			$aFData['mimeTypeFName'] = 'Folder';
			$aFData['mimeTypeIcon'] = KTrss::getFolderIcon();

        	$aFolder[] = $aFData;
        	$aFolder[] = $aFTransactions;
        }
        if ($aFolder){
            return $aFolder;
        }
    }

    function rss_sanitize($str, $do_amp=true)
    {

        $result = str_replace("\\\"","\"",str_replace('\\\'','\'',htmlspecialchars($str,ENT_NOQUOTES, 'UTF-8')));
        if ($do_amp)
        {
            $result = str_replace('&','&amp;',$result);
        }
        return $result;
    }

    // Takes in an array as a parameter and returns rss2.0 compatible xml
    function arrayToXML($aItems){
    	$hostPath = KTUtil::kt_url() . '/';

    	$head = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n
    	       <rss version=\"2.0\">\n
    	           <channel>\n
    	               <title>".APP_NAME." RSS</title>\n
    	               <copyright>(c) 2008 KnowledgeTree Inc.</copyright>\n
    	               <link>".$hostPath."</link>\n
    	               <description>KT-RSS</description>\n
    	               <image>\n
        	               <title>".APP_NAME." RSS</title>\n
        	               <width>140</width>\n
        	               <height>28</height>
        	               <link>".$hostPath."</link>\n
        	               <url>".$hostPath."resources/graphics/ktlogo_rss.png</url>\n
    	               </image>\n";


    	$feed = '';
    	foreach($aItems as $aItem){

    	    $aItemHead = $aItem[0][0];
    	    $aItemList = $aItem[1];

	    	if($aItem[0][itemType] == 'folder'){
	    		$sTypeSelect = 'folder.transactions&fFolderId';
	    	}elseif($aItem[0][itemType] == 'document'){
	    		$sTypeSelect = 'document.transactionhistory&fDocumentId';
	    	}


	    	if($aItem[0][0][owner]){
	    	    $owner = $aItem[0][0][owner];
	    	}else{
	    	    $owner = _kt('None');
	    	}

	    	$type = '';
	    	if($aItem[0][0][type]){
	    	    $type = '<tr><td>Document type: '.$aItem[0][0][type]."</td>\n<td></td></tr>\n";
	    	}

	    	if($aItem[0][0][workflow_status]){
	    	    $workflow = $aItem[0][0][workflow_status];
	    	}else{
	    	    $workflow = _kt('No Workflow');
	    	}

	    	$xmlItemHead = "<item>\n
	    	      <title>".htmlspecialchars($aItem[0][0][name], ENT_QUOTES, 'UTF-8')."</title>\n
	    	      <link>".$hostPath."action.php?kt_path_info=ktcore.actions.".htmlspecialchars($sTypeSelect, ENT_QUOTES, 'UTF-8')."=".$aItem[0][0]['id']."</link>\n
	    	      <description>\n";

	    	$htmlItem = "<table border='0' width='90%'>\n
	    	      <tr>\n
	    	          <td width='5%' height='16px'>
	    	              <a href='".$hostPath."action.php?kt_path_info=ktcore.actions.".$sTypeSelect."=".$aItem[0][0][id]."' >
	    	              <img src='".$aItem[0][mimeTypeIcon]."' align='left' height='16px' width='16px' alt='' border='0' /></a>
	    	          </td>\n
	    	          <td align='left'> ".$aItem[0][mimeTypeFName]."</td>\n
	    	      </tr>\n
	    	      <tr>\n
    	    	      <td colspan='2'>\n
        	    	      ".ucfirst($aItem[0]['itemType'])." Information (ID: ".$aItem[0][0][id].")</>\n
        	    	      <hr>\n

        	    	      <table width='95%'>\n
        	    	          <tr>\n
        	    	              <td>"._kt('Filename').": ".htmlspecialchars($aItem[0][0][filename], ENT_QUOTES, 'UTF-8')."</td>\n
        	    	          </tr>\n
        	    	          <tr>\n
        	    	              <td>"._kt('Author').": ".htmlspecialchars($aItem[0][0][author], ENT_QUOTES, 'UTF-8')."</td>\n
        	    	          </tr>\n
        	    	          <tr>\n
            	    	          <td>"._kt('Owner').": ".htmlspecialchars($owner, ENT_QUOTES, 'UTF-8')."</td>\n
            	    	          <td></td>\n
        	    	          </tr>\n
        	    	          ".$type."\n
        	    	          <tr>\n
        	    	              <td>"._kt('Workflow status').": ".htmlspecialchars($workflow, ENT_QUOTES, 'UTF-8')."</td>\n
        	    	              <td></td>\n
        	    	          </tr>\n
        	    	      </table><br>\n

        	    	      "._kt('Transaction Summary (Last 4)')."\n
        	    	      <hr>\n

	    	                  <table width='100%'>\n";

                        	    	foreach($aItem[1] as $item){
                        	    	    $htmlItem .= "<tr>\n
                            	    	        <td>".$item[type]." name:</td>\n
                            	    	        <td>".htmlspecialchars($item[name], ENT_QUOTES, 'UTF-8')."</td>\n
                        	    	        </tr>\n
                        	    	        <tr>\n
                        	    	            <td>Path:</td>\n
                        	    	            <td>".htmlspecialchars($item[fullpath], ENT_QUOTES, 'UTF-8')."</td>\n
                        	    	        </tr>\n
                        	    	        <tr>\n
                        	    	            <td>Transaction:</td>\n
                        	    	            <td>".htmlspecialchars($item[transaction_name], ENT_QUOTES, 'UTF-8')."</td>\n
                        	    	        </tr>\n
                        	    	        <tr>\n
                        	    	            <td>Comment:</td>\n
                        	    	            <td>".htmlspecialchars($item[comment], ENT_QUOTES, 'UTF-8')."</td>\n
											</tr>\n
											<tr>\n";

                        	    	            if($item[version]){
                        	    	                $htmlItem .= "<td>Version:</td>\n
                        	    	                <td>".$item[version]."</td>\n";
                        	    	            }
                        	    	        $htmlItem .= "</tr>\n
                        	    	        <tr>\n
                        	    	            <td>Date:</td>\n
                        	    	            <td>".$item[datetime]."</td>\n
                        	    	        </tr>\n
                        	    	        <tr>\n
                        	    	            <td>User:</td>\n
                        	    	            <td>".htmlspecialchars($item[user_name], ENT_QUOTES, 'UTF-8')."</td>\n
                        	    	        </tr>\n
                        	    	        <tr>\n
                        	    	            <td colspan='2'><hr width='100' align='left'></td>\n
                        	    	        </tr>\n";
                        	    	}
                        	   $htmlItem .= "</table>\n
                      </td>\n
                  </tr>\n
              </table>";

          $xmlItemFooter = "</description>\n</item>\n";

          // Use htmlspecialchars to allow html tags in the xml.
          $htmlItem = htmlspecialchars($htmlItem, ENT_QUOTES, 'UTF-8');

          $feed .= $xmlItemHead.$htmlItem.$xmlItemFooter;
	    }
	    $footer = "</channel>\n</rss>\n";

	    return $head.$feed.$footer;
    }

    // Takes in an array as a parameter and returns rss2.0 compatible xml
    function errorToXML($sError){
    	// Build path to host
    	$aPath = explode('/', trim($_SERVER['PHP_SELF']));
    	global $default;
    	$hostPath = "http" . ($default->sslEnabled ? "s" : "") . "://".$_SERVER['HTTP_HOST']."/".$aPath[1]."/";
    	$feed = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n
    	    <rss version=\"2.0\">\n

    			<channel>\n
	    			<title>".APP_NAME." RSS</title>\n
	    			<copyright>(c) 2008 KnowledgeTree Inc.</copyright>\n
	    			<link>{$hostPath}</link>\n
	    			<description>KT-RSS</description>\n
	    			<image>\n
					      <title>".APP_NAME." RSS</title>\n
					      <width>140</width>\n
					      <height>28</height>
					      <link>{$hostPath}knowledgeTree/</link>\n
					      <url>{$hostPath}resources/graphics/ktlogo_rss.png</url>\n
					  </image>\n
            <item>\n
    	          <title>Feed load error</title>\n
    	          <description>".$sError."</description>\n
    			 </item>\n
	      </channel>\n

	    	</rss>\n";

	   return $feed;
    }

    // Delete feed function
    function deleteFeed($iFeedId){
    	$res = DBUtil::autoDelete('plugin_rss', $iFeedId);
    }

    // Get title for external feed
    function getExternalFeedTitle($iFeedId){
    	$sQuery = "SELECT title FROM plugin_rss WHERE id = ?";
        $aParams = array($iFeedId);
        $sFeedTitle = DBUtil::getOneResultKey(array($sQuery, $aParams), 'title');

        if (PEAR::isError($sFeedTitle)) {
            // XXX: log error
            return false;
        }
        if ($sFeedTitle) {
            return $sFeedTitle;
        }
    }

    // Get url for external feed
    function getExternalFeedUrl($iFeedId){
    	$sQuery = "SELECT url FROM plugin_rss WHERE id = ?";
        $aParams = array($iFeedId);
        $sFeedUrl = DBUtil::getOneResultKey(array($sQuery, $aParams), 'url');

        if (PEAR::isError($sFeedUrl)) {
            // XXX: log error
            return false;
        }
        if ($sFeedUrl) {
            return $sFeedUrl;
        }
    }

    // Update external feed data
    function updateFeed($iFeedId, $sFeedTitle, $sFeedUrl){
    	$sQuery = "UPDATE plugin_rss SET title=?, url=? WHERE id=?";
        $aParams = array($sFeedTitle, $sFeedUrl, $iFeedId);
        $res = DBUtil::runQuery(array($sQuery, $aParams));

        return $res;
    }

    // Create new external feed
    function createFeed($sFeedTitle, $sFeedUrl, $iUserId){
        $aParams = array(
        'user_id' => $iUserId,
        'url' => $sFeedUrl,
        'title' => $sFeedTitle,
        );
        $res = DBUtil::autoInsert('plugin_rss', $aParams);

        return $res;
    }

    // Function to validate that a user has permissions for a specific document
    function validateDocumentPermissions($iUserId, $iDocumentId){
		// check if user id is in session. If not, set it
		if(!isset($_SESSION["userID"])){
			$_SESSION['userID'] = $iUserId;
		}
		// get document object
		$oDocument =& Document::get($iDocumentId);
		if (PEAR::isError($oDocument)) {
            return false;
        }

		// check permissions for document
		if(Permission::userHasDocumentReadPermission($oDocument)){
		    return true;
		}else{
			return false;
		}
	}

	// Function to validate that a user has permissions for a specific folder
	function validateFolderPermissions($iUserId, $iFolderId){
		// check if user id is in session. If not, set it
		if(!isset($_SESSION["userID"])){
			$_SESSION['userID'] = $iUserId;
		}
		// get folder object
		$oFolder = Folder::get($iFolderId);
		if (PEAR::isError($oFolder)) {
            return false;
        }

		// check permissions for folder
		if(Permission::userHasFolderReadPermission($oFolder)){
		    return true;
		}else{
			return false;
		}
	}

	// get icon link for rss
	function getRssLinkIcon(){
    	// built server path
        global $default;
    	$sHostPath = "http" . ($default->sslEnabled ? "s" : "") . "://".$_SERVER['HTTP_HOST']."/".$GLOBALS['KTRootUrl']."/";

        // create image
        $icon = "<img src='".$sHostPath."resources/graphics/rss.gif' alt='RSS' border=0/>";

        return $icon;
    }

    // get rss link for a document/folder
    function getRssLink($iItemId, $sItemType){
        $item = strToLower($sItemType);
        if($item == 'folder'){
        	$sItemParameter = '?folderId';
        }else if($item == 'document'){
        	$sItemParameter = '?docId';
        }

        // built server path
        global $default;
        $sHostPath = "http" . ($default->sslEnabled ? "s" : "") . "://" . $_SERVER['HTTP_HOST'];

        // build link
    	$sLink = $sHostPath.KTBrowseUtil::buildBaseUrl('rss').$sItemParameter.'='.$iItemId;

    	return $sLink;
    }

    // get rss icon link
    function getImageLink($iItemId, $sItemType){
    	return "<a href='".KTrss::getRssLink($iItemId, $sItemType)."' target='_blank'>".KTrss::getRssLinkIcon()."</a>";
    }

    // get the mime type id for a document
    function getDocumentMimeTypeId($iUserId, $iDocumentId){
		if(!isset($_SESSION["userID"])){
			$_SESSION['userID'] = $iUserId;
		}
		// get document object
		$oDocument =& Document::get($iDocumentId);

		$docMime = $oDocument->getMimeTypeID();
		return $docMime;
	}

	// get mime information for a document
    function getMimeTypeInfo($iUserId, $iDocumentId){
        global $default;
    	$mimeinfo['typeId'] = KTrss::getDocumentMimeTypeId($iUserId, $iDocumentId); // mime type id
		$mimeinfo['typeName'] = KTMime::getMimeTypeName($mimeinfo['typeId']); // mime type name
		$mimeinfo['typeFName'] = KTMime::getFriendlyNameForString($mimeinfo['typeName']); // mime type friendly name
		$mimeinfo['typeIcon'] = "http" . ($default->sslEnabled ? "s" : "") . "://".$_SERVER['HTTP_HOST']."/".$GLOBALS['KTRootUrl']."/resources/mimetypes/".KTMime::getIconPath($mimeinfo['typeId']).".png"; //icon path

		return $mimeinfo;
    }

    // get the default folder icon
    function getFolderIcon(){
    	global $default;
    	return $mimeinfo['typeIcon'] = "http" . ($default->sslEnabled ? "s" : "") . "://".$_SERVER['HTTP_HOST']."/".$GLOBALS['KTRootUrl']."/thirdparty/icon-theme/16x16/mimetypes/x-directory-normal.png"; //icon path
    }

    // get a document information
    function getDocumentData($iUserId, $iDocumentId){
    	if(!isset($_SESSION["userID"])){
			$_SESSION['userID'] = $iUserId;
		}
		// get document object
		$oDocument =& Document::get($iDocumentId);

		$cv = $oDocument->getContentVersionId();
		$mv = $oDocument->getMetadataVersionId();

		$sQuery = "SELECT dcv.document_id AS id, dmver.name AS name, dcv.filename AS filename, c.name AS author, o.name AS owner, dtl.name AS type, dwfs.name AS workflow_status " .
				"FROM documents AS d LEFT JOIN document_content_version AS dcv ON d.id = dcv.document_id " .
				"LEFT JOIN users AS o ON d.owner_id = o.id " .
				"LEFT JOIN users AS c ON d.creator_id = c.id " .
				"LEFT JOIN document_metadata_version AS dmv ON d.id = dmv.document_id " .
				"LEFT JOIN document_types_lookup AS dtl ON dmv.document_type_id = dtl.id " .
				"LEFT JOIN document_metadata_version AS dmver ON d.id = dmver.document_id " .
				"LEFT JOIN workflow_states AS dwfs ON dmver.workflow_state_id = dwfs.id " .
				"WHERE d.id = ? " .
				"AND dmver.id = ? " .
				"AND dcv.id = ? " .
				"LIMIT 1";

		$aParams = array($iDocumentId, $mv, $cv);
        $aDocumentData = DBUtil::getResultArray(array($sQuery, $aParams));
        if($aDocumentData){
			return $aDocumentData;
        }
    }

    // get a folder information
    function getFolderData($iFolderId){
		$sQuery = "SELECT f.id AS id, f.name AS name, f.name AS filename, c.name AS author, o.name AS owner, f.description AS description " .
				"FROM folders AS f " .
				"LEFT JOIN users AS o ON f.owner_id = o.id " .
				"LEFT JOIN users AS c ON f.creator_id = c.id " .
				"WHERE f.id = ? " .
				"LIMIT 1";

		$aParams = array($iFolderId);
        $aFolderData = DBUtil::getResultArray(array($sQuery, $aParams));
        if($aFolderData){
			return $aFolderData;
        }
    }

    // get a listing of the latest 3 transactions for a document
    function getDocumentTransactions($aDocumentIds){
        $sDocumentIds = implode(', ', $aDocumentIds);

    	$sQuery = "SELECT DT.datetime AS datetime, 'Document' AS type, DMV.name, D.full_path AS fullpath,
    	   DTT.name AS transaction_name, U.name AS user_name, DT.version AS version, DT.comment AS comment
    	   FROM document_transactions AS DT
    	   INNER JOIN users AS U ON DT.user_id = U.id
    	   INNER JOIN document_transaction_types_lookup AS DTT ON DTT.namespace = DT.transaction_namespace
    	   LEFT JOIN documents AS D ON DT.document_id = D.id
    	   LEFT JOIN document_metadata_version AS DMV ON D.metadata_version_id = DMV.id
    	   WHERE DT.document_id IN ($sDocumentIds)
    	   ORDER BY DT.datetime DESC
    	   LIMIT 4";

    	$aDocumentTransactions = DBUtil::getResultArray($sQuery);
    	if(!PEAR::isError($aDocumentTransactions)){
            return $aDocumentTransactions;
    	}
    }

    // Get a listing of the latest transactions for a folder and its child folders
    function getFolderTransactions($aFolderIds){
        $sFolderIds = implode(', ', $aFolderIds);

    	$sQuery = "SELECT FT.datetime AS datetime, 'Folder' AS type, F.name, F.full_path AS fullpath,
    	   DTT.name AS transaction_name, U.name AS user_name, FT.comment AS comment
    	   FROM folder_transactions AS FT LEFT JOIN users AS U ON FT.user_id = U.id
    	   LEFT JOIN document_transaction_types_lookup AS DTT ON DTT.namespace = FT.transaction_namespace
    	   LEFT JOIN folders AS F ON FT.folder_id = F.id
    	   WHERE FT.folder_id IN ($sFolderIds)
    	   ORDER BY FT.datetime DESC
    	   LIMIT 4";

    	$aFolderTransactions = DBUtil::getResultArray($sQuery);
		return $aFolderTransactions;
    }
}
?>
