<?php
/**
 * $Id$
 *
 * Edit document type fields.
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
 * @package administration.doctypemanagement
 */
 
require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("editDocTypeFieldsUI.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentType.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentField.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTypeFieldLink.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();
	if ($fDocTypeID) {
		if ($fDocFieldID) {
			$oDocTypeFieldLink = DocumentTypeFieldLink::getByFieldAndTypeIDs($fDocTypeID, $fDocFieldID);			
			if (isset($fRemove)) {
				if ($fConfirm) {
					$oDocTypeFieldLink = DocumentTypeFieldLink::getByFieldAndTypeIDs($fDocTypeID, $fDocFieldID);
					if ($oDocTypeFieldLink->delete()) {
						// success
						$oPatternCustom->setHtml(getSuccessPage(_("Document field successfully deleted."), $fDocTypeID));
					} else {
						// failure
						$oPatternCustom->setHtml(getSuccessPage(_("Error deleting document field."), $fDocTypeID));
					}
				} else {
					// ask for confirmation
					$oPatternCustom->setHtml(getDeleteConfirmationPage($fDocTypeID, $fDocFieldID));
				}
			} else if(isset($fUpdateMandatory)) {
				if ($fConfirm) {
					if (isset($fIsMandatory)) {
						$oDocTypeFieldLink->setIsMandatory(true);
					} else {
						$oDocTypeFieldLink->setIsMandatory(false);
					}
					$default->log->info("dfl=" . arrayToString($oDocTypeFieldLink));
					$oDocTypeFieldLink->update();
					$oPatternCustom->setHtml(getDetailsPage($fDocTypeID));
				} else {
					// display edit form
					$oPatternCustom->setHtml(getEditDocumentFieldLinkPage($oDocTypeFieldLink));
					$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdateMandatory=1");
				}
			}
		} else {
			$oPatternCustom->setHtml(getDetailsPage($fDocTypeID));
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fUpdateMandatory=1");
		}
	} else {
		// no document type selected to edit
		// FIXME
	}
	
	//render the page
	$main->setCentralPayload($oPatternCustom);
	$main->render();	
}
?>
