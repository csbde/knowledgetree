<?php

require_once(KT_LIB_DIR . '/util/ktVar.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/users/shareduserutil.inc.php');

/**
 * Browse view base class
 */
class BrowseView {

    private $pages = array();
    private $range = array();
    private $initialLoad = 3;
    // NOTE if you change the limit here, be sure to also change it in the client side js;
    //      the value may be overridden by the javascript, but this value is always a fallback.
    private $limit = 5;
    private $folderId;

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

    /**
	 * Sets the start page based on page requested and pages already loaded.
	 * Aims to get one or more pages on either side of requested page, if not already loaded.
	 *
	 * @param int $folderId
	 * @param int $requested
	 * @return array
	 */
    private function getLazyOptions($folderId, $requested)
    {
        if (!$_SESSION) { session_start(); }
        $session = !empty($_SESSION['ktPageSet'][$folderId]) ? $_SESSION['ktPageSet'][$folderId] : array();

        $mid = null;
        $half = floor($this->limit / 2);
        $remainder = $this->limit % 2;
        if ($remainder != 0) {
            $mid = $half + 1;
            $first = $requested - $half;
        }
        else {
            $mid = $half;
            $first = $requested - $half - 1;
        }

        $index = ($first > 0) ? $first : 1;
        $limit = $index + $this->limit;
        $pages = array();
        for ($i = $index; $i < $limit; ++$i) {
            if (!isset($session[$i])) { $pages[] = $i; }
        }

        // TODO if we end up with only one page and it is the first or last in the set, perhaps
        //      load an extra page in the appropriate direction?

        $options = array();
        $options['limit'] = count($pages);
        $options['offset'] = ($options['limit'] > 0) ? $pages[0] : 0;

        return $options;
    }

    /**
	 * Sets offset and limit for browsing
	 *
	 * @param int $pageCount
	 */
    public function setPagingOptions($pageCount = 1, $limit = null)
    {
        if (empty($limit)) {
            $limit = $this->limit;
        }

        $this->pages['count'] = $pageCount;
        $this->pages['perPage'] = 15;
        $this->range['offset'] = ($this->pages['count'] - 1) * $this->pages['perPage'];
        $this->range['limit'] = $this->pages['perPage'] * $limit;
    }

    /**
	 * Sets/Updates a session value to contain the list of pages already loaded
	 *
	 * @param array $options
	 * @param int $folderId
	 */
    private function updateSession($folderId)
    {
        if (!$_SESSION) { session_start(); }
        $session = (!empty($_SESSION['ktPageSet'][$folderId]) && ($this->range['offset'] > 0)) ? $_SESSION['ktPageSet'][$folderId] : array();

        $limit = $this->pages['count'] + ($this->range['limit'] / $this->pages['perPage']);
        for ($i = $this->pages['count']; $i < $limit; ++$i) {
            if (!isset($session[$i])) {
                $session[$i] = 1;
            }
        }

        $_SESSION['ktPageSet'][$folderId] = $session;
    }

    /**
	 * Loads additional pages on request, returned as a json encoded array.
	 *
	 * @param int $folderId
	 * @param int $pageCount
	 * @param array $options If these are submitted then the requested range will be force loaded
	 *                       even if there was a previous load.  This allows pages to recover from
	 *                       a partially failed request.
	 */
    public function lazyLoad($folderId, $requested = 1, $options = array())
    {
        $response = array();

        if (empty($folderId)) {
            return $response;
        }

        // TODO can improve performance on calling already loaded pages if we set these options
        //      before doing the folder setup, etc., which currently happens before.
        if (empty($options)) {
            $options = $this->getLazyOptions($folderId, $requested);
        }

        // ignore a request for already loaded content
        $responseData = array();
        if ($options['limit'] > 0) {
            $this->setPagingOptions($options['offset'], $options['limit']);
            $folderContentItems = $this->getFolderContent($folderId);
            if (count($folderContentItems['documents']) + count($folderContentItems['folders']) > 0) {
                $responseData = $this->buildFolderView($folderId, $folderContentItems);
            }
        }

        $response['folderContents'] = json_encode($responseData);

        return $response;
    }

