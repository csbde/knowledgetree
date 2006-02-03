<?php

require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');
require_once(KT_LIB_DIR . '/unitmanagement/UnitOrganisationLink.inc');
require_once(KT_LIB_DIR . '/orgmanagement/Organisation.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");

class KTUnitAdminDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Unit Management'));
        return parent::check();
    }

    function do_main() {
		$this->oPage->setBreadcrumbDetails(_('select a unit'));
		$this->oPage->setTitle(_("Unit Management"));

		$unit_list =& Unit::getList();
		
		$oTemplating = new KTTemplating;        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/unitadmin");
		$aTemplateData = array(
			"context" => $this,
			"unit_list" => $unit_list,
		);
 		return $oTemplate->render($aTemplateData);
    }

    function do_addUnit() {
        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        $_REQUEST['fFolderId'] = $iFolderId;
        $oFolder = $this->oValidator->validateFolder($_REQUEST['fFolderId']);

        $this->oPage->setBreadcrumbDetails(_('Add a new unit'));

        $this->oPage->setTitle(_("Add a new unit"));

        $edit_fields = array();
        $add_fields[] =  new KTStringWidget(_('Unit Name'),_('A short name for the unit.  e.g. <strong>Accounting</strong>.'), 'unit_name', null, $this->oPage, true);

        $collection = new DocumentCollection();
        $collection->addColumn(new KTUnitTitleColumn("Test 1 (title)","title"));
        $qObj = new FolderBrowseQuery($oFolder->getId());
        $collection->setQueryObject($qObj);
        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("action=addUnit&fFolderId=%d", $oFolder->getId()));
        $collection->setBatching($resultURL, $batchPage, $batchSize);

        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");

        $collection->setSorting($displayControl, $displayOrder);

        $collection->getResults();

        $aBreadcrumbs = array();
        $folder_path_names = $oFolder->getPathArray();
        $folder_path_ids = explode(',', $oFolder->getParentFolderIds());
        $folder_path_ids[] = $oFolder->getId();
        if ($folder_path_ids[0] == 0) {
            array_shift($folder_path_ids);
            array_shift($folder_path_names);
        }

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("action=addUnit&fFolderId=%d", $id));
            $aBreadcrumbs[] = array("url" => $url, "name" => $folder_path_names[$index]);
        }

        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/addunit");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
            "collection" => $collection,
            "collection_breadcrumbs" => $aBreadcrumbs,
            "folder" => $oFolder,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_createUnit() {
        $sName = $this->oValidator->validateString($_REQUEST['unit_name']);
        $oParentFolder = $this->oValidator->validateFolder($_REQUEST['fFolderId']);

        $oFolder = KTFolderUtil::add($oParentFolder, $sName, $this->oUser);
        $oUnit = Unit::createFromArray(array(
            'name' => $sName,
            'folderid' => $oFolder->getId(),
        ));
        return $this->successRedirectToMain('Unit created');
    }
}

class KTUnitTitleColumn extends TitleColumn {
    function buildFolderLink($aDataRow) {
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], 'action=addUnit&fFolderId=' . $aDataRow['folder']->getId());
    }
}


?>
