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

    public function __construct($user = null, $plugin = null)
    {
        $this->oUser = $user;
        $this->oPlugin = $plugin;
        $this->oConfig = KTConfig::getSingleton();

        parent::KTStandardDispatcher();
    }

    public function _show()
    {
        return true;
    }

    public function getInfo()
    {
        $check = $this->_show();
        if ($check === false) {
            $check = 'disabled';
        }

        $info = array(
            'description' => $this->sDescription,
            'name' => $this->getDisplayName(),
            'ns' => $this->sName,
        );

        $info = $this->customiseInfo($info);
        return $info;
    }

    public function getName()
    {
        return $this->sName;
    }

    public function getDisplayName()
    {
        // Should be overridden by the i18nised display name
        // This is here solely for backwards compatibility
        return $this->sDisplayName;
    }

    public function getDescription()
    {
        return $this->sDescription;
    }

    public function getButton()
    {
        return false;
    }

    public function customiseInfo($info)
    {
        return $info;
    }

}

class KTDashboardActionUtil {

    public static function getDashboardActionInfo($slot = 'dashboardsidebar')
    {
        $registry = KTActionRegistry::getSingleton();
        return $registry->getActions($slot);
    }

    public static function getActionsForDashboard($user, $slot = 'dashboardsidebar')
    {
        $objects = array();
        $actions = KTDashboardActionUtil::getDashboardActionInfo($slot);
        foreach ($actions as $action) {
            list($className, $path, $plugin) = $action;
            $registry = KTPluginRegistry::getSingleton();
            $plugin = $registry->getPlugin($plugin);
            if (!empty($path)) {
                require_once($path);
            }
            $objects[] = new $className($user, $plugin);
        }

        return $objects;
    }

    public static function getActionForDashboard($user, $slot = 'dashboardsidebar')
    {
        $objects = KTDashboardActionUtil::getActionsForDashboard($user, $slot);
        if (count($objects) == 1) {
            return $objects[0];
        }
        else {
            return $objects;
        }
    }

    public static function getAllDashboardActions($slot = 'dashboardsidebar')
    {
        $objects = array();
        $user = null;
        foreach (KTDashboardActionUtil::getDashboardActionInfo($slot) as $action) {
            list($className, $path, $sName, $plugin) = $action;
            $registry = KTPluginRegistry::getSingleton();
            $plugin = $registry->getPlugin($plugin);
            if (!empty($path)) {
                require_once($path);
            }
            $objects[] = new $className($user, $plugin);
        }

        return $objects;
    }

    public static function getDashboardActionsByNames($aNames, $slot = 'dashboardsidebar', $user = null)
    {
        $objects = array();
        foreach (KTDashboardActionUtil::getDashboardActionInfo($slot) as $action) {
            list($className, $path, $sName, $plugin) = $action;
            $registry = KTPluginRegistry::getSingleton();
            $plugin = $registry->getPlugin($plugin);
            if (!in_array($sName, $aNames)) {
                continue;
            }
            if (!empty($path)) {
                require_once($path);
            }
            $objects[] = new $className($user, $plugin);
        }
        return $objects;
    }

}

?>
