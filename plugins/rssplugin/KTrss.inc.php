<?php
/*
 * Created on 08 Jan 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
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

// widget includes.
require_once(KT_LIB_DIR . "/widgets/portlet.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
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
    	$aFullList = array_merge(KTrss::getDocuments($iUserId), KTrss::getFolders($iUserId));
    	if($aFullList){
    		$internalFeed = KTrss::arrayToXML($aFullList);
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
		    	$sQuery = "SELECT dt.document_id AS id, dt.datetime AS date, dt.comment AS transaction, dmv.name AS name " .
		    			"FROM document_metadata_version AS dmv, document_subscriptions AS ds, document_transactions AS dt " .
		    			"WHERE dmv.document_id = ds.document_id " .
		    			"AND dt.document_id = ds.document_id " .
		    			"AND ds.document_id = ? " .
		    			"AND ds.user_id = ? " .
		    			"ORDER BY date DESC " .
		    			"LIMIT 1";
		        $aParams = array($document_id, $iUserId);
		        $aDocumentsInfo = DBUtil::getResultArray(array($sQuery, $aParams));
		        $aDocuments[] = $aDocumentsInfo;
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
		    	$sQuery = "SELECT ft.folder_id AS id, ft.datetime AS date, ft.comment AS transaction, f.name AS name " .
		    			"FROM folders AS f, folder_subscriptions AS fs, folder_transactions AS ft " .
		    			"WHERE f.id = fs.folder_id " .
		    			"AND ft.folder_id = fs.folder_id " .
		    			"AND fs.folder_id = ? " .
		    			"AND fs.user_id = ? " .
		    			"ORDER BY date DESC " .
		    			"LIMIT 1";
		        $aParams = array($folder_id, $iUserId);
		        $aFoldersInfo = DBUtil::getResultArray(array($sQuery, $aParams));
		        if($aFoldersInfo){
		        	$aFolders[] = $aFoldersInfo;
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
    
    function getOneDocument($iDocumentId){
		$sQuery = "SELECT dt.document_id AS id, dt.datetime AS date, dt.comment AS transaction, dmv.name AS name " .
				"FROM document_metadata_version AS dmv, document_transactions AS dt " .
				"WHERE dmv.document_id = dt.document_id " .
				"AND dt.document_id = ? " .
				"ORDER BY date DESC ";
        $aParams = array($iDocumentId);
        $aDocumentInfo = DBUtil::getResultArray(array($sQuery, $aParams));
        if($aDocumentInfo){
        	$aDocuments[] = $aDocumentInfo;
        }
    	if (PEAR::isError($aDocumentInfo)) {
            return false;
        }
        if ($aDocuments){
            return $aDocuments;
        }
    }
    
    function getOneFolder($iFolderId){
    	$sQuery = "SELECT ft.folder_id AS id, ft.datetime AS date, ft.comment AS transaction, f.name AS name " .
    			"FROM folders AS f, folder_transactions AS ft " .
    			"WHERE ft.folder_id = f.id " .
    			"AND f.id = ? " .
    			"ORDER BY date DESC " .
    			"LIMIT 1";
        $aParams = array($iFolderId);
        $aFoldersInfo = DBUtil::getResultArray(array($sQuery, $aParams));
        if($aFoldersInfo){
        	$aFolders[] = $aFoldersInfo;
        }
        if (PEAR::isError($aFoldersInfo)) {
            // XXX: log error
            return false;
        }
        if ($aFolders){
            return $aFolders;
        }
    }
    
    // Takes in an array as a parameter and returns rss2.0 compatible xml
    function arrayToXML($aItems){
    	// Build path to host
    	$aPath = explode('/', trim($_SERVER['PHP_SELF']));
    	$hostPath = "http://".$_SERVER['HTTP_HOST']."/".$aPath[1]."/";
    	$feed = "<?xml version=\"1.0\"?>\n";
    	$feed .= "<rss version=\"2.0\">\n".
    			 "<channel>\n" .
	    			"<title>KnowledgeTree RSS</title>\n" .
	    			"<copyright>(c) 2006 The Jam Warehouse Software (Pty) Ltd. All Rights Reserved - KnowledgeTree Version: OSS 3.3 beta 7</copyright>\n" .
	    			"<link>".$hostPath."</link>\n" .
	    			"<description>KT-RSS</description>\n" .
	    			"<image>\n".
					"<title>KNowledgeTree RSS</title>\n".
					"<width>140</width>\n".
					"<height>28</height>".
					"<link>".$hostPath."knowledgeTree/</link>\n".
					"<url>".$hostPath."resources/graphics/ktlogo_rss.png</url>\n".
					"</image>\n";
	    foreach($aItems as $item){
	    	$feed .= "<item>" .
	    	         	"<title>".$item[0]['name']."</title>\n" .
	    	         	"<link>".$hostPath."view.php?fDocumentId=".$item[0]['id']."</link>\n" .
	    	         	"<description>".$item[0]['transaction']."</description>\n".
	    			 "</item>\n";
	    }
	    $feed .= "</channel>\n" .
	    		 "</rss>\n";
	    		 
	   return $feed;		
    }
    
    // Takes in an array as a parameter and returns rss 2.0 compatible xml
    function arrayToXMLSingle($aItems){
    	// Build path to host
    	$aPath = explode('/', trim($_SERVER['PHP_SELF']));
    	$hostPath = "http://".$_SERVER['HTTP_HOST']."/".$aPath[1]."/";
    	$feed = "<?xml version=\"1.0\"?>\n";
    	$feed .= "<rss version=\"2.0\">\n".
    			 "<channel>\n" .
	    			"<title>KnowledgeTree RSS</title>\n" .
	    			"<copyright>(c) 2006 The Jam Warehouse Software (Pty) Ltd. All Rights Reserved - KnowledgeTree Version: OSS 3.3 beta 7</copyright>\n" .
	    			"<link>".$hostPath."</link>\n" .
	    			"<description>KT-RSS</description>\n" .
	    			"<image>\n".
					"<title>KNowledgeTree RSS</title>\n".
					"<width>140</width>\n".
					"<height>28</height>".
					"<link>".$hostPath."knowledgeTree/</link>\n".
					"<url>".$hostPath."resources/graphics/ktlogo_rss.png</url>\n".
					"</image>\n";
	    foreach($aItems as $item){
	    	$feed .= "<item>" .
	    	         	"<title>".$item[0]['name']."</title>\n" .
	    	         	"<link>".$hostPath."view.php?fDocumentId=".$item[0]['id']."</link>\n" .
	    	         	"<description>".$item[0]['transaction']."</description>\n".
	    			 "</item>\n";
	    }
	    $feed .= "</channel>\n" .
	    		 "</rss>\n";
	    		 
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
    
    // Should be removed...not being used anywhere
    function authenticateFolder($iUserId, $iFolderId){
    	$aFList = KTrss::getFolderList($iUserId);
    	$result = false;
    	if($aFList){
	    	foreach($aFList as $folder_id){
	    		if($folder_id == $iFolderId){
	    			$result = true;
	    		}
	    	}
    	}
    	
    	return $result;
    }
    
    // Should be removed...not being used anywhere
    function authenticateDocument($iUserId ,$iDocumentId){
    	$aDList = KTrss::getDocumentList($iUserId);
    	$result = false;
    	if($aDList){
	    	foreach($aDList as $document_id){
	    		if($document_id == $iDocumentId){
	    			$result = true;
	    		}
	    	}
    	}
    	
    	return $result;
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
}
?>
