<?php
/**
* BL information for viewing a Discussion	
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
	
if (checkSession()) {

	$oPatternCustom = & new PatternCustom();
	$aDocuments = Document::getList("is_checked_out = 1");
	$sToRender .= renderHeading("Checked out Documents");	
	$sToRender .= "<table><tr><th width=\"80\">Document</th><th>Checked Out By</th><th>&nbsp;</th>";
	if (count($aDocuments) > 0) {
		for ($i=0; $i<count($aDocuments); $i++) {
			if ($aDocuments[$i]) {
				$oUser = User::get($aDocuments[$i]->getCheckedOutUserID());
				$sToRender .= "<tr><td>" . $aDocuments[$i]->getDisplayPath() . "</td>";
				$sToRender .= "<td>" . $oUser->getName() . "</td>";
				$sToRender .= "<td>" . generateControllerLink("editDocCheckout", "fDocumentID=" . $aDocuments[$i]->getID(), "Check In") . "</td>"; 
			}
		}
	}  else {
		$sToRender .= "<tr><td colspan=\"3\">There are no checked out document</td></tr>";
	}
	$sToRender .= "</table>";

    $oPatternCustom->setHtml($sToRender);
   	    
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    	    
	$main->setCentralPayload($oPatternCustom);
	$main->setFormAction($_SERVER['PHP_SELF']);	
    $main->render();
}
?>