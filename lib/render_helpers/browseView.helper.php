<?php
require_once(KT_LIB_DIR . '/util/ktVar.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/users/shareduserutil.inc.php');
require_once('sharedContent.inc');

/**
 * Utility class to switch between user specific browse views
 *
 */
class browseViewUtil
{
    static function getBrowseView()
    {
    	$oUser = User::get($_SESSION['userID']);
    	$userType = $oUser->getDisabled();
    	switch ($userType)
    	{
    		case 0 :
    			return new userBrowseView();
    			break;
    		case 4 :
    			return new sharedUserBrowseView();
    			break;
    		default:
    			return new userBrowseView();
    			break;
    	}
	}
}

class sharedUserBrowseAsView extends browseView
{
	/**
	 * Get the shared users
	 *
	 * @param string $folderId
	 * @param string $sortField
	 * @param string $asc
	 * @return mixed $ret
	 */
	public function getFolderContent($folderId, $sortField = 'title', $asc = true)
	{

	}
}

/**
 * Shared user browse view class
 *
 */
class sharedUserBrowseView extends browseView
{
	/**
	 * Get the folder listing
	 *
	 * @param string $folderId
	 * @param string $sortField
	 * @param string $asc
	 * @return mixed $ret
	 */
	public function getFolderContent($folderId, $sortField = 'title', $asc = true)
	{
		$user_id = $_SESSION['userID'];
		$oUser = User::get($user_id);
		$disabled = $oUser->getDisabled();

		$oSharedContent = new SharedContent();
		$aSharedContent = $oSharedContent->getUsersSharedContents($user_id, $folderId);
		$ret = array(	'folders' => array(),
						'documents'=>array()
					);
		foreach ($aSharedContent['documents'] as $item)
		{
			$item['user_id'] = $user_id;
			$item['user_disabled'] = $disabled;
			$item['item_type'] = 'D';
			$item['version'] = $item['major_version'] . '.' .$item['minor_version'];
			$ret['documents'][] = $this->browseViewItems($item, $folderId);
		}

		foreach ($aSharedContent['folders'] as $item)
		{
			$item['item_type'] = 'F';
			$item['mime_type'] = 'folder';
			$item['mime_icon_path'] = 'folder';
			$item['mime_display'] = 'Folder';
			$ret['folders'][] = $this->browseViewItems($item, $folderId);
		}

		if (isset($sortField))
		{
			$ret['documents'] = ktvar::sortArrayMatrixByKeyValue($ret['documents'], $sortField, $asc);
			$ret['folders'] = ktvar::sortArrayMatrixByKeyValue($ret['folders'], $sortField, $asc);
		}

		return $ret;
	}

	public function renderBulkActionMenu($items, $folder)
	{
		return '';
	}

