<?php
/**
 * $Id$
 *
 * Add a unit.
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
 * @package administration.unitmanagement
 */
require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fForStore', 'fOrgID', 'fUnitName');

require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
require_once("$default->fileSystemRoot/lib/orgmanagement/Organisation.inc");
require_once("$default->fileSystemRoot/lib/unitmanagement/UnitOrganisationLink.inc");
require_once("$default->fileSystemRoot/lib/security/Permission.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/administration/adminUI.inc");
require_once("addUnitUI.inc");

if (checkSession()) {

	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();

	if (isset($fFolderID)) {

		if (isset($fForStore)) {
			if($fUnitName != "" and $fOrgID != "" and $fFolderID != "") {
        	// #2944 a folder will be created for this unit, so check if there is already a folder with the name
        	// of the unit before creating the unit
        	$oFolder = new Folder($fUnitName, $fUnitName . " " . _("Unit Root Folder"), $fFolderID, $_SESSION["userID"], 0);
			if (!$oFolder->exists()) {
                    	
				$oUnit = new Unit($fUnitName, $fFolderID);

	            // if creation is successfull..get the unit id
	            if ($oUnit->create()) {
	                $unitID = $oUnit->getID();
	                $oUnitOrg = new UnitOrganisationLink($unitID,$fOrgID);
	
	                if($oUnitOrg->create()) {
	                    // if successfull print out success message
	                    $oPatternCustom->setHtml(getAddPageSuccess());
	                } else {
	                    // if fail print out fail message
	                    $oPatternCustom->setHtml(getAddToOrgFail());
	                }
	            } else {
	                // if fail print out fail message
	                $oPatternCustom->setHtml(getAddPageFail(_("The Unit was not added. Unit Name Already exists!")));
	            }
			} else {
				// #2944 failed with duplicate folder error message
				$oPatternCustom->setHtml(getAddPageFail(_("The folder") . $fUnitName . _("already exists, please rename folder before creating this unit.")));
			}
        } else {
            $oPatternCustom->setHtml(getPageFail());
        }

    } else {
		// display add unit page
        $oPatternCustom->setHtml(getAddPage());
        $oPatternCustom->addHtml(renderBrowsePage($fFolderID));
        $main->setHasRequiredFields(true);
        $main->setFormAction($_SERVER["PHP_SELF"]. "?fForStore=1&fFolderID=$fFolderID");

    }
    $main->setCentralPayload($oPatternCustom);
    $main->render();
	}
}
?>
