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
 * Business logic for adding a news item.
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
    
    // check that we have all the required parameters
    if ($fStore) {
    	// construct the news object
    	$oDashboardNews = new DashboardNews($fSynopsis, $fBody, $fRank);
    	$oDashboardNews->setActive($fActive);

	    if (isset($fSynopsis) && isset($fBody) && isset($fRank)) {
			// if we have a new image
			if (strlen($_FILES['fImage']['name']) > 0) {
				// return the size of the image
				$aSize = getimagesize($_FILES['fImage']['tmp_name']);
				// don't accept it if it isn't an image
				if (!$aSize) {
					$default->log->error("addNewsBL.php attempted to upload a non-image:" . $_FILES['fImage']['tmp_name']);
					$oContent->setHtml(renderAddNewsPage($oDashboardNews, "You may only upload an image file."));
				} else {
					// we have an image, now check the size
					$iImgWidth = $aSize[0];
					$iImgHeight = $aSize[1];
					if ($oDashboardNews->checkImageSize($iImgWidth, $iImgHeight)) {
						// size is fine, so set it
						$default->log->info("setting image file=" . $_FILES['fImage']['tmp_name']);
						$oDashboardNews->setImageFile($_FILES['fImage']['tmp_name']);
						
						// store it
			    		if ($oDashboardNews->create()) {
			    			$default->log->info("addNewsBL.php successfully created dashboard news id=" . $oDashboardNews->getID());
			    			// redirect to view page
			    			redirect("$default->rootUrl/control.php?action=listNews");
			    		} else {
			    			// insert failed
			    			$default->log->error("addNewsBL.php DB error inserting dashboard news ($fSynopsis, $fBody, $fRank, with image)");
			    			$oContent->setHtml(renderAddNewsPage($oDashboardNews, _("An error occurred while creating this news item.")));
			    		}	    		
						
					} else {
						// the image is too big
						$oContent->setHtml(renderAddNewsPage($oDashboardNews, 
							sprintf(_("The image you have submitted is too big (%sx%s > %s), please correct and retry"), $iImgWidth, $iImgHeight, $oDashboardNews->getMaxImageDimensions())));
					}    			
				}
			} else {
				$default->log->info("no image");
				// no image uploaded, store what we've got
				$default->log->info("about to create");
				if ($oDashboardNews->create()) {
					// insert worked
					$default->log->info("addNewsBL.php successfully created dashboard news id=" . $oDashboardNews->getID());
	    			// redirect to view page
	    			redirect("$default->rootUrl/control.php?action=listNews");
				} else {					
					// insert failed
	    			$default->log->error("addNewsBL.php DB error inserting dashboard news ($fSynopsis, $fBody, $fRank, no image)");
	    			$oContent->setHtml(renderAddNewsPage($oDashboardNews, _("An error occurred while creating this news item.")));					
				}
			}
	    } else {
	    	// all params not present, so display an error message
	    	$oContent->setHtml(renderAddNewsPage($oDashboardNews, _("Please complete the form before submitting.")));
	    }
    } else {
    	// display the form
    	$oContent->setHtml(renderAddNewsPage(null));
    }         

	// build the page
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");    
	$main->setCentralPayload($oContent);
	$main->setFormEncType("multipart/form-data");
	$main->setFormAction($_SERVER['PHP_SELF'] . "?fStore=1");	
	$main->setHasRequiredFields(true);			
	$main->render();	
} 
?>
