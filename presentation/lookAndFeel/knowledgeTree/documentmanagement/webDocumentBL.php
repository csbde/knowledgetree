<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/email/Email.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentCollaboration.inc");
require_once("$default->fileSystemRoot/lib/web/WebDocument.inc");
require_once("$default->fileSystemRoot/lib/web/WebSite.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("webDocumentUI.inc");
/**
 * $Id$
 *
 * This page displays a web document pending publication and allows the webmaster
 * to flag the upload to 3rd party website as completed.
 *
 * Querystring variables
 * ---------------------
 * fWebDocumentID - the web document to process
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
                    $oContent->setHtml(renderErrorPage(_("There was an error updating the web document status")));
                }
            } else {
                // display the upload instructions for the web master
                $oContent->setHtml(renderUploadPage($oWebDocument));
            }
        } else {
            $oContent->setHtml(renderErrorPage(_("The web document could not be retrieved from the database")));
        }
    } else {
        $oContent->setHtml(renderErrorPage(_("No web document selected")));
    }
    
    require_once("../../../webpageTemplate.inc");
    $main->setCentralPayload($oContent);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->render();
}
?>
