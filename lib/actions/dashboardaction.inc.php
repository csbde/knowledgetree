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
 *
 */

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

/**
 * Base class for document actions within KnowledgeTree
 *
 * @author KnowledgeTree Team
 * @package KTDashboardActions
 */

class KTDashboardAction extends KTStandardDispatcher {

    public function KTDashboardAction($oUser = null, $oPlugin = null) {
        $this->oUser = $oUser;
        $this->oPlugin = $oPlugin;
        $this->oConfig = KTConfig::getSingleton();

        parent::KTStandardDispatcher();
    }

    public function _show() {
    	return true;
    }

	public function getInfo() {
    	if(!empty($this->bulkActionInProgress)) {
    		if(!in_array($this->bulkActionInProgress, $this->showIfBulkActions)) {
    			return '';
    		}
    	}
        $check = $this->_show();
        if ($check === false) {
            $check = 'disabled';
        }

        $aInfo = array(
            'description' => $this->sDescription,
            'name' => $this->getDisplayName(),
            'ns' => $this->sName,
        );

        $aInfo = $this->customiseInfo($aInfo);
        return $aInfo;
    }

    public function getName() {
        return $this->sName;
    }

    public function getDisplayName() {
        // Should be overridden by the i18nised display name
        // This is here solely for backwards compatibility
        return $this->sDisplayName;
    }

    public function getDescription() {
        return $this->sDescription;
    }

    public function getButton(){
        return false;
    }

    public function customiseInfo($aInfo) {
        return $aInfo;
    }
}

class KTDashboardActionUtil {
    public function getDashboardActionInfo($slot = 'dashboardsidebar') {
        $oRegistry = KTActionRegistry::getSingleton();
        return $oRegistry->getActions($slot);
    }

    public static function getActionsForDashboard($oUser, $slot = 'dashboardsidebar') {
        $aObjects = array();
        $actions = KTDashboardActionUtil::getDashboardActionInfo($slot);
        foreach ($actions as $aAction) {
            list($sClassName, $sPath, $sPlugin) = $aAction;
            $oRegistry = KTPluginRegistry::getSingleton();
            $oPlugin = $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] = new $sClassName($oUser, $oPlugin);
        }
        return $aObjects;
    }

    public function getAllDashboardActions($slot = 'dashboardsidebar') {
        $aObjects = array();
        $oUser = null;
        foreach (KTDashboardActionUtil::getDashboardActionInfo($slot) as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry = KTPluginRegistry::getSingleton();
            $oPlugin = $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] = new $sClassName($oUser, $oPlugin);
        }
        return $aObjects;
    }

    public function getDashboardActionsByNames($aNames, $slot = 'dashboardsidebar', $oUser = null) {
        $aObjects = array();
        foreach (KTDashboardActionUtil::getDashboardActionInfo($slot) as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry = KTPluginRegistry::getSingleton();
            $oPlugin = $oRegistry->getPlugin($sPlugin);
            if (!in_array($sName, $aNames)) {
                continue;
            }
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] = new $sClassName($oUser, $oPlugin);
        }
        return $aObjects;
    }
}

?>
