<?php

//require_once('../../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/orgmanagement/Organisation.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

class KTOrgAdminDispatcher extends KTAdminDispatcher {
    function do_main() {
		$this->aBreadcrumbs[] = array('action' => 'orgManagement', 'name' => _('Organisation Management'));
		$this->oPage->setBreadcrumbDetails(_('select a organisation'));
		$this->oPage->setTitle(_("Organisation Management"));

		$org_id= KTUtil::arrayGet($_REQUEST, 'org_id', null);
		if ($org_id === null) { $for_edit = false; }
		else { $for_edit = true; }
		
		$org_list =& Organisation::getList();
		
		$edit_fields = array();
		$edit_org = null;
		if ($for_edit === true) {
		    $oOrg = Organisation::get($org_id);
			if (PEAR::isError($oOrg) || ($oOrg == false)) { $this->errorRedirectToMain(_('Invalid Organisation')); }
		    $edit_fields[] =  new KTStringWidget(_('Organisation Name'),_("The organisation's visible name.  e.g. <strong>Tech Support</strong>"), 'name', $oOrg->getName(), $this->oPage, true);
        }
			
		$oTemplating = new KTTemplating;        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/orgadmin");
		$aTemplateData = array(
			"context" => $this,
			"for_edit" => $for_edit,
			"edit_fields" => $edit_fields,
			"edit_org" => $oOrg,
			"org_list" => $org_list,
		);
 		return $oTemplate->render($aTemplateData);
    }

	function do_updateOrg() {
	    $org_id = KTUtil::arrayGet($_REQUEST, 'org_id');
		$oOrg = Organisation::get($org_id);
		if (PEAR::isError($oOrg) || ($oOrg == false)) {
		    $this->errorRedirectToMain(_('Please specify an organisation.'));
			exit(0);
		}
		
		$org_name = KTUtil::arrayGet($_REQUEST, 'name', null);
		if (empty($org_name)) {
		    $this->errorRedirectToMain(_('Please specify an org name.'));
			exit(0);
		}
		
		$this->startTransaction();
		$oOrg->setName($org_name);
		$res = $oOrg->update();
		if (PEAR::isError($res)) {
		    $this->errorRedirectToMain(_('Failed to update org name.'));
			exit(0);
		}
		
		$this->commitTransaction();
		$this->successRedirectToMain(_('Org name changed to') . ': "' . $org_name . '"');
	}
	
}


?>
