<?php
/**
 * $Id$
 *
 * Edit a role.
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
 * @package administration.rolemanagement
 */
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("editRoleUI.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/lib/roles/Role.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");


    $oPatternCustom = & new PatternCustom();

    // if a new group has been added
    if (isset($fFromCreate)) {

        if($fRoleID == -1) {
            $oPatternCustom->setHtml(getAddFailPage());
        } else {
            $oPatternCustom->setHtml(getCreatePage($fRoleID));
        }

        $main->setFormAction("$default->rootUrl/presentation/lookAndFeel/knowledgeTree/store.php?fReturnURL=" . urlencode("$default->rootUrl/control.php?action=listRoles"));

        // coming from manual edit page
    }
    else if (isset($fForStore)) {
        $oRole = Role::get($fRoleID);
        $oRole->setName($fRoleName);

        //check if checkbox checked
        if (isset($fActive)) {
            $oRole->setActive(true);
        } else {
            $oRole->setActive(false);
        }        
        //check if checkbox checked
        if (isset($fReadable)) {
            $oRole->setReadable(true);
        } else {
            $oRole->setReadable(false);
        }
        //check if checkbox checked
        if (isset($fWriteable)) {
            $oRole->setWriteable(true);
        } else {
            $oRole->setWriteable(false);
        }
        if ($oRole->update()) {
            // if successfull redirec to list page        	
        	controllerRedirect("listRoles");
        } else {
            // if fail print out fail message
            $oPatternCustom->setHtml(getEditPageFail());
        }
    } else if (isset($fRoleID)) {
        // post back on group select from manual edit page
        $oPatternCustom->setHtml(getEditPage($fRoleID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");


    } else {
        // if nothing happens...just reload edit page
        $oPatternCustom->setHtml(getEditPage(null));
        $main->setFormAction($_SERVER["PHP_SELF"]);

    }
    //render the page
    $main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
    $main->render();
}
?>
