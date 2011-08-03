<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
require_once(KT_LIB_DIR . '/browse/BrowseColumns.inc.php');
require_once(KT_LIB_DIR . '/browse/PartialQuery.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentType.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentField.inc');

require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_DIR . '/plugins/ktcore/KTFolderActions.php');

require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');

require_once(KT_LIB_DIR . '/users/userhistory.inc.php');

require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');
require_once(KT_LIB_DIR . '/actions/entitylist.php');
require_once(KT_LIB_DIR . '/actions/bulkaction.php');

require_once(KT_LIB_DIR . '/util/ktRenderArray.php');
require_once(KT_LIB_DIR . '/render_helpers/browseView.helper.php');

require_once(KT_PLUGIN_DIR . '/ktstandard/KTSubscriptions.php');

require_once(KT_LIB_DIR . '/memcache/ktmemcache.php');

$sectionName = 'browse';

class BrowseDispatcher extends KTStandardDispatcher {

	public $sName = 'ktcore.actions.folder.view';

	public $oFolder = null;
	public $sSection = 'browse';
	public $browse_mode = null;
	public $query = null;
	public $resultURL;
	public $sHelpPage = 'ktcore/browse.html';
	public $permissions;

	public function __construct()
	{
		$this->permissions = array();
	    $this->aBreadcrumbs = array(array('action' => 'browse', 'name' => _kt('Browse')));

	    return parent::KTStandardDispatcher();
	}

	function check()
	{
            $this->browse_mode = KTUtil::arrayGet($_REQUEST, 'fBrowseMode', 'folder');
            $action = KTUtil::arrayGet($_REQUEST, $this->event_var, 'main');
            $this->permissions['editable'] = false;

            // catch the alternative actions.
            if ($action != 'main') {
                return true;
            }

            switch ($this->browse_mode) {
                case 'folder':
                    $this->browseFolder();
                    break;
                case 'lookup_value':
                    $this->browseByLookup();
                    // Surely there should be a break here!?!  If not, then a comment as to WHY NOT.
                case 'document_type':
                    $this->browseByDocument();
                    break;
                default:
                    // FIXME What should we do if we can't initiate the browse?  We "pretend" to have no perms.
                    return false;
            }

            return true;
	}

	public function do_main()
	{
	    global $default;
	    $this->bulkActionInProgress = $this->isBulkActionInProgress();
	    $bulkActions = '';
	    if($this->bulkActionInProgress == '') {
		    /* New ktapi based method */
	        $bulkActions = KTBulkActionUtil::getAllBulkActions();
	    }
        $sidebars = KTFolderActionUtil::getFolderActionsForFolder($this->oFolder, $this->oUser, 'mainfoldersidebar');
        $folderSidebars = isset($sidebars[0]) ? $sidebars[0] : array();
	    if (ACCOUNT_ROUTING_ENABLED && $default->tier == 'trial') {
	        $this->includeOlark();
	    }

	    $templating =& KTTemplating::getSingleton();
	    $template = $templating->loadTemplate('kt3/browse');

		global $main;
	    $templateData = array(
	           'context' => $this,
	           'page' => $main,
	           'browse_mode' => $this->browse_mode,
	           'isEditable' => $this->permissions['editable'],
	           'bulkactions' => $bulkActions,
	           'browseutil' => new KTBrowseUtil(),
	           'returnaction' => 'browse',
	           'folderSidebars' => $folderSidebars,
	    );

	    if($this->bulkActionInProgress != '') {
	    	$templateData['notifyBulkAction'] = $this->getBulkNotification();
	    }

	    // NOTE Don't quite know why this is in here. Someone reports that it is there for search browsing which seem to be disabled.
	    if ($this->oFolder) {
	        $this->showBtns();
    		$folderId = $this->oFolder->getId();

	        $renderHelper = BrowseViewUtil::getBrowseView($this->bulkActionInProgress);
	        $renderData = $renderHelper->renderBrowseFolder($folderId, $bulkActions, $this->oFolder, $this->permissions);
	        if($renderData['documentCount'] > 0)
	        	$this->loadDocumentJS();
	        $templateData = array_merge($templateData, $renderData);
	    }
	    else if ($this->oFolder === false) {
	    	$this->addErrorMessage(_kt('The selected folder cannot be found, it may have been deleted.'));
	    	$browse = '<a href = "'.KTUtil::buildUrl('browse.php') . '">' . _kt('browse') . '</a>';

	    	return $this->errorPage(_kt("Return to the main {$browse} page."));
	    }

	    return $template->render($templateData);
	}

