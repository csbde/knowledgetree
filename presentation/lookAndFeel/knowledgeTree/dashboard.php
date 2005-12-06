<?php

/**
 * $Id$
 *
 * Main dashboard page -- This page is presented to the user after login.
 * It contains a high level overview of the users subscriptions, checked out 
 * document, pending approval routing documents, etc. 
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

// main library routines and defaults
require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/unitmanagement/Unit.inc");

require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php");
require_once(KT_LIB_DIR . "/dashboard/dashlet.inc.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

$sectionName = "dashboard";

class DashboardDispatcher extends KTStandardDispatcher {
	
	var $notifications = array();

    function DashboardDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'dashboard', 'name' => 'Dashboard'),
        );
        return parent::KTStandardDispatcher();
    }
    function do_main() {
	    $this->oPage->setShowPortlets(false);
		// retrieve action items for the user.
		// FIXME what is the userid?
		
		
		$oDashletRegistry =& KTDashletRegistry::getSingleton();
		$aDashlets = $oDashletRegistry->getDashlets($this->oUser);
		
		$this->sSection = "dashboard";
		$this->oPage->setBreadcrumbDetails(_("Home"));
		$this->oPage->title = _("Dashboard");
	
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("kt3/dashboard");
		$aTemplateData = array(
              "context" => $this,
			  "dashlets" => $aDashlets,
		);
		return $oTemplate->render($aTemplateData);
	}   
}

$oDispatcher = new DashboardDispatcher();
$oDispatcher->dispatch();

?>

