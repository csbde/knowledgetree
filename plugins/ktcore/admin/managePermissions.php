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

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/permissions/permission.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

class ManagePermissionsDispatcher extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/manage permissions.html'; 
    function do_main() {
        session_start();
        $this->oPage->setTitle(_kt('Manage Permissions'));
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Manage Permissions'));
        
        $add_fields = array();
        $add_fields[] = new KTStringWidget(_kt('System Name'), _kt('The internal name used for the permission.  This should never be changed.'), 'name', null, $this->oPage, true, 'name');
        $add_fields[] = new KTStringWidget(_kt('Display Name'), _kt('A short name that is shown to users whenever permissions must be assigned.'), 'human_name', null, $this->oPage, true, 'human_name');
    
    	if($_SESSION['Permission']['NameValue'])
        {
        	$this->sNameVal = $_SESSION['Permission']['NameValue'];
        	$_SESSION['Permission']['NameValue'] = '';
        }
        else if($_SESSION['Permission']['HumanNameValue'])
        {
        	$this->sHumanNameVal = $_SESSION['Permission']['HumanNameValue'];
        	$_SESSION['Permission']['HumanNameValue'] = '';
        }    

        $oTemplating =& KTTemplating::getSingleton();
        $aPermissions =& KTPermission::getList();
        $oTemplate = $oTemplating->loadTemplate("ktcore/manage_permissions");
        $aTemplateData = array(
            'context' => $this,
            "permissions" => $aPermissions,
            'add_fields' => $add_fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_newPermission() {
        session_start();
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
        $sHumanName = KTUtil::arrayGet($_REQUEST, 'human_name');
        $sError = 'An error occured while creating your permission';
        
        //Checking that the System Name and Display Name fields aren't empty        
        if (empty($sName) && !empty($sHumanName))
        {
        	$sError = 'An error occured while creating your permission: The System Name was not provided.';
        	$_SESSION['Permission']['HumanNameValue'] = $sHumanName;
        	return $this->errorRedirectToMain(sprintf(_kt('%s') , $sError));
        }
        else if(!empty($sName) && empty($sHumanName))
        {
        	$sError = 'An error occured while creating your permission: The Display Name was not provided.';
        	$_SESSION['Permission']['NameValue'] = $sName;
        	return $this->errorRedirectToMain(sprintf(_kt('%s') , $sError));
        }
        else if (empty($sName) && empty($sHumanName))
        {
        	$sError = 'An error occured while creating your permission: The Display Name and System Name weren\'t provided.';
        	return $this->errorRedirectToMain(sprintf(_kt('%s') , $sError));
        }
        
        //Checking that the System Name and Display Name aren't already in the database
        $aPermissions = KTPermission::getList();
        //$iNameErrorCount and $iHumanNameErrorCount are used to check whether only one name is duplicated or if two names are duplicated.
        $iNameErrorCount = 0;
        $iHumanNameErrorCount = 0;
        foreach ($aPermissions as $aPermission)
    	{
    		if($sName == $aPermission->getName())
    		{
    			$iNameErrorCount ++;
    		}	
    		if ($sHumanName == $aPermission->getHumanName())
    		{
				$iHumanNameErrorCount ++;
    		}
    	}
    	if ($iNameErrorCount > 0 && $iHumanNameErrorCount > 0) 
    	{
			$sError = 'An error occured while creating your permission: The Display Name and System Name you have provided both already exist.';
			return $this->errorRedirectToMain(sprintf(_kt('%s') , $sError));
    	}
    	else if ($iNameErrorCount > 0 && $iHumanNameErrorCount == 0)
    	{
    		if(!empty($sHumanName))
			{
				$_SESSION['Permission']['HumanNameValue'] = $sHumanName;		
			}
    		$sError = 'An error occured while creating your permission: A permission with the same System Name already exists.';
			return $this->errorRedirectToMain(sprintf(_kt('%s') , $sError));
		}
    	else if ($iNameErrorCount == 0 && $iHumanNameErrorCount > 0)
    	{
    		if(!empty($sName))
			{
				$_SESSION['Permission']['NameValue'] = $sName;		
			}
    		$sError = 'An error occured while creating your permission: A permission with the same Display Name already exists.';
			return $this->errorRedirectToMain(sprintf(_kt('%s') , $sError));    		
    	}
    	$oPerm = KTPermission::createFromArray(array(
            'name' => $sName,
            'humanname' => $sHumanName,
        ));
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain(sprintf(_kt('%s') , $sError));
        }
        return $this->successRedirectToMain(_kt("Permission created"));
    }

    function do_deletePermission() {
        $id = KTUtil::arrayGet($_REQUEST, 'id');
        if (empty($id)) {
            return $this->errorRedirectToMain(_kt("Both names not given"));
        }
        $oPerm = KTPermission::get($id);
        if (PEAR::isError($oPerm)) {
            return $this->errorRedirectToMain(_kt("Error finding permission"));
        }
        if ($oPerm->getBuiltIn() === true) {
            return $this->errorRedirectToMain(_kt("Can't delete built-in permission"));
        }
        $res = $oPerm->delete();
        if (PEAR::isError($res)) {
            return $this->errorRedirectToMain(_kt("Error deleting permission"));
        }
        return $this->successRedirectToMain(_kt("Permission deleted"));
    }
}

?>
