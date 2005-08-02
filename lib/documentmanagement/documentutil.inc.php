<?php /* vim: set expandtab softtabstop=4 shiftwidth=4 foldmethod=marker: */
/**
 * $Id$
 *
 * Document-handling utility functions
 * 
 * Simplifies and canonicalises operations such as adding, updating, and
 * deleting documents from the repository.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . '/documentmanagement/DocumentFieldLink.inc');

class KTDocumentUtil {
    function createMetadataVersion($oDocument) {
        if (is_numeric($oDocument)) {
            $oDocument =& Document::get($oDocument);
            if (PEAR::isError($oDocument)) {
                return $oDocument;
            }
        }
        // XXX: PHP5 clone
        $oVersionDocument = $oDocument;
        $oVersionDocument->iId = -1;
        $oVersionDocument->setStatusID(STATUS_VERSION);
        $oVersionDocument->setLiveDocumentID($oDocument->getID());
        $oVersionDocument->setIsCheckedOut(false);
        $oVersionDocument->setCheckedOutUserID(null);
        $res = $oVersionDocument->create();
        if ($res !== true) {
            if (PEAR::isError($res)) {
                return $res;
            }
            // XXX: Remove when Document uses PEAR Errors
            return PEAR::raiseError($_SESSION["errorMessage"]);
        }

        $aFields =& DocumentFieldLink::getByDocument($oDocument);
        $iVersionDocumentID = $oVersionDocument->getID();
        foreach ($aFields as $oDFL) {
            // XXX: PHP5 clone
            $oVersionDFL = $oDFL;
            $oVersionDFL->iId = -1;
            $oVersionDFL->setDocumentID($iVersionDocumentID);
            $res = $oVersionDFL->create();
        }

        return $oVersionDocument;
    }

    function bumpVersion($oDocument) {
        if (is_numeric($oDocument)) {
            $oDocument =& Document::get($oDocument);
            if (PEAR::isError($oDocument)) {
                return $oDocument;
            }
        }
        $oDocument->setMetadataVersion($oDocument->getMetadataVersion()+1);
        return $oDocument->update();
    }

    function setModifiedDate($oDocument) {
        if (is_numeric($oDocument)) {
            $oDocument =& Document::get($oDocument);
            if (PEAR::isError($oDocument)) {
                return $oDocument;
            }
        }
        $oDocument->setLastModifiedDate(getCurrentDateTime());
        return $oDocument->update();
    }

    function checkin($oDocument, $sFilename, $sCheckInComment, $sCheckInType = "minor") {
        $sBackupPath = $oDocument->getPath() . "-" .  $oDocument->getMajorVersionNumber() . "." .  $oDocument->getMinorVersionNumber();
        $bSuccess = @copy($oDocument->getPath(), $sBackupPath);
        if ($bSuccess === false) {
            return PEAR::raiseError(_("Unable to backup document prior to upload"));
        }
        $oVersionedDocument = KTDocumentUtil::createMetadataVersion($oDocument);
        if (PEAR::isError($oVersionedDocument)) {
            return $oVersionedDocument;
        }

        if (!PhysicalDocumentManager::uploadPhysicalDocument($oDocument, $oDocument->getFolderID(), "", $sFilename)) {
            // reinstate the backup
            copy($sBackupPath, $oDocument->getPath());
            // remove the backup
            unlink($sBackupPath);
            return PEAR::raiseError(_("An error occurred while storing the new file on the filesystem"));
        }

        $oDocument->setMetadataVersion($oDocument->getMetadataVersion()+1);

        $oDocument->setFileSize($_FILES['fFile']['size']);
        $oDocument->setLastModifiedDate(getCurrentDateTime());
        $oDocument->setIsCheckedOut(false);
        $oDocument->setCheckedOutUserID(-1);

        // bump the version numbers
        if ($sCheckInType == "major") {
            $oDocument->setMajorVersionNumber($oDocument->getMajorVersionNumber()+1);
            $oDocument->setMinorVersionNumber(0);
        } else if ($sCheckInType == "minor") {
            $oDocument->setMinorVersionNumber($oDocument->getMinorVersionNumber()+1);
        }

        $bSuccess = $oDocument->update();
        if ($bSuccess !== true) {
            if (PEAR::isError($bSuccess)) {
                return $bSuccess;
            }
            return PEAR::raiseError(_("An error occurred while storing this document in the database"));
        }

        // create the document transaction record
        $oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), $sCheckInComment, CHECKIN);
        // TODO: check transaction creation status?
        $oDocumentTransaction->create();

        // fire subscription alerts for the checked in document
        $count = SubscriptionEngine::fireSubscription($oDocument->getID(), SubscriptionConstants::subscriptionAlertType("CheckInDocument"),
                 SubscriptionConstants::subscriptionType("DocumentSubscription"),
                 array( "folderID" => $oDocument->getFolderID(),
                        "modifiedDocumentName" => $oDocument->getName() ));
        global $default;
        $default->log->info("checkInDocumentBL.php fired $count subscription alerts for checked out document " . $oDocument->getName());
        return true;
    }
}

?>
