<?php

/* base class for dashlets. */
require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php");

class KTBaseDashlet {
    var $oPlugin;
    
    function setPlugin($oPlugin) { $this->oPlugin =& $oPlugin; }
    
    // precondition check.
    function is_active($oUser) { return true; }
    function render() { return '<div class="ktError"><p>' . _("This Dashlet is incomplete.") . '</p></div>'; }
}

//$oDashletRegistry =& KTDashletRegistry::getSingleton();
//$oDashletRegistry->registerDashlet('KTBaseDashlet','ktcore.dashlets.abstractbase',__FILE__, null)

?>