    /**
	 * Fetch and render the contents of a folder.
	 * This function is intended for the initial rendering.
	 *
	 * @param int $folderId
	 * @param array $aBulkActions
	 * @param object $folder
	 * @param boolean $editable
	 * @param int $pageCount
	 */
    public function renderBrowseFolder($folderId, $aBulkActions, $folder, $editable, $pageCount = 1)
    {
        $response = array();

        if (empty($folderId)) {
            return $response;
        }

        $this->folderId = $folderId;
        $this->setPagingOptions($pageCount, $this->initialLoad);

        $response['returndata'] = $folderId;
        // TODO consider this perhaps moving back out to the dispatcher?
        $response['bulkActionMenu'] = $this->renderBulkActionMenu($aBulkActions, $folder);

        $totalItems = 0;
        $folderContentItems = $this->getFolderContent($folderId, $totalItems);
        $folderView = $this->buildFolderView($folderId, $folderContentItems, $editable);
        $response['folderContents'] = join($folderView);

        // Adding Fragments for drag & drop client side processing
        $response['fragments'] = '';
        $response['fragments'] = '';
        $response['fragments'] .= $this->renderDocumentItem(null, true);
        $response['fragments'] .= $this->renderFolderItem(null, true);

        // Apply Clientside Pagination element
        $fullPageCount = ceil($totalItems / $this->pages['perPage']);
        $response['pagination'] = $this->paginateByDiv($fullPageCount, 'page', 'paginate', 'item', "kt.pages.browse.viewPage([page], [folder]);", "kt.pages.browse.prevPage({$this->folderId});", "kt.pages.browse.nextPage({$this->folderId});");

        // Add Additional browse view Javascript
        $response['javascript'] = $this->getJavaScript();

        return $response;
    }

    private function getFolderItems($folderContentItems)
    {
        $folderItems = array();

        foreach ($folderContentItems['folders'] as $item) {
            $folderItems[] = $this->renderFolderItem($item);
        }

        foreach ($folderContentItems['documents'] as $item) {
            $folderItems[] = $this->renderDocumentItem($item);
        }

        return $folderItems;
    }

    private function buildFolderView($folderId, $folderContentItems, $editable = null)
    {
        $folderItems = $this->getFolderItems($folderContentItems);
        $itemCount = count($folderItems);
        $curItem = 0;

        $folderView = array();
        $folderView[$this->pages['count']] = '<div class="page page_' . $this->pages['count'] . ' ">';

        foreach ($folderItems as $item) {
            ++$curItem;
            if ($curItem > $this->pages['perPage']) {
                $folderView[$this->pages['count']] .= '</div>';
                ++$this->pages['count'];
                $curItem = 1;
                $folderView[$this->pages['count']] = '<div class="page page_' . $this->pages['count'] . ' ">';
            }

            $folderView[$this->pages['count']] .= $item;
        }

        // Deal with scenario where there are no items in a folder
        if ($itemCount <= 0) {
            $folderView[$this->pages['count']] .= $this->noFilesOrFoldersMessage($folderId, $editable);
        }

        $folderView[$this->pages['count']] .= '</div>';

        return $folderView;
    }

    /**
	 * Get the folder listing
	 *
	 * @param string $folderId
	 * @param int $totalItems
	 * @param array $options Offset/Limit
	 * @param string $sortField
	 * @param string $asc
	 * @return mixed $ret
	 */
    public function getFolderContent($folderId, &$totalItems = 0, $options = array(), $sortField = 'title', $asc = true)
    {
        if (empty($options)) {
            $options = $this->range;
        }

        $user_id = $_SESSION['userID'];
        if (is_null($this->oUser)) {
            $this->oUser =  User::get($user_id);
        }
        $disabled = $this->oUser->getDisabled();

        $kt = new KTAPI(3);
        $session = $kt->start_system_session($this->oUser->getUsername());

        //Get folder content, depth = 1, types= Directory, File, Shortcut, webserviceversion override
        $totalItems = 0;
        $folder =& $kt->get_folder_contents($folderId, 1, 'DFS', $totalItems, $options);
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

        // NOTE the sort field cannot be used here if we are getting paged results, it must be used in the main query...
        /*if (isset($sortField)) {
        $ret['documents'] = ktvar::sortArrayMatrixByKeyValue($ret['documents'], $sortField, $asc);
        $ret['folders'] = ktvar::sortArrayMatrixByKeyValue($ret['folders'], $sortField, $asc);
        }*/

        $this->updateSession($folderId);

        return $ret;
    }