	public function renderDocumentItem($item = null, $empty = false, $shortcut = false) {
	 	$fileNameCutoff = 100;
	 	$ns = ' not_supported';
		$permissions = SharedContent::canAccessDocument($item['user_id'], $item['id'], null, 1);
		$hasCheckedOut = ($_SESSION['userID'] == $item['checked_out_by_id']);
		// Icons
		$iconFile = 'resources/mimetypes/newui/' . KTMime::getIconPath($item['mimetypeid']) . '.png';
		$item['icon_exists'] = file_exists(KT_DIR . '/' . $iconFile);
		$item['icon_file'] = $iconFile;
		if ($item['icon_exists']) {
			$item['mimeicon'] = str_replace('\\', '/', $GLOBALS['default']->rootUrl . '/' . $iconFile);
			$item['mimeicon'] = "background-image: url(" . $item['mimeicon'] . ")";
		} else {
			$item['mimeicon'] = '';
		}
		// Create link, which will always be of a document and not a shortcut
		$item['document_link'] = KTUtil::buildUrl("view.php", array('fDocumentId'=>$item['id']));
		$item['filename'] = (strlen($item['filename']) > $fileNameCutoff) ? substr($item['filename'], 0, $fileNameCutoff - 3) . "..." : $item['filename'];
		$item['has_workflow'] = '';
		$item['is_immutable'] = ($item['is_immutable'] == 1) ? true : false;
		$item['is_immutable'] = $item['is_immutable'] ? '' : $ns;
		$item['is_checkedout'] = $item['checked_out_date'] ? '' : $ns;
		// Check parent folder if user type is shared (disabled == 4)
		if (isset($item['object_permissions'])) {
			// check permissions based on object_permissions, if set, or shared user access if shared user
			// and check if the user has checkd out the document
		    $item['actions.checkout'] = ($item['object_permissions'] == 0) ? $ns : ($item['checked_out_date'] ? $ns : '');
		    $item['actions.checkin'] = ($item['object_permissions'] == 0 || !$hasCheckedOut) ? $ns : ($item['is_checked_out'] == 0 ? $ns : '');
			$item['actions.cancel_checkout'] = ($item['object_permissions'] == 0 || !$hasCheckedOut) ? $ns : ($item['is_checked_out'] == 0 ? $ns : '');
		}
		else if ($item['user_disabled'] == 4) {
		    // check permissions on parent folder if document not present in shared content for user
		    $item['actions.checkout'] = ($permissions == false) ? $ns : $item['checked_out_date'] ? '' : $ns;
		    $item['actions.checkin'] = ($permissions == false) ? $ns : (($item['is_checked_out'] == 0) ? '' : $ns);
			$item['actions.cancel_checkout'] = ($permissions == false) ? $ns : (($item['is_checked_out'] == 0) ? '' : $ns);
		}

		//Modifications to perform when the document has been checked out
		if ($item['checked_out_date']) {
			list($item['checked_out_date_d'], $item['checked_out_date_t']) = split(" ", $item['checked_out_date']);
		}

		if ($item['is_immutable'] == '') {
			$item['actions.checkin'] = $ns;
			$item['actions.checkout'] = $ns;
			$item['actions.cancel_checkout'] = $ns;
			$item['actions.alerts'] = $ns;
			$item['actions.email'] = $ns;
			$item['actions.change_owner'] = $ns;
			$item['actions.finalize_document'] = $ns;
		}

		$item['separatorA'] = $item['actions.copy'] == '' ? '' : $ns;
		$item['separatorB'] = $item['actions.download'] == '' || $item['actions.instantview'] == '' ? '' : $ns;
		$item['separatorC'] = $item['actions.checkout'] == '' || $item['actions.checkin'] == '' || $item['actions.cancel_checkout'] == '' ? '' : $ns;
		$item['separatorD'] = $item['actions.alert'] == '' || $item ['actions.email'] == '' ? '' : $ns;

		if ($item['is_immutable'] == '') {
			$item['separatorB'] = $item['separatorC'] = $item['separatorD'] = $ns;
		}

		// Check if the thumbnail exists
        $dev_no_thumbs = (isset($_GET['noThumbs']) || $_SESSION['browse_no_thumbs']) ? true : false;
        $_SESSION['browse_no_thumbs'] = $dev_no_thumbs;
        $item['thumbnail'] = '';
        $item['thumbnailclass'] = 'nopreview';

        // When item is null, thumbnails won't exist so skip the check
        if (!$dev_no_thumbs)
        {
            // Check if the document has a thumbnail rendition -> has_rendition = 2, 3, 6, 7
            // 0 = nothing, 1 = pdf, 2 = thumbnail, 4 = flash
            // 1+2 = 3: pdf & thumbnail; 1+4 = 5: pdf & flash; 2+4 = 6: thumbnail & flash; 1+2+4 = 7: all
            // If the flag hasn't been set, check against storage and update the flag - for documents where the flag hasn't been set
            $check = false;
            if (is_null($item['has_rendition']))
            {
                $oStorage = KTStorageManagerUtil::getSingleton();
				$oDocument = Document::get($item['id']);
				if (!PEAR::isError($oDocument))
				{
	                $varDir = $GLOBALS['default']->varDirectory;
	                $thumbnailCheck = $varDir . '/thumbnails/' . $item['id'] . '.jpg';
	                if ($oStorage->file_exists($thumbnailCheck)) {
	                    $oDocument->setHasRendition(2);
	                    $check = true;
	                } else {
	                    $oDocument->setHasRendition(0);
	                }
	                $oDocument->update();
				}
            }

            if ($check || in_array($item['has_rendition'], array(2, 3, 6, 7)))
            {
                $item['thumbnail'] = '<img src="plugins/thumbnails/thumbnail_view.php?documentId=' . $item['id'] . '" onClick="document.location.replace(\'view.php?fDocumentId=' . $item['id'] . '#preview\');">';
                $item['thumbnailclass'] = 'preview';
            }
        }
		// Default - hide edit online
		$item['allowdoczohoedit'] = '';

		if ($this->zohoEnabled) {
			if (Zoho::resolve_type($oDocument))
			{
				if ($item['actions.checkout'] != $ns) {
					$item['allowdoczohoedit'] = '<li class="action_zoho_document"><a href="javascript:;" onclick="zohoEdit(\'' . $item['id'] . '\')">Edit Document Online</a></li>';
				}
			}
		}
		// Get the name of the user that checked out document
		if(!is_null($item['checked_out_by_id']))
		{
			$coUser = User::get($item['checked_out_by_id']);
			$item['checked_out_by'] = $coUser->getName();
		}
		$checkbox = '';
		// Sanitize document title
		$item['title'] = sanitizeForHTML($item['title']);
		$tpl='
			<span class="doc browseView 1">
				<table cellspacing="0" cellpadding="0" width="100%" border="0" class="doc item ddebug">
					<tr>
						'.$checkbox.'
						<td class="doc icon_cell" width="1">
							<div class="doc icon" style="[mimeicon]">
								<span class="immutable_info[is_immutable]">
									<span>This document has been <strong>finalized</strong> and can no longer be modified.</span>
									</span>
								<span class="checked_out[is_checkedout]">
									<span>This document is <strong>checked-out</strong> by <strong>[checked_out_by]</strong> and cannot be edited until it is Checked-in.</span>
								</span>
								<span class="doc [thumbnailclass]">[thumbnail]</span>
							</div>
						</td>
						<td class="doc summary_cell fdebug">
							<ul class="doc actionMenu">
								<!-- li class="actionIcon comments"></li -->
								<li class="actionIcon actions">
									<ul>
										<li class="action_download [actions.download]"><a href="action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=[id]">Download</a></li>
										<li class="action_instant_view [actions.instant_view]"><a href="[document_link]#preview">Instant View</a></li>
										[allowdoczohoedit]

										<li class="action_checkout [actions.checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.checkout&fDocumentId=[id]">Check-out</a></li>
										<li class="action_cancel_checkout [actions.cancel_checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.cancelcheckout&fDocumentId=[id]">Cancel Check-out</a></li>
										<li class="action_checkin [actions.checkin]"><a href="action.php?kt_path_info=ktcore.actions.document.checkin&fDocumentId=[id]">Check-in</a></li>
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

		if ($empty) { return '<span class="fragment document" style="display:none;">' . $tpl . '</span>'; }

		return ktVar::parseString($tpl, $item);
	}

