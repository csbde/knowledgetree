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

require_once(KT_LIB_DIR .'/util/ktVar.php');

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
			$aTemplateData['bulkActionMenu']=$this->renderBulkActionMenu($aBulkActions);
			
			$folderContentItems=$this->getTagContent($tag);
			
			$folderView=$pre_folderView=array();
			//foreach($folderContentItems['folders'] as $item)$pre_folderView[]=$this->renderFolderItem($item);
			foreach($folderContentItems['documents'] as $item)$pre_folderView[]=$this->renderDocumentItem($item);
			
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
			$aTemplateData['fragments'].=$this->renderDocumentItem(null,true);
			//$aTemplateData['fragments'].=$this->renderFolderItem(null,true);
			$aTemplateData['pagination']=$this->paginateByDiv($pageCount,'page','paginate','item',"kt.pages.browse.viewPage('[page]');","kt.pages.browse.prevPage();","kt.pages.browse.nextPage();");
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
	
	
	
	/* NEED TO BE PUT IN A SEPARATE CLASS */
	
	public function paginateByDiv($pageCount,$pageClass,$paginationClass="paginate",$itemClass="item",$pageScript="alert([page])",$prevScript="alert('previous');",$nextScript="alert('next');"){
		$idClass=$pageClass.'_[page]';
		$pages=array();
		$pages[]='<ul class="'.$paginationClass.'">';
		$pages[]='<li class="'.$itemClass.'" onclick="'.$prevScript.'">Previous</li>';
		for($i=1;$i<=$pageCount; $i++){
			$pages[]=ktVar::parseString('<li class="'.$itemClass.' '.$idClass.'" onclick="'.$pageScript.'">'.$i.'</li>',array('page'=>$i));
		}
		$pages[]='<li class="'.$itemClass.'" onclick="'.$nextScript.'">Next</li>';
		$pages[]='</ul>';
		$pages=join($pages);
		return $pages;
	}


	private function renderBulkActionMenu($items){
		$tpl='<table class="browseView bulkActionMenu" cellspacing="0" cellpadding="0"><tr><td>
		<input type="checkbox" class="select_all" />
		<input type="hidden" value="" name="sListCode"><input type="hidden" value="bulkaction" name="action">
		<input type="hidden" value="browse" name="fReturnAction"><input type="hidden" value="1" name="fReturnData">';
		
		$parts=array();
		
		foreach($items as $item){
			$parts[$item->getName()]='<input type="submit" name="submit['.$item->getName().']" value="'.$item->getDisplayName().'" />';
		}
		
		//parts order: Copy move, archive, delete, download all
		
		$tpl.=join($parts);

		$tpl.='</td><td class="status" style="width: 200px; text-align: right;"></td></tr></table>';
		return $tpl;
	}
	
	private function renderDocumentItem($item=NULL,$empty=false){
		$fileNameCutoff=100;
		
		$item['filename']=(strlen($item['filename'])>$fileNameCutoff)?substr($item['filename'],0,$fileNameCutoff-3)."...":$item['filename'];
		
		$ns=" not_supported";
		$item['has_workflow']='';
		$item['is_immutable']=$item['is_immutable']=='true'?true:false;
		$item['is_immutable']=$item['is_immutable']?'':$ns;
		$item['is_checkedout']=$item['checked_out_date']?'':$ns;
		
		$item['actions.checkin']=$item['checked_out_date']?'':$ns;
		$item['actions.cancel_checkout']=$item['checked_out_date']?'':$ns;
		$item['actions.checkout']=$item['checked_out_date']?$ns:'';
		
		//Modifications to perform when the document has been checked out
		if($item['checked_out_date']){
			list($item['checked_out_date_d'],$item['checked_out_date_t'])=split(" ",$item['checked_out_date']);
		}
		
		if($item['is_immutable']==''){
			$item['actions.checkin']=$ns;
			$item['actions.checkout']=$ns;
			$item['actions.cancel_checkout']=$ns;
			$item['actions.alerts']=$ns;
			$item['actions.email']=$ns;
			$item['actions.change_owner']=$ns;
			$item['actions.finalize_document']=$ns;
		}
		
		$item['separatorA']=$item['actions.download']=='' || $item['actions.instantview']=='' ?'':$ns;
		$item['separatorB']=$item['actions.checkout']=='' || $item['actions.checkin']=='' || $item['actions.cancel_checkout']=='' ?'':$ns;
		$item['separatorC']=$item['actions.alert']=='' || $item ['actions.email']=='' ?'':$ns;

		if($item['is_immutable']==''){
			$item['separatorA']=$item['separatorB']=$item['separatorC']=$ns;
		}
		

		// Check if the thumbnail exists
		$dev_no_thumbs=false;
		if(!$dev_no_thumbs){
			$oStorage=KTStorageManagerUtil::getSingleton();
	        
	        $varDir = $GLOBALS['default']->varDirectory;
			$thumbnailCheck = $varDir . '/thumbnails/'.$item['id'].'.jpg';
			
			if ($oStorage->file_exists($thumbnailCheck)) {
				$item['thumbnail'] = '<img src="plugins/thumbnails/thumbnail_view.php?documentId='.$item['id'].'" onClick="document.location.replace(\'view.php?fDocumentId='.$item['id'].'#preview\');">';
				$item['thumbnailclass'] = 'preview';
			} else {
				$item['thumbnail'] = '';
				$item['thumbnailclass'] = 'nopreview';
			}
		}else{
			$item['thumbnail'] = '';
			$item['thumbnailclass'] = 'nopreview';
		}
		
		$tpl='
			<span class="doc browseView">
				<table cellspacing="0" cellpadding="0" width="100%" border="0" class="doc item ddebug">
					<tr>
						<td width="1" class="checkbox">
							<input name="selection_d[]" type="checkbox" value="[id]" />
						</td>
						<td class="doc icon_cell" width="1">
							<div class="doc icon">
								<span class="immutable_info[is_immutable]">
									<span>This document has been <strong>finalized</strong> and can no longer be modified.</span>
									</span>
								<span class="checked_out[is_checkedout]">
									<span>This document is <strong>Checked-out</strong> by <strong>[checked_out_by]</strong> and cannot be edited until it is Checked-in.</span>
								</span>
								<span class="doc [thumbnailclass]">[thumbnail]</span>
							</div>
						</td>
						<td class="doc summary_cell fdebug">
							<ul class="doc actionMenu">
								<!-- li class="actionIcon comments"></li -->
								<li class="actionIcon actions">
									<ul>
										<li class="[actions.download]"><a href="action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=[id]">Download</a></li>
										<li class="[actions.instant_view]"><a href="view.php?fDocumentId=[id]#preview">Instant View</a></li>
										
										<li class="separator[separatorA]"></li>
										
										<li class="[actions.checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.checkout&fDocumentId=[id]">Check-out</a></li>
										<li class="[actions.cancel_checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.cancelcheckout&fDocumentId=[id]">Cancel Check-out</a></li>
										<li class="[actions.checkin]"><a href="action.php?kt_path_info=ktcore.actions.document.checkin&fDocumentId=[id]">Check-in</a></li>
										
										<li class="separator[separatorB]"></li>
										
										<li class="[actions.alerts]"><a href="action.php?kt_path_info=alerts.action.document.alert&fDocumentId=[id]">Alerts</a></li>
										<li class="[actions.email]"><a href="action.php?kt_path_info=ktcore.actions.document.email&fDocumentId=[id]">Email</a></li>
										
										<li class="separator[separatorC]"></li>
										
										<li class="[actions.change_owner]"><a href="action.php?kt_path_info=ktcore.actions.document.ownershipchange&fDocumentId=[id]">Change Document Ownership</a></li>
										<li class="[actions.finalize_document]"><a href="action.php?kt_path_info=ktcore.actions.document.immutable&fDocumentId=[id]">Finalize Document</a></li>
									</ul>
								</li>
							</ul>
							<div class="title"><a class="clearLink" href="view.php?fDocumentId=[id]" style="">[filename]</a></div>
							
							<div class="detail"><span class="item">Owner: <span class="user">[owned_by]</span></span><span class="item">Created: <span class="date">[created_date]</span> by <span class="user">[created_by]</span></span><span class="item">Updated: <span class="date">[modified_date]</span> by <span class="user">[modified_by]</span></span></div>
						</td>
					</tr>
					<tr>
						<td class="expanderField" colspan="3">
							<span class="expanderWidget comments">
								<H1>Comments</H1>
								<span>The comment display and add widget will be inserted here.</span>
							</span>
							<span class="expanderWidget properties">
								<H1>Properties</H1>
								<span>The properties display and edit widget will be inserted here.</span>
							</span>
						</td>
					</tr>
				</table>
			</span>
		';
		if($empty)return '<span class="fragment document" style="display:none;">'.$tpl.'</span>';
		return ktVar::parseString($tpl,$item);
	}
	
}
?>
