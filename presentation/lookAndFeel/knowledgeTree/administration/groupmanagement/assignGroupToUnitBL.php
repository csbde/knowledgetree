<?php
/**
 * $Id$
 *
 * Map a group to a unit.
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
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("assignGroupToUnitUI.inc");
    require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
    require_once("$default->fileSystemRoot/lib/groups/Group.inc");
    require_once("$default->fileSystemRoot/lib/groups/GroupUnitLink.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    /*if (isset($fGroupID) ){
    	// build first page
        $oPatternCustom->setHtml(getGotGroupPage($fGroupID,null));
        $main->setHasRequiredFields(true);
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1");
    }    
    else*/ 
    if(isset($fGroupSet)) {
        // build first page
        $oPatternCustom->setHtml(getPage(null,null));
        $main->setHasRequiredFields(true);
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1");
    } else {
        // do a check to see both drop downs selected
        if(!isset($fGroupID) AND !isset($fUnitID )) {
            $oPatternCustom->setHtml(getPageNotSelected());
        } else { //check if it belongs to a unit
            $unitLink = GroupUnitLink::groupBelongsToUnit($fGroupID);
            // if it does'nt ..then go to normal page
            if($unitLink == false) {
                $oPatternCustom->setHtml(getPage($fGroupID,$fUnitID));
                $main->setHasRequiredFields(true);
                $main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupAssign=1");
            } else {
                //if it does...then go to failure page
                $oPatternCustom->setHtml(getPageFail($fGroupID));
            }
        }
    }

    if (isset($fGroupAssign)) {
        // else add to db and then goto page succes
        $oGroupUnit = new GroupUnitLink($fGroupID,$fUnitID);
        $oGroupUnit->create();
        $oPatternCustom->setHtml(getPageSuccess());
    }

    // render page
    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