	public function renderFolderItem($item = null, $empty = false, $shortcut = false)
	{
		$item['link'] = KTUtil::buildUrl('browse.php', array('fFolderId'=>$item['id']));
		$checkbox = '';
		// Sanitize folder title
		$item['title'] = sanitizeForHTML($item['title']);
		$tpl='
			<span class="doc browseView">
			<table cellspacing="0" cellpadding="0" width="100%" border="0" class="folder item">
				<tr>
					'.$checkbox.'
					<td class="folder icon_cell" width="1">
						<div class="folder icon">
						</div>
					</td>
					<td class="folder summary_cell">
						<div class="title"><a class="clearLink" href="[link]">[title]</a></div>
						<div class="detail"><span class="item">Created by: <span class="creator">[created_by]</span></span></div>
					</td>
				</tr>
			</table>
			</span>';

		if ($empty) { return '<span class="fragment folder" style="display:none;">' . $tpl . '</span>'; }

		return ktVar::parseString($tpl,$item);
	}

	/**
	 * Sanitize a browse view items attributes
	 *
	 * @param array $item
	 * @param int $folderId
	 * @return array $item
	 */

	private function browseViewItems($item, $folderId)
	{
		foreach ($item as $key=>$value)
		{
			if ($value=='n/a')
			{
				$item[$key]=null;
			}
		}
		$item['container_folder_id']=$folderId;

		return $item;
	}
}
/**
 * Default user browse view class
 *
 */
class userBrowseView extends browseView
{

}

/**
 * Browse view base class
 *
 */
class browseView {

	public function __construct()
	{
		if (KTPluginUtil::pluginIsActive('zoho.plugin')) {
			$this->zohoEnabled = true;
			require_once(KT_PLUGIN_DIR . '/ktlive/zoho/zoho.inc.php');
		} else {
			$this->zohoEnabled = false;
		}

		// Include new browse view css
		$oPage = $GLOBALS['main'];
		$oPage->requireCSSResource("resources/css/newui/browseView.css?" . rand());
	}

	public function getJavaScript()
	{
		$javaScript = '';

		if ($this->zohoEnabled) {
			$javaScript .= '<script type="text/javascript">' . Zoho::editScript() . '</script>';
		}

		return $javaScript;
	}

