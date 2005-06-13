<?php
/**
 * $Id$
 *
 * Edit a document link type.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once("../../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fLinkID', 'fLinkName', 'fDescription', 'fForStore');

if (checkSession()) {
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternListBox.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternEditableListFromQuery.inc");
	require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");	

	require_once("$default->fileSystemRoot/presentation/Html.inc");
	require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");

	require_once("$default->fileSystemRoot/lib/documentmanagement/LinkType.inc");
	require_once("editLinkTypeUI.inc");
	
	
	$oPatternCustom = & new PatternCustom();		
	
	if (isset($fForStore)){
		$oLink = LinkType::get($fLinkID);
		$oLink->setName($fLinkName);
		$oLink->setDescription($fDescription);
		
		if ($oLink->update()) {
				// if successfull print out success message
				$oPatternCustom->setHtml(getEditPageSuccess());
				
		} else {
				// if fail print out fail message
				$oPatternCustom->setHtml(getEditPageFail());
		}
	} else if (isset($fLinkID)){		
		// post back on Link select from manual edit page	
		$oPatternCustom->setHtml(getEditPage($fLinkID));
		$main->setFormAction($_SERVER["PHP_SELF"] . "?fForStore=1");
		
		
	} else {
		// if nothing happens...just reload edit page
		$oPatternCustom->setHtml(getEditPage(null));
		$main->setFormAction($_SERVER["PHP_SELF"]);
			
	}
	//render the page
	$main->setCentralPayload($oPatternCustom);
    $main->setHasRequiredFields(true);
	$main->render();
}
?>
