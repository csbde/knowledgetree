<?php

/*
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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


require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_DIR. '/plugins/rssplugin/KTrss.inc.php');

class ManageRSSFeedsDispatcher extends KTStandardDispatcher {
 
    function do_main() {
        // This line adds your page to the breadcrumbs list at the top
	    $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Manage External RSS Feeds','rssplugin'));	
		$iUId = $this->oUser->getId();
		
    	$oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('RSSPlugin/managerssfeeds');

        $aFeedsList = array();
        $aFeedsList = KTrss::getExternalFeedsList($iUId);

       	$aTemplateData = array(
				'context' => $this,
				'feedlist' => $aFeedsList,
			       );

        return $oTemplate->render($aTemplateData);
    }
	
	// Delete feed function
    function do_deleteFeed(){
       $iFeedId = KTUtil::arrayGet($_REQUEST, 'feed_id');
	   
       $res = KTrss::deleteFeed($iFeedId);
       
       if (PEAR::isError($res)) { 
            $this->errorRedirectToMain(sprintf(_kt('Unable to delete item: %s','rssplugin'), $res->getMessage())); 
       }
       else{
            $this->successRedirectToMain(sprintf(_kt('RSS feed deleted','rssplugin')));
       }
    }
    
    // Edit feed function
    function do_editFeed(){    
        $iFeedId = KTUtil::arrayGet($_REQUEST, 'feed_id');
        
        $add_fields = array();
        $add_fields[] =  new KTStringWidget(_kt('Title','rssplugin'),_kt('The title of the RSS feed','rssplugin'), 'title', KTrss::getExternalFeedTitle($iFeedId), $this->oPage, true, null, null);  
        $add_fields[] =  new KTStringWidget(_kt('URL','rssplugin'),_kt('The url of the RSS feed','rssplugin'), 'url', KTrss::getExternalFeedUrl($iFeedId), $this->oPage, false, null, null);
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("RSSPlugin/editfeed");
        $aTemplateData = array(
            "context" => &$this,
            "add_fields" => $add_fields,
            "feed_id" => $iFeedId,
            );
          return $oTemplate->render($aTemplateData);
    
    }
    
    // Update feed function on post
    function do_updateFeed(){    
        $iFeedId = KTUtil::arrayGet($_REQUEST, 'feed_id');      
        
        $aErrorOptions = array(
                'redirect_to' => array('editFeed', sprintf('feed_id=%s', $iFeedId))
        );
        
        $sTitle = $this->oValidator->validateString(
                KTUtil::arrayGet($_REQUEST, 'title'),
                KTUtil::meldOptions($aErrorOptions, array('message' => _kt("You must provide a title",'rssplugin')))
        );
        
        $sUrl =KTUtil::arrayGet($_REQUEST, 'url');
        
        $res = KTrss::updateFeed($iFeedId, $sTitle, $sUrl);

        if (PEAR::isError($res)) { 
            $this->errorRedirectToMain(sprintf(_kt('Unable to delete item: %s','rssplugin'), $res->getMessage())); 
        }
        else{
            $this->successRedirectToMain(sprintf(_kt('Updated news item.','rssplugin')));
        }
    }
    
    // Add feed function
    function do_addFeed(){
		$this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Manage RSS Feeds'));	
        $this->oPage->setBreadcrumbDetails(_kt("Create a new RSS feed",'rssplugin'));
        $this->oPage->setTitle(_kt("Create a link to a new RSS feed",'rssplugin'));
        
        $add_fields = array();
        $add_fields[] =  new KTStringWidget(_kt('Title','rssplugin'),_kt('The title of rss feed','rssplugin'), 'title', null, $this->oPage, true, null, null);
         
        $add_fields[] =  new KTStringWidget(_kt('URL','rssplugin'),_kt('The url to the rss feed','rssplugin'), 'url', null, $this->oPage, false, null, null);
    
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("RSSPlugin/addfeed");
        $aTemplateData = array(
            "context" => &$this,
            "add_fields" => $add_fields,
            );
	return $oTemplate->render($aTemplateData);
    
    }
    
    // Create feed on post
    function do_createFeed() {
		$iFeedId = KTUtil::arrayGet($_REQUEST, 'feed_id');
		// use the validator object
		$aErrorOptions = array('redirect_to' => array('addFeed'), 'message' => _kt('You must specify a title for the rss feed.','newsdashletplugin'));
		$sTitle = $this->oValidator->validateString(KTUtil::arrayGet($_REQUEST, 'title'), $aErrorOptions);
	
		$sUrl = KTUtil::arrayGet($_REQUEST, 'url');
		$res = KTrss::createFeed($sTitle, $sUrl, $this->oUser->getId());
	    
	    if (PEAR::isError($res)) { 
	        $this->errorRedirectToMain(sprintf(_kt('Unable to create feed: %s','rssplugin'), $res->getMessage())); 
	    }
	    else{
	        $this->successRedirectToMain(sprintf(_kt('Created new rss feed: %s','rssplugin'),  KTrss::getExternalFeedTitle($res)));
	    }
    }
}

?>
