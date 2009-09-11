<?php
/*
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
require_once(KT_LIB_DIR . "/actions/documentviewlet.inc.php");
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

class instaViewLinkAction extends KTDocumentAction {
    var $sName = 'instaview.processor.link';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = "Instant View";
    var $pluginDir;
    
    function getDisplayName() {
        //get document object
        $oDocument = $this->oDocument;
        
        // get document id
        if(!isset($oDocument)){
        	return 'Instant View';
        }
        $iFId = $oDocument->getID();
       
        // return link...
        return "InstaView ".$this->getImageLink($iFId, 'document');
    }
    
    function do_main() {
    	//get document object
        $oDocument = $this->oDocument;
       global $default;
        // get document id
        $iDId = $oDocument->getID();
    	$oUser = User::get($_SESSION['userID']);
		if(Pear::isError($oUser)){
			die("Invalid user");
		}
		$dir = KTPlugin::_fixFilename(__FILE__);
        $this->pluginDir = dirname($dir) . '/';
        
		//check permissions on the document
		if(KTPermissionUtil::userHasPermissionOnItem($oUser,'ktcore.permissions.read',$oDocument) == true){
			// NOTE this is to turn the config setting for the PDF directory into a proper URL and not a path
			$pdfDir = str_replace($default->varDirectory, 'var/', $default->pdfDirectory);
			$swfFile = $pdfDir .'/'. $iDId.'.swf';
			return instaViewlet::display_viewlet($swfFile,$this->pluginDir);			
		}else{
			die("You don't have permission to view this document.");
		}     
     
    }
     // get instaView link for a document
    function getViewLink($iItemId, $sItemType){
        $item = strToLower($sItemType);
        if($item == 'folder'){
        	$sItemParameter = '?folderId';
        }else if($item == 'document'){
        	$sItemParameter = '?instaview.processor.link&fDocumentId';
        }

        // built server path
        global $default;
        $sHostPath = "http" . ($default->sslEnabled ? "s" : "") . "://" . $_SERVER['HTTP_HOST'];

        // build link
    	$sLink = $sHostPath.KTBrowseUtil::buildBaseUrl('action.php?kt_path_info').$sItemParameter.'='.$iItemId;

    	return $sLink;
    }

    // get rss icon link
    function getImageLink($iItemId, $sItemType){
    	return "<a href='".$this->getViewLink($iItemId, $sItemType)."' target='_blank'>".$this->getIcon()."</a>";
    }
    // get icon for instaView
	function getIcon(){
    	// built server path
        global $default;
    	$sHostPath = "http" . ($default->sslEnabled ? "s" : "") . "://".$_SERVER['HTTP_HOST']."/".$GLOBALS['KTRootUrl']."/";

        // create image
        $icon = "<img src='".$sHostPath."resources/graphics/edit-find.png' alt='InstaView' border=0/>";

        return $icon;
    }
}

/**
 * Main display class for instantView
 *
 */
class instaViewlet extends KTDocumentViewlet {
    var $sName = 'instaView.viewlets';

    function display_viewlet($flashdocument,$plugin) {
    	global $main;
        
        $main->requireJSResource($plugin."/resources/swfobject.js");
		$oKTTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oKTTemplating->loadTemplate('instaview_viewlet');
        if (is_null($oTemplate)) return '';
        
        $aTemplatesetData =array('document' =>$flashdocument,
        							'mainobject' => $plugin."/resources/zviewer.swf",
        							'defaultobject' => $plugin."/resources/expressinstall.swf");
        $output = $oTemplate->render($aTemplatesetData);
        return $output;
    }
}
?>
