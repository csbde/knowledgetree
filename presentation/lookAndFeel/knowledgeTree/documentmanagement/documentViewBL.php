<?php
/**
* documentViewUI.php
* Contains the business logic required to build the document view page.
* Will use documentViewUI.php for HTML
*
* Variables expected:
*			o $fDocumentID		Primary key of document to view
*
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 21 January 2003
* @package presentation.lookAndFeel.knowledgeTree.documentManager
*/


require_once("../../../../config/dmsDefaults.php");
require_once("$default->owl_fs_root/lib/security/permission.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternListFromQuery.inc");
require_once("$default->owl_fs_root/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->owl_fs_root/presentation/lookAndFeel/knowledgeTree/documentmanagement/documentViewUI.php");

//if (checkSession()) {
	$oDocument = & Document::get(12);	
	//renderDocumentMetaData($oDocument);
	//renderTypeSpecificMetaData($oDocument);
	//renderDocumentRouting($oDocument);
	renderPage($oDocument);
/*} else {
	echo "You do not have permission for this page";
}*/

?>
