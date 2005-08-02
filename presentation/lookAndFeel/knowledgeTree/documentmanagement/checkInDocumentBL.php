<?php
/**
 * $Id$
 *
 * Business Logic to check in a document.
 *
 * Expected form variable:
 * o $fDocumentID - primary key of document user is checking out
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
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

KTUtil::extractGPC('fDocumentID', 'fForStore', 'fFolderID', 'fCheckInComment', 'fCheckInType');

if (!checkSession()) {
    die("Session not set up");
}

require_once("$default->fileSystemRoot/lib/email/Email.inc");

require_once("$default->fileSystemRoot/lib/users/User.inc");

require_once("$default->fileSystemRoot/lib/documentmanagement/PhysicalDocumentManager.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentCollaboration.inc");    

require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderUserRole.inc");
require_once("$default->fileSystemRoot/lib/roles/Role.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");

require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/checkInDocumentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewUI.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");

function checkinDocument() {
    $oPatternCustom = & new PatternCustom();

    $iDocumentID = KTUtil::arrayGet($_REQUEST, 'fDocumentID');

    if (empty($iDocumentID)) {
        // no document id was set when coming to this page,
        $oPatternCustom->setHtml(renderErrorPage(_("No document is currently selected for check in")));
        return $oPatternCustom;
    }
    // instantiate the document
    $oDocument = & Document::get($iDocumentID);

    if (!$oDocument) {
        // couldn't instantiate the document
        $oPatternCustom->setHtml(renderErrorPage(_("Could not check in this document")));
        return $oPatternCustom;
    }

    // user has permission to check the document in
    if (!Permission::userHasDocumentWritePermission($oDocument)) {
        // no permission to checkout the document
        $oPatternCustom->setHtml(renderErrorPage(_("You do not have permission to check in this document")));
        return $oPatternCustom;
    }

    // and the document is checked out
    if (!$oDocument->getIsCheckedOut()) {
        $oPatternCustom->setHtml(renderErrorPage(_("You can't check in this document because its not checked out")));
        return $oPatternCustom;
    }

    // by you
    if ($oDocument->getCheckedOutUserID() != $_SESSION["userID"]) {
        // you don't have this doc checked out
        $oUser = User::get($oDocument->getCheckedOutUserID()); 
        $oPatternCustom->setHtml(renderErrorPage(_("You can't check in this document because its checked out by") . $oUser->getName()));
        return $oPatternCustom;
    }

    // if we're ready to perform the updates
    $bForStore = KTUtil::arrayGet($_REQUEST, 'fForStore');
    if (! $bForStore) {
        // prompt the user for a check in comment and the file
        $oPatternCustom->setHtml(getCheckInPage($oDocument));
        return $oPatternCustom;
    }

    // make sure the user actually selected a file first
    if (strlen($_FILES['fFile']['name']) == 0) {
        $sErrorMessage = _("Please select a document by first clicking on 'Browse'.  Then click 'Check-In'");
        $oPatternCustom->setHtml(getCheckInPage($oDocument));
        return $oPatternCustom;
    }

    // and that the filename matches
    global $default;
    $default->log->info("checkInDocumentBL.php uploaded filename=" . $_FILES['fFile']['name'] . "; current filename=" . $oDocument->getFileName());
    if ($oDocument->getFileName() != $_FILES['fFile']['name']) {
        $sErrorMessage = _("The file you selected does not match the current filename in the DMS.  Please try again.");
        $oPatternCustom->setHtml(getCheckInPage($oDocument));
        return $oPatternCustom;
    }

    $sCheckInComment = KTUtil::arrayGet($_REQUEST, 'fCheckInComment');
    $sCheckInType = KTUtil::arrayGet($_REQUEST, 'fCheckInType');
    $res = KTDocumentUtil::checkin($oDocument, $_FILES['fFile']['tmp_name'], $sCheckInComment, $sCheckInType);
    if (PEAR::isError($res)) {
        $oPatternCustom->setHtml(getErrorPage($res));
        return $oPatternCustom;
    }
    redirect("$default->rootUrl/control.php?action=viewDocument&fDocumentID=" . $oDocument->getID());
}

require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
$oPatternCustom = checkinDocument();
$main->setCentralPayload($oPatternCustom);
$main->setFormAction($_SERVER["PHP_SELF"]);
$main->setFormEncType("multipart/form-data");
if (isset($sErrorMessage)) {
    $main->setErrorMessage($sErrorMessage);
}
$main->render();

?>
