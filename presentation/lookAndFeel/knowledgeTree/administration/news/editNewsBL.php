<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/dashboard/DashboardNews.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->uiDirectory/administration/news/newsUI.inc");

/**
 * $Id$
 *  
 * Business logic for editing a news item
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
    	if ($fUpdate) {
    		// overwrite the news attributes and save
    		
    		// only overwrite synopsis, body and rank if they've different
    		if ($oDashboardNews->getSynopsis() <> $fSynopsis) {
    			$oDashboardNews->setSynopsis($fSynopsis);
    		}
    		if ($oDashboardNews->getBody() <> $fBody) {
    			$oDashboardNews->setBody($fBody);
    		}
    		if ($oDashboardNews->getRank() <> $fRank) {
    			$oDashboardNews->setRank($fRank);
    		}
    		// if we have a new image
    		// TODO: check that it is an image   		
    		if (strlen($_FILES['fImage']['name']) > 0) { 
				$oDashboardNews->setImageFile($_FILES['fImage']['tmp_name']);
    		}
    		
    		if ($oDashboardNews->update()) {
    			$default->log->info("editNewsBL.php successfully updated dashboard news id=$fNewsID");
    			// redirect to view page
    			redirect("$default->rootUrl/control.php?action=viewNews");
    		} else {
    			// update failed
    			$default->log->error("editNewsBL.php DB error updating dashboard news id=$fNewsID; ($fSynopsis, $fBody, $fRank)");
    			$oContent->setHtml(renderErrorMessage("An error occurred while updating this news item."));
    		}	    		
    	} else {
    		// display the edit form    		
    		$oContent->setHtml(renderEditNewsPage($oDashboardNews));
    	}
    } else {
    	// no news id, so display an error message
    	$oContent->setHtml(renderErrorMessage("No news item was selected for editing"));
    }        

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormEncType("multipart/form-data");
	$main->setFormAction($_SERVER['PHP_SELF']);	
	$main->setHasRequiredFields(true);			
	$main->render();	
} 
?>