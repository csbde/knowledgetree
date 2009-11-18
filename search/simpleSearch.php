<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the 
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original 
 * copyright notice.
 * Contributor( s): ______________________________________
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
require_once(KT_LIB_DIR . "/util/sanitize.inc");

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
        $searchable_text = sanitizeForSQL(KTUtil::arrayGet($_REQUEST, "fSearchableText"));
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