	public function renderBrowseFolder($folderId = null) {
		$response = array();
		$response['bulkActionMenu'] = $this->renderBulkActionMenu($aBulkActions);
		$folderContentItems = $this->getCurrentFolderContent($this->oFolder->getId());

		$folderView = $pre_folderView = array();
		foreach ($folderContentItems['folders'] as $item) {
		    $pre_folderView[] = $this->renderFolderItem($item);
		}
		foreach ($folderContentItems['documents'] as $item) {
		    $pre_folderView[] = $this->renderDocumentItem($item);
		}

		$pageCount = 1;
		$perPage = 15;
		$itemCount = count($pre_folderView);
		$curItem = 0;

		$folderView[] = '<div class="page page_' . $pageCount . ' ">';
		foreach ($pre_folderView as $item) {
			++$curItem;
			if ($curItem > $perPage) {
				++$pageCount;
				$curItem = 1;
				$folderView[] = '</div><div class="page page_' . $pageCount . ' ">';
			}
			$folderView[] = $item;
		}

		if ($itemCount <= 0) {
			$userHasWritePermissions = true; // Need to fix this
			$folderView[] = $this->noFilesOrFoldersMessage($folderId, $userHasWritePermissions);
		}
		$folderView[] ="</div>";

		$response['folderContents'] = join($folderView);
		$response['fragments'] = '';
		$response['fragments'] .= $this->renderDocumentItem(null, true);
		$response['fragments'].= $this->renderFolderItem(null, true);
		$response['pagination'] = $this->paginateByDiv($pageCount, 'page', 'paginate', 'item', "kt.pages.browse.viewPage('[page]');", "kt.pages.browse.prevPage();", "kt.pages.browse.nextPage();");
	}

	/**
	 * Get the folder listing
	 *
	 * @param string $folderId
	 * @param string $sortField
	 * @param string $asc
	 * @return mixed $ret
	 */
	public function getFolderContent($folderId, $sortField = 'title', $asc = true) {
		$user_id = $_SESSION['userID'];
		$oUser = User::get($user_id);
		$disabled = $oUser->getDisabled();

		$kt = new KTAPI(3);
		$session=$kt->start_system_session($oUser->getUsername());

		//Get folder content, depth = 1, types= Directory, File, Shortcut, webserviceversion override
		$folder = &$kt->get_folder_contents($folderId, 1, 'DFS');
		$items = $folder['results']['items'];
		$ret = array('folders' => array(), 'documents' => array());

		foreach ($items as $item) {
			foreach ($item as $key => $value) {
				if ($value == 'n/a') { $item[$key] = null; }
			}
			$item['user_id'] = $user_id;
			$item['user_disabled'] = $disabled;
			$item['container_folder_id'] = $folderId;

			switch($item['item_type']) {
				case 'F':
					$item['is_shortcut'] = false;
					$ret['folders'][] = $item;
					break;
				case 'D':
					$item['is_shortcut'] = false;
					$ret['documents'][] = $item;
					break;
				case 'S':
					$item['is_shortcut'] = true;
					if ($item['mime_type'] == 'folder') {
						$ret['folders'][] = $item;
					} else {
						$ret['documents'][] = $item;
					}
					break;
			}
		}

		if (isset($sortField)) {
			$ret['documents'] = ktvar::sortArrayMatrixByKeyValue($ret['documents'], $sortField, $asc);
			$ret['folders'] = ktvar::sortArrayMatrixByKeyValue($ret['folders'], $sortField, $asc);
		}

//		ktvar::quickDebug($ret);
		return $ret;
	}

