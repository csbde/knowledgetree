<?php
/**
 * $Id$
 *
 * Contains the business logic required to download a document.
 *
 * Expected form varaibles:
 *   o $fDocumentID - Primary key of document to view
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/permission.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

if (checkSession()) {
    if (isset($fDocumentID)) {
    	$oDocument = Document::get($fDocumentID);
    	if (Permission::userHasDocumentReadPermission($fDocumentID)) {
	        if (isset($fForInlineView)) {
				$oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Inline view", VIEW);
	            $oDocumentTransaction->create();
	            PhysicalDocumentManager::inlineViewPhysicalDocument($fDocumentID);			
			} else {
	            //if the user has document read permission, perform the download
	            if (isset($fVersion)) {
	                // we're downloading an old version of the document
	                $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document version $fVersion downloaded", DOWNLOAD);
	                $oDocumentTransaction->create();
	                
		        	// if the document is currently checked out, and we're the version we're downloading
		        	// is the same as the current version, then download the current version of the document	                
	                if ($oDocument->getIsCheckedOut() && ($fVersion == $oDocument->getVersion())) {
						PhysicalDocumentManager::downloadPhysicalDocument($fDocumentID);	                	
	                } else {
	                	PhysicalDocumentManager::downloadVersionedPhysicalDocument($fDocumentID, $fVersion);
	                }
	            } else {
	                // download the current version
	                $oDocumentTransaction = & new DocumentTransaction($fDocumentID, "Document downloaded", DOWNLOAD);
	                $oDocumentTransaction->create();
	                PhysicalDocumentManager::downloadPhysicalDocument($fDocumentID);
	            }
				exit;
	        }
    	} else {
	        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	        $oPatternCustom = new PatternCustom();    		
        	if ($oDocument) {
            	$oPatternCustom->setHtml("<a href=\"" . generateControllerLink("browse", "fFolderID=" . $oDocument->getFolderID()) . "\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        	} else {
        		$oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        	}
            $main->setErrorMessage("Either you do not have permission to view this document, or the document you have chosen no longer exists on the file system.");
            $main->setCentralPayload($oPatternCustom);            
			$main->render();
    	}
    } else {
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
        $oPatternCustom = new PatternCustom();
        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        $main->setErrorMessage("You have not chosen a document to view");
        $main->setCentralPayload($oPatternCustom);            
		$main->render();
    }
}         
?>