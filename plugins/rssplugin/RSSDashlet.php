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
