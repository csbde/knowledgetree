<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
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
 *         http://www.ktdms.com/
 */

// FIXME should we refactor this into a separate file?  Do we gain anything?

class KTAdminNavigationRegistry {
    var $aResources = array();
    var $aCategorisation = array();
    var $aCategories = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTAdminNavigationRegistry')) {
            $GLOBALS['oKTAdminNavigationRegistry'] = new KTAdminNavigationRegistry;
        }
        return $GLOBALS['oKTAdminNavigationRegistry'];
    }

    // name is the suburl below admin
    // namespace, class, category, title, description
    // if category is specified, it looks for an item with THAT NAME for its details.
    function registerLocation($sName, $sClass, $sCategory, $sTitle, $sDescription, $sDispatcherFilePath = null, $sURL = null) {
        $sFullname = $sCategory . '/' . $sName;
        $aInfo = array(
            "name" => $sName,
            "class" => $sClass,
            "title" => $sTitle,
            "description"=> $sDescription, 
            "filepath" => $sDispatcherFilePath, 
            "url" => $sURL,
            "fullname" => $sFullname);     
        $this->aResources[$sFullname] = $aInfo;
        // is this a toplevel item?
        if ($sCategory != null) {
            if (!array_key_exists($sCategory, $this->aCategories)) { 
                $this->registerCategory($sCategory, $sCategory, ''); 
            }
            $this->aCategorisation[$sCategory][] = $aInfo;
        } 
    }

    function isRegistered($sName) {
        if (KTUtil::arrayGet($this->aResources, $sName)) {
            return true;
        }
        return false;
    }
    
    function registerCategory($sName, $sTitle, $sDescription) {
        $this->aCategories[$sName] = array("title" => $sTitle, "description" => $sDescription, "name" => $sName);
    }
    function getCategories() { return $this->aCategories; }
    function getCategory($sCategory) { return $this->aCategories[$sCategory]; }
    function getItemsForCategory($sCategory) { return $this->aCategorisation[$sCategory]; }
    
    function getDispatcher($sName) {
        // FIXME this probably needs to use require_once mojo.
        $aInfo = $this->aResources[$sName];
        if ($aInfo["filepath"] !== null) { require_once($aInfo["filepath"]); }
        if ($aInfo["url"] !== null) { 
           return new RedirectingDispatcher($aInfo["url"]);
        }
        return new $aInfo["class"]; 
    }
}

class RedirectingDispatcher {
    var $url = '';
 
    function RedirectingDispatcher($sURL) {
        $this->url = $sURL;
    }
    
    function dispatch() {
        redirect($this->url);
    }
}

?>