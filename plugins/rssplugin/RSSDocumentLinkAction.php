<?php
/*
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
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
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
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
        	return 'RSS';
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
