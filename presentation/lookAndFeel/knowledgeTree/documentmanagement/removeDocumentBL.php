<?php
/**
 * $Id$
 *
 * Business Logic to remove a document from the database.
 *
 * Expected form variable:
 * o $fDocumentID - primary key of the document to be deleted
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {    
    require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");    
    require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
    require_once("$default->owl_fs_root/lib/documentmanagement/PhysicalDocumentManager.inc");    
    
    $oPatternCustom = & new PatternCustom();

    if (isset($fDocumentID)) {
        // if the user selected a document to delete
        
        // retrieve the document from the db
        $oDocument = & Document::get($fDocumentID);
        
        // retrieve the folderID
        $iFolderID = $oDocument->getFolderID();
        
        if (Permission::userHasFolderWritePermission($iFolderID)) {
            // user has permission to remove a document from this folder
                        
            // remove the document from the database (making sure that it exists first)
            if (Document::documentExists($oDocument->getFileName(), $oDocument->getFolderID())) {
                /*
                $sDeletedName = $oDocument->getFileName() . "_deleted_" . getCurrentDateTime();
                // rename physical file
                if (PhysicalDocumentManager::renamePhysicalDocument($oDocument, $sDeletedName)) {
                    // rename filename in database
                    $oDocument->setFileName($sDeletedName);
                
                    if ($oDocument->update()) {*/
                    if ($oDocument->delete()) {
                        // TODO: insert into sys_deleted
                        
                        // success, redirect to the browse folder view
                        controllerRedirect("browse", "fFolderID=$iFolderID");
                    } else {
                        // failure deleting document
                        $oPatternCustom->setHtml("<p class=\"errorText\">There was an error removing the document.</p>\n");
                    }
                /*} else {
                    // fs rename failed
                    $oPatternCustom->setHtml("<p class=\"errorText\">There was am error removing the document.</p>\n");
                }*/
            } else {
                // document doesn't exist
                $oPatternCustom->setHtml("<p class=\"errorText\">The document you're trying to remove does not exist.</p>\n");                
            }
        } else {
            // no permission to remove document
            $oPatternCustom->setHtml("<p class=\"errorText\">You do not have permission to remove a document from this folder.</p>\n");
        }
    } else {
        // no documentID passed in
        $oPatternCustom->setHtml("<p class=\"errorText\">No document which can be removed is currently selected.</p>\n");
    }
    
    require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    $main->render();            
}
?>
