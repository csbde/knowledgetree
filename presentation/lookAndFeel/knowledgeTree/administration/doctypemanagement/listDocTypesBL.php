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
    //require_once("listDocTypesUI.inc"); 
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
    require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	
    $oPatternCustom = & new PatternCustom();

if(checkSession()) {	
		global $default;
		
		$oPatternCustom->addHtml(renderHeading("Current System Organisations"));		// Create the Heading				
		 		
		$main->setFormAction($_SERVER['PHP_SELF']);
				
		$sQuery = 	"SELECT id as DocTypeID, name as DocTypeName, " . 
					"'Edit', 'Delete', 'Edit Fields' " .
					"FROM " . $default->owl_document_types_table . " " .
					"ORDER BY name";
		
	    $aColumns = array("DocTypeName", "Edit", "Delete", "Edit Fields");
	    $aColumnNames = array("Name", "Edit", "Delete", "Edit Fields");
	    $aColumnTypes = array(1,3,3,3);
	    $aDBColumnArray = array("DocTypeID");
	    $aQueryStringVariableNames = array("fDocTypeID");
	    	    
	    $aHyperLinkURL = array(	1=> "$default->rootUrl/control.php?action=editDocType&fDocTypeSelected=1",                       			
                       			2=> "$default->rootUrl/control.php?action=removeDocType",
                       			3=> "$default->rootUrl/control.php?action=editDocTypeFields&fDocTypeSelected=1");
	    	    
	    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
		$oSearchResults->setDisplayColumnHeadings(true);
	    $htmlTables = $oSearchResults->render() ;
	
	    $oPatternCustom->addHtml($htmlTables);	    
	
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
