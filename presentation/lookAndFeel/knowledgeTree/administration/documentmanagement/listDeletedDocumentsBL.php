<?php

require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fDocumentID', 'fDocumentIDs');

require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->uiDirectory/documentmanagement/documentUI.inc");

require_once("listDeletedDocumentsUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
/**
 * $Id$
 *
 * Business logic for listing deleted documents.
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.documentmanagement
 */

if (checkSession()) {	
	global $default;
			
    $oContent = new PatternCustom();
    
    if ($fDocumentIDs) {
    	// tack on POSTed document ids and redirect to the expunge deleted documents page
    	foreach ($fDocumentIDs as $fDocumentID) {
    		$sQueryString .= "fDocumentIDs[]=$fDocumentID&";
    	}
    	controllerRedirect("expungeDeletedDocuments", $sQueryString);
    } else {
		$oContent->setHtml(renderListDeletedDocumentsPage(Document::getList("status_id=" . DELETED)));/*ok*/
    }
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER["PHP_SELF"]);
	$main->render();
}
?>
