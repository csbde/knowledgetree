<?php
/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');

class RSSDocumentLinkAction extends KTDocumentAction {
    var $sName = 'ktcore.rss.plugin.document.link';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = "RSS";
    
    function getDisplayName() {
        //get document object
        $oDocument = $this->oDocument;
        
        // get document id
        if(!isset($oDocument)){
        	return null;
        }
        $iFId = $oDocument->getID();
        
        // return link...there MIGHT be a nicer way of doing this?
        return "RSS ".KTrss::getImageLink($iFId, 'document');
    }
    
    function do_main() {
    	//get document object
        $oDocument = $this->oDocument;
        
        // get document id
        $iDId = $oDocument->getID();
    	
    	$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('RSSPlugin/rssdocumentaction');
       	
       	$aTemplateData = array(
			'context' => $this,
			'link' => KTrss::getRssLink($iDId, 'document'),
			'linkIcon' => KTrss::getImageLink($iDId, 'document'),
		);
      
        return $oTemplate->render($aTemplateData);
    }
}
?>