<?php

/**
 * $Id$
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
 * @author Brad Shuttleworth <brad@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @author Neil Blakey-Milner <nbm@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

// main library routines and defaults
require_once("../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");


require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");

class SimpleSearchTitleColumn extends TitleColumn {
    function setSearch($sSearch) {
        $this->sSearch = $sSearch;
    }
    function renderData($aDataRow) {
        $iDocumentId =& $aDataRow['document']->getId();
        
        $aLocs = array();
        $bFound = true;
        $iLastFound = 0;
        $iNumFound = 0;
        while ($bFound && $iNumFound < 5) {
            $sQuery = "SELECT LOCATE(?, document_text, ?) AS posi FROM document_searchable_text WHERE document_id = ?";
            $aParams = array($this->sSearch, $iLastFound + 1, $iDocumentId);
            $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'posi');
            if (PEAR::isError($res)) {
                var_dump($res);
                exit(0);
            }
            if (empty($res)) {
                break;
            }
            $iNumFound++;
            $iLastFound = $res;
            $bFound = $res;
            if ($iLastFound) {
                $aLocs[] = $iLastFound;
            }
        }

        $iBack = 20;
        $iForward = 50;

        $aTexts = array();
        foreach ($aLocs as $iLoc) {
            $iThisForward = $iForward;
            $iThisBack = $iBack;
            if ($iLoc - $iBack < 0) {
                $iThisForward = $iForward + $iLoc;
                $iThisBack = 0;
                $iLoc = 1;
            }
            $sQuery = "SELECT SUBSTRING(document_text FROM ? FOR ?) AS text FROM document_searchable_text WHERE document_id = ?";
            $aParams = array($iLoc - $iThisBack, $iThisForward + $iThisBack, $iDocumentId);
            $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'text');
            if (PEAR::isError($res)) {
                var_dump($res);
                exit(0);
            }
            $res = htmlentities($res);
            $aSearch = array(sprintf('#(%s)#i', $this->sSearch));
            $aReplace = array('&nbsp; <span class="searchresult" style="color: red">\1</span> &nbsp;');
            $sText = preg_replace($aSearch, $aReplace, $res);
            $aFirstSpace = array(strpos($sText, " "), strpos($sText, "\n"));
            $iFirstSpace = false;
            foreach ($aFirstSpace as $iPos) {
                if ($iFirstSpace === false) {
                    $iFirstSpace = $iPos;
                    continue;
                }
                if ($iPos === false) {
                    continue;
                }
                if ($iPos < $iFirstSpace) {
                    $iFirstSpace = $iPos;
                }
            }
            if ($iFirstSpace === false) {
                $iFirstSpace = -1;
            }
            $iLastSpace = strrpos($sText, " ");
            $sText = substr($sText, $iFirstSpace + 1, $iLastSpace - $iFirstSpace - 1);
            $sText = str_replace("&nbsp; ", "", $sText);
            $sText = str_replace(" &nbsp;", "", $sText);
            $aTexts[] = $sText;
        }

        $sFullTexts = join(" &hellip; ", $aTexts);

        return sprintf('<div>%s</div><div class="searchresults" style="margin-top: 0.5em; color: grey">%s</div>', parent::renderData($aDataRow), $sFullTexts);
    }
}

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
		$t = new SimpleSearchTitleColumn("Test 1 (title)","title");
        $t->setOptions(array('documenturl' => $GLOBALS['KTRootUrl'] . '/view.php'));
        $t->setSearch($searchable_text);
		$collection->addColumn($t);
		$collection->addColumn(new DateColumn(_("Created"),"created", "getCreatedDateTime"));
		$collection->addColumn(new DateColumn(_("Last Modified"),"modified", "getLastModifiedDate"));
        $collection->addColumn(new UserColumn(_('Creator'),'creator_id','getCreatorID'));
		$collection->addColumn(new WorkflowColumn(_('Workflow State'),'workflow_state'));
		
		$batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
		$batchSize = 20;
		
		$resultURL = KTUtil::addQueryStringSelf("fSearchableText=" . $searchable_text);
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