	public function noFilesOrFoldersMessage($folderId = null, $editable = true)
	{
		$folderMessage = '<h2>There\'s nothing in this folder yet!</h2>';
		if(SharedUserUtil::isSharedUser())
		{
			$folderMessage = '<h2>There\'s no shared content in this folder yet!</h2>';
			$perm = SharedContent::getPermissions($_SESSION['userID'], $folderId, null, 'folder');
			if($perm == 1)
			{
				 $editable = true;
			}
			else
			{
				 $editable = false;
			}
		}

		if (!$editable) {
			return "<span class='notification'>
						$folderMessage
			</span>";
		} else {
			$hint = '(Here are three easy ways you can change that...)';
			$upload = '					<td><div class="roundnum">1</div></td>
					<td class="info">
						<h2>Upload files and folders</h2>
						Upload one or more files including .zip files and other archives

						<br />
						<br />
						<div>
							<a href="javascript:kt.app.upload.showUploadWindow();"><span class="uploadButton">Upload</span></a>
						</div>

					</td>';
			$dragndrop = '					<td><div class="roundnum">2</div></td>
					<td class="info">
						<h2>Drag and Drop files here</h2>
						<img src="/resources/graphics/newui/dragdrop.png" />
					</td>';
			$createonline = '					<td><div class="roundnum">3</div></td>
					<td class="info">
						<h2>Create content online</h2>
						Create and share files right within KnowledgeTree
						<br />
						<br />
						<div>
							<a href="action.php?kt_path_info=zoho.new.document&fFolderId='.$folderId.'"><span class="createdocButton">Online Doc</span></a>
						</div>
					</td>';

			return '<span class="notification">
			' . $folderMessage . '
			' . $hint . '
			<table>
				<tr>
					' . $upload . '
					' . $dragndrop . '
					' . $createonline . '
				</tr>
			</table>
			</span>';
		}
	}

	public function paginateByDiv($pageCount,$pageClass,$paginationClass="paginate",$itemClass="item",$pageScript="alert([page])",$prevScript="alert('previous');",$nextScript="alert('next');") {
		$idClass=$pageClass.'_[page]';
		$pages=array();
		$pages[]='<ul class="'.$paginationClass.'">';
		$pages[]='<li class="'.$itemClass.'" onclick="'.$prevScript.'">Previous</li>';
		for($i=1;$i<=$pageCount; $i++) {
			$pages[]=ktVar::parseString('<li class="'.$itemClass.' '.$idClass.'" onclick="'.$pageScript.'">'.$i.'</li>',array('page'=>$i));
		}
		$pages[]='<li class="'.$itemClass.'" onclick="'.$nextScript.'">Next</li>';
		$pages[]='</ul>';
		$pages=join($pages);
		return $pages;
	}


	public function renderBulkActionMenu($items, $folder)
	{
	    $canDelete = Permission::userHasDeleteFolderPermission($folder);
	    $canWrite = Permission::userHasFolderWritePermission($folder);

		$tpl='<table class="browseView bulkActionMenu" cellspacing="0" cellpadding="0"><tr><td>
		<input type="checkbox" class="select_all" />
		<input type="hidden" value="" name="sListCode"><input type="hidden" value="bulkaction" name="action">
		<input type="hidden" value="browse" name="fReturnAction"><input type="hidden" value="1" name="fReturnData">';

		$parts=array();

		foreach ($items as $item) {
			$parts[$item->getName()]='<input type="submit" name="submit['.$item->getName().']" value="'.$item->getDisplayName().'" />';
		}

		// Unset the bulk actions dependent on the users permissions
		if (!$canDelete) {
		    unset($parts['ktcore.actions.bulk.delete']);
		}

		if (!$canWrite) {
		    unset($parts['ktcore.actions.bulk.move']);
		    unset($parts['ktcore.actions.bulk.archive']);
		}

		//parts order: Copy, move, archive, delete, download all

		$tpl.=join($parts);

		$tpl.='</td><td class="status" style="width: 200px; text-align: right;"></td></tr></table>';
		return $tpl;
	}

