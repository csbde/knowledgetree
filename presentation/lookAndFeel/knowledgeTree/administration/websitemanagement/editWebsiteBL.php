<?php
/**
 * $Id$
 *
 * Edit a website.
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
 * @author Mukhtar Dharsey, Jam Warehouse (Pty) Ltd, South Africa
 * @package administration.websitemanagement
 */
 
require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fForStore', 'fWebMasterID', 'fWebSiteID', 'fWebSiteName', 'fWebSiteURL');

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
    require_once("editWebsiteUI.inc");
    require_once("$default->fileSystemRoot/lib/security/Permission.inc");
    require_once("$default->fileSystemRoot/lib/users/User.inc");
    require_once("$default->fileSystemRoot/lib/web/WebSite.inc");
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
    require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
    require_once("$default->fileSystemRoot/presentation/Html.inc");

    $oPatternCustom = & new PatternCustom();

    if ($fWebSiteID) {
    	$oWebSite = WebSite::get($fWebSiteID);
    	if ($oWebSite) {
	    	if ($fForStore) {
	    		$oWebSite->setWebSiteName($fWebSiteName);
	    		$oWebSite->setWebMasterID($fWebMasterID);
	    		$oWebSite->setWebSiteURL($fWebSiteURL);
	            if ($oWebSite->update()) {
	                $oPatternCustom->setHtml(getSuccessPage());
	            } else {
	                $oPatternCustom->setHtml(getFailPage());
	            }
		    } else {
		        $oPatternCustom->setHtml(getEditWebSitePage($oWebSite));
		        $main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
		    }
    	} else {
			$oPatternCustom->setHtml(statusPage(_("Edit Website"), "", _("The selected website no longer exists in the database."), "listWebsites"));    		
    	}
  	} else {
  		$oPatternCustom->setHtml(statusPage(_("Edit Website"), "", _("No website has been selected for editing."), "listWebsites"));
  	}
    	
    //render the page
    $main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
    $main->render();
}
?>
