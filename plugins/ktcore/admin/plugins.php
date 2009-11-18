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

        // Update disabled plugins
        $sIds = implode(',', $aIds);
        $sQuery = "UPDATE $sTable SET disabled = 1 WHERE list_admin = 1 AND id NOT IN ($sIds)";
        DBUtil::runQuery(array($sQuery));

        // Select disabled plugins that have been enabled
        $sQuery = "SELECT * FROM $sTable WHERE disabled = 1 AND id IN ($sIds)";
        $res = DBUtil::getResultArray($sQuery);

        if(!PEAR::isError($res)){
            // Enable the disabled plugins
            $sQuery = "UPDATE $sTable SET disabled = 0 WHERE id IN ($sIds)";
            DBUtil::runQuery(array($sQuery));

            // run setup for each plugin
            $aEnabled = array();
            if(!empty($res)){
                foreach ($res as $item){
                    $aEnabled[] = $item['id'];
                }

                $sEnabled = implode(',', $aEnabled);

                $sQuery = "SELECT h.classname, h.pathname FROM $sTable p
                    INNER JOIN plugin_helper h ON (p.namespace = h.plugin)
                    WHERE classtype = 'plugin' AND p.id IN ($sEnabled)";
                $res = DBUtil::getResultArray($sQuery);

                if(!PEAR::isError($res)){
                    foreach($res as $item){
                        $classname = $item['classname'];
                        $path = $item['pathname'];

                        if (!empty($path)) {
                            require_once($path);
                        }

                    	$oPlugin = new $classname($path);
                    	$oPlugin->setup();
                    }
                }
            }
        }
        KTPluginEntity::clearAllCaches();

        // FIXME!!! Plugin manager needs to be updated to deal with this situation. This code should be in the plugin.
        //enabling or disabling Tag fieldset depending on whether tag cloud plugin is enabled or disabled.
        //Get tag cloud object
        $oTagClouPlugin = KTPluginEntity::getByNamespace('ktcore.tagcloud.plugin');
        if(!PEAR::isError($oTagClouPlugin) && !is_a($oTagClouPlugin, 'KTEntityNoObjects') && !is_null($oTagClouPlugin)){
            if($oTagClouPlugin->getDisabled() == '1')
            {
            	//disable tag fieldset
            	$aFV = array(
                    'disabled' => true,
                );
                $aWFV = array(
                    'namespace' => 'tagcloud'
                );
            	$res = DBUtil::whereUpdate('fieldsets', $aFV, $aWFV);
            } else {
            	//enable tag fieldset
            	$aFV = array(
                    'disabled' => false,
                );
                $aWFV = array(
                    'namespace' => 'tagcloud'
                );
            	$res = DBUtil::whereUpdate('fieldsets', $aFV, $aWFV);
            }
        }

        // we reregister the plugins to ensure they are in the correct order
        KTPluginUtil::registerPlugins();
        $this->successRedirectToMain(_kt('Plugins updated'));
    }

    function do_reread() {
        /**
         * The plugin re-register is now handled by the super global _force_plugin_truncate
         * in pluginutil.inc.php
         * KTPluginUtil::registerPlugins();
        */
        $this->successRedirectToMain(_kt('Plugins read from the filesystem'));
    }
}

?>
