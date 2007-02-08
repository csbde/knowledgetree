<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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

require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');
require_once(KT_LIB_DIR . '/actions/bulkaction.php');

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
            array('action' => 'browse', 'name' => _kt('Browse')),
            array('name' => _kt('Simple Search'))
        );
        return parent::KTStandardDispatcher();
    }
	

    function do_main() {
        $aErrorOptions = array(
            "message" => _kt("Please provide a search term"),
        );
        $searchable_text = KTUtil::arrayGet($_REQUEST, "fSearchableText");
        $this->oValidator->notEmpty($searchable_text, $aErrorOptions);


        $collection = new AdvancedCollection;       
        $oColumnRegistry = KTColumnRegistry::getSingleton();
        $aColumns = $oColumnRegistry->getColumnsForView('ktcore.views.search');
        $collection->addColumns($aColumns);	
        
        // set a view option
        $aTitleOptions = array(
            'documenturl' => $GLOBALS['KTRootUrl'] . '/view.php',
            'direct_folder' => true,
        );
        $collection->setColumnOptions('ktcore.columns.title', $aTitleOptions);

        // set the selection options
        $collection->setColumnOptions('ktcore.columns.selection', array(
            'rangename' => 'selection',
            'show_folders' => true,
            'show_documents' => true,
        ));

        
        $aOptions = $collection->getEnvironOptions(); // extract data from the environment
        
        $aOptions['return_url'] = KTUtil::addQueryStringSelf("fSearchableText=" . urlencode($searchable_text));
        $aOptions['empty_message'] = _kt("No documents or folders match this query.");
        $aOptions['is_browse'] = true;        

        $collection->setOptions($aOptions);
        $collection->setQueryObject(new SimpleSearchQuery($searchable_text));    
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/browse");
        $aTemplateData = array(
            "context" => $this,
            "collection" => $collection,
            'isEditable' => true,
            'bulkactions' => KTBulkActionUtil::getAllBulkActions(),
            'browseutil' => new KTBrowseUtil(),
            'returnaction' => 'simpleSearch',
            'returndata' => $searchable_text,
        );
        return $oTemplate->render($aTemplateData);
        }   
}

$oDispatcher = new SimpleSearchDispatcher();
$oDispatcher->dispatch();

?>

