<?php
/**
 * $Id$
 *
 * Remove document field.
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

require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("removeDocFieldUI.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();	
	
	if (isset($fDocFieldID)) {
		$oDocField = DocumentField::get($fDocFieldID);
		if ($oDocField) {
			// check if we're trying to delete the category field
			if ($oDocField->getName() != "Category") {	
				// check if the document field is mapped to a document type first
				$aDocumentTypes = $oDocField->getDocumentTypes();
				if (count($aDocumentTypes) > 0) {
					// display status message- can't delete
					$oPatternCustom->setHtml(getFieldMappedPage($oDocField->getName(), $aDocumentTypes));
				} else {
					// perform the deletion
					if (isset($fForDelete)) {
						if ($oDocField->delete()) {
							$oPatternCustom->setHtml(getDeleteSuccessPage());
						} else {
							$oPatternCustom->setHtml(getDeleteFailPage());
						}
					} else {
						// delete confirmation page
						$oPatternCustom->setHtml(getDeletePage($fDocFieldID));
						$main->setFormAction($_SERVER["PHP_SELF"] . "?fForDelete=1");
					}
				}
			} else {
				// couldn't retrieve document field from db
				$oPatternCustom->setHtml(getStatusPage("Read-only document field", "The 'Category' document field cannot be deleted."));
			}
		} else {
			// couldn't retrieve document field from db
			$oPatternCustom->setHtml(getStatusPage("Non-existent document field", "This document field does not exist in the database"));
		}
	} else {
		// prompt for a field to delete
		$oPatternCustom->setHtml(getDeletePage(null));
		$main->setFormAction($_SERVER["PHP_SELF"] );
	}
	
	$main->setCentralPayload($oPatternCustom);				
	$main->render();		
}
?>