<?php

require_once(KT_LIB_DIR . '/render_helpers/BrowseView.inc.php');
require_once('sharedContent.inc');

/**
 * Shared user browse view class
 *
 */
class SharedUserBrowseView extends BrowseView {

	private $oUser = null;

	/**
	 * Get the folder listing
	 *
	 * @param string $folderId
	 * @param $totalItems
	 * @param string $sortField
	 * @param string $asc
	 * @return mixed $ret
	 */
	// TODO add support for total items here...
	public function getFolderContent($folderId, &$totalItems = 0, $sortField = 'title', $asc = true)
	{
		$user_id = $_SESSION['userID'];
		$this->oUser = is_null($this->oUser) ? User::get($user_id) : $this->oUser;
		$disabled = $this->oUser->getDisabled();

		$oSharedContent = new SharedContent();
		$aSharedContent = $oSharedContent->getUsersSharedContents($user_id, $folderId);
		$ret = array(
                        'folders' => array(),
                        'documents'=>array()
					);

		foreach ($aSharedContent['documents'] as $item) {
			$item['user_id'] = $user_id;
			$item['user_disabled'] = $disabled;
			$item['item_type'] = 'D';
			$item['version'] = $item['major_version'] . '.' .$item['minor_version'];
			$ret['documents'][] = $this->browseViewItems($item, $folderId);
		}

		foreach ($aSharedContent['folders'] as $item) {
			$item['item_type'] = 'F';
			$item['mime_type'] = 'folder';
			$item['mime_icon_path'] = 'folder';
			$item['mime_display'] = 'Folder';
			$ret['folders'][] = $this->browseViewItems($item, $folderId);
		}

		if (isset($sortField)) {
			$ret['documents'] = ktvar::sortArrayMatrixByKeyValue($ret['documents'], $sortField, $asc);
			$ret['folders'] = ktvar::sortArrayMatrixByKeyValue($ret['folders'], $sortField, $asc);
		}

		return $ret;
	}

	public function renderBulkActionMenu($items, $folder)
	{
		return '';
	}

	public function renderDocumentItem($item = null, $empty = false, $shortcut = false)
	{
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
			$item['mimeicon'] = 'background-image: url(' . $item['mimeicon'] . ')';
		} else {
			$item['mimeicon'] = '';
		}

		// Create link, which will always be of a document and not a shortcut
		$item['document_link'] = KTUtil::buildUrl('view.php', array('fDocumentId'=> $item['id']));
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
        if (!$dev_no_thumbs) {
            // Check if the document has a thumbnail rendition -> has_rendition = 2, 3, 6, 7
            // 0 = nothing, 1 = pdf, 2 = thumbnail, 4 = flash
            // 1+2 = 3: pdf & thumbnail; 1+4 = 5: pdf & flash; 2+4 = 6: thumbnail & flash; 1+2+4 = 7: all
            // If the flag hasn't been set, check against storage and update the flag - for documents where the flag hasn't been set
            $check = false;
            if (is_null($item['has_rendition'])) {
                $oStorage = KTStorageManagerUtil::getSingleton();
				$oDocument = Document::get($item['id']);
				if (!PEAR::isError($oDocument)) {
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

            if ($check || in_array($item['has_rendition'], array(2, 3, 6, 7))) {
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
		if (!is_null($item['checked_out_by_id'])) {
			$coUser = User::get($item['checked_out_by_id']);
			$item['checked_out_by'] = $coUser->getName();
		}

		// Sanitize document title
		$item['title'] = sanitizeForHTML($item['title']);
		$item['filesize'] = KTUtil::filesizeToString($item['filesize']);

		$tpl = $this->getDocumentTemplate(2);

		if ($empty) { return '<span class="fragment document" style="display:none;">' . $tpl . '</span>'; }

		return ktVar::parseString($tpl, $item);
	}

	public function renderFolderItem($item = null, $empty = false, $shortcut = false)
	{
		$item['link'] = KTUtil::buildUrl('browse.php', array('fFolderId'=> $item['id']));
		// Sanitize folder title
		$item['title'] = sanitizeForHTML($item['title']);
		$tpl = $this->getFolderTemplate(false);

		if ($empty) { return '<span class="fragment folder" style="display:none;">' . $tpl . '</span>'; }

		return ktVar::parseString($tpl,$item);
	}

	protected function getDocumentActionMenu($share_separator = null)
	{
	    return '<ul class="doc actionMenu">
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
							</ul>';
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
		foreach ($item as $key=> $value) {
			if ($value == 'n/a') {
				$item[$key] = null;
			}
		}

		$item['container_folder_id'] = $folderId;

		return $item;
	}

}

?>
