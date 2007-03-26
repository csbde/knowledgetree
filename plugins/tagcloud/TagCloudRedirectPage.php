<?php
/**
 * $Id: TagCloudRedirectPage.php,v 1.1 2006/02/28 16:53:49 nbm Exp $
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.knowledgetree.com/
 **/
 
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/browse/Criteria.inc');
require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');
require_once(KT_LIB_DIR . '/search/searchutil.inc.php');

require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
require_once(KT_LIB_DIR . '/browse/BrowseColumns.inc.php');
require_once(KT_LIB_DIR . '/browse/PartialQuery.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/actions/bulkaction.php');

class TagCloudRedirectPage extends KTStandardDispatcher {

	/**
	 * Dispatcher main method
	 *
	 * @return unknown
	 */
	function do_main() {
			$searchable_text = KTUtil::arrayGet($_REQUEST, 'tag');
			// set breadcrumbs
            $this->aBreadcrumbs[] = array('url' => 'dashboard.php', 'name' => _kt('Dashboard'));
			$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'].'&tag='.$searchable_text, 'name' => _kt('Tag Cloud Search'));
            // set page title
            $sTitle =  _kt('Search Results');
            
            $this->oPage->setBreadcrumbDetails($sTitle);                            
			
        	// build criteria set
            $aCriteriaSet = array(
            	'join'=>'AND',
            	'subgroup'=>array(
            		0=>array(
            			'join'=>'AND',
            			'values'=>array(
            				1=>array(
            					'data'=>array(
            						'ktcore.criteria.tagcloud'=>$searchable_text,
            						'ktcore.criteria.tagcloud_not'=>0
            						),
            					'type'=>'ktcore.criteria.tagcloud'
            				)
            			)
            		)
            	)
            );
            
		
        $this->browseType = 'Folder';
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
        
        $aOptions['return_url'] = KTUtil::addQueryString('dashboard.php', false);
        $aOptions['empty_message'] = _kt('No documents or folders match this query.');
        $aOptions['is_browse'] = true;
                
        $collection->setOptions($aOptions);
        $collection->setQueryObject(new BooleanSearchQuery($aCriteriaSet));

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/browse');
        $aTemplateData = array(
            'context' => $this,
            'collection' => $collection,
            'custom_title' => $sTitle,
            'isEditable' => true,
            'boolean_search' => $sSearch,
            'bulkactions' => KTBulkActionUtil::getAllBulkActions(),
            'browseutil' => new KTBrowseUtil(),
            'returnaction' => 'booleanSearch',
            'returndata' => $sSearch,

        );
        return $oTemplate->render($aTemplateData);
	
	}
}
?>
