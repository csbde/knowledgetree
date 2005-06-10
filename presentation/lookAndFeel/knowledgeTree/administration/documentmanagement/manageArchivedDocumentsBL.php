<?php
/**
 * $Id$
 *
 * Business logic for searching archived documents
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
 * @package administration.documentmanagement
 */

require_once("../../../../../config/dmsDefaults.php");

require_once("$default->fileSystemRoot/presentation/Html.inc");

KTUtil::extractGPC('fConfirm', 'fDocumentIDs', 'fForSearch', 'fSearchString', 'fShowSection', 'fStartIndex', 'fToSearch');

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableTableSqlQuery.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/lib/archiving/ArchiveRestorationRequest.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");
    require_once(KT_DIR . "/presentation/lookAndFeel/knowledgeTree/search/advancedSearchUtil.inc");
    require_once(KT_DIR . "/presentation/lookAndFeel/knowledgeTree/search/advancedSearchUI.inc");
    require_once("archivedDocumentsUI.inc");

    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

    $main->setFormAction($_SERVER["PHP_SELF"]);

    if (!isset($fStartIndex)) {
        $fStartIndex = 1;
    }
    
    $oContent = new PatternCustom;

    if (strlen($fForSearch)) {
        dealWithAdvancedSearch($_REQUEST, $fStartIndex);
    } else if ($fDocumentIDs) {
        // got some documents to restore

        // instantiate document objects
        $aDocuments = array();
        for ($i = 0; $i < count($fDocumentIDs); $i++) {
            $aDocuments[] = & Document::get($fDocumentIDs[$i]);
        }

        if ($fConfirm) {
            // restore the specified documents

            $aErrorDocuments = array();
            $aSuccessDocuments = array();
            for ($i = 0; $i < count($aDocuments); $i++) {
                if ($aDocuments[$i]) {
                    // set the status to live
                    $aDocuments[$i]->setStatusID(LIVE);
                    if ($aDocuments[$i]->update()) {
                        // success
                        $default->log->info("manageArchivedDocumentsBL.php set status for document id=" . $fDocumentIDs[$i]);
                        $aSuccessDocuments[] = $aDocuments[$i];

                        // check if there are requests for this document to be archived
                        $aRequests = ArchiveRestorationRequest::getList(array("document_id = ?", $aDocuments[$i]->getID()));/*ok*/
                        $default->log->info("manageArchivedDocumentsBL.php about to send notification for " . count($aRequests) . " restoration requests for document id " . $aDocuments[$i]->getID());
                        for ($j=0; $j<count($aRequests); $j++) {
                            // email the users
                            // FIXME: refactor notification
                            // TODO: check email notification and valid email address
                            $oRequestUser = User::get($aRequests[$j]->getRequestUserID());
                            $sBody = "The document '" . generateControllerLink("viewDocument", "fDocumentID=" . $aDocuments[$i]->getID(), $aDocuments[$i]->getName()) . "'";
                            $sBody .= " has been restored from the archive.";
                            $oEmail = & new Email();
                            if ($oEmail->send($oRequestUser->getEmail(), _("Archived Document Restored"), $sBody)) {
                                $default->log->info("manageArchivedDocumentsBL.php sent email to " . $oRequestUser->getEmail());
                                // now delete the request
                                $iRequestID = $aRequests[$j]->getID();
                                if ($aRequests[$j]->delete()) {
                                    $default->log->info("manageArchivedDocumentsBL.php removing restoration request $iRequestID");
                                } else {
                                    $default->log->error("manageArchivedDocumentsBL.php error removing request $iRequestID");
                                }
                            } else {
                                $default->log->error("manageArchivedDocumentsBL.php error notifying " . arrayToString($oEmail) . " for document id " . $aDocuments[$i]->getID() . " restoration");
                            }
                        }
                    } else {
                        // error updating status change
                        $default->log->error("manageArchivedDocumentsBL.php couldn't retrieve document id=" . $fDocumentIDs[$i]);
                        $aErrorDocuments[] = $aDocuments[$i];
                    }
                } else {
                    // error retrieving document object
                    $default->log->error("manageArchivedDocumentsBL.php couldn't retrieve document id=" . $fDocumentIDs[$i]);
                }
            }
            // display status page.
            $oContent->setHtml(renderStatusPage($aSuccessDocuments, $aErrorDocuments));
        } else {
            // ask for confirmation before restoring the documents
            $oContent->setHtml(renderRestoreConfirmationPage($aDocuments));
        }
    } else {
        //display search criteria
		$oContent->setHtml(getSearchPage("", array(), _("Archived Documents Search"), true));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForSearch=1");
    }
    $main->setHasRequiredFields(true);
    $main->setCentralPayload($oContent);
    $main->render();
}

?>
