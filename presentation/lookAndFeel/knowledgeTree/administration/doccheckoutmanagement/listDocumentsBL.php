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

if (checkSession()) {    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");    
    //require_once("listOrgUI.inc"); 
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");    
    require_once("$default->fileSystemRoot/lib/security/permission.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternBrowsableSearchResults.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");    
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionThread.inc");  
    require_once("$default->fileSystemRoot/lib/discussions/DiscussionComment.inc");   
    require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	
    $oPatternCustom = & new PatternCustom();

if(checkSession()) {	

		$oPatternCustom->addHtml(renderHeading("Checked out Documents"));		// Create the Heading				
		 
		
		$main->setFormAction($_SERVER['PHP_SELF']);
				
		$sQuery = "SELECT documents.id as DocId, documents.name as Name, documents.filename as File, " .
				"CASE  WHEN users.name Is Null THEN '<font color=blue>* No one</font>' ELSE  users.name END AS UserName, " .
				"'Check In' " .
				"FROM documents left join users " .
				"on documents.checked_out_user_id = users.id " .
				"WHERE documents.is_checked_out = 1";

	    $aColumns = array("Name", "File", "UserName", "Check In");
	    $aColumnNames = array("Name", "File", "Checked Out by", "");
	    $aColumnTypes = array(1,1,1,3);
	    $aDBColumnArray = array("DocId");
	    $aQueryStringVariableNames = array("fDocID");
	    	    
	    $aHyperLinkURL = array(	3=> "$default->rootUrl/control.php?action=editDocCheckout");
	    	    
	    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
		$oSearchResults->setDisplayColumnHeadings(true);
		$oSearchResults->setEmptyTableMessage("No checked out documents");
	
	    $oPatternCustom->addHtml($oSearchResults->render());	    
	
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
