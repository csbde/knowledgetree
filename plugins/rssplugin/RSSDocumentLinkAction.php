<?php
/**
 *
 * Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.knowledgetree.com/
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