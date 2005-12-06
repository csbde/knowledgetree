<?php

require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

$oPRegistry =& KTPortletRegistry::getSingleton();

class KTSearchPortlet extends KTPortlet {

    function KTSearchPortlet() {
        parent::KTPortlet(_("Search"));
    }
    function render() {
        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/search_portlet");
        $aTemplateData = array(
            "context" => $this,
        );

        return $oTemplate->render($aTemplateData);
    }
}

$oPRegistry->registerPortlet(array('browse', 'dashboard'), 'KTSearchPortlet', 'ktcore.portlets.search', '/plugins/ktcore/KTPortlets.php');

