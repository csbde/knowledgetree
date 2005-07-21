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
 * @package administration.groupmanagement
 */
 
require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fAssign', 'fGroupID', 'userAddedLeft', 'userAddedRight', 'userRemovedLeft', 'userRemovedRight', 'userNewLeft', 'userNewRight', 'chosenUsers', 'userSelect');

/*
 * Update all Users/Group association
 * Return 1 if success
 *        0 if fail
 */
function updateUsers($iGroupID, $aToAddIDs, $aToRemoveIDs) {
    $oGroup = Group::get($iGroupID);
    if (PEAR::isError($oGroup)) {
        return false;
    }

    if ($oGroup === false) {
        return false;
    }

    // Add Users
    foreach ($aToAddIDs as $iUserID ) {
        if ($iUserID > 0) {
            $oUser = User::get($iUserID);
            $res = $oGroup->addMember($oUser);
            if (PEAR::isError($res)) {
                $_SESSION["KTErrorMessage"][] = "Failed to add " . $oUser->getName() . " to " . $oGroup->getName();
            }
        }
    }

    // Remove Users
    foreach ($aToRemoveIDs as $iUserID ) {
        if ($iUserID > 0) {
            $oUser = User::get($iUserID);
            $res = $oGroup->removeMember($oUser);
            if (PEAR::isError($res)) {
                $_SESSION["KTErrorMessage"][] = "Failed to remove " . $oUser->getName() . " from " . $oGroup->getName();
            }
        }
    }

    return true;
}

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("editGroupUsersUI.inc");
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

    if(isset($fGroupID)) {
        // do a check to see both drop downs selected
        if($fGroupID == -1) {
            $oPatternCustom->setHtml(getPageNotSelected());
        } else {
			$oPatternCustom->setHtml(renderGroupPicker($fGroupID));
			$main->setOnLoadJavaScript("optUser.init(document.forms[0]);");
			$main->setHasRequiredFields(false);
			$main->setAdditionalJavaScript(initialiseOptionTransferJavaScript());
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupID=$fGroupID&fAssign=1");
			$main->setDHTMLScrolling(false);
				
			if (isset($fAssign)) {
			
				$aUserToAddIDs = explode(",", $userAddedLeft);
				$aUserToRemoveIDs = explode(",", $userAddedRight);
				
				// Add/Remove new users to group
				if ( updateUsers($fGroupID, $aUserToAddIDs, $aUserToRemoveIDs) ) {
					// Redirect edit groups page
					redirect($_SERVER["PHP_SELF"] . "?fGroupID=$fGroupID");
				} else {
					$main->setErrorMessage("Some problems in updating users. Please contact your administrator");
				}
			}
		}
    } else {
        // build first page
        $oPatternCustom->setHtml(getPage(null));
        $main->setFormAction($_SERVER["PHP_SELF"] . "?fGroupSet=1");
    }

	// render page
    $main->setCentralPayload($oPatternCustom);
	$main->render();
}



function initialiseOptionTransferJavascript() {
	return "<script LANGUAGE=\"JavaScript\">\n" .
		"var optUser = new OptionTransfer(\"userSelect\",\"chosenUsers\");\n" .
		"optUser.setAutoSort(true);\n" .
		"optUser.setDelimiter(\",\");\n" .
		"optUser.saveNewLeftOptions(\"userNewLeft\");\n" .
		"optUser.saveNewRightOptions(\"userNewRight\");\n" .
		"optUser.saveRemovedLeftOptions(\"userRemovedLeft\");\n" .
		"optUser.saveRemovedRightOptions(\"userRemovedRight\");\n" .
		"optUser.saveAddedLeftOptions(\"userAddedLeft\");\n" .
		"optUser.saveAddedRightOptions(\"userAddedRight\");\n" .
	"</SCRIPT>";		
}

?>
