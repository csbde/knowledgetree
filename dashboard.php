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
require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');
require_once(KT_LIB_DIR . '/dashboard/dashletregistry.inc.php');
require_once(KT_LIB_DIR . '/dashboard/dashlet.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/dashboard/DashletDisables.inc.php');
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
require_once(KT_LIB_DIR . '/actions/actionsutil.inc.php');
require_once(KT_LIB_DIR . '/actions/dashboardaction.inc.php');

$sectionName = 'dashboard';

class DashboardDispatcher extends KTStandardDispatcher {

    public $sSection = 'dashboard';
    public $notifications = array();
    public $sHelpPage = 'ktcore/dashboard.html';
    public $aCannotView = array(4);

    public function DashboardDispatcher()
    {
        $this->aBreadcrumbs = array(
            array('action' => 'dashboard', 'name' => _kt('Dashboard')),
        );

        return parent::KTStandardDispatcher();
    }

    public function do_main()
    {
        $this->oPage->setShowPortlets(false);
        // retrieve action items for the user.
        // FIXME what is the userid?

        $dashletRegistry =& KTDashletRegistry::getSingleton();
        $dashlets = $dashletRegistry->getDashlets($this->oUser);

        $this->sSection = 'dashboard';
        $this->oPage->showDashboardBtn = true;
        $this->oPage->title = _kt('Dashboard');

        // simplistic improvement over the standard rendering:  float half left
        // and half right.  +Involves no JS -can leave lots of white-space at the bottom.

        $dashletsLeft = array();
        $dashletsRight = array();

        $i = 0;
        foreach ($dashlets as $dashlet) {
            if ((strpos(strtolower($dashlet->sTitle), 'welcome to knowledgetree') !== false) && !empty($dashletsLeft)) {
                array_unshift($dashletsLeft, $dashlet);
            }
            else {
                if ($i == 0) {
                    $dashletsLeft[] = $dashlet;
                }
                else {
                    $dashletsRight[] = $dashlet;
                }
            }

            $i += 1;
            $i %= 2;
        }

        $this->oPage->requireJSResource('thirdpartyjs/jquery/jquery_noconflict.js');
        $this->oPage->requireJSResource('resources/js/newui/dashboard/moreSidebarItems.js');

        $this->oUser->refreshDashboadState();

        $dashboardState = $this->oUser->getDashboardState();
        $dashboardJavascript = 'var savedState = ';
        if ($dashboardState == null) {
            $dashboardJavascript .= 'false';
            $dashboardState = false;
        }
        else {
            $dashboardJavascript .= $dashboardState;
        }

        $dashboardJavascript .= ';';
        $this->oPage->requireJSStandalone($dashboardJavascript);

        $ktOlarkPopup = null;
        // temporarily disabled
        global $default;
        if (ACCOUNT_ROUTING_ENABLED && $default->tier == 'trial' && isset($_SESSION['isFirstLogin'])) {
            $js = preg_replace('/.*[\/\\\\]plugins/', 'plugins', KT_LIVE_DIR) . '/resources/js/olark/olark.js';
            $this->oPage->requireJsResource($js);
            // add popup to page
            $ktOlarkPopup = '<script type="text/javascript">
    ktOlarkPopupTrigger("Welcome to KnowledgeTree.  If you have any questions, please let us know.", 0);
</script>';
            unset($_SESSION['isFirstLogin']);
        }

        $sidebars = KTDashboardActionUtil::getActionForDashboard($this->oUser, 'maindashsidebar');
        $dashboardViewlets = KTDashboardActionUtil::getAllDashboardActions('dashboardviewlet');
        $orderedKeys = ActionsUtil::sortActions($dashboardViewlets);

        // render
        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('kt3/dashboard');
        $templateData = array(
              'context' => $this,
              'dashlets_left' => $dashletsLeft,
              'dashlets_right' => $dashletsRight,
              'ktOlarkPopup' => $ktOlarkPopup,
              'dashboardViewlets' => $orderedKeys['ordered'],
              'keys' => $orderedKeys['keys'],
              'sidebars' => $sidebars,
        );

        // TODO : Is this ok?
        $ds = DIRECTORY_SEPARATOR;
        if (file_exists(KT_DIR . $ds . 'var' . $ds . 'bin' . $ds . 'firstlogin.lock')) {
            $this->runFirstLoginWizard($template, $templateData);
        }

        return $template->render($templateData);
    }

    //
    public function runFirstLoginWizard($template, $templateData)
    {
        $this->oPage->requireCSSResource('setup/wizard/resources/css/modal.css');
        $this->oPage->requireJSResource('setup/wizard/resources/js/jquery-1.4.2.min.js');
        $this->oPage->requireJSResource('thirdpartyjs/jquery/jquery_noconflict.js');
        $this->oPage->requireJSResource('setup/wizard/resources/js/firstlogin.js');
    }

    // return some kind of ID for each dashlet
    // currently uses the class name
    public function _getDashletId($dashlet)
    {
        return get_class($dashlet);
    }

    // disable a dashlet.
    // FIXME this very slightly violates the separation of concerns, but its not that flagrant.
    public function do_disableDashlet()
    {
        $sNamespace = KTUtil::arrayGet($_REQUEST, 'fNamespace');
        $iUserId = $this->oUser->getId();

        if (empty($sNamespace)) {
            $this->errorRedirectToMain('No dashlet specified.');
            exit(0);
        }

        $this->startTransaction();
        $aParams = array('sNamespace' => $sNamespace, 'iUserId' => $iUserId);
        $oDD = KTDashletDisable::createFromArray($aParams);
        if (PEAR::isError($oDD)) {
            $this->errorRedirectToMain('Failed to disable the dashlet.');
        }

        $this->commitTransaction();
        $this->successRedirectToMain('Dashlet disabled.');
    }

    public function json_saveDashboardState()
    {
        $state = KTUtil::arrayGet($_REQUEST, 'state', array('error' => true));
        $this->oUser->setDashboardState($state);
        return array('success' => true);
    }

}

$dispatcher = new DashboardDispatcher();
$dispatcher->dispatch();

?>
