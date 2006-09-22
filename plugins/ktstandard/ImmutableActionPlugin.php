<?php

/**
 * $Id: KTBulkExportPlugin.php 5758 2006-07-27 10:17:43Z bshuttle $
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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php'); 

class KTImmutableActionPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.immutableaction.plugin";

    function KTImmutableActionPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Immutable action plugin');
        return $res;
    }

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentImmutableAction', 'ktcore.actions.document.immutable');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTImmutableActionPlugin', 'ktstandard.immutableaction.plugin', __FILE__);

class KTDocumentImmutableAction extends KTDocumentAction {
    var $sName = "ktcore.actions.document.immutable";
    var $_bMutator = true;
    var $_sShowPermission = 'ktcore.permissions.security';
    
    function getDisplayName() {
        return _kt('Make immutable');
    }

    function do_main() {
        $this->oDocument->setImmutable(true);
        $this->oDocument->update();
        controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
    }
}

