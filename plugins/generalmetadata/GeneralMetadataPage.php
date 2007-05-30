<?php

/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */
 
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/browse/Criteria.inc");
require_once(KT_LIB_DIR . "/search/savedsearch.inc.php");
require_once(KT_LIB_DIR . "/search/searchutil.inc.php");

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/actions/bulkaction.php');

class GeneralMetadataPage extends KTStandardDispatcher {

	function do_main() {
			$searchable_text = KTUtil::arrayGet($_POST, "fSearchableText");
			
            // set breadcrumbs
            $this->aBreadcrumbs[] = array('url' => 'dashboard.php', 'name' => _kt('Dashboard'));
			$this->aBreadcrumbs[] = array('name' => _kt('General Metadata Search'));
			
            $sTitle =  _kt('Search Results');
            $this->oPage->setBreadcrumbDetails($sTitle);                            
        	

            $aCriteriaSet = array(
            	'join'=>'AND',
            	'subgroup'=>array(
            		0=>array(
            			'join'=>'AND',
            			'values'=>array(
            				1=>array(
            					'data'=>array(
            						'ktcore.criteria.generalmetadata'=>$searchable_text,
            						'ktcore.criteria.generalmetadata_not'=>0
            						),
            					'type'=>'ktcore.criteria.generalmetadata'
            				)
            			)
            		)
            	)
            );
            

        $this->browseType = "Folder";
        $sSearch = md5(serialize($aCriteriaSet));
        $_SESSION['boolean_search'][$sSearch] = $aCriteriaSet;

        $collection = new AdvancedCollection;       
        $oColumnRegistry = KTColumnRegistry::getSingleton();
        $aColumns = $oColumnRegistry->getColumnsForView('ktcore.views.search');
        $collection->addColumns($aColumns);
        
        // set a view option
        $aTitleOptions = array(
            'documenturl' => $GLOBALS['KTRootUrl'] . '/view.php',
        );
        $collection->setColumnOptions('ktcore.columns.title', $aTitleOptions);
        $collection->setColumnOptions('ktcore.columns.selection', array(
            'rangename' => 'selection',
            'show_folders' => true,
            'show_documents' => true,
        ));
        
        $aOptions = $collection->getEnvironOptions(); // extract data from the environment
        
        $aOptions['return_url'] = KTUtil::addQueryStringSelf("action=performSearch&boolean_search_id=" . urlencode($sSearch));
        $aOptions['empty_message'] = _kt("No documents or folders match this query.");
        $aOptions['is_browse'] = true;
                
        $collection->setOptions($aOptions);
        $collection->setQueryObject(new BooleanSearchQuery($aCriteriaSet));    
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/browse");
        $aTemplateData = array(
            "context" => $this,
            "collection" => $collection,
            "custom_title" => $sTitle,
            "params" => $aParams,
	    	"joins" => $aJoins,
            'isEditable' => true,
            "boolean_search" => $sSearch,
            'bulkactions' => KTBulkActionUtil::getAllBulkActions(),
            'browseutil' => new KTBrowseUtil(),
            'returnaction' => 'booleanSearch',
            'returndata' => $sSearch,

        );
        return $oTemplate->render($aTemplateData);
	}
}
?>