	public function renderDocumentItem($item=null,$empty=false,$shortcut=false) {
		$fileNameCutoff=100;

		// When $item is null, $oDocument resolves to a PEAR Error, we should add a check for $item and initialise the document data at the top
		// instead of using $oDocument in the code.
		$oDocument = Document::get($item[id]);
		$item['mimetypeid']=(method_exists($oDocument,'getMimeTypeId'))?$oDocument->getMimeTypeId():'0';

		$iconFile='resources/mimetypes/newui/'.KTMime::getIconPath($item['mimetypeid']).'.png';
		//echo($iconFile);exit;
		$item['icon_exists']=file_exists(KT_DIR.'/'.$iconFile);
		$item['icon_file']=$iconFile;

		if ($item['icon_exists']) {
			$item['mimeicon']=str_replace('\\','/',$GLOBALS['default']->rootUrl.'/'.$iconFile);
			$item['mimeicon']="background-image: url(".$item['mimeicon'].")";
		}else{
			$item['mimeicon']='';
		}

		// Get the users permissions on the document
		$permissions = $item['permissions'];
		$hasWrite = (strpos($permissions, 'W') === false) ? false : true;
		$hasDelete = (strpos($permissions, 'D') === false) ? false : true;

		$item['filename']=(strlen($item['filename'])>$fileNameCutoff)?substr($item['filename'],0,$fileNameCutoff-3)."...":$item['filename'];

		$ns = " not_supported";
		$item['has_workflow'] = '';
		$item['is_immutable'] = $item['is_immutable'] == 'true' ? true : false;
		$item['is_immutable'] = $item['is_immutable'] ? '' : $ns;
		$item['is_checkedout'] = $item['checked_out_date'] ? '' : $ns;
		$item['is_shortcut'] = $item['is_shortcut'] ? '' : $ns;

		$item['actions.checkin'] = $item['actions.cancel_checkout'] = $item['actions.checkout'] = $ns;
		$item['actions.move'] = $item['actions.copy'] = $item['actions.delete'] = $ns;

		$isCheckedOut = ($item['checked_out_date']) ? true : false;
		if(get_class($oDocument) == 'Document'){
    		if($hasWrite) {
        		$item['actions.checkout'] = $item['checked_out_date'] ? $ns : '';
                $hasCheckedOut = ($_SESSION['userID'] == $item['checked_out_by_id']);
        		$item['actions.checkin'] = ($item['checked_out_date'] && $hasCheckedOut) ? '' : $ns;
        		$item['actions.cancel_checkout'] = ($item['checked_out_date'] && $hasCheckedOut) ? '' : $ns;
    			$item['actions.move'] = KTDocumentUtil::canBeMoved($oDocument) ? '' : $ns;
    		}

    		$item['actions.delete'] = (KTDocumentUtil::canBeDeleted($oDocument) && $hasDelete) ? '' : $ns;
    		$item['actions.copy'] = KTDocumentUtil::canBeCopied($oDocument) ? '' : $ns;
		}

		//Modifications to perform when the document has been checked out
		if ($item['checked_out_date']) {
			list($item['checked_out_date_d'],$item['checked_out_date_t'])=split(" ",$item['checked_out_date']);
		}

		if ($item['is_immutable']== '') {
			$item['actions.checkin']=$ns;
			$item['actions.checkout']=$ns;
			$item['actions.cancel_checkout']=$ns;
			$item['actions.alerts']=$ns;
			$item['actions.email']=$ns;
			$item['actions.change_owner']=$ns;
			$item['actions.finalize_document']=$ns;
		}

		$item['actions.finalize_document'] = ($isCheckedOut) ? $ns : $item['actions.finalize_document'];

		$item['separatorE']='';
		if(!$hasWrite){
		    $item['actions.change_owner'] = $ns;
		    $item['actions.share_document'] = $ns;
		    if($isCheckedOut || $item['actions.finalize_document'])
		    {
		    	$oUser = User::get($_SESSION['userID']);
		    	$sPermissions = 'ktcore.permissions.write';
		    	if(KTPermissionUtil::userHasPermissionOnItem($oUser, $sPermissions, $oDocument))
		    	{
		    		$item['actions.share_document'] = '';
		    	}
		    }
			$item['actions.finalize_document'] = $ns;
			$item['separatorE']=$ns;
		}

		$item['separatorA'] = $item['actions.copy'] == '' ? '' : $ns;
		$item['separatorB'] = $item['actions.download'] == '' || $item['actions.instantview'] == '' ? '' : $ns;
		$item['separatorC'] = $item['actions.checkout'] == '' || $item['actions.checkin'] == '' || $item['actions.cancel_checkout']== '' ? '' : $ns;
		$item['separatorD'] = ($item['actions.alert'] == '' || $item ['actions.email'] == '') && $hasWrite ? '' : $ns;

		if ($item['is_immutable']== '') {
			$item['separatorB']=$item['separatorC']=$item['separatorD']=$ns;
		}

		// Check if the thumbnail exists
        $dev_no_thumbs=(isset($_GET['noThumbs']) || $_SESSION['browse_no_thumbs'])?true:false;
        $_SESSION['browse_no_thumbs']=$dev_no_thumbs;
        $item['thumbnail'] = '';
        $item['thumbnailclass'] = 'nopreview';

        // When item is null, thumbnails won't exist so skip the check
        if (!$dev_no_thumbs && !PEAR::isError($oDocument)) {
            // Check if the document has a thumbnail rendition -> has_rendition = 2, 3, 6, 7
            // 0 = nothing, 1 = pdf, 2 = thumbnail, 4 = flash
            // 1+2 = 3: pdf & thumbnail; 1+4 = 5: pdf & flash; 2+4 = 6: thumbnail & flash; 1+2+4 = 7: all

            // If the flag hasn't been set, check against storage and update the flag - for documents where the flag hasn't been set
            $check = false;
            if (is_null($item['has_rendition'])) {

                $oStorage=KTStorageManagerUtil::getSingleton();

                $varDir = $GLOBALS['default']->varDirectory;
                $thumbnailCheck = $varDir . '/thumbnails/'.$item['id'].'.jpg';

                if ($oStorage->file_exists($thumbnailCheck)) {
                    $oDocument->setHasRendition(2);
                    $check = true;
                }else {
                    $oDocument->setHasRendition(0);
                }
                $oDocument->update();
            }

            if ($check || in_array($item['has_rendition'], array(2, 3, 6, 7))) {
                $item['thumbnail'] = '<img src="plugins/thumbnails/thumbnail_view.php?documentId='.$item['id'].'" onClick="document.location.replace(\'view.php?fDocumentId='.$item['id'].'#preview\');">';
                $item['thumbnailclass'] = 'preview';
            }
        }

		// Default - hide edit online
		$item['allowdoczohoedit'] = '';

		if ($this->zohoEnabled && $hasWrite) {
			if (Zoho::resolve_type($oDocument))
			{
				if ($item['actions.checkout'] != $ns) {
					$item['allowdoczohoedit'] = '<li class="action_zoho_document"><a href="javascript:;" onclick="zohoEdit(\''.$item['id'].'\')">Edit Document Online</a></li>';
				}
			}
		}

		$item['isfinalize_document'] = ($item['actions.finalize_document']) ? 0 : 1;
		// Sanitize document title
		$item['title'] = sanitizeForHTML($item['title']);
		// Check if the document is a shortcut
		if ($item['linked_document_id']) {
			$item['actions.share_document'] = $ns;
			$item['document_link']=KTUtil::buildUrl("view.php", array('fDocumentId'=>$item['linked_document_id'], 'fShortcutFolder'=>$item['container_folder_id']));
		}else{
			$item['document_link']=KTUtil::buildUrl("view.php", array('fDocumentId'=>$item['id']));
		}
		$share_separator = '';
		if($item['actions.share_document'] != $ns)
		{
			$share_separator = '<li class="separator[separatorE]"></li>';
		}
		
		$tpl='
			<span class="doc browseView 2">
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
									<span>This document is <strong>checked-out</strong> by <strong>[checked_out_by]</strong> and cannot be edited until it is Checked-in.</span>
								</span>
								<span class="shortcut[is_shortcut]">
									<span>This is a shortcut to the file.</span>
								</span>
								<span class="doc [thumbnailclass]">[thumbnail]</span>
							</div>
						</td>
						<td class="doc summary_cell fdebug">

							<div class="title"><a class="clearLink" href="[document_link]" style="">[title]</a></div>

							<div class="detail"><span class="item">
								Owner: <span class="user">[owned_by]</span></span><span class="item">Created: <span class="date">[created_date]</span> by <span class="user">[created_by]</span></span><span class="item">Updated: <span class="date">[modified_date]</span> by <span class="user">[modified_by]</span></span>
							</div>
						</td>
						<td>
							<ul class="doc actionMenu">
								<!-- li class="actionIcon comments"></li -->
								<li class="actionIcon actions">
									<ul>
										<li class="action_share_document [actions.share_document]"><a href="#" onclick="javascript:kt.app.sharewithusers.shareContentWindow(\'[id]\',\'[item_type]\',\'[user_id]\', \'[isfinalize_document]\');">Share This Document</a></li>
										'. $share_separator .'
										<li class="action_download [actions.download]"><a href="action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=[id]">Download</a></li>
										<li class="action_instant_view [actions.instant_view]"><a href="[document_link]#preview">Instant View</a></li>
										[allowdoczohoedit]

										<li class="separator[separatorA]"></li>

										<li class="action_copy [actions.copy]"><a href="action.php?kt_path_info=ktcore.actions.document.copy&fDocumentId=[id]">Copy</a></li>
										<li class="action_move [actions.move]"><a href="action.php?kt_path_info=ktcore.actions.document.move&fDocumentId=[id]">Move</a></li>
										<li class="action_delete [actions.delete]"><a href="action.php?kt_path_info=ktcore.actions.document.delete&fDocumentId=[id]">Delete</a></li>

										<li class="separator[separatorB]"></li>

										<li class="action_checkout [actions.checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.checkout&fDocumentId=[id]">Check-out</a></li>
										<li class="action_cancel_checkout [actions.cancel_checkout]"><a href="action.php?kt_path_info=ktcore.actions.document.cancelcheckout&fDocumentId=[id]">Cancel Check-out</a></li>
										<li class="action_checkin [actions.checkin]"><a href="action.php?kt_path_info=ktcore.actions.document.checkin&fDocumentId=[id]">Check-in</a></li>

										<li class="separator[separatorC]"></li>

										<li class="action_alerts [actions.alerts]"><a href="action.php?kt_path_info=alerts.action.document.alert&fDocumentId=[id]">Alerts</a></li>
										<li class="action_email [actions.email]"><a href="action.php?kt_path_info=ktcore.actions.document.email&fDocumentId=[id]">Email</a></li>

										<li class="separator[separatorD]"></li>

										<li class="action_change_owner [actions.change_owner]"><a href="action.php?kt_path_info=ktcore.actions.document.ownershipchange&fDocumentId=[id]">Change Owner</a></li>
										<li class="action_finalize_document [actions.finalize_document]"><a href="action.php?kt_path_info=ktcore.actions.document.immutable&fDocumentId=[id]">Finalize Document</a></li>
									</ul>
								</li>
							</ul>
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

		if ($empty) { return '<span class="fragment document" style="display:none;">'.$tpl.'</span>'; }

		return ktVar::parseString($tpl,$item);
	}

