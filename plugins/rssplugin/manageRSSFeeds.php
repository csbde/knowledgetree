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
