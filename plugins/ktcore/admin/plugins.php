<?php
/**
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
