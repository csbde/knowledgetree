<?php
/*
* $Id$
*
* KnowledgeTree Open Source Edition
* Document Management Made Simple
* Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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

require_once(KT_DIR . '/plugins/tagcloud/TagCloudPortlet.php');

require_once(KT_LIB_DIR .'/render_helpers/browseView.helper.php');

class TagCloudRedirectPage extends KTStandardDispatcher {

    /**
	 * Dispatcher main method
	 *
	 * @return unknown
	 */
    function do_main() {
        // Clear the session for a new search
        $url = isset($_REQUEST['tag']) ? 'tag='.urlencode($_REQUEST['tag']).'&decode=true' : '';
        $_SESSION['tagList'] = array();
        $this->redirectTo('search', $url);
    }

    /**
     * Recall a previous tag search and remove the tags that were selected after it.
     */
    function do_recall() {
        $tag = $_REQUEST['tag'];
        $pos = $_REQUEST['pos'];

        // Delete all tags after and including the selected tag
        $tagList = $_SESSION['tagList'];
        $tagList = array_slice($tagList, 0, $pos);

        $_SESSION['tagList'] = $tagList;

        $url = 'tag='.urlencode($tag).'&decode=true';
        $this->redirectTo('search', $url);
    }

    function do_search() {
        // Get the tag to search for and create search query
        $tag = isset($_REQUEST['tag']) ? $_REQUEST['tag'] : '';
        $decode = isset($_REQUEST['decode']) ? $_REQUEST['decode'] : '';
        if($decode == 'true'){
            $tag = urldecode($tag);
        }

        $iUserId = $_SESSION['userID'];
        $oUser = User::get($iUserId);

        // set breadcrumbs
		/*
        $this->aBreadcrumbs[] = array('url' => 'dashboard.php', 'name' => _kt('Dashboard'));
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Tag Cloud Search'));

        $tagList = $_SESSION['tagList'];
        if(!empty($tagList)){
            $aPrevTag = end($tagList);
            $aTagTree = $aPrevTag['tagTree'];

            $base = KTUtil::addQueryString('TagCloudRedirection&action=recall', null);
            foreach($aTagTree as $key => $item){
                if($tag == $item){
                    continue;
                }
                $url = $base.'&tag='.urlencode($item).'&pos='.$key;
                $this->aBreadcrumbs[] = array('url' => $url, 'name' => $item);
            }
        }
        if(!empty($tag)){
            $this->aBreadcrumbs[] = array('url' => '', 'name' => $tag);
        }*/

        // set page title
        $sTitle =  _kt('Search Results - Tag:').' '.$tag;
        $this->oPage->setBreadcrumbDetails($sTitle);

        // Set tag cloud portlet
        $portlet = new TagCloudPortlet($oUser, $tag);
        $this->oPage->addPortlet($portlet);

		/*
        $collection = new AdvancedCollection;
        $oColumnRegistry = KTColumnRegistry::getSingleton();
        $aColumns = $oColumnRegistry->getColumnsForView('ktcore.views.search');
        $collection->addColumns($aColumns);

        // set a view option
        $aTitleOptions = array('documenturl' => $GLOBALS['KTRootUrl'] . '/view.php',);
        $collection->setColumnOptions('ktcore.columns.title', $aTitleOptions);
        $collection->setColumnOptions('ktcore.columns.selection', array(
            'rangename' => 'selection',
            'show_folders' => true,
            'show_documents' => true,
            ));

        $aOptions = $collection->getEnvironOptions(); // extract data from the environment

        $returnUrl = KTUtil::addQueryString('TagCloudRedirection&action=search&tag='. urlencode($tag), false);
        $aOptions['return_url'] = $returnUrl;
        $aOptions['empty_message'] = _kt('No documents or folders match this query.');
        $aOptions['is_browse'] = true;

        $collection->setOptions($aOptions);
        $collection->setQueryObject(new TagQuery($oUser, $tag));
		*/

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
            'returnaction' => $returnUrl,
        );
		
		
		
		//if(!$aTemplateData['oldBrowse']){
			$browseViewRender = new browseViewHelper();
		
			$aTemplateData['bulkActionMenu']=$browseViewRender->renderBulkActionMenu($aBulkActions);
			
			$folderContentItems=$this->getTagContent($tag);
			
			
			
			$folderView=$pre_folderView=array();
			//foreach($folderContentItems['folders'] as $item)$pre_folderView[]=$this->renderFolderItem($item);
			foreach($folderContentItems['documents'] as $item)
			{
				$item['id'] = $item['document_id'];
				
				if ($item['checked_out_date'] == 'n/a') {
					$item['checked_out_date'] = '';
				}
				
				$pre_folderView[]=$browseViewRender->renderDocumentItem($item);
			}
			
			$pageCount=1;
			$perPage=15;
			$itemCount=count($pre_folderView);
			$curItem=0;
			
			$folderView[]='<div class="page page_'.$pageCount.' ">';
			foreach($pre_folderView as $item){
				$curItem++;
				if($curItem>$perPage){
					$pageCount++;
					$curItem=1;
					$folderView[]='</div><div class="page page_'.$pageCount.' ">';
				}
				$folderView[]=$item;
			}
			if($itemCount<=0){
				$folderView[]='<span class="notification" id="empty_message">There are currently no viewable items in this folder.</span>';
			}
			$folderView[]="</div>";
			
			$aTemplateData['folderContents']=join($folderView);
			
			$aTemplateData['fragments']='';
			$aTemplateData['fragments'].=$browseViewRender->renderDocumentItem(null,true);
			$aTemplateData['fragments'].=$browseViewRender->renderFolderItem(null,true);
			$aTemplateData['pagination']=$browseViewRender->paginateByDiv($pageCount,'page','paginate','item',"kt.pages.browse.viewPage('[page]');","kt.pages.browse.prevPage();","kt.pages.browse.nextPage();");
			
			$aTemplateData['javascript'] = $browseViewRender->getJavaScript();
		//}
		
		
        return $oTemplate->render($aTemplateData);
    }
	
	function getTagContent($tag)
	{
		$oUser=KTEntityUtil::get('User',  $_SESSION['userID']);
		$KT=new KTAPI();
		$session=$KT->start_system_session($oUser->getUsername());
		
		$results = $KT->get_tag_contents ( $tag );
		
		$ret=array('folders'=>array(),'documents'=>$results['results'],'shortcuts'=>array());
		
		return $ret;
	}


	
	
}
?>
