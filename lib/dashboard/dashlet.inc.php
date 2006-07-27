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

/* base class for dashlets. */
require_once(KT_LIB_DIR . "/dashboard/dashletregistry.inc.php");

class KTBaseDashlet {
    var $oPlugin;
    
    function setPlugin($oPlugin) { $this->oPlugin =& $oPlugin; }
    
    // precondition check.
    function is_active($oUser) { return true; }
    function render() { return '<div class="ktError"><p>' . _kt("This Dashlet is incomplete.") . '</p></div>'; }
}

//$oDashletRegistry =& KTDashletRegistry::getSingleton();
//$oDashletRegistry->registerDashlet('KTBaseDashlet','ktcore.dashlets.abstractbase',__FILE__, null)

?>
