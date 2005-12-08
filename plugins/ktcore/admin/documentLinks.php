<?php

/*
 * Document Link Type management
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @version $Revision$
 * @author Brad Shuttleworth <brad@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package documentmanagement
 */

/* boilerplate */
//require_once('../../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

require_once(KT_LIB_DIR . '/documentmanagement/LinkType.inc');  // a horrible piece of work.

class KTDocLinkAdminDispatcher extends KTAdminDispatcher {

   // Breadcrumbs base - added to in methods
    function check() {
        return true;
    }

    function do_main() {
        $this->aBreadcrumbs[] = array('name' => _('Document Links'));
        $this->oPage->setBreadcrumbDetails(_("view"));
        
        $aLinkTypes =& LinkType::getList('id > 0');
        
        $addLinkForm = array();
        // KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) 
        $addLinkForm[] = new KTStringWidget('Name',_('A short, human-readable name for the link type.'), 'fName', null, $this->oPage, true);
        $addLinkForm[] = new KTStringWidget('Description',_('A short brief description of the relationship implied by this link type.'), 'fDescription', null, $this->oPage, true);
        
        
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
           $this->errorRedirectToMain(_("Please specify a link type to edit."));
        }
        
        $oLinkType =& LinkType::get($link_id);
        
        $this->aBreadcrumbs[] = array('name' => _('Document Links'));
        $this->oPage->setBreadcrumbDetails(_("view"));
        
        $aLinkTypes =& LinkType::getList('id > 0');
        
        $editLinkForm = array();
        // KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) 
        $editLinkForm[] = new KTStringWidget('Name',_('A short, human-readable name for the link type.'), 'fName', $oLinkType->getName(), $this->oPage, true);
        $editLinkForm[] = new KTStringWidget('Description',_('A short brief description of the relationship implied by this link type.'), 'fDescription', $oLinkType->getDescription(), $this->oPage, true);
        
        
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
            $this->errorRedirectToMain(_("Please specify a link type to update."));
        }
        
        $name = KTUtil::arrayGet($_REQUEST, 'fName');        
        $description = KTUtil::arrayGet($_REQUEST, 'fDescription');

        if (empty($name) || empty($description)) { // for bonus points, make this go to edit, and edit catch it.
            $this->errorRedirectToMain(_('Please enter information for all fields.'));
        }
        
        $oLinkType =& LinkType::get($link_id);
        
        $oLinkType->setName($name);
        $oLinkType->setDescription($description);
        $oLinkType->update();
        
        $this->successRedirectToMain(_("Link Type updated."));
    }
    
    function do_add() {
        $name = KTUtil::arrayGet($_REQUEST, 'fName');        
        $description = KTUtil::arrayGet($_REQUEST, 'fDescription');

        if (empty($name) || empty($description)) {
            $this->errorRedirectToMain(_('Please enter information for all fields.'));
        }
        
        $oLinkType = new LinkType($name, $description);
        $oLinkType->create();
             
        //$oLinkType =& LinkType::createFromArray(array("sName" => $name, "sDescription" => $description));
        
        $this->successRedirectToMain(_("Link Type created."));
    }
    
    function do_delete() {
        $types_to_delete = KTUtil::arrayGet($_REQUEST, 'fLinksToDelete');         // is an array.

        if (empty($types_to_delete)) {
            $this->errorRedirectToMain(_('Please select one or more link types to delete.'));
        }
        
        $count = 0;
        foreach ($types_to_delete as $link_id) {
            $oLinkType = LinkType::get($link_id);
            $oLinkType->delete(); // technically, this is a bad thing
            $count += 1; 
        }
        
        //$oLinkType =& LinkType::createFromArray(array("sName" => $name, "sDescription" => $description));
        
        $this->successRedirectToMain($count . " " . _("Link types deleted."));
    }


}

// use the new admin framework.
//$d = new KTDocLinkAdminDispatcher();
//$d->dispatch();

?>