	public function loadDocumentJS() {
		// TODO : Check if plugin is available.
		$alertUtilPath = KT_PLUGIN_DIR . '/commercial/alerts/alertUtil.inc.php';
		if(file_exists($alertUtilPath)) {
			require_once($alertUtilPath);
			$pluginPath = alertUtil::getPluginPath();
	        $this->oPage->requireJSResource($pluginPath . '/resources/blocks/alertsActions.js');
			$this->oPage->requireCSSResource($pluginPath . '/resources/alerts.css');
		}
	}

	public function showBtns()
	{
		$list = array();
		$submenu = array();
		$actions = KTFolderActionUtil::getFolderActionsForFolder($this->oFolder, $this->oUser);
		foreach ($actions as $oAction) {
			$oAction->setBulkAction($this->bulkActionInProgress);
            $info = $oAction->getInfo();
            // Skip if action is disabled
            if (is_null($info)) { continue; }
            // Skip if no name provided - action may be disabled for permissions reasons
            if (empty($info['name'])) { continue; }
            if(!empty($info['parent'])) {
                $submenu[$info['parent']][] = $info;
            } else {
            	$list[] = $info;
            }
		}

		// Create the More button => if additional split buttons are needed this can be extended.
		$more = array('name' => _kt('More'), 'url' => '#', 'class' => 'more');
		$more['submenu'] = $submenu['more'];
		//$split = array($more);

		$btns = array();
		$btns['buttons'] = $list;
		$btns['split'] = $more;

		$this->actionBtns = $btns;
	}

	/**
	 * Fetches folder content for a paging request.
	 * Content from this function will not be rendered and must be rendered by the calling code.
	 *
	 * @return string a JSON encoded output string
	 */
	public function do_paging()
	{
	    $folder_id = $this->getFolderId();
	    $page = KTUtil::arrayGet($_REQUEST, 'page');
	    $options = KTUtil::arrayGet($_REQUEST, 'options');
	    $options = json_decode($options, true);
	    $_REQUEST['fBrowseMode'] = 'paging';

	    $oFolder =& Folder::get($folder_id);
	    if (PEAR::isError($oFolder)) {
	        return false; // just fail.
	    }

	    $this->setEditable($oFolder);
	    $this->oFolder =& $oFolder;

	    // we now have a folder, and need to create the query.
	    $aOptions = array('ignorepermissions' => KTBrowseUtil::inAdminMode($this->oUser, $oFolder));
	    $this->oQuery = new BrowseQuery($oFolder->getId(), $this->oUser, $aOptions);

	    $renderHelper = BrowseViewUtil::getBrowseView();
	    $renderData = $renderHelper->lazyLoad($this->oFolder->getId(), $page, (!empty($options) ? $options : array()));

	    echo $renderData['folderContents'];
	    exit(0);
	}

	public function do_selectField()
	{
		$aFields = DocumentField::getList('has_lookup = 1');

		if (empty($aFields)) {
			$this->errorRedirectToMain(_kt('No lookup fields available.'));
			exit(0);
		}

		$_REQUEST['fBrowseMode'] = 'lookup_value';

		$templating =& KTTemplating::getSingleton();
		$template = $templating->loadTemplate('kt3/browse_lookup_selection');
		$templateData = array(
              'context' => $this,
              'fields' => $aFields,
		);

		return $template->render($templateData);
	}

