<?php

require_once("../../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/dashboard/DashboardNews.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternMainPage.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternTableSqlQuery.inc");
require_once("$default->uiDirectory/administration/news/newsUI.inc");
require_once("$default->uiDirectory/administration/adminUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");

/**
 * $Id$
 *
 * Business logic for deleting a news item.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.news
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