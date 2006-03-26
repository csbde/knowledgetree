<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginentity.inc.php');

require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");

class KTPluginDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Plugins'),
        );
        return parent::check();
    }

    function do_main() {
        $aPlugins = KTPluginEntity::getList();
        $aEnabledPluginIds = KTPluginEntity::getEnabledPlugins();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate('ktcore/plugins/list');
        $oTemplate->setData(array(
            'context' => $this,
            'plugins' => $aPlugins,
            'enabled_plugins' => $aEnabledPluginIds,
        ));
        return $oTemplate;
    }

    function do_update() {
        $sTable = KTUtil::getTableName('plugins');
        $aIds = (array) KTUtil::arrayGet($_REQUEST, 'pluginids');
        KTPluginEntity::setEnabled($aIds);
        $this->successRedirectToMain('Plugins updated');
    }

    function do_reread() {
        KTPluginUtil::registerPlugins();
        $this->successRedirectToMain('Plugins read from the filesystem');
    }
}

?>
