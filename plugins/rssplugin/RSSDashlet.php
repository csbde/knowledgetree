<?php
/*
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_DIR. '/plugins/rssplugin/rss2array.inc.php');
require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');

class RSSDashlet extends KTBaseDashlet {
	var $oUser;
	
	function RSSDashlet(){
		$this->sTitle = _kt('RSS Feeds');
	}
	
	function is_active($oUser) {
		$this->oUser = $oUser;
		return true;
	}

	function render() {
		global $main;
        $main->requireJSResource("plugins/rssplugin/js/update.js");
		
		$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('RSSPlugin/dashlet');
       	
       	$iUId = $this->oUser->getId();
       	
       	// Get internal Feed
        $aInternalRSS = KTrss::getInternalFeed($iUId);

        // Get count of all items in feed
        $iCountItems = count($aInternalRSS[items]);
        
        // Get listing of external feeds
        $aExternalFeedsList = KTrss::getExternalFeedsList($iUId);
        
        // Create action for external feed management to be linked to inside of dashlet
        $action = array("name" => _kt("Manage External RSS Feeds"), "url" => $this->oPlugin->getPagePath('managerssfeeds'));
        
        // Prepare template data
		$aTemplateData = array(
			'context' => $this,
			'internalrss' => $aInternalRSS,
			'itemcount' => $iCountItems,
			'feedlist' => $aExternalFeedsList,
			'user' => $iUId,
			'action' => $action,
		);
      
        return $oTemplate->render($aTemplateData);
    }
}
?>
