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
 * Business logic for editing a news item.
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
    		$oDashboardNews->setActive($fActive);
    		// if we have a new image  		
    		if (strlen($_FILES['fImage']['name']) > 0) {
    			// return the size of the image
    			$aSize = getimagesize($_FILES['fImage']['tmp_name']);
    			// don't accept it if it isn't an image
    			if (!$aSize) {
    				$default->log->error("editNewsBL.php attempted to upload a non-image:" . $_FILES['fImage']['name']);
		    		// display the edit form, with error message    		
		    		$oContent->setHtml(renderEditNewsPage($oDashboardNews, _("You may only upload an image.")));
    				
    			} else {
    				// we have an image, now check the size
    				$iImgWidth = $aSize[0];
    				$iImgHeight = $aSize[1];
    				if ($oDashboardNews->checkImageSize($iImgWidth, $iImgHeight)) {
    					// size is fine, so set it
						$oDashboardNews->setImageFile($_FILES['fImage']['tmp_name']);

						// store it
			    		if ($oDashboardNews->update()) {
			    			$default->log->info("editNewsBL.php successfully updated dashboard news id=$fNewsID");
			    			// redirect to view page
			    			redirect("$default->rootUrl/control.php?action=listNews");
			    		} else {
			    			// update failed
			    			$default->log->error("editNewsBL.php DB error updating dashboard news id=$fNewsID; ($fSynopsis, $fBody, $fRank)");
				    		// display the edit form, with error message    		
				    		$oContent->setHtml(renderEditNewsPage($oDashboardNews, _("An error occurred while updating this news item.")));
			    		}    		
												
    				} else {
    					// the image is too big
					$oContent->setHtml(renderEditNewsPage($oDashboardNews,
						sprintf(_("The image you have submitted is too big (%sx%s > %s), please correct and retry"), $iImgWidth, $iImgHeight, $oDashboardNews->getMaxImageDimensions())));
    				}    			
    			}
    		} else {
				// store it
	    		if ($oDashboardNews->update()) {
	    			$default->log->info("editNewsBL.php successfully updated dashboard news id=$fNewsID");
	    			// redirect to view page
	    			redirect("$default->rootUrl/control.php?action=listNews");
	    		} else {
	    			// update failed
	    			$default->log->error("editNewsBL.php DB error updating dashboard news id=$fNewsID; ($fSynopsis, $fBody, $fRank)");
	    			$oContent->setHtml(renderErrorMessage(_("An error occurred while updating this news item.")));
	    		}    		
    		}    			    		
    		
    	} else {
    		// display the edit form    		
    		$oContent->setHtml(renderEditNewsPage($oDashboardNews));
    	}
    } else {
    	// no news id, so display an error message
    	$oContent->setHtml(renderErrorMessage(_("No news item was selected for editing")));
    }        

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormEncType("multipart/form-data");
	$main->setFormAction($_SERVER['PHP_SELF'] . "?fUpdate=1");	
	$main->setHasRequiredFields(true);			
	$main->render();	
} 
?>
