<?php

require_once("../../../../../config/dmsDefaults.php");
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
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.documentmanagement
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
		$oContent->setHtml(renderListDeletedDocumentsPage(Document::getList("status_id=" . DELETED)));
    }
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER["PHP_SELF"]);
	$main->render();
}
?>