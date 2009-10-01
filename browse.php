<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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

$sectionName = 'browse';

class BrowseDispatcher extends KTStandardDispatcher {

	var $sName = 'ktcore.actions.folder.view';

	var $oFolder = null;
	var $sSection = 'browse';
	var $browse_mode = null;
	var $query = null;
	var $resultURL;
	var $sHelpPage = 'ktcore/browse.html';
	var $editable;

	function BrowseDispatcher() {
		$this->aBreadcrumbs = array(
		array('action' => 'browse', 'name' => _kt('Browse')),
		);
		return parent::KTStandardDispatcher();
	}

	function check() {
		$this->browse_mode = KTUtil::arrayGet($_REQUEST, 'fBrowseMode', 'folder');
		$action = KTUtil::arrayGet($_REQUEST, $this->event_var, 'main');
		$this->editable = false;


		// catch the alternative actions.
		if ($action != 'main') {
			return true;
		}

		// if we're going to main ...

		// folder browse mode
		if ($this->browse_mode == 'folder') {
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

			$_REQUEST['fBrowseMode'] = 'folder';

			// here we need the folder object to do the breadcrumbs.
			$oFolder =& Folder::get($folder_id);
			if (PEAR::isError($oFolder)) {
				return false; // just fail.
			}

			// check whether the user can edit this folder
			$oPerm = KTPermission::getByName('ktcore.permissions.write');
			if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPerm, $oFolder)) {
				$this->editable = true;
			} else {
				$this->editable = false;
			}

			// set the title and breadcrumbs...
			$this->oPage->setTitle(_kt('Browse'));