    /**
	 * Displays a message when there is no folder content
	 *
	 * @param int $folderId
	 * @param boolean $editable
	 * @return string
	 */
    public function noFilesOrFoldersMessage($folderId = null, $editable = true)
    {
        if (SharedUserUtil::isSharedUser()) {
            $folderMessage = '<h2>There\'s no shared content in this folder yet!</h2>';
            $perm = SharedContent::getPermissions($_SESSION['userID'], $folderId, null, 'folder');
            if ($perm == 1) {
                $editable = true;
            }
            else {
                $editable = false;
            }
        }
        else {
            $folderMessage = '<h2>There\'s nothing in this folder yet!</h2>';
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
							<a href="action.php?kt_path_info=zoho.new.document&fFolderId=' . $folderId . '"><span class="createdocButton">Online Doc</span></a>
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

    /**
	 * Create the pagination element.
	 *
	 * @param int $pageCount
	 * @param string $pageClass
	 * @param string $paginationClass
	 * @param string $itemClass
	 * @param string $pageScript
	 * @param string $prevScript
	 * @param string $nextScript
	 * @return string
	 */
    public function paginateByDiv($pageCount, $pageClass, $paginationClass = 'paginate', $itemClass = 'item', $pageScript = 'alert([page])', $prevScript = "alert('previous');", $nextScript = "alert('next');")
    {
        if ($pageCount <= 0) {
            return '';
        }

        $idClass = $pageClass . '_[page]';
        $pages = array();
        $pages[] = '<ul class="' . $paginationClass . '">';
        $pages[] = '<li class="' . $itemClass . '" onclick="' . $prevScript . '">Previous</li>';
        for($i = 1; $i <= $pageCount; ++$i) {
            $pages[] = ktVar::parseString('<li class="' . $itemClass . ' ' . $idClass . '" onclick="' . $pageScript . '">' . $i . '</li>', array('page'=> $i, 'folder' => $this->folderId));
        }
        $pages[] = '<li class="' . $itemClass . '" onclick="' . $nextScript . '">Next</li>';
        $pages[] = '</ul>';
        $pages = join($pages);

        return $pages;
    }

    public function renderBulkActionMenu($items, $folder)
    {
        $canDelete = Permission::userHasDeleteFolderPermission($folder);
        $canWrite = Permission::userHasFolderWritePermission($folder);

        $tpl = '<table class="browseView bulkActionMenu" cellspacing="0" cellpadding="0"><tr><td>
		<input type="checkbox" class="select_all" />
		<input type="hidden" value="" name="sListCode"><input type="hidden" value="bulkaction" name="action">
		<input type="hidden" value="browse" name="fReturnAction"><input type="hidden" value="1" name="fReturnData">';

        $parts = array();

        foreach ($items as $item) {
            $parts[$item->getName()] = '<input type="submit" name="submit[' . $item->getName() . ']" value="' . $item->getDisplayName() . '" />';
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
        $tpl .= join($parts);
        $tpl .= '</td><td class="status" style="width: 200px; text-align: right;"></td></tr></table>';

        return $tpl;
    }

    /**
	 * Checks the systems workflow permissions to see if actions have been overriden
	 *
	 * @param array $item
	 * @return array $item
	 */
    private function checkWorkflowPermissions($item = null, $oDocument)
    {
        $ns = ' not_supported';
        // Check workflow action restrictions
        $actions = array_merge(KTDocumentActionUtil::getDocumentActionsForDocument($oDocument, $this->oUser), KTDocumentActionUtil::getDocumentActionsForDocument($oDocument, $this->oUser, 'documentinfo'));

        foreach ($actions as $oAction) {
            $actionname = $oAction->getName();
            $aname = explode('.', $actionname);
            $name = $aname[count($aname) - 1];
            $allowaction[$name] = $oAction->_show();
        }

        if (!$allowaction['immutable']) { $item['actions.finalize_document'] = $ns; }
        if (!$allowaction['ownershipchange']) { $item['actions.change_owner'] = $ns; }

        if (!$allowaction['checkout']) {
            $item['allowdoczohoedit'] = '';
            $item['actions.checkout'] = $ns;
        }

        if (!$allowaction['cancelcheckout']) { $item['actions.cancel_checkout'] = $ns; }
        if (!$allowaction['checkin']) { $item['actions.checkin'] = $ns; }
        if (!$allowaction['alert']) { $item['actions.alerts'] = $ns; }
        if (!$allowaction['email']) { $item['actions.email'] = $ns; }
        if (!$allowaction['view']) { $item['actions.download'] = $ns; }
        if (!$allowaction['sharecontent']) { $item['actions.share_document'] = $ns; }

        return $item;
    }

    /**
	 * Renders html block for a document in the new browse
	 *
	 * @param array $item
	 * @param boolean $empty
	 * @param boolean $shortcut
	 * @return string
	 */
    public function renderDocumentItem($item = null, $empty = false, $shortcut = false)
    {
        // When $item is null, $oDocument resolves to a PEAR Error, we should add a check for $item and initialise the document data at the top
        // instead of using $oDocument in the code.
        $oDocument = Document::get($item['id']);
        $fileNameCutoff = 100;
        $share_separator = '';
        $item['separatorE'] = '';
        $ns = ' not_supported';

        $item['mimetypeid'] = (method_exists($oDocument,'getMimeTypeId')) ? $oDocument->getMimeTypeId() : '0';
        $iconFile = 'resources/mimetypes/newui/' . KTMime::getIconPath($item['mimetypeid']) . '.png';
        $item['icon_exists'] = file_exists(KT_DIR . '/' . $iconFile);
        $item['icon_file'] = $iconFile;

        if ($item['icon_exists']) {
            $item['mimeicon'] = str_replace('\\', '/', $GLOBALS['default']->rootUrl . '/' . $iconFile);
            $item['mimeicon'] = 'background-image: url(' . $item['mimeicon'] . ')';
        } else {
            $item['mimeicon'] = '';
        }

        // Get the users permissions on the document
        $permissions = $item['permissions'];
        $hasWrite = (strpos($permissions, 'W') === false) ? false : true;
        $hasDelete = (strpos($permissions, 'D') === false) ? false : true;

        $item['filename'] = (strlen($item['filename']) > $fileNameCutoff) ? (substr($item['filename'], 0, $fileNameCutoff - 3) . "...") : $item['filename'];

        $item['has_workflow'] = '';
        $item['is_immutable'] = ($item['is_immutable'] == 'true') ? true : false;
        $item['is_immutable'] = $item['is_immutable'] ? '' : $ns;
        $item['is_checkedout'] = $item['checked_out_date'] ? '' : $ns;
        $item['is_shortcut'] = $item['is_shortcut'] ? '' : $ns;

        $item['actions.checkin'] = $item['actions.cancel_checkout'] = $item['actions.checkout'] = $ns;
        $item['actions.move'] = $item['actions.copy'] = $item['actions.delete'] = $ns;

        $isCheckedOut = ($item['checked_out_date']) ? true : false;
        if (get_class($oDocument) == 'Document') {
            if ($hasWrite) {
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

        $item['actions.finalize_document'] = ($isCheckedOut) ? $ns : $item['actions.finalize_document'];

        if (!$hasWrite) {
            $item['actions.change_owner'] = $ns;
            $item['actions.share_document'] = $ns;
            if ($isCheckedOut || $item['actions.finalize_document']) {
                $this->oUser = is_null($this->oUser) ? User::get($user_id) : $this->oUser;
                $sPermissions = 'ktcore.permissions.write';
                if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, $sPermissions, $oDocument)) {
                    $item['actions.share_document'] = '';
                }
            }
            $item['actions.finalize_document'] = $ns;
            $item['separatorE']=$ns;
        }

        // Check if the thumbnail exists
        $dev_no_thumbs = (isset($_GET['noThumbs']) || $_SESSION['browse_no_thumbs']) ? true : false;
        $_SESSION['browse_no_thumbs'] = $dev_no_thumbs;
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
                $varDir = $GLOBALS['default']->varDirectory;
                $thumbnailCheck = $varDir . '/thumbnails/'.$item['id'].'.jpg';

                $oStorage = KTStorageManagerUtil::getSingleton();
                if ($oStorage->file_exists($thumbnailCheck)) {
                    $oDocument->setHasRendition(2);
                    $check = true;
                } else {
                    $oDocument->setHasRendition(0);
                }

                $oDocument->update();
            }

            if ($check || in_array($item['has_rendition'], array(2, 3, 6, 7))) {
                $item['thumbnail'] = '<span class="popover"><span class="popoverTip"></span><img src="plugins/thumbnails/thumbnail_view.php?documentId=' . $item['id'] . '" onClick="document.location.replace(\'view.php?fDocumentId=' . $item['id'] . '#preview\');"></span>';
                $item['thumbnailclass'] = 'preview';
            }
        }

        // Default - hide edit online
        $item['allowdoczohoedit'] = '';

        if ($this->zohoEnabled && $hasWrite) {
            if (Zoho::resolve_type($oDocument)) {
                if ($item['actions.checkout'] != $ns) {
                    $item['allowdoczohoedit'] = '<li class="action_zoho_document"><a href="javascript:;" onclick="zohoEdit(\'' . $item['id'] . '\')">Edit Document Online</a></li>';
                }
            }
        }

        $item['isfinalize_document'] = ($item['actions.finalize_document']) ? 0 : 1;
        // Sanitize document title
        $item['title'] = sanitizeForHTML($item['title']);
        $item['filesize'] = KTUtil::filesizeToString($item['filesize']);

        // Check if the document is a shortcut
        if (!is_null($item['linked_document_id'])) {
            $item['actions.share_document'] = $ns;
            $item['document_link'] = KTUtil::buildUrl('view.php', array('fDocumentId' => $item['linked_document_id'], 'fShortcutFolder' => $item['container_folder_id']));
        } else {
            $item['document_link'] = KTUtil::buildUrl('view.php', array('fDocumentId' => $item['id']));
        }
        // Check if document is in workflow and if action has not been restricted.
        // Another layer of permissions
        //$item = $this->checkWorkflowPermissions($item, $oDocument);

        $item['separatorA'] = $item['actions.copy'] == '' ? '' : $ns;
        $item['separatorB'] = $item['actions.download'] == '' || $item['actions.instantview'] == '' ? '' : $ns;
        $item['separatorC'] = $item['actions.checkout'] == '' || $item['actions.checkin'] == '' || $item['actions.cancel_checkout']== '' ? '' : $ns;
        $item['separatorD'] = $ns;//($item['actions.alert'] == '' || $item ['actions.email'] == '') && $hasWrite ? '' : $ns;
        if ($item['is_immutable'] == '') { $item['separatorB'] = $item['separatorC'] = $item['separatorD'] = $ns; }
        // Add line separator after share link
        if ($item['actions.share_document'] != $ns) {
            $share_separator = '<li class="separator[separatorE]"></li>';
        }

        $tpl = $this->getDocumentTemplate(1, '<td width="1" class="checkbox">
							<input name="selection_d[]" type="checkbox" value="[id]" />
						</td>', $share_separator, '<span class="shortcut[is_shortcut]">
									<span>This is a shortcut to the file.</span>
								</span>');

        if ($empty) { return '<span class="fragment document" style="display:none;">' . $tpl . '</span>'; }

        return ktVar::parseString($tpl, $item);
    }

    public function renderFolderItem($item = null, $empty = false, $shortcut = false)
    {
        //TODO: Tohir, if you put the .selected thing on the table $(.folder.item), it should work fine
        $ns = ' not_supported';
        $item['is_shortcut'] = $item['is_shortcut'] ? '' : $ns;

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

        // Check for shortcut
        if (!is_null($item['linked_folder_id'])) {
            $item['actions.share_folder'] = $ns;
            $item['link'] = KTUtil::buildUrl('browse.php', array('fFolderId'=> $item['linked_folder_id'], 'fShortcutFolder'=> $item['container_folder_id']));
        } else {
            $item['link'] = KTUtil::buildUrl('browse.php', array('fFolderId'=> $item['id']));
        }

        $tpl = $this->getFolderTemplate(true, '<td width="1" class="checkbox">
						<input name="selection_f[]" type="checkbox" value="[id]" />
					</td>', '<span class="shortcut[is_shortcut]"><span>This is a shortcut to the folder.</span></span>');

        if ($empty) { return '<span class="fragment folder" style="display:none;">' . $tpl . '</span>'; }

        return ktVar::parseString($tpl, $item);
    }

    protected function getDocumentTemplate($browseViewId, $checkbox = null, $share_separator = null, $shortcut = null)
    {
        return '
			<span class="doc browseView ' . $browseViewId . '">
				<table cellspacing="0" cellpadding="0" width="100%" border="0" class="doc item ddebug">
					<tr>
						' . $checkbox . '
						<td class="doc icon_cell" width="1">
							<div class="doc icon" style="[mimeicon]">
							    <span class="immutable_info[is_immutable]">
									<span>This document has been <strong>finalized</strong> and can no longer be modified.</span>
								</span>
								<span class="checked_out[is_checkedout]">
									<span>This document is <strong>checked-out</strong> by <strong>[checked_out_by]</strong> and cannot be edited until it is Checked-in.</span>
								</span>
								' . $shortcut . '
								<span class="doc [thumbnailclass]">[thumbnail]</span>
							</div>
                        </td>
						<td class="doc summary_cell fdebug">
                            <div class="title"><a class="clearLink" href="[document_link]" style="">[title]</a></div>
							<div class="detail">
								<span class="item"> Owner: <span class="user">[owned_by]</span></span><span class="item">Created: <span class="date">[created_date]</span> by <span class="user">[created_by]</span></span><span class="item">Updated: <span class="date">[modified_date]</span> by <span class="user">[modified_by]</span></span><span class="item">File size: <span class="user">[filesize]</span></span>
							</div>
						</td>
						<td>
							' . $this->getDocumentActionMenu($share_separator) . '
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
			</span>';
    }

    protected function getDocumentActionMenu($share_separator = null)
    {
        return '<ul class="doc actionMenu">
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
							</ul>';
    }

    protected function getFolderTemplate($fetchActionMenu, $checkbox = null, $shortcut = null)
    {
        return '
			<span class="doc browseView">
			<table cellspacing="0" cellpadding="0" width="100%" border="0" class="folder item">
				<tr>
					' . $checkbox . '
					<td class="folder icon_cell" width="1">
						<div class="folder icon">
							' . $shortcut . '
						</div>
					</td>
					<td class="folder summary_cell">
						<div class="title"><a class="clearLink" href="[link]">[title]</a></div>
						<div class="detail"><span class="item">Created by: <span class="creator">[created_by]</span></span></div>
					</td>
					' . ($fetchActionMenu ? $this->getFolderActionMenu() : '') . '
				</tr>
			</table>
			</span>';
    }

    protected function getFolderActionMenu()
    {
        return '<td>
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
					</td>';
    }

}

?>
