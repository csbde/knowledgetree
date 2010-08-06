<?php
require_once(KT_LIB_DIR .'/util/ktVar.php');


class browseViewHelper {
	
	public function renderBrowseFolder($folderId=NULL){
		$response=Array();
		$response['bulkActionMenu']=$this->renderBulkActionMenu($aBulkActions);
		$folderContentItems=$this->getCurrentFolderContent($this->oFolder->getId());
		
		$folderView=$pre_folderView=array();
		foreach($folderContentItems['folders'] as $item)$pre_folderView[]=$this->renderFolderItem($item);
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
			$folderView[]='<span class="notification">There are currently no viewable items in this folder.</span>';
		}
		$folderView[]="</div>";
		
		$response['folderContents']=join($folderView);
		
		$response['fragments']='';
		$response['fragments'].=$this->renderDocumentItem(null,true);
		$response['fragments'].=$this->renderFolderItem(null,true);
		$response['pagination']=$this->paginateByDiv($pageCount,'page','paginate','item',"kt.pages.browse.viewPage('[page]');","kt.pages.browse.prevPage();","kt.pages.browse.nextPage();");
	}
	
	public function getFolderContent($folderId,$sortField='title',$asc=true){
		$oUser=KTEntityUtil::get('User',  $_SESSION['userID']);
		$KT=new KTAPI();
		$session=$KT->start_system_session($oUser->getUsername());

		//Get folder content, depth = 1, types= Directory, File, Shortcut, webserviceversion override
		$folder = &$KT->get_folder_contents($folderId,1,'DFS',3);
		
		$items=$folder['results']['items'];
		
		
		$ret=array('folders'=>array(),'documents'=>array());

		foreach($items as $item){
			foreach($item as $key=>$value){
				if($value=='n/a')$item[$key]=null;
			}
			$item['container_folder_id']=$folderId;
			switch($item['item_type']){
				case 'F':
					$item['is_shortcut']=false;
					$ret['folders'][]=$item;
					break;
				case 'D':
					$item['is_shortcut']=false;
					$ret['documents'][]=$item;
					break;
				case 'S':
					$item['is_shortcut']=true;
					if($item['mime_type']=='folder'){
						$ret['folders'][]=$item;
					}else{
						$ret['documents'][]=$item;
					}
					break;
			}
		}
		
		if(isset($sortField)){
			$ret['documents']=ktvar::sortArrayMatrixByKeyValue($ret['documents'],$sortField,$asc);
			$ret['folders']=ktvar::sortArrayMatrixByKeyValue($ret['folders'],$sortField,$asc);
		}
		
//		ktvar::quickDebug($ret);
		return $ret;
	}
		

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


	public function renderBulkActionMenu($items){
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
	
	public function renderDocumentItem($item=NULL,$empty=false,$shortcut=false){
		$fileNameCutoff=100;
		$oDocument = Document::get($item[id]);
		$item['mimetypeid']=(method_exists($oDocument,'getMimeTypeId'))?$oDocument->getMimeTypeId():'0';
		
		$iconFile='resources/mimetypes/newui/'.KTMime::getIconPath($item['mimetypeid']).'.png';
		//echo($iconFile);exit;
		$item['icon_exists']=file_exists(KT_DIR.'/'.$iconFile);
		$item['icon_file']=$iconFile;
		
		if($item['icon_exists']){		
			$item['mimeicon']=str_replace('\\','/',$GLOBALS['default']->rootUrl.'/'.$iconFile);
			$item['mimeicon']="background-image: url(".$item['mimeicon'].")";
		}else{
			$item['mimeicon']='';
		}
		
		
		if($item['linked_document_id']){
			$item['document_link']="view.php?fDocumentId={$item['linked_document_id']}&fShortcutFolder={$item['container_folder_id']}";
		}else{
			$item['document_link']="view.php?fDocumentId={$item['id']}";
		}
		
		$item['filename']=(strlen($item['filename'])>$fileNameCutoff)?substr($item['filename'],0,$fileNameCutoff-3)."...":$item['filename'];
		
		$ns=" not_supported";
		$item['has_workflow']='';
		$item['is_immutable']=$item['is_immutable']=='true'?true:false;
		$item['is_immutable']=$item['is_immutable']?'':$ns;
		$item['is_checkedout']=$item['checked_out_date']?'':$ns;
		$item['is_shortcut']=$item['is_shortcut']?'':$ns;
		
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
		$dev_no_thumbs=(isset($_GET['noThumbs']) || $_SESSION['browse_no_thumbs'])?true:false;
		$_SESSION['browse_no_thumbs']=$dev_no_thumbs;
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
		
//		$item['zoho_url']=Zoho::kt_url() . '/' . Zoho::plugin_path() . '/zohoEdit.php?session='.session_id().'&document_id='.$item['id'];
//		$item['zoho_edit']="zoho_edit" . time();
		
		$tpl='
			<span class="doc browseView">
				<table cellspacing="0" cellpadding="0" width="100%" border="0" class="doc item ddebug">
					<tr>
						<td width="1" class="checkbox">
							<input name="selection_d[]" type="checkbox" value="[id]" />
						</td>
						<td class="doc icon_cell" width="1">
							<div class="doc icon" style="[mimeicon]">
								<span class="immutable_info[is_immutable]">
									<span>This document has been <strong>finalized</strong> and can no longer be modified.</span>
									</span>
								<span class="checked_out[is_checkedout]">
									<span>This document is <strong>Checked-out</strong> by <strong>[checked_out_by]</strong> and cannot be edited until it is Checked-in.</span>
								</span>
								<span class="shortcut[is_shortcut]">
									<span>This is a shortcut to the file.</span>
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
										<!-- <li class="[actions.edit_online]"><a href="javascript:;" onclick="window.open(\'[zoho_url]\',\'[zoho_edit]\',\'menubar=no, toolbar=no, directories=no, location=no, scrollbars=no, resizable=yes, status=no, width=1024, height=768\')">Edit Online</a></li> -->
										
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
							<div class="title"><a class="clearLink" href="[document_link]" style="">[title]</a></div>
							
							<div class="detail"><span class="item">
								Owner: <span class="user">[owned_by]</span></span><span class="item">Created: <span class="date">[created_date]</span> by <span class="user">[created_by]</span></span><span class="item">Updated: <span class="date">[modified_date]</span> by <span class="user">[modified_by]</span></span>
							</div>
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
	
	public function renderFolderItem($item=NULL,$empty=false,$shortcut=false){
		//TODO: Tohir, if you put the .selected thing on the table $(.folder.item), it should work fine
		$ns=" not_supported";
		$item['is_shortcut']=$item['is_shortcut']?'':$ns;
		
		$tpl='
			<span class="doc browseView">
			<table cellspacing="0" cellpadding="0" width="100%" border="0" class="folder item">
				<tr>
					<td width="1" class="checkbox">
						<input name="selection_f[]" type="checkbox" value="[id]" />
					</td>
					<td class="folder icon_cell" width="1">
						<div class="folder icon">
							<span class="shortcut[is_shortcut]"><span>This is a shortcut to the folder.</span></span>
						</div>
					</td>
					<td class="folder summary_cell">
						<ul class="folder actionMenu">
							<li class="actionIcon actions">
									<ul>
										<li><a href="action.php?kt_path_info=ktcore.actions.folder.rename&fFolderId=[id]">Rename Folder</a></li>
										<li><a href="action.php?kt_path_info=ktcore.actions.folder.permissions&fFolderId=[id]">Share Folder</a></li>
										<!-- <li><a href="#" onclick=\'alert("JavaScript to be modified")\'>Subscribe to Folder</a></li> -->
										<li><a href="action.php?kt_path_info=ktcore.actions.folder.transactions&fFolderId=[id]">View Folder Activity</a></li>
									</ul>
							</li>
						</ul>
						<div class="title"><a class="clearLink" href="browse.php?fFolderId=[id]">[title]</a></div>
						<div class="detail"><span class="item">Created by: <span class="creator">[created_by]</span></span></div>
					</td>
				</tr>
			</table>
			</span>';
		if($empty)return '<span class="fragment folder" style="display:none;">'.$tpl.'</span>';
		return ktVar::parseString($tpl,$item);
	}

}
?>