	public function do_selectLookup()
	{
		$field = KTUtil::arrayGet($_REQUEST, 'fField', null);
		$oField = DocumentField::get($field);
		if (PEAR::isError($oField) || ($oField == false) || (!$oField->getHasLookup())) {
			$this->errorRedirectToMain('No Field selected.');
			exit(0);
		}

		$_REQUEST['fBrowseMode'] = 'lookup_value';

		$aValues = MetaData::getByDocumentField($oField);

		$templating =& KTTemplating::getSingleton();
		$template = $templating->loadTemplate('kt3/browse_lookup_value');
		$templateData = array(
              'context' => $this,
              'oField' => $oField,
              'values' => $aValues,
		);

		return $template->render($templateData);
	}

	public function do_selectType()
	{
		$aTypes = DocumentType::getList();
		// FIXME what is the error message?

		$_REQUEST['fBrowseMode'] = 'document_type';

		if (empty($aTypes)) {
			$this->errorRedirectToMain('No document types available.');
			exit(0);
		}

		$templating =& KTTemplating::getSingleton();
		$template = $templating->loadTemplate('kt3/browse_types');
		$templateData = array(
              'context' => $this,
              'document_types' => $aTypes,
		);

		return $template->render($templateData);
	}

	public function do_enableAdminMode()
	{
		$iDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		$iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId');
		if ($iDocumentId) {
			$oDocument = Document::get($iDocumentId);
			if (PEAR::isError($oDocument) || ($oDocument === false)) {
				return null;
			}
			$iFolderId = $oDocument->getFolderId();
		}

		if (!Permission::userIsSystemAdministrator() && !Permission::userIsUnitAdministrator()) {
			$this->errorRedirectToMain(_kt('You are not an administrator'));
		}

		// log this entry
		$oLogEntry =& KTUserHistory::createFromArray(array(
            'userid' => $this->oUser->getId(),
            'datetime' => date('Y-m-d H:i:s', time()),
            'actionnamespace' => 'ktcore.user_history.enable_admin_mode',
            'comments' => 'Admin Mode enabled',
            'sessionid' => $_SESSION['sessionID'],
		));
		$aOpts = array(
            'redirect_to' => 'main',
            'message' => _kt('Unable to log admin mode entry.  Not activating admin mode.'),
		);
		$this->oValidator->notError($oLogEntry, $aOpts);

		$_SESSION['adminmode'] = true;

		if ($_REQUEST['fDocumentId']) {
			$_SESSION['KTInfoMessage'][] = _kt('Administrator mode enabled');
			redirect(KTBrowseUtil::getUrlForDocument($iDocumentId));
			exit(0);
		}

		if ($_REQUEST['fFolderId']) {
			$this->successRedirectToMain(_kt('Administrator mode enabled'), sprintf('fFolderId=%d', $_REQUEST['fFolderId']));
		}

		$this->successRedirectToMain(_kt('Administrator mode enabled'));
	}

	public function do_disableAdminMode()
	{
		$iDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		$iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId');
		if ($iDocumentId) {
			$oDocument = Document::get($iDocumentId);
			if (PEAR::isError($oDocument) || ($oDocument === false)) {
				return null;
			}
			$iFolderId = $oDocument->getFolderId();
		}

		if (!Permission::userIsSystemAdministrator() && !Permission::userIsUnitAdministrator()) {
			$this->errorRedirectToMain(_kt('You are not an administrator'));
		}

		// log this entry
		$oLogEntry =& KTUserHistory::createFromArray(array(
            'userid' => $this->oUser->getId(),
            'datetime' => date('Y-m-d H:i:s', time()),
            'actionnamespace' => 'ktcore.user_history.disable_admin_mode',
            'comments' => 'Admin Mode disabled',
            'sessionid' => $_SESSION['sessionID'],
		));
		$aOpts = array(
            'redirect_to' => 'main',
            'message' => _kt('Unable to log admin mode exit.  Not de-activating admin mode.'),
		);
		$this->oValidator->notError($oLogEntry, $aOpts);

		$_SESSION['adminmode'] = false;

		if ($_REQUEST['fDocumentId']) {
			$_SESSION['KTInfoMessage'][] = _kt('Administrator mode disabled');
			redirect(KTBrowseUtil::getUrlForDocument($iDocumentId));
			exit(0);
		}

		if ($_REQUEST['fFolderId']) {
			$this->successRedirectToMain(_kt('Administrator mode disabled'), sprintf('fFolderId=%d', $_REQUEST['fFolderId']));
		}

		$this->successRedirectToMain(_kt('Administrator mode disabled'));
	}

