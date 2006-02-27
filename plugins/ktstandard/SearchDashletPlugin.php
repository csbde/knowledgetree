<?php

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class SearchDashletPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.searchdashlet.plugin";

    function setup() {
        $this->registerDashlet('SearchDashlet', 'ktstandard.searchdashlet.dashlet', 'SearchDashlet.php');

        require_once(KT_LIB_DIR . "/templating/templating.inc.php");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('searchdashlet', '/plugins/searchdashlet/templates');
    }
}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('SearchDashletPlugin', 'ktstandard.searchdashlet.plugin', __FILE__);
?>
