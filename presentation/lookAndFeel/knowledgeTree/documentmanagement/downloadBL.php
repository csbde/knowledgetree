<?php
/**
 * $Id$
 *
 * Contains the business logic required to download a document.
 *
 * Expected form varaibles:
 *   o $fDocumentID - Primary key of document to view
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");

// start the session for a download- workaround for the IE SSL bug
if (checkSession(true)) {
    if (isset($fDocumentID)) {
    	$oDocument = Document::get($fDocumentID);
    	if (Permission::userHasDocumentReadPermission($oDocument)) {
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
            $main->setErrorMessage(_("Either you do not have permission to view this document, or the document you have chosen no longer exists on the file system."));
            $main->setCentralPayload($oPatternCustom);            
			$main->render();
    	}
    } else {
        require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
        $oPatternCustom = new PatternCustom();
        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\" /></a>\n");
        $main->setErrorMessage(_("You have not chosen a document to view"));
        $main->setCentralPayload($oPatternCustom);            
		$main->render();
    }
}         
?>
