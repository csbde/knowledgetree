<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/email/Email.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
require_once("$default->fileSystemRoot/lib/web/WebSite.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("webDocumentUI.inc");

/**
 * $Id$
 *  
 * This page displays a web document pending publication and allows the webmaster
 * to flag the upload to 3rd party website as completed.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.documentmanagement
 */
 
/*
 * Querystring variables
 * ---------------------
 * fWebDocumentID - the web document to process
 */

// -------------------------------
// page start
// -------------------------------

// only if we have a valid session
if (checkSession()) {

    $oContent = new PatternCustom();
    
    // retrieve variables
    if ($fWebDocumentID) {
        $oWebDocument = WebDocument::get($fWebDocumentID);
        if ($oWebDocument) {
            if ($fUploaded && $fUploadUrl) {
                // the web document has been uploaded, so update the document status
                $oWebDocument->setStatusID(PUBLISHED);
                $oWebDocument->setDateTime(getCurrentDateTime());
                if ($oWebDocument->update()) {
                    // successfully updated- notify the originator
                    $oDocument = Document::get($oWebDocument->getDocumentID());
					$oUser = User::get($oDocument->getCreatorID());
					$oEmail = new Email();
					$sBody = "The document entitled '" . $oDocument->getName() . "' you requested for web publication has been uploaded to $fUploadUrl.";
					$oEmail->send($oUser->getEmail(), "Web publication", $sBody);
                    // redirect to the dashboard
                    controllerRedirect("dashboard", "");
                } else {
                    $oContent->setHtml(renderErrorPage("There was an error updating the web document status"));
                }
            } else {
                // display the upload instructions for the web master
                $oContent->setHtml(renderUploadPage($oWebDocument));
            }
        } else {
            $oContent->setHtml(renderErrorPage("The web document could not be retrieved from the database"));
        }
    } else {
        $oContent->setHtml(renderErrorPage("No web document selected"));
    }
    
    require_once("../../../webpageTemplate.inc");
    $main->setCentralPayload($oContent);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->render();
}
?>