	public function renderFolderItem($item = null, $empty = false, $shortcut = false)
	{
		//TODO: Tohir, if you put the .selected thing on the table $(.folder.item), it should work fine
		$ns = " not_supported";
		$item['is_shortcut']=$item['is_shortcut']?'':$ns;

		// Get the users permissions on the folder
		$permissions = $item['permissions'];
		$hasWrite = (strpos($permissions, 'W') === false) ? false : true;
		$hasRename = (strpos($permissions, 'N') === false) ? false : true;
		$hasSecurity = (strpos($permissions, 'S') === false) ? false : true;

		$item['actions.share_folder'] = ($hasWrite) ? '' : $ns;
		$item['actions.permissions'] = ($hasSecurity) ? '' : $ns;
		$item['actions.rename'] = ($hasRename) ? '' : $ns;

		$item['separatorA'] = ($hasWrite || $hasSecurity || $hasRename) ? '' : $ns;
		// Sanitize folder title
		$item['title'] = sanitizeForHTML($item['title']);
		if ($item['linked_folder_id'] == '') {
			$item['link'] = KTUtil::buildUrl('browse.php', array('fFolderId'=>$item['id']));
		} else {
			$item['actions.share_folder'] = $ns;
			$item['link'] = KTUtil::buildUrl('browse.php', array('fFolderId'=>$item['linked_folder_id'], 'fShortcutFolder'=>$item['container_folder_id']));
		}
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

						<div class="title"><a class="clearLink" href="[link]">[title]</a></div>
						<div class="detail"><span class="item">Created by: <span class="creator">[created_by]</span></span></div>
					</td>
					<td>
						<ul class="folder actionMenu">
							<li class="actionIcon actions">
									<ul>
                                        <li class="action_share_folder [actions.share_folder]"><a href="#" onclick="javascript:kt.app.sharewithusers.shareContentWindow(\'[id]\',\'[item_type]\',\'[user_id]\');">Share This Folder</a></li>
                                        <li class="action_rename_folder [actions.rename]"><a href="action.php?kt_path_info=ktcore.actions.folder.rename&fFolderId=[id]">Rename Folder</a></li>
                                        <li class="action_folder_permissions [actions.permissions]"><a href="action.php?kt_path_info=ktcore.actions.folder.permissions&fFolderId=[id]">Set Folder Permissions</a></li>
                                        <!-- <li class="[actions.subscribe]"><a href="#" onclick=\'alert("JavaScript to be modified")\'>Subscribe to Folder</a></li> -->
                                        <li class="separator[separatorA]"></li>
                                        <li class="action_view_transactions [actions.view_transactions]"><a href="action.php?kt_path_info=ktcore.actions.folder.transactions&fFolderId=[id]">View Folder Activity</a></li>
									</ul>
							</li>
						</ul>
					</td>
				</tr>
			</table>
			</span>';

		if ($empty) { return '<span class="fragment folder" style="display:none;">'.$tpl.'</span>'; }

		return ktVar::parseString($tpl,$item);
	}

}
?>
