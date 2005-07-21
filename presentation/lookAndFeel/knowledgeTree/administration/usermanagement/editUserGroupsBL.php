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

KTUtil::extractGPC('fAssign', 'fUserID', 'fUserSet', 'groupAddedLeft', 'groupAddedRight');

/*
 * Update all User/Groups association
 * Return 1 if success
 *        0 if fail
 */
function updateGroups($iUserID, $aToAddIDs, $aToRemoveIDs) {

    $oUser = User::get($iUserID);

	foreach ($aToAddIDs as $iGroupID ) {
		if ($iGroupID > 0) {
            $oGroup = Group::get($iGroupID);
            $oGroup->addMember($oUser);
		}
	}

	// Remove groups
	foreach ($aToRemoveIDs as $iGroupID ) {
		if ($iGroupID > 0) {
            $oGroup = Group::get($iGroupID);
            $oGroup->removeMember($oUser);
		}
	}

	return true;
}

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

    if(isset($fUserID)) { // isset($fUserSet))
        // do a check to see both drop downs selected
        if($fUserID == -1) {
            $oPatternCustom->setHtml(getPageNotSelected());
        } else {
			  	$oPatternCustom->setHtml(renderGroupPicker($fUserID));
				$main->setOnLoadJavaScript("optGroup.init(document.forms[0]);");
				$main->setHasRequiredFields(false);
				$main->setAdditionalJavaScript(initialiseOptionTransferJavaScript());
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fUserID=$fUserID&fAssign=1");
				$main->setDHTMLScrolling(false);
				
				if (isset($fAssign)) {
				
					$aGroupToAddIDs = explode(",", $groupAddedLeft);
					$aGroupToRemoveIDs = explode(",", $groupAddedRight);
					
					// Add/Remove new groups to user 
					if ( updateGroups($fUserID, $aGroupToAddIDs, $aGroupToRemoveIDs) ) {
						// Redirect edit groups page
						redirect($_SERVER["PHP_SELF"] . "?fUserID=$fUserID");
					} else {
						$main->setErrorMessage(_("Some problems in updating groups") . ".  " .  _("Please contact your administrator") . ".");
					}
				}
		}
    } else {
        // build first page
        $oPatternCustom->setHtml(getPage(null));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fUserSet=1");
    }

	// render page
    $main->setCentralPayload($oPatternCustom);
	$main->render();
}



function initialiseOptionTransferJavascript() {
	return "<script LANGUAGE=\"JavaScript\">\n" .
		"var optGroup = new OptionTransfer(\"groupSelect\",\"chosenGroups\");\n" .
		"optGroup.setAutoSort(true);\n" .
		"optGroup.setDelimiter(\",\");\n" .
		"optGroup.saveNewLeftOptions(\"groupNewLeft\");\n" .
		"optGroup.saveNewRightOptions(\"groupNewRight\");\n" .
		"optGroup.saveRemovedLeftOptions(\"groupRemovedLeft\");\n" .
		"optGroup.saveRemovedRightOptions(\"groupRemovedRight\");\n" .
		"optGroup.saveAddedLeftOptions(\"groupAddedLeft\");\n" .
		"optGroup.saveAddedRightOptions(\"groupAddedRight\");\n" .
	"</SCRIPT>";		
}

?>
