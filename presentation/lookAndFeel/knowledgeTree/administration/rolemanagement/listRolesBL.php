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
    require_once("listRolesUI.inc"); 
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
		global $default;
		
		$oPatternCustom->addHtml(renderHeading("Current System Organisations"));		// Create the Heading				
		 
		
		$main->setFormAction($_SERVER['PHP_SELF']);
				
		$sQuery = 	"SELECT id as roleID, name as name, active, can_read as reader, can_write as writer, " . 
					"'Edit', 'Delete' " .
					"FROM roles " .
					"ORDER BY name";
				
		
	    $aColumns = array("name", 	  "active", "reader", "writer", "Edit", "Delete");
	    $aColumnNames = array("Name", "Active", "Read", "Write", "Edit", "Delete");
	    $aColumnTypes = array(1,4,4,4,3,3);
	    $aDBColumnArray = array("roleID");
	    $aQueryStringVariableNames = array("fRoleID");
	    	    
	    $aHyperLinkURL = array(	4=> "$default->rootUrl/control.php?action=editRole",                       			
                       			5=> "$default->rootUrl/control.php?action=removeRole"); //"$default->rootUrl/control.php?action=removeUserFromGroup");
	    	    
	    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	    $oSearchResults->setPicPath("$default->graphicsUrl/widgets/checked.gif");
		$oSearchResults->setDisplayColumnHeadings(true);
	    $htmlTables = $oSearchResults->render() ;
	
	    $oPatternCustom->addHtml($htmlTables);	    
	
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
