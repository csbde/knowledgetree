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
        
    function KTPortlet($title='') {
        $this->sTitle = $title;
    }

    function setPlugin(&$oPlugin) {
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

    // current action is the one we are currently on.
    function setActions($actions, $currentaction) {
        foreach ($actions as $action) {
            $aInfo = $action->getInfo();
            
            if ($aInfo !== null) {
                if ($aInfo["name"] == $currentaction) {
                    unset($aInfo["url"]);
                }
                $this->actions[] = $aInfo;
            }
        }
        //var_dump($this->actions);
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
