<?php
/**
 * $Id$
 *
 * Remove a user.
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
 * @package administration.usermanagement
 */

require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fForDeleteConfirmed', 'fUserID', 'fUserName');

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("removeUserUI.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    require_once("$default->fileSystemRoot/lib/groups/Group.inc");
    require_once("$default->fileSystemRoot/lib/groups/GroupUserLink.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    // get main page
    if (isset($fUserID)) {
        $oPatternCustom->setHtml(getDeleteConfirmedPage($fUserID));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForDeleteConfirmed=1");
    } else {
        $oPatternCustom->setHtml(getDeletePage(null));
        $main->setFormAction($_SERVER["PHP_SELF"]);
    }

    if (isset($fForDeleteConfirmed)) {
        //get User object
        $oUser = User::get($fUserID);
        $oUser->setUserName($fUserName);

        //delete from all groups
        $oUser->deleteFromSystem();
        
        //delete the User object
        if ($oUser->delete()) {
            $oPatternCustom->setHtml(getDeleteSuccessPage());
        } else {
            $oPatternCustom->setHtml(getDeleteFailPage());
        }
    }

    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
