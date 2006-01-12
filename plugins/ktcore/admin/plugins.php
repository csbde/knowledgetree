<?php

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
            'name' => _('Plugins'),
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
        $aIds = KTUtil::arrayGet($_REQUEST, 'pluginids');
        $sIds = DBUtil::paramArray($aIds);
        $sQuery = sprintf('UPDATE %s SET disabled = 1 WHERE id NOT IN (%s)', $sTable, $sIds);
        DBUtil::runQuery(array($sQuery, $aIds));
        $sQuery = sprintf('UPDATE %s SET disabled = 0 WHERE id IN (%s)', $sTable, $sIds);
        DBUtil::runQuery(array($sQuery, $aIds));
        $this->successRedirectToMain('Plugins updated');
    }

    function do_reread() {
        KTPluginUtil::registerPlugins();
        $this->successRedirectToMain('Plugins read from the filesystem');
    }
}

?>
