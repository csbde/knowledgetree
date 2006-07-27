<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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
    var $sHelpPage = 'ktcore/admin/manage plugins.html';

    function check() {
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt('Plugins'),
        );
        return parent::check();
    }

    function do_main() {
        $aPlugins = KTPluginEntity::getAvailable();
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
        $this->successRedirectToMain(_kt('Plugins updated'));
    }

    function do_reread() {
        KTPluginUtil::registerPlugins();
        $this->successRedirectToMain(_kt('Plugins read from the filesystem'));
    }
}

?>
