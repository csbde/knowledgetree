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
 *
 */

require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

class KTUnitAdminDispatcher extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/control units.html';
    var $bAutomaticTransaction = true;

    function check() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Unit Management'));
        return parent::check();
    }

    function do_main() {
		$this->oPage->setBreadcrumbDetails(_kt('select a unit'));
		$this->oPage->setTitle(_kt("Unit Management"));

		$unit_list =& Unit::getList();
		
		$oTemplating =& KTTemplating::getSingleton();        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/unitadmin");
		$aTemplateData = array(
			"context" => $this,
			"unit_list" => $unit_list,
		);
 		return $oTemplate->render($aTemplateData);
    }

    function do_addUnit() {
        $this->oPage->setBreadcrumbDetails(_kt('Add a new unit'));
        $this->oPage->setTitle(_kt("Add a new unit"));

        $add_fields = array();
        $add_fields[] =  new KTStringWidget(_kt('Unit Name'), _kt('A short name for the unit.  e.g. <strong>Accounting</strong>.'), 'unit_name', null, $this->oPage, true);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/addunit");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_addUnit2() {
        $this->oPage->setBreadcrumbDetails(_kt('Add a new unit'));
        $this->oPage->setTitle(_kt("Add a new unit"));

        $aOptions = array(
            'redirect_to' => array('addUnit'),
            'message' => _kt('No name given'),
        );
        $sName = $this->oValidator->validateString($_REQUEST['unit_name'], $aOptions);
		$aOptions['message'] = _kt('A unit with that name already exists.');
		$sName = $this->oValidator->validateDuplicateName('Unit', $sName, $aOptions);

        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        $_REQUEST['fFolderId'] = $iFolderId;
        $oFolder = $this->oValidator->validateFolder($_REQUEST['fFolderId']);



        // Setup the collection for move display.
        $collection = new AdvancedCollection();

        $oCR =& KTColumnRegistry::getSingleton();
        $col = $oCR->getColumn('ktcore.columns.title');
        $col->setOptions(array('qs_params'=>array('fFolderId'=>$oFolder->getId())));
        $collection->addColumn($col);

        $qObj = new FolderBrowseQuery($oFolder->getId());
        $collection->setQueryObject($qObj);

        $collection->empty_message = _kt('No folders available in this location.');
        $aOptions = $collection->getEnvironOptions();
        $collection->setOptions($aOptions);

        $oWF =& KTWidgetFactory::getSingleton();
        $oWidget = $oWF->get('ktcore.widgets.collection',
                             array('label' => _kt('Target Folder'),
                                   'description' => _kt('<p>The folder given below is where the unit folder will be created. Use the folder collection and path below to browse to the folder you wish to create the unit folder into.</p><p>The unit administrators have additional rights within that portion of the document management system.
</p>'),
                                   'required' => true,
                                   'name' => 'browse',
                                   'folder_id' => $oFolder->getId(),
                                   'collection' => $collection));
                                                       
        

        $add_fields = array();
        $add_fields[] =  new KTStaticTextWidget(_kt('Unit Name'), _kt('A short name for the unit.  e.g. <strong>Accounting</strong>.'), 'unit_name', $sName, $this->oPage, true);

        $add_fields[] = $oWidget;

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/addunit2");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
            "unit_name" => $sName,
            "folder" => $oFolder,
            "name" => $sName,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_createUnit() {
        $aOptions = array(
            'redirect_to' => array('main'),
            'message' => _kt('Invalid folder chosen'),
        );

        $oParentFolder = $this->oValidator->validateFolder($_REQUEST['browse'], $aOptions);
        $aOptions = array(
            'redirect_to' => array('addUnit', sprintf('browse=%d', $oParentFolder->getId())),
            'message' => _kt('No name given'),
        );
        $sName = $this->oValidator->validateString($_REQUEST['unit_name'], $aOptions);
        $aOptions['message'] = _kt('A unit with that name already exists.');
        $sName = $this->oValidator->validateDuplicateName('Unit', $sName, $aOptions);
                
        $oFolder = KTFolderUtil::add($oParentFolder, $sName, $this->oUser);
        $aOptions = array(
            'redirect_to' => array('addUnit2', sprintf('fFolderId=%d&unit_name=%s', $oParentFolder->getId(), $sName)),
            'defaultmessage' => 'Error creating folder',
        );
        $this->oValidator->notError($oFolder, $aOptions);

        KTPermissionUtil::copyPermissionObject($oFolder);
        
        $oUnit = Unit::createFromArray(array(
            'name' => $sName,
            'folderid' => $oFolder->getId(),
        ));
        return $this->successRedirectToMain(_kt('Unit created'));
    }

    function do_editUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 

        $fields = array();
        $fields[] =  new KTStringWidget(_kt('Unit Name'), _kt('A short name for the unit.  e.g. <strong>Accounting</strong>.'), 'unit_name', $oUnit->getName(), $this->oPage, true);

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/principals/editunit');
        $aTemplateData = array(
            "context" => $this,
            "edit_unit" => $oUnit,
            "edit_fields" => $fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_saveUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 
        $aOptions = array(
            'redirect_to' => array('editUnit', sprintf('unit_id=%d', $oUnit->getId())),
            'message' => _kt('No name given'),
        );
        $sName = $this->oValidator->validateString($_REQUEST['unit_name'], $aOptions);
		$aOptions['message'] = _kt('A unit with that name already exists.');
		$aOptions['rename'] = $oUnit->getId();
		$sName = $this->oValidator->validateDuplicateName('Unit', $sName, $aOptions);
        $oUnit->setName($sName);
        $res = $oUnit->update();
        if (($res == false) || (PEAR::isError($res))) {
            return $this->errorRedirectToMain(_kt('Failed to set unit details.'));
        }
        $iFolderId = $oUnit->getFolderId();
        $oFolder = Folder::get($iFolderId);
        if (!PEAR::isError($oFolder) && ($oFolder !== false)) {
            KTFolderUtil::rename($oFolder, $sName, $this->oUser);
        }

        $this->successRedirectToMain(_kt("Unit details updated"));
    }

    function do_deleteUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 

        $fields = array();
        $fields[] = new KTCheckboxWidget(_kt('Delete folder'), _kt('Each unit has an associated folder.  While the unit is being deleted, there may be some documents within the associated folder.  By unselecting this option, they will not be removed.'), 'delete_folder', true, $this->oPage, true);

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/principals/deleteunit');
        $aTemplateData = array(
            "context" => $this,
            "unit" => $oUnit,
            "fields" => $fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_removeUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 
        $bDeleteFolder = KTUtil::arrayGet($_REQUEST, 'delete_folder', false);
        $res = $oUnit->delete();
        $aOptions = array(
            'redirect_to' => array('main'),
            'message' => _kt("Could not delete this unit because it has groups assigned to it"),
            'no_exception' => true,
        );
        $this->oValidator->notError($res, $aOptions);
        if ($bDeleteFolder) {
            $iFolderId = $oUnit->getFolderId();
            $oFolder = Folder::get($iFolderId);
            if (!PEAR::isError($oFolder) && ($oFolder !== false)) {
                $aOptions = array(
                    'ignore_permissions' => true,
                );
                KTFolderUtil::delete($oFolder, $this->oUser, "Unit deleted", $aOptions);
            }
        }
        $this->successRedirectToMain(_kt("Unit removed"));
    }
}

class KTUnitTitleColumn extends TitleColumn {
    function KTUnitTitleColumn($sName) {
        $this->sName = $sName;
        parent::TitleColumn("Unit", "title");
    }
    function buildFolderLink($aDataRow) {
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], 
            sprintf('action=addUnit2&unit_name=%s&fFolderId=%d', $this->sName, $aDataRow['folder']->getId()));
    }
}


?>
