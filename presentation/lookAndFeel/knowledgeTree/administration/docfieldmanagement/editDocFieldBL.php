<?php
/**
 * $Id$
 *
 * Edit document field.
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
 * @package administration.docfieldmanagement
 */
/**
* BL information for adding a DocField
*
* @author Mukhtar Dharsey
* @date 5 February 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/
require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fDocFieldDataType', 'fDocFieldHasLookup', 'fDocFieldID', 'fDocFieldIsGeneric', 'fDocFieldName', 'fForStore');

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("editDocFieldUI.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();		
	
	 if (isset($fForStore)) {

		$oDocField = DocumentField::get($fDocFieldID);
		$oDocField->setName($fDocFieldName);
		$oDocField->setDataType($fDocFieldDataType);
		
		//check if checkbox checked || hidden value
		if ($fDocFieldIsGeneric) {
			$oDocField->setIsGeneric(true);
		} else {
			$oDocField->setIsGeneric(false);
		}
		//check if checkbox checked
		if (isset($fDocFieldHasLookup)) {
			$oDocField->setHasLookup(true);
		} else {
			$oDocField->setHasLookup(false);
		}
		if ($oDocField->update()) {

			// if we're setting lookup to be true, then prompt for an initial lookup value??
			if (isset($fDocFieldHasLookup)) {
				// and there are no metadata values for this lookup
				if (DocumentField::getLookupCount($fDocFieldID) == 0) {
					// then redirect to the edit metadata page
					controllerRedirect("addMetaDataForField", "fDocFieldID=$fDocFieldID");
					exit;
				}
			}
			// otherwise, go to the list page
			controllerRedirect("listDocFields", "");
		} else {
			// if fail print out fail message
			$oPatternCustom->setHtml(getEditPageFail());
		}
	} else if (isset($fDocFieldID)){	
		// post back on DocField select from manual edit page	
		$oPatternCustom->setHtml(getEditPage($fDocFieldID));
		$sFormAction = $_SERVER["PHP_SELF"] . "?fForStore=1";
	} else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getEditPage(null));
		$sFormAction = $_SERVER["PHP_SELF"];
	}
	
	//render the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	$main->setFormAction($sFormAction);	
	$main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
	$main->render();	
}
?>
