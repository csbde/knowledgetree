<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/dashboard/DashboardNews.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->uiDirectory/administration/news/newsUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

/**
 * $Id$
 *  
 * Business logic for deleting a news item
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.administration.news
 */

if (checkSession()) {	
	global $default;
			
    // instantiate my content pattern
    $oContent = new PatternCustom();
    
    if (isset($fNewsID)) {
    	// we have an id, so instantiate the news object
    	$oDashboardNews = DashboardNews::get($fNewsID);
    	if ($fDeleteConfirmed) {
    		// confirmation received, so delete    		
    		if ($oDashboardNews->delete()) {
    			$default->log->info("deleteNewsBL.php successfully deleted dashboard news id=$fNewsID");
    			// redirect to view page
    			redirect("$default->rootUrl/control.php?action=listNews");
    		} else {
    			// delete failed
    			$default->log->error("deleteNewsBL.php DB error deleting dashboard news (" . arrayToString($oDashboardNews) . ")");
    			$oContent->setHtml(renderErrorMessage("An error occurred while deleting this news item."));
    			// TODO: incorporate message into another page
    		}	    		
    	} else {
    		// display the delete confirmation page		
    		$oContent->setHtml(renderDeleteNewsConfirmationPage($oDashboardNews));
    	}
    } else {
    	// no news id, so display an error message
    	$oContent->setHtml(renderErrorMessage("No news item was selected for deletion"));
    	// TODO: incorporate message into another page
    }        

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormAction($_SERVER['PHP_SELF']);			
	$main->render();	
} 
?>