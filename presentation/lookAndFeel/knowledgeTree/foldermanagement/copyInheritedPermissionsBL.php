<?php
/**
 * $Id$
 *
 * Business logic used to edit folder properties
 *
 * Expected form variables:
 * o $fFolderID - primary key of folder user is currently browsing
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package foldermanagement
 */
 
require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fInheritedFolderID', 'fFolderID');

if (!checkSession()) {
    exit(0);
}

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListFromQuery.inc");
require_once("editUI.inc");
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    

$oPatternCustom = & new PatternCustom();

if (!isset($fFolderID)) {
    $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
    $main->setErrorMessage(_("No folder currently selected"));
    $main->setCentralPayload($oPatternCustom);						
    $main->render();
    exit(0);
}

$oFolder = Folder::get($fFolderID);
if (!$oFolder) {
    // folder doesn't exist
    $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
    $main->setErrorMessage(_("The folder you're trying to modify does not exist in the DMS"));
    $main->setCentralPayload($oPatternCustom);						
    $main->render();
    exit(0);
}

if (!isset($fInheritedFolderID)) {
    //else display an error message
    $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
    $main->setErrorMessage(_("No inherited folder given"));
    $main->setCentralPayload($oPatternCustom);						
    $main->render();
    exit(0);
}

$oInheritedFolder = Folder::get($fInheritedFolderID);
if (!$oInheritedFolder) {
    //else display an error message
    $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
    $main->setErrorMessage(_("The inherited folder given does not exist in the DMS"));
    $main->setCentralPayload($oPatternCustom);						
    $main->render();
    exit(0);
}

//if the user can edit the folder
if (!Permission::userHasFolderWritePermission($oFolder)) {
    //user does not have write permission for this folder,
    $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
    $main->setErrorMessage(_("You do not have permission to edit this folder"));
    $main->setCentralPayload($oPatternCustom);						
    $main->render();
    exit(0);
}

$sQuery = DBUtil::compactQuery("
SELECT
    GFL.group_id AS group_id,
    GFL.can_read AS can_read,
    GFL.can_write AS can_write
FROM
    $default->groups_folders_table AS GFL
WHERE GFL.folder_id = ?");
$aParams = array($fInheritedFolderID);
$aPermissions = DBUtil::getResultArray(array($sQuery, $aParams));

if (PEAR::isError($aPermissions)) {
    $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
    $main->setErrorMessage(_("Error retrieving folder permissions"));
    $main->setCentralPayload($oPatternCustom);						
    $main->render();
    exit(0);
}

foreach ($aPermissions as $aRow) {
    $aRow['folder_id'] = $fFolderID;
    $res = DBUtil::autoInsert($default->groups_folders_table, $aRow);
    if (PEAR::isError($res)) {
        $oPatternCustom->setHtml("<a href=\"javascript:history.go(-1)\"><img src=\"" . KTHtml::getBackButton() . "\" border=\"0\" /></a>\n");
        $main->setErrorMessage(_("Error saving folder permissions"));
        $main->setCentralPayload($oPatternCustom);						
        $main->render();
        exit(0);
    }
}

controllerRedirect("editFolder", "fFolderID=$fFolderID&fShowSection=folderPermissions");

?>
