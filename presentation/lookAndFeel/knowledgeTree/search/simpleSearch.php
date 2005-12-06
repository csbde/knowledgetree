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

class SimpleSearchDispatcher extends KTStandardDispatcher {
	var $sSection = "search";
	var $browseType;
	
    function SimpleSearchDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _('Browse')),
            array('name' => _('Simple Search'))
        );
        return parent::KTStandardDispatcher();
    }
	

    function do_main() {
        $aErrorOptions = array(
            "message" => _("Please provide a search term"),
        );
		$searchable_text = KTUtil::arrayGet($_REQUEST, "fSearchableText");
        $this->oValidator->notEmpty($searchable_text, $aErrorOptions);
		
		$collection = new DocumentCollection;
		$this->browseType = "Folder"; 
		
		$collection->addColumn(new SelectionColumn("Browse Selection","selection"));
		$t = new TitleColumn("Test 1 (title)","title");
        $t->setOptions(array('documenturl' => '../documentmanagement/view.php'));
		$collection->addColumn($t);
		$collection->addColumn(new DateColumn(_("Created"),"created", "getCreatedDateTime"));
		$collection->addColumn(new DateColumn(_("Last Modified"),"modified", "getLastModifiedDate"));
		$collection->addColumn(new DateColumn(_("Last Modified"),"modified", "getLastModifiedDate"));
        $collection->addColumn(new UserColumn(_('Creator'),'creator_id','getCreatorID'));
		
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

