<?php
/**
 * $Id$
 *
 * Add a group.
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

KTUtil::extractGPC('fGroupName', 'fUnitID');

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/lib/groups/Group.inc");
require_once("$default->fileSystemRoot/lib/groups/GroupUnitLink.inc");
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/administration/adminUI.inc");
require_once("addGroupUI.inc");

if (checkSession()) {

	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
	$oPatternCustom = & new PatternCustom();
    if (isset($fGroupName) && isset($fUnitID)) {
        // add new group
        $oGroup = new Group($fGroupName);
        if($oGroup->create()) {
            // now set the group's unit
            $default->log->info("set group (id=" . $oGroup->getID() . ") to unit id=$fUnitID"); 
            $oGroupUnit = new GroupUnitLink($oGroup->getID(), $fUnitID);
            if ($oGroupUnit->create()) {
                // redirect to group users page
                controllerRedirect("editGroupUsers", "fGroupID=" . $oGroup->getID());
            } else {
                $oPatternCustom->setHtml(statusPage(_("Add A New Group"), _("Addition Unsuccessful") . "!", _("There was an error associating the new group with the specified unit."), "addGroup"));
            }
        } else {
            $oPatternCustom->setHtml(statusPage(_("Add A New Group"), _("Addition Unsuccessful") . "!", _("There was an error creating the new group (Check that a group with this name doesn't already exist)."), "addGroup"));
        }
    } else {
       // display form
	   $oPatternCustom->setHtml(getPage());
    }
	$main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->setHasRequiredFields(true);
	$main->render();
}
?>
