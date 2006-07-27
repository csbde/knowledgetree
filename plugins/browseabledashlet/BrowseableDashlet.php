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

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

class BrowseableFolderDashlet extends KTBaseDashlet {
	var $oUser;
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		
		return true;
	}
	
    function render() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('browseabledashlet/dashlet');

        $aFolders = KTBrowseUtil::getBrowseableFolders($this->oUser);
        if (PEAR::isError($aFolders)) { 
            // just hide it.
            $aFolders = array();
        }

        if (empty($aFolders)) {
            return;
        }
        
        $aTemplateData = array(
            'folders' => $aFolders,
        );
        return $oTemplate->render($aTemplateData);
    }
}

?>
