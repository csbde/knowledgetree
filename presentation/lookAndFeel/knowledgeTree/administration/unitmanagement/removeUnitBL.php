<?php
/**
* BL information for adding a Unit
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("removeUnitUI.inc");
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
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