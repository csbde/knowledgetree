<?php

/**
* viewHistoryUI.php
* Contains the business logic required to build the document history view page.
* Will use viewHistoryUI.php for HTML
*
* Expected form varaibles:
*   o $fDocumentID - Primary key of document to view
*
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 12 February 2003
* @package presentation.lookAndFeel.knowledgeTree.documentManager
*/

require_once("../../../../config/dmsDefaults.php");

require_once("$default->owl_fs_root/lib/security/permission.inc");

require_once("$default->owl_fs_root/lib/users/User.inc");

require_once("$default->owl_fs_root/lib/documentmanagement/DocumentTransaction.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");

require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternCustom.inc");

require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/documentmanagement/viewHistoryUI.inc");
require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
//require_once("$default->owl_fs_root/presentation/Html.inc");
require_once("$default->owl_fs_root/presentation/webpageTemplate.inc");

if (checkSession()) {	
    if (isset($fDocumentID)) {
		
		$oDocument = & Document::get($fDocumentID);
        $oPatternCustom = & new PatternCustom();
        $oPatternCustom->setHtml(getPage($oDocument->getID(), $oDocument->getFolderID(), $oDocument->getName()));
        $main->setCentralPayload($oPatternCustom);   
        $main->render();
		
	}
}

?>
