<?php

//require_once('../../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');
require_once(KT_LIB_DIR . '/unitmanagement/UnitOrganisationLink.inc');
require_once(KT_LIB_DIR . '/orgmanagement/Organisation.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

class KTUnitAdminDispatcher extends KTAdminDispatcher {
    function do_main() {
		$this->aBreadcrumbs[] = array('action' => 'unitManagement', 'name' => _('Unit Management'));
		$this->oPage->setBreadcrumbDetails(_('select a unit'));
		$this->oPage->setTitle(_("Unit Management"));

		$unit_id= KTUtil::arrayGet($_REQUEST, 'unit_id', null);
		if ($unit_id === null) { $for_edit = false; }
		else { $for_edit = true; }
	
		
		$add_fields = array();
		$add_fields[] =  new KTStringWidget(_('Unit Name'),_("The unit's visible name.  e.g. <strong>Tech Support</strong>"), 'name', null, $this->oPage, true);

		$unit_list =& Unit::getList();
		
		$edit_fields = array();
		$edit_unit = null;
		if ($for_edit === true) {
		    $oUnit = Unit::get($unit_id);
		    $edit_fields[] =  new KTStringWidget(_('Unit Name'),_("The unit's visible name.  e.g. <strong>Tech Support</strong>"), 'name', $oUnit->getName(), $this->oPage, true);
        }
			
		$oTemplating = new KTTemplating;        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/unitadmin");
		$aTemplateData = array(
			"context" => $this,
			"add_fields" => $add_fields,
			"for_edit" => $for_edit,
			"edit_fields" => $edit_fields,
			"edit_unit" => $oUnit,
			"unit_list" => $unit_list,
		);
 		return $oTemplate->render($aTemplateData);
    }

	function do_updateUnit() {
	    $unit_id = KTUtil::arrayGet($_REQUEST, 'unit_id');
		$oUnit = Unit::get($unit_id);
		if (PEAR::isError($oUnit) || ($oUnit == false)) {
		    $this->errorRedirectToMain(_('Please specify a unit.'));
			exit(0);
		}
		
		$unit_name = KTUtil::arrayGet($_REQUEST, 'name', null);
		if (empty($unit_name)) {
		    $this->errorRedirectToMain(_('Please specify a unit name.'));
			exit(0);
		}
		
		$this->startTransaction();
		$oUnit->setName($unit_name);
		$res = $oUnit->update();
		if (PEAR::isError($res)) {
		    $this->errorRedirectToMain(_('Failed to update unit name.'));
			exit(0);
		}
		
		$this->commitTransaction();
		$this->successRedirectToMain(sprintf(_('Unit name changed to "%s"'), $unit_name));
	}
	
}


?>
