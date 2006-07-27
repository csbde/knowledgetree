<?php

/*
 * Document Link Type management
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
 *
 * @author Brad Shuttleworth <brad@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . '/documentmanagement/LinkType.inc');  // a horrible piece of work.

class KTDocLinkAdminDispatcher extends KTAdminDispatcher {
    var $sHelpPage = 'ktcore/admin/link type management.html';

   // Breadcrumbs base - added to in methods
    function check() {
        return true;
    }

    function do_main() {
        $this->aBreadcrumbs[] = array('name' => _kt('Document Links'));
        $this->oPage->setBreadcrumbDetails(_kt("view"));
        
        $aLinkTypes =& LinkType::getList('id > 0');
        
        $addLinkForm = array();
        // KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) 
        $addLinkForm[] = new KTStringWidget(_kt('Name'), _kt('A short, human-readable name for the link type.'), 'fName', null, $this->oPage, true);
        $addLinkForm[] = new KTStringWidget(_kt('Description'), _kt('A short brief description of the relationship implied by this link type.'), 'fDescription', null, $this->oPage, true);
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/linktypesadmin');       
        $oTemplate->setData(array(
            "context" => $this,
            "add_form" => $addLinkForm,
            "links" => $aLinkTypes,
        ));
        return $oTemplate;
    }
    
    function do_edit() {
        $link_id = KTUtil::arrayGet($_REQUEST, 'fLinkTypeId', null, false);
        if ($link_id === null) {
           $this->errorRedirectToMain(_kt("Please specify a link type to edit."));
        }
        
        $oLinkType =& LinkType::get($link_id);
        
        $this->aBreadcrumbs[] = array('name' => _kt('Document Links'));
        $this->oPage->setBreadcrumbDetails(_kt("view"));
        
        $aLinkTypes =& LinkType::getList('id > 0');
        
        $editLinkForm = array();
        // KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) 
        $editLinkForm[] = new KTStringWidget(_kt('Name'), _kt('A short, human-readable name for the link type.'), 'fName', $oLinkType->getName(), $this->oPage, true);
        $editLinkForm[] = new KTStringWidget(_kt('Description'), _kt('A short brief description of the relationship implied by this link type.'), 'fDescription', $oLinkType->getDescription(), $this->oPage, true);
        
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktcore/document/admin/linktypesadmin');       
        $oTemplate->setData(array(
            "context" => $this,
            "edit_form" => $editLinkForm,
            "old_link" => $oLinkType,
            "links" => $aLinkTypes,
        ));
        return $oTemplate;
    }    
    

    function do_update() {
        $link_id = KTUtil::arrayGet($_REQUEST, 'fLinkTypeId', null, false);
        if ($link_id === null) {
            $this->errorRedirectToMain(_kt("Please specify a link type to update."));
        }
        
        $name = KTUtil::arrayGet($_REQUEST, 'fName');        
        $description = KTUtil::arrayGet($_REQUEST, 'fDescription');

        if (empty($name) || empty($description)) { // for bonus points, make this go to edit, and edit catch it.
            $this->errorRedirectToMain(_kt('Please enter information for all fields.'));
        }
        
        $oLinkType =& LinkType::get($link_id);
        
        $oLinkType->setName($name);
        $oLinkType->setDescription($description);
        $oLinkType->update();
        
        $this->successRedirectToMain(_kt("Link Type updated."));
    }
    
    function do_add() {
        $name = KTUtil::arrayGet($_REQUEST, 'fName');        
        $description = KTUtil::arrayGet($_REQUEST, 'fDescription');

        if (empty($name) || empty($description)) {
            $this->errorRedirectToMain(_kt('Please enter information for all fields.'));
        }
        
        $oLinkType = new LinkType($name, $description);
        $oLinkType->create();
             
        //$oLinkType =& LinkType::createFromArray(array("sName" => $name, "sDescription" => $description));
        
        $this->successRedirectToMain(_kt("Link Type created."));
    }
    
    function do_delete() {
        $types_to_delete = KTUtil::arrayGet($_REQUEST, 'fLinksToDelete');         // is an array.

        if (empty($types_to_delete)) {
            $this->errorRedirectToMain(_kt('Please select one or more link types to delete.'));
        }
        
        $count = 0;
        foreach ($types_to_delete as $link_id) {
            $oLinkType = LinkType::get($link_id);

            foreach(DocumentLink::getList(sprintf("link_type_id = %d", $link_id)) as $oLink) {
                $oLink->delete();
            }
            
            $oLinkType->delete(); // technically, this is a bad thing
            $count += 1; 
        }
        
        //$oLinkType =& LinkType::createFromArray(array("sName" => $name, "sDescription" => $description));
        
        $this->successRedirectToMain($count . " " . _kt("Link types deleted."));
    }


}

?>
