<?php
/**
 * $Id$
 *
 * Map unit to organisation.
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
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");
    require_once("addUnitToOrgUI.inc");
    require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
    require_once("$default->fileSystemRoot/lib/unitmanagement/UnitOrganisationLink.inc");
    require_once("$default->fileSystemRoot/lib/orgmanagement/Organisation.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();
	
	$oPatternCustom->addHtml(renderHeading("Add Unit to an Organisation"));    
    
    if (isset($fUnitID)) {    	    
    	if ($fOrgID == "" && $fAdd == 1){
	    	$main->setErrorMessage(_("Select an Organisation"));	
    		$main->setFormAction($_SERVER["PHP_SELF"] . "?fUnitID=$fUnitID&fAdd=1" );
    	}	
		if ($fOrgID > 0) {    	
	    	$oUnitOrgLink = & new UnitOrganisationLink($fUnitID,$fOrgID);    	
			if ($oUnitOrgLink->create()) {
				$oPatternCustom->addHtml(getAddSuccessPage());
			}else{
				$main->setErrorMessage(_("Unit cannot be linked to the Organisation."));
				$oPatternCustom->addHtml(getAddFailPage());
			}   	    	    	   
	    } else{
	    	$oUnit = Unit::get($fUnitID);    	
	    	$oPatternCustom->addHtml(getAddUnitsPage($oUnit));
	    	$main->setFormAction($_SERVER["PHP_SELF"] . "?fUnitID=$fUnitID&fAdd=1" );	    	
		}    	
    }
    else {  
    
	    if (isset($fForStore)) {
	        if($fUnitName != "" and $fOrgID != "") {
	            $oUnit = new Unit($fUnitName);
	
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
	                $oPatternCustom->setHtml(getAddPageFail());
	            }
	        } else {
	            $oPatternCustom->setHtml(getPageFail());
	        }
	
	    } else if (isset($fUnitID)) {
	        // post back on Unit select from manual edit page
	        $oPatternCustom->setHtml(getAddPage($fUnitID));
	        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
	    } else {
	        // if nothing happens...just reload edit page
	        $oPatternCustom->setHtml(getAddPage(null));
	        $main->setFormAction($_SERVER["PHP_SELF"]. "?fForStore=1");
	
	    }
    
    }
    
    $main->setCentralPayload($oPatternCustom);    
    $main->render();
}
?>
