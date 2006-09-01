<?php

/**
 * $Id: documentaction.inc.php 5848 2006-08-16 15:58:51Z bshuttle $
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
 
require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");

class KTDocumentViewlet extends KTDocumentAction {
    var $sName;
    var $sDescription;

    var $_sShowPermission = "ktcore.permissions.read";
    
    // the only major distinction of the viewlet vs. the action is the
    // display_viewlet() method.

    function display_viewlet() {
        return "";
    }
}

?>
