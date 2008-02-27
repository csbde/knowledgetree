<?php

/**
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
        if(!$this->oDocument->getIsCheckedOut())
        {
	        $this->oDocument->setImmutable(true);
	        $this->oDocument->update();
	        controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
        }
        else
        {
        	$this->addErrorMessage(_kt('Document is checked out and cannot be made immutable'));
        	controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
        }
    }
}

