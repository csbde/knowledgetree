<?php
/**
* BL information for listing Documemnt Fields	
*
* @author Omar Rahbeeni
* @date 19 May 2003
* @package presentation.lookAndFeel.knowledgeTree.
*
*/

	require_once("../../../../../config/dmsDefaults.php");

if (checkSession()) {    
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCreate.inc");    
    require_once("listDocFieldsUI.inc"); 
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
		
		$oPatternCustom->addHtml(renderHeading("Document Fields List"));		// Create the Heading				
		 		
		$main->setFormAction($_SERVER['PHP_SELF']);
				
		$sQuery = 	"SELECT id as DocFieldID, name as DocFieldName, data_type, is_generic, has_lookup, " . 
					"'Edit', 'Delete', 'Edit Lookups' " .
					"FROM " . $default->owl_fields_table . " " .
					"ORDER BY name";
		
	    $aColumns = array("DocFieldName", "data_type", "is_generic", "has_lookup","Edit", "Delete", "Edit Lookups" );
	    $aColumnNames = array("Name", "Data type", "Generic?", "Lookup?", "Edit", "Delete", "Edit Lookups");
	    $aColumnTypes = array(1,1,2,2,3,3,3);
	    $aDBColumnArray = array("DocFieldID");
	    $aQueryStringVariableNames = array("fDocFieldID");
	    	    
	    $aHyperLinkURL = array(	4=> "$default->rootUrl/control.php?action=editDocField",                       			
                       			5=> "$default->rootUrl/control.php?action=removeDocField",
                       			6=> "$default->rootUrl/control.php?action=editDocFieldLookups");                       			
	    	    
	    $oSearchResults = & new PatternTableSqlQuery($sQuery, $aColumns, $aColumnTypes, $aColumnNames, "100%", $aHyperLinkURL,$aDBColumnArray,$aQueryStringVariableNames);	    
		$oSearchResults->setDisplayColumnHeadings(true);
	    $htmlTables = $oSearchResults->render() ;
	
	    $oPatternCustom->addHtml($htmlTables);	    
	
	} // end of if checksession
	
	$main->setCentralPayload($oPatternCustom);
    $main->render();
}
?>
