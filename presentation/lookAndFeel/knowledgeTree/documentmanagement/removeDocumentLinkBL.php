<?php
/**
 * $Id$
 *
 * Business logic for unlinking a parent document from a child documenbt
 *
 * Expected form variables:
 *	$fDocumentLinkID - primary key of document link to delete
 *	$fChildDocumentID - primary key of child document to which parent document is linked
 *	$fParentDocumentID - primary key of parent document
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

require_once("../../../../config/dmsDefaults.php");

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");	
	require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentLink.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("documentUI.inc");
	require_once("removeDocumentLinkUI.inc");

	$oDocument = Document::get($fDocumentID);
	if (Permission::userHasDocumentWritePermission($oDocument)) {
		if (isset($fForDelete)) {
			//deleting a document link
			$oDocumentLink = DocumentLink::get($fDocumentLinkID);
			if ($oDocumentLink->delete()) {
				controllerRedirect("viewDocument", "fDocumentID=$fParentDocumentID&fShowSection=linkedDocuments");			
			} else {
				//an error occured whilst trying to delete the document link
				require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
										
				$oParentDocument = Document::get($fParentDocumentID);
				$oChildDocument = Document::get($fChildDocumentID);
			
				$oPatternCustom = & new PatternCustom();
				$oPatternCustom->setHtml(getPage($oParentDocument->getName(), $oChildDocument->getName(), $fParentDocumentID));
				$main->setCentralPayload($oPatternCustom);
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentLinkID=$fDocumentLinkID&fParentDocumentID=$fParentDocumentID&fChildDocumentID=$fChildDocumentID&fForDelete=1");
				$main->setErrorMessage(_("An error occured whilst attempting to delete the link between the two documents"));
				$main->render();				
			}			
		} else {
			//user has document write permission and can therefore remove the
			//link between the two documents		
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
										
			$oParentDocument = Document::get($fParentDocumentID);
			$oChildDocument = Document::get($fChildDocumentID);
			
			$oPatternCustom = & new PatternCustom();
			$oPatternCustom->setHtml(getPage($oParentDocument->getName(), $oChildDocument->getName(), $fParentDocumentID));
			$main->setCentralPayload($oPatternCustom);
			$main->setFormAction($_SERVER["PHP_SELF"] . "?fDocumentLinkID=$fDocumentLinkID&fParentDocumentID=$fParentDocumentID&fChildDocumentID=$fChildDocumentID&fForDelete=1");	
			$main->render();	
		}
	} else {
		//user does not have permission to be here
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
										
		$oPatternCustom = & new PatternCustom();
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage(_("You do not have permission to delete links between documents"));
		$main->render();
	}
}
?>
