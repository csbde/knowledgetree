<?php
/**
 * $Id$
 *
 * Edit Document CheckOut Status.
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
 * @package administration.doccheckoutmanagement
 */
require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fDocumentID', 'fUpdate');

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("editDocCheckoutUI.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/DocumentTransaction.inc");	
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/links/Link.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	global $default;
	$oPatternCustom = & new PatternCustom();		
	
	if (isset($fDocumentID)) {	
		if (isset($fUpdate)) {	
			$oDocument = Document::get($fDocumentID);
			$oDocument->setIsCheckedOut(0);
			$oDocument->setCheckedOutUserID(-1);
			if ($oDocument->update()) {
				// checkout cancelled transaction
				$oDocumentTransaction = & new DocumentTransaction($oDocument->getID(), "Document checked out cancelled", FORCE_CHECKIN);
				if ($oDocumentTransaction->create()) {
					$default->log->debug("editDocCheckoutBL.php created forced checkin document transaction for document ID=" . $oDocument->getID());                                    	
				} else {
					$default->log->error("editDocCheckoutBL.php couldn't create create document transaction for document ID=" . $oDocument->getID());
				}                                    
				$oPatternCustom->setHtml(getEditCheckoutSuccessPage());
			} else {
				$oPatternCustom->setHtml(getErrorPage(_("Error while trying to update the document checkout.")));
			}
		} else {
			$oPatternCustom->addHtml(getEditCheckoutPage($fDocumentID));
			$main->setFormAction($_SERVER["PHP_SELF"]);
		}
	} else {
		// no document selected
		$oPatternCustom->setHtml(getErrorPage(_("No document selected to check back in")));
	}
	//render the page
	$main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
	$main->render();
}
?>
