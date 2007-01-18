<?php
/*
 * Created on 08 Jan 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class KTrss extends KTEntity {
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
    
    // Takes in an array as a parameter and returns rss2.0 compatible xml
    function arrayToXML($aItems){
    	// Build path to host
    	$aPath = explode('/', trim($_SERVER['PHP_SELF']));
    	$hostPath = "http://".$_SERVER['HTTP_HOST']."/".$aPath[1]."/";
    	$feed = "<?xml version=\"1.0\"?>";
    	$feed .= "<rss version=\"2.0\">".
    			 "<channel>" .
	    			"<title>KnowledgeTree RSS</title>" .
	    			"<copyright>(c) 2006 The Jam Warehouse Software (Pty) Ltd. All Rights Reserved - KnowledgeTree Version: OSS 3.3 beta 7</copyright>" .
	    			"<link>".$hostPath."</link>" .
	    			"<description>KT-RSS</description>" .
	    			"<image>".
					"<title>KNowledgeTree RSS</title>".
					"<width>140</width>".
					"<height>28</height>".
					"<link>".$hostPath."knowledgeTree/</link>".
					"<url>".$hostPath."resources/graphics/ktlogo_rss.png</url>".
					"</image>";
	    foreach($aItems as $item){
	    	$feed .= "<item>" .
	    	         	"<title>".$item[0]['name']."</title>" .
	    	         	"<link>".$hostPath."view.php?fDocumentId=".$item[0]['id']."</link>" .
	    	         	"<description>".$item[0]['transaction']."</description>".
	    			 "</item>";
	    }
	    $feed .= "</channel>" .
	    		 "</rss>";
	    		 
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
}
?>