			if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.folder_details', $oFolder)) {
				$this->oPage->setSecondaryTitle($oFolder->getName());
			} else {
				if (KTBrowseUtil::inAdminMode($this->oUser, $oFolder)) {
					$this->oPage->setSecondaryTitle(sprintf('(%s)', $oFolder->getName()));
				} else {
					$this->oPage->setSecondaryTitle('...');
				}
			}

			//Figure out if we came here by navigating trough a shortcut.
			//If we came here from a shortcut, the breadcrumbspath should be relative
			//to the shortcut folder.
			$iSymLinkFolderId = KTUtil::arrayGet($_REQUEST, 'fShortcutFolder', null);
			if(is_numeric($iSymLinkFolderId)){
				$oBreadcrumbsFolder = Folder::get($iSymLinkFolderId);
				$this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForFolder($oBreadcrumbsFolder,array('final' => false)));
				$this->aBreadcrumbs[] = array('name'=>$oFolder->getName());
			}else{
				$this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, KTBrowseUtil::breadcrumbsForFolder($oFolder));
			}
			$this->oFolder =& $oFolder;


			// we now have a folder, and need to create the query.
			$aOptions = array(
                'ignorepermissions' => KTBrowseUtil::inAdminMode($this->oUser, $oFolder),
			);
			$this->oQuery =  new BrowseQuery($oFolder->getId(), $this->oUser, $aOptions);

			$this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fFolderId=%d', $oFolder->getId()));

			// and the portlets
			$portlet = new KTActionPortlet(sprintf(_kt('About this folder')));
			$aActions = KTFolderActionUtil::getFolderInfoActionsForFolder($this->oFolder, $this->oUser);
			$portlet->setActions($aActions,$this->sName);
			$this->oPage->addPortlet($portlet);

			$portlet = new KTActionPortlet(sprintf(_kt('Actions on this folder')));
			$aActions = KTFolderActionUtil::getFolderActionsForFolder($oFolder, $this->oUser);
			$portlet->setActions($aActions,null);
			$this->oPage->addPortlet($portlet);



		} else if ($this->browse_mode == 'lookup_value') {
			// browsing by a lookup value

			$this->editable = false;

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

			// setup breadcrumbs
			$this->aBreadcrumbs =
			array(
			array('name' => _kt('Lookup Values'),
                            'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectField')),
			array('name' => $oField->getName(),
                            'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=selectLookup&fField=' . $oField->getId())),
			array('name' => $oValue->getName(),
                            'url' => KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fBrowseMode=lookup_value&fField=%d&fValue=%d', $field, $value))));



		} else if ($this->browse_mode == 'document_type') {
			// browsing by document type


			$this->editable = false;
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

			$this->resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf('fType=%s&fBrowseMode=document_type', $doctype));;


		} else {
			// FIXME what should we do if we can't initiate the browse?  we "pretend" to have no perms.
			return false;
		}

		return true;
	}

	function do_main() {
		$oColumnRegistry =& KTColumnRegistry::getSingleton();

		$collection = new AdvancedCollection;
		$collection->addColumns($oColumnRegistry->getColumnsForView('ktcore.views.browse'));

		$aOptions = $collection->getEnvironOptions(); // extract data from the environment
		$aOptions['result_url'] = $this->resultURL;
		$aOptions['is_browse'] = true;



		$collection->setOptions($aOptions);
		$collection->setQueryObject($this->oQuery);
		$collection->setColumnOptions('ktcore.columns.selection', array(
            'rangename' => 'selection',
            'show_folders' => true,
            'show_documents' => true,
		));

		// get bulk actions
		$aBulkActions = KTBulkActionUtil::getAllBulkActions();

		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('kt3/browse');
		$aTemplateData = array(
              'context' => $this,
              'collection' => $collection,
              'browse_mode' => $this->browse_mode,
              'isEditable' => $this->editable,
              'bulkactions' => $aBulkActions,
              'browseutil' => new KTBrowseUtil(),
              'returnaction' => 'browse',
		);
		if ($this->oFolder) {
			$aTemplateData['returndata'] = $this->oFolder->getId();
		}
		return $oTemplate->render($aTemplateData);
	}



	function do_selectField() {
		$aFields = DocumentField::getList('has_lookup = 1');

		if (empty($aFields)) {
			$this->errorRedirectToMain(_kt('No lookup fields available.'));
			exit(0);
		}

		$_REQUEST['fBrowseMode'] = 'lookup_value';

		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('kt3/browse_lookup_selection');
		$aTemplateData = array(
              'context' => $this,
              'fields' => $aFields,
		);
		return $oTemplate->render($aTemplateData);
	}

	function do_selectLookup() {
		$field = KTUtil::arrayGet($_REQUEST, 'fField', null);
		$oField = DocumentField::get($field);
		if (PEAR::isError($oField) || ($oField == false) || (!$oField->getHasLookup())) {
			$this->errorRedirectToMain('No Field selected.');
			exit(0);
		}

		$_REQUEST['fBrowseMode'] = 'lookup_value';

		$aValues = MetaData::getByDocumentField($oField);

		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('kt3/browse_lookup_value');
		$aTemplateData = array(
              'context' => $this,
              'oField' => $oField,
              'values' => $aValues,
		);
		return $oTemplate->render($aTemplateData);
	}

	function do_selectType() {
		$aTypes = DocumentType::getList();
		// FIXME what is the error message?

		$_REQUEST['fBrowseMode'] = 'document_type';

		if (empty($aTypes)) {
			$this->errorRedirectToMain('No document types available.');
			exit(0);
		}

		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('kt3/browse_types');
		$aTemplateData = array(
              'context' => $this,
              'document_types' => $aTypes,
		);
		return $oTemplate->render($aTemplateData);
	}

	function do_enableAdminMode() {
		$iDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		$iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId');
		if ($iDocumentId) {
			$oDocument = Document::get($iDocumentId);
			if (PEAR::isError($oDocument) || ($oDocument === false)) {
				return null;
			}
			$iFolderId = $oDocument->getFolderId();
		}

		if (!Permission::userIsSystemAdministrator() && !Permission::isUnitAdministratorForFolder($this->oUser, $iFolderId)) {
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

	function do_disableAdminMode() {
		$iDocumentId = KTUtil::arrayGet($_REQUEST, 'fDocumentId');
		$iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId');
		if ($iDocumentId) {
			$oDocument = Document::get($iDocumentId);
			if (PEAR::isError($oDocument) || ($oDocument === false)) {
				return null;
			}
			$iFolderId = $oDocument->getFolderId();
		}

		if (!Permission::userIsSystemAdministrator() && !Permission::isUnitAdministratorForFolder($this->oUser, $iFolderId)) {
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
}

$oDispatcher = new BrowseDispatcher();
$oDispatcher->dispatch();

?>

