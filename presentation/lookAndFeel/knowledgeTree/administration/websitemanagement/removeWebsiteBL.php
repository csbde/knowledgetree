<?php
/**
 * $Id$
 *
 * Remove a website.
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

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("removeWebsiteUI.inc");
	require_once("$default->fileSystemRoot/lib/security/Permission.inc");
	require_once("$default->fileSystemRoot/lib/web/WebSite.inc");
	require_once("$default->fileSystemRoot/lib/users/User.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");
	require_once("$default->fileSystemRoot/presentation/Html.inc");
	
	$oPatternCustom = & new PatternCustom();	
	
	// get main page
	if (isset($_REQUEST['fWebSiteID'])) {
		$oWebSite = Website::get($_REQUEST['fWebSiteID']);
	    // if delete entry
		if (array_key_exists('fForDelete', $_REQUEST)) {

			$oWebSite->setWebSiteName($_REQUEST['fWebSiteName']);
				
			if ($oWebSite->delete()) {
				$oPatternCustom->setHtml(getDeleteSuccessPage());
				
			} else {
				$oPatternCustom->setHtml(getDeleteFailPage());
			}
		} else {
			// check that the website isn't involved in any publishing request
			if ($oWebSite->inUse()) {
				$oPatternCustom->setHtml(statusPage(_("Remove Website"), _("This website can not be removed since it is still in use."), "", "listWebsites"));
			} else { 
				// ask for confirmation
				$oPatternCustom->setHtml(getDeletePage($_REQUEST['fWebSiteID']));
				$main->setFormAction($_SERVER["PHP_SELF"] . "?fForDelete=1");
			}
		}
	}
	$main->setCentralPayload($oPatternCustom);				
	$main->render();		
}
?>
