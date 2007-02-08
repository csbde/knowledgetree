<?php

/**
 * $Id$
 *
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
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

// FIXME should we refactor this into a separate file?  Do we gain anything?

class KTAdminNavigationRegistry {
    var $aResources = array();
    var $aCategorisation = array();
    var $aCategories = array();

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTAdminNavigationRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTAdminNavigationRegistry'] = new KTAdminNavigationRegistry;
        }
        return $GLOBALS['_KT_PLUGIN']['oKTAdminNavigationRegistry'];
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
