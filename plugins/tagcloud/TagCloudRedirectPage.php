<?php
/*
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
 *
 */

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
			$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'].'&tag='.htmlentities($searchable_text,ENT_QUOTES,'UTF-8'), 'name' => _kt('Tag Cloud Search'));
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

        //$aOptions['return_url'] = KTUtil::addQueryString('dashboard.php', false);
		$aOptions['return_url'] = KTUtil::addQueryString('TagCloudRedirection&tag='. $searchable_text, false );
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
