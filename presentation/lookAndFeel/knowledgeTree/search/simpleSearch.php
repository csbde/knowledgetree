<?php

/**
 * $Id$
 *
 * Main dashboard page -- This page is presented to the user after login.
 * It contains a high level overview of the users subscriptions, checked out 
 * document, pending approval routing documents, etc. 
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
 */

// main library routines and defaults
require_once("../../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");


require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

$sectionName = "search";

class SimpleSearchDispatcher extends KTStandardDispatcher {

    
    // Breadcrumbs base - added to in methods
    var $aBreadcrumbs = array(
        array('action' => 'browse', 'name' => 'Browse'),
		array('name' => 'Simple Search')
    );
	var $sSection = "search";
	var $browseType;
	
	

    function do_main() {
	    // There's a fair amount here, so we want to break it down.
		// we want:
		//      - folder_id
		//      - batch info.
		//      - (browse type)? 
		
		
	    	
		$collection = new DocumentCollection;
		$this->browseType = "Folder"; 
		
		$collection->addColumn(new SelectionColumn("Browse Selection","selection"));
		$collection->addColumn(new TitleColumn("Test 1 (title)","title"));
		$collection->addColumn(new DateColumn("Created","created", "getCreatedDateTime"));
		$collection->addColumn(new DateColumn("Last Modified","modified", "getLastModifiedDate"));
		$collection->addColumn(new BrowseColumn("Test 3","test3"));
		$collection->addColumn(new BrowseColumn("Test 4","test4"));
		
		$searchable_text = KTUtil::arrayGet($_REQUEST, "fSearchableText");
		
		$batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
		$batchSize = 20;
		
		$resultURL = "?fSearchableText=" . $searchable_text;
		$collection->setBatching($resultURL, $batchPage, $batchSize); 
		
		
		// ordering. (direction and column)
		$displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");		
		if ($displayOrder !== "asc") { $displayOrder = "desc"; }
		$displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");		
		
		$collection->setSorting($displayControl, $displayOrder);
		
		// add in the query object.
		$qObj = new SimpleSearchQuery($searchable_text);
		$collection->setQueryObject($qObj);
		
		// breadcrumbs
		// FIXME handle breadcrumbs
		$collection->getResults();
		
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/browse");
		$aTemplateData = array(
              "context" => $this,
			  "collection" => $collection,
		);
		return $oTemplate->render($aTemplateData);
	}   
}

$oDispatcher = new SimpleSearchDispatcher();
$oDispatcher->dispatch();

?>

