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
    require_once("listUsersUI.inc"); 
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

		$oPatternCustom->addHtml(renderHeading("Current System Users"));		// Create the Heading				
		$oPatternCustom->addHtml("<table align='left' border=0><tr><td><b>Filter by group </b></td><td>");	// 
		if (isset($fGroupID)) {  // Check if should use the query string OR ...	
			$sFilterOptions = getFilterOptions($fGroupID);
			$oPatternCustom->addHtml($sFilterOptions . "</td></tr></table>\n");		// Get Groups Dropdown for filter option			 
		} else {				 // ... should use session variable
			$sFilterOptions = getFilterOptions($_SESSION['UserGroupFilter'][0]->iSessionGroupID);
			$oPatternCustom->addHtml(getFilterOptions($sFilterOptions) . "</td></tr></table>");		// Get Groups Dropdown for filter option			
		}
		$oPatternCustom->addHtml(getSubmit());
		$oPatternCustom->addHtml("<br><br><br>");
		$main->setFormAction($_SERVER['PHP_SELF']);
		  
		
		if($fGroupID == "") {    // Used when user wants to filter by group		
			$sQuery = 	"SELECT users.id as userID, users.name as name, username, " . 
						"'Edit' , 'Delete', 'Edit Groups' " .
						"FROM users " .
						"ORDER BY users.name";
			$_SESSION['UserGroupFilter'][0]->iSessionGroupID = "";		
												
		} else {	// List all users on the system
			if (isset($fGroupID)) {
				$_SESSION['UserGroupFilter'][0]->iSessionGroupID = $fGroupID ;
			} else { 
				$fGroupID = $_SESSION['UserGroupFilter'][0]->iSessionGroupID;
			}
			$sQuery = 	"SELECT users.id as userID, users.name as name, username, " . 
						"'Edit' , 'Delete', 'Edit Groups' , users_groups_link.group_id " .
						"FROM users inner JOIN users_groups_link ON users.id = users_groups_link.user_id " .
 						"WHERE users_groups_link.group_id = $fGroupID " .  
						"ORDER BY users.name";			
		}
		
	    $aColumns = array("name", "username",  "Edit", "Delete", "Edit Groups");
	    $aColumnNames = array("Name", "Username", "Edit", "Delete", "Edit Groups");
	    $aColumnTypes = array(1,1,3,3,3);
	    $aDBColumnArray = array("userID");
	    $aQueryStringVariableNames = array("fUserID");
	    	    
	    $aHyperLinkURL = array(	2=> "$default->rootUrl/control.php?action=editUser",                       			
                       			3=> "$default->rootUrl/control.php?action=removeUser",
                       			4=> "$default->rootUrl/control.php?action=editUserGroups"); //"$default->rootUrl/control.php?action=removeUserFromGroup");
	    	    
	    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);
	    
		$oSearchResults->setDisplayColumnHeadings(true);
	    $htmlTables = $oSearchResults->render() ;
	
	    $oPatternCustom->addHtml($htmlTables);	    
			
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
