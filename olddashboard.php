<?php

/**
 * $Id$
 *
 * Main dashboard page -- This page is presented to the user after login.
 * It contains a high level overview of the users subscriptions, checked out 
 * document, pending approval routing documents, etc. 
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
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/unitmanagement/Unit.inc");

require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php");
require_once(KT_LIB_DIR . "/dashboard/dashlet.inc.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

require_once(KT_LIB_DIR . "/dashboard/DashletDisables.inc.php");

$sectionName = "dashboard";

class DashboardDispatcher extends KTStandardDispatcher {
    
    var $notifications = array();
    var $sHelpPage = 'ktcore/dashboard.html';    

    function DashboardDispatcher() {
        $this->aBreadcrumbs = array(
            array('action' => 'olddashboard', 'name' => _kt('Dashboard')),
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
        $this->oPage->setBreadcrumbDetails(_kt("Home"));
        $this->oPage->title = _kt("Dashboard");
    
        // simplistic improvement over the standard rendering:  float half left
        // and half right.  +Involves no JS -can leave lots of white-space at the bottom.

        $aDashletsLeft = array();
        $aDashletsRight = array(); 

        $i = 0;
        foreach ($aDashlets as $oDashlet) {
            if ($i == 0) { $aDashletsLeft[] = $oDashlet; }
            else {$aDashletsRight[] = $oDashlet; }
            $i += 1;
            $i %= 2;
        }


        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/olddashboard");
        $aTemplateData = array(
              "context" => $this,
              "dashlets_left" => $aDashletsLeft,
              "dashlets_right" => $aDashletsRight,
        );
        return $oTemplate->render($aTemplateData);
    }
    
    // disable a dashlet.  
    // FIXME this very slightly violates the separation of concerns, but its not that flagrant.
    function do_disableDashlet() {
        $sNamespace = KTUtil::arrayGet($_REQUEST, 'fNamespace');
        $iUserId = $this->oUser->getId();
        
        if (empty($sNamespace)) {
            $this->errorRedirectToMain('No dashlet specified.');
            exit(0);
        }
    
        // do the "delete"
        
        $this->startTransaction();
        $aParams = array('sNamespace' => $sNamespace, 'iUserId' => $iUserId);
        $oDD = KTDashletDisable::createFromArray($aParams);
        if (PEAR::isError($oDD)) {
            $this->errorRedirectToMain('Failed to disable the dashlet.');
        }
    
        $this->commitTransaction();
        $this->successRedirectToMain('Dashlet disabled.');
    }
}

$oDispatcher = new DashboardDispatcher();
$oDispatcher->dispatch();

?>

