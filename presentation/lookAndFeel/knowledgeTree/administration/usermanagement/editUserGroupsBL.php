<?php
/**
 * $Id$
 *
 * Edit user-group mappings.
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

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("editUserGroupsUI.inc");
    require_once("$default->fileSystemRoot/lib/groups/Group.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    require_once("$default->fileSystemRoot/lib/groups/GroupUserLink.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    if(isset($fUserID)) { //isset($fUserSet)) {
        // do a check to see both drop downs selected
        if($fUserID == -1) {
            $oPatternCustom->setHtml(getPageNotSelected());
        } else {
            $faGroupID = GroupUserLink::getGroups($fUserID);
            $oPatternCustom->setHtml(getGroupPage($fUserID,$faGroupID));
            $main->setFormAction($_SERVER["PHP_SELF"] . "?fUserSet=1&fGroupSet=1");
        }
    } else {
        // build first page
        $oPatternCustom->setHtml(getPage(null,null));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fUserSet=1");
    }

    if(isset($fGroupSet)) {
        if($fOtherGroupID) {
        	$oPatternCustom->setHtml("Add");
        } else {	                
	        $oPatternCustom->setHtml("Delete");
	        $main->setFormAction($_SERVER["PHP_SELF"] . "?fDeleteConfirmed=1&fGroupID=$fGroupID"); 		   
        }        
    }

    if (isset($fDeleteConfirmed)) {
        // else add to db and then goto page succes
        $oUserGroup = new GroupUserLink($fGroupID, $fUserID);
        $oUserGroup->setUserGroupID($fGroupID,$fUserID);
        if($oUserGroup->delete()) {
            $oPatternCustom->setHtml(getPageSuccess());
        } else {
            $oPatternCustom->setHtml(getPageFail());
        }
    }

    // render page
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
