<?php
/**
 * $Id$
 *
 * Remove a unit.
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

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("removeUnitUI.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
    require_once("$default->fileSystemRoot/lib/orgmanagement/Organisation.inc");
    require_once("$default->fileSystemRoot/lib/unitmanagement/UnitOrganisationLink.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    if ($fUnitID) {
        // retrieve unit object
        $oUnit = Unit::get($fUnitID);
		if ($oUnit) {
	    
	        // if the unit has groups linked to it, then it can't be deleted
	        if ($oUnit->hasGroups()) {
	        	// display error message
	        	$oPatternCustom->setHtml(getStatusPage("Can't delete Unit '" . $oUnit->getName() . "'", "Please remove all groups belonging to this Unit before attempting to delete it"));
	        } else {
		        // retrieve organisation link (for later deletion or to get the organisation id)
			    $oUnitOrg = UnitOrganisationLink::getByUnitID($fUnitID);
			    if ($oUnitOrg) {
			    	$oOrganisation = Organisation::get($oUnitOrg->getOrgID());
			    }			    
	        		
				// we've received confirmation, so delete
			    if (isset($fForDeleteConfirmed)) {
			        //delete unit object
			        if ($oUnit->delete()) {
				        // delete the link between this unit and its organisation if there is one
						if ($oUnitOrg) {
					       	if ($oUnitOrg->delete()) {
					       		$oPatternCustom->setHtml(getStatusPage("Unit SuccessFully Removed!"));
					        } else {
					        	// couldn't delete the link to the organisation
								$oPatternCustom->setHtml(getStatusPage("Deletion of Unit Organisation Link Failed!", "The Unit was deleted, but the link to the Organisation could not be deleted"));
					       	}
						} else {
							// no organisation mapped
							$oPatternCustom->setHtml(getStatusPage("Unit SuccessFully Removed!"));
						}
			        } else {
			            $oPatternCustom->setHtml(getStatusPage("Deletion of Unit '" . $oUnit->getName() . "' Failed!"));
			        }
	        	// ask for confirmation before deleting		        
			    } else {
			        $oPatternCustom->setHtml(getConfirmDeletePage($oUnit, $oOrganisation));
			        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForDeleteConfirmed=1");
			    }
	        }
		} else {
			// couldn't retrieve unit from db
        	$oPatternCustom->setHtml(getStatusPage("No Unit selected for deletion."));
		}			
    } else {
    	// no params received, error 
        $oPatternCustom->setHtml(getStatusPage("No Unit selected for deletion."));
    }

    $main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>