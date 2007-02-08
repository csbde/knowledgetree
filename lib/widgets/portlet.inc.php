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

/*  KT3 Basic Portlet
 *
 *  Very simple wrapper that establishes the absolutely basic API.
 *
 *  author: Brad Shuttleworth <brad@jamwarehouse.com>
 */
 
require_once(KT_LIB_DIR . "/templating/templating.inc.php");


// FIXME need to establish some kind of api to pass in i18n information.
class KTPortlet {
    var $sTitle;
    var $oPlugin;
    var $bActive = false;
        
    function KTPortlet($title='') {
        $this->sTitle = $title;
    }

    function setPlugin(&$oPlugin) {
        global $default;
        $default->log->debug('portlet regging plugin: ' . $oPlugin->sNamespace);
        $this->oPlugin =& $oPlugin;
    }
    
    // this should multiplex i18n_title
    function getTitle() { return $this->sTitle; }
    
    function render() {
        return '<p class="ktError">Warning:  Abstract Portlet created.</p>';
    }

    function setDispatcher(&$oDispatcher) {
        $this->oDispatcher =& $oDispatcher; 
    }
    
    function getActive() {
        return $this->bActive;
    }
}


/* Encapsulates the logic for showing navigation items.
 *
 */
class KTNavPortlet extends KTPortlet {

    // list of dict {url:'',label:''}
    var $navItems = Array();

    function setOldNavItems($aNavLinks) {

        $this->navItems = array_map(array(&$this, "_oldNavZip"), $aNavLinks["descriptions"], $aNavLinks["links"]);
        
    }
    
    // legacy support helper
    function _oldNavZip($d, $u) {
        $aZip = array(
            "label" => $d, 
            "url" => $u,
        );
        return $aZip;
    }
    
    function render() {
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/nav_portlet");
        $aTemplateData = array(
            "context" => $this,
        );

        return $oTemplate->render($aTemplateData);        
    }
}

class KTActionPortlet extends KTPortlet {
    var $actions = array();
    
    var $bActive = true;

    // current action is the one we are currently on.
    function setActions($actions, $currentaction) {
        foreach ($actions as $action) {
            $aInfo = $action->getInfo();

            if ($aInfo !== null) {
                if ($aInfo["ns"] == $currentaction) {
                    unset($aInfo["url"]);
                    $aInfo['active'] = true;
                }
                $this->actions[$aInfo['name']] = $aInfo;
            }
        }
        ksort($this->actions);
    }
    
    function render() {
        if (empty($this->actions)) {
            return null;
        }
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/actions_portlet");
        $aTemplateData = array(
            "context" => $this,
        );

        return $oTemplate->render($aTemplateData);     
    }
}

?>
