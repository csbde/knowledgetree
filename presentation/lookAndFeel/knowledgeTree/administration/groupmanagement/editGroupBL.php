<?php
/**
 * $Id$
 *
 * Edit a group.
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.groupmanagement
 */
 
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("editGroupUI.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/lib/groups/Group.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");


    $oPatternCustom = & new PatternCustom();

    // if a new group has been added
    if (isset($fFromCreate)) {

        if($fGroupID == -1) {
            $oPatternCustom->setHtml(getAddFailPage());
        } else {
            $oPatternCustom->setHtml(getCreatePage($fGroupID));
        }

        $main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=editGroupSuccess"));

        // coming from manual edit page
    }
    else if (isset($fForStore)) {
        $oGroup = Group::get($fGroupID);
        $oGroup->setName($fGroupName);

        //check if checkbox checked
        $oGroup->setUnitAdmin(isset($fGroupUnitAdmin));
        //check if checkbox checked
        $oGroup->setSysAdmin(isset($fGroupSysAdmin));

        if ($oGroup->update()) {
            // if successfull print out success message
            $oPatternCustom->setHtml(getEditPageSuccess());
        } else {
            // if fail print out fail message
            $oPatternCustom->setHtml(getEditPageFail());
        }
    } else if (isset($fGroupID)) {
        // post back on group select from manual edit page
        $oPatternCustom->setHtml(getEditPage($fGroupID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
    } else {
        // if nothing happens...just reload edit page
        $oPatternCustom->setHtml(getEditPage(null));
        $main->setFormAction($_SERVER["PHP_SELF"]);
    }
    //render the page
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