	/**
	 * Determine the folder id for which to browse.
	 *
	 * @return int
	 */
	private function getFolderId()
	{
	    $folder_id = 1;

	    $in_folder_id = KTUtil::arrayGet($_REQUEST, 'fFolderId');
	    if (empty($in_folder_id)) {
	        $oConfig = KTConfig::getSingleton();
	        if ($oConfig->get('tweaks/browseToUnitFolder')) {
	            $iHomeFolderId = $this->oUser->getHomeFolderId();
	            if ($iHomeFolderId) {
	                $in_folder_id = $iHomeFolderId;
	            }
	        }
	    }

	    $folder_id = (int) $in_folder_id; // conveniently, will be 0 if not possible.
	    if ($folder_id == 0) {
	        $folder_id = 1;
	    }

	    return $folder_id;
	}

	/**
	 * Check permissions for whether the user can edit this folder
	 *
	 * @param folder object $oFolder
	 */
	private function setEditable($oFolder)
	{
	    // check whether the user can edit this folder
	    if (SharedUserUtil::isSharedUser()) {
	        if (SharedContent::getPermissions($this->oUser->getId(), $oFolder->getId(), null, 'folder') == 1) {
	            $this->permissions['editable'] = true;
	        }
	        else {
	            $this->permissions['editable'] = false;
	        }
	    }
	    else {
	        if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.write', $oFolder)) {
	            $this->permissions['editable'] = true;
	        } else {
	            $this->permissions['editable'] = false;
	        }

	        $this->permissions['folderDetails'] = KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.folder_details', $oFolder);
	    }
	}

	/**
	 * Browse folder contents
	 *
	 * @return boolean (on failure only)
	 */
	private function browseFolder()
	{
	    $folder_id = $this->getFolderId();
	    $_REQUEST['fBrowseMode'] = 'folder';

	    $oFolder =& Folder::get($folder_id);
	    if (PEAR::isError($oFolder)) {
	    	$this->oFolder = false;
	        return false; // just fail.
	    }

	    $this->setEditable($oFolder);

	    // set the title and breadcrumbs...
	    $this->oPage->setTitle(_kt('Browse'));
	    if (SharedUserUtil::isSharedUser()) {
	        // TODO : What should we do if it is a shared user.
	    }
	    else {
	        if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.folder_details', $oFolder)) {
	            $this->oPage->setSecondaryTitle($oFolder->getName());
	        } else {
	            if (KTBrowseUtil::inAdminMode($this->oUser, $oFolder)) {
	                $this->oPage->setSecondaryTitle(sprintf('(%s)', $oFolder->getName()));
	            } else {
	                $this->oPage->setSecondaryTitle('...');
	            }
	        }
	    }

	    // Figure out if we came here by navigating trough a shortcut.
	    // If we came here from a shortcut, the breadcrumbs path should be relative to the shortcut folder.
	    $symLinkFolderId = KTUtil::arrayGet($_REQUEST, 'fShortcutFolder', null);
	    if (is_numeric($symLinkFolderId)) {
	        $breadcrumbsFolder = Folder::get($symLinkFolderId);
	        $breadcrumbs = KTBrowseUtil::breadcrumbsForFolder($breadcrumbsFolder, array('final' => false));
	        $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, $breadcrumbs);
	        $this->aBreadcrumbs[] = array('name' => $oFolder->getName());
	    } else {
	        $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForFolder($oFolder));
	    }

	    $this->oFolder =& $oFolder;

	    // we now have a folder, and need to create the query.
	    $aOptions = array('ignorepermissions' => KTBrowseUtil::inAdminMode($this->oUser, $oFolder));
	    $this->oQuery = new BrowseQuery($oFolder->getId(), $this->oUser, $aOptions);

	    $this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fFolderId=%d', $oFolder->getId()));

	    return true;
	}

	/**
	 * Browse by lookup value
	 */
	private function browseByLookup()
	{
	    $this->permissions['editable'] = false;

	    // check the inputs
	    $field = KTUtil::arrayGet($_REQUEST, 'fField', null);
	    $oField = DocumentField::get($field);
	    if (PEAR::isError($oField) || ($oField == false)) {
	        $this->errorRedirectToMain('No Field selected.');
	        exit(0);
	    }

	    $value = KTUtil::arrayGet($_REQUEST, 'fValue', null);
	    $oValue = MetaData::get($value);
	    if (PEAR::isError($oValue) || ($oValue == false)) {
	        $this->errorRedirectToMain('No Value selected.');
	        exit(0);
	    }

	    $this->oQuery = new ValueBrowseQuery($oField, $oValue);
	    $this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'],
	    sprintf('fBrowseMode=lookup_value&fField=%d&fValue=%d', $field, $value));

	    $this->aBreadcrumbs = array(
	       array('name' => _kt('Lookup Values'),
	             'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectField')),
	       array('name' => $oField->getName(),
	             'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectLookup&fField=' . $oField->getId())),
	       array('name' => $oValue->getName(),
	             'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fBrowseMode=lookup_value&fField=%d&fValue=%d', $field, $value))));
	}

	/**
	 * Browse a document
	 */
	private function browseByDocument()
	{
	    // browsing by document type
	    $this->permissions['editable'] = false;

	    $doctype = KTUtil::arrayGet($_REQUEST, 'fType',null);
	    $oDocType = DocumentType::get($doctype);
	    if (PEAR::isError($oDocType) || ($oDocType == false)) {
	        $this->errorRedirectToMain('No Document Type selected.');
	        exit(0);
	    }

	    $this->oQuery =  new TypeBrowseQuery($oDocType);

	    // FIXME probably want to redirect to self + action=selectType
	    $this->aBreadcrumbs[] = array('name' => _kt('Document Types'), 'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectType'));
	    $this->aBreadcrumbs[] = array('name' => $oDocType->getName(), 'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'fBrowseMode=document_type&fType=' . $oDocType->getId()));

	    $this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fType=%s&fBrowseMode=document_type', $doctype));
	}

	private function getCurrentFolderContent($folderId, $page = 1, $itemsPerPage = 5)
	{
		$oUser = KTEntityUtil::get('User',  $_SESSION['userID']);
		$KT = new KTAPI(3);
		$session = $KT->start_system_session($oUser->getUsername());

		//Get folder content, depth = 1, types= Directory, File, Shortcut, webserviceversion override
		$folder = &$KT->get_folder_contents($folderId, 1, 'DFS');
		$items = $folder['results']['items'];
		$ret = array('folders' => array(), 'documents' => array(), 'shortcuts' => array());

		foreach ($items as $item) {
			foreach ($item as $key => $value) {
				if ($value == 'n/a') { $item[$key] = null; }
			}

			switch($item['item_type']) {
				case 'F':
					$ret['folders'][] = $item;
					break;
				case 'D':
					$ret['documents'][] = $item;
					break;
				case 'S':
					$ret['shortcuts'][] = $item;
					break;
			}
		}

		return $ret;
	}

	private function includeOlark()
	{
	    $user = User::get($_SESSION['userID']);
	    $js = preg_replace('/.*[\/\\\\]plugins/', 'plugins', KT_LIVE_DIR) . '/resources/js/olark/olark.js';
	    $this->oPage->requireJsResource($js);

	    if (isset($_SESSION['isFirstLogin'])) {
	        $this->oPage->setBodyOnload("javascript: ktOlark.popupTrigger('Welcome to KnowledgeTree.  If you have any questions, please let us know.', 0, '" . $user->getName() . "', '" . $user->getEmail() . "');");
	        unset($_SESSION['isFirstLogin']);
	    }
	    else {
	        $this->oPage->setBodyOnload("javascript: ktOlark.setUserData('" . $user->getName() . "', '" . $user->getEmail() . "');");
	    }
	}

	private function getBulkNotification() {
   		return "Bulk {$this->bulkActionInProgress} action in progress.";
	}
}

$oDispatcher = new BrowseDispatcher();
$oDispatcher->dispatch();

?>
