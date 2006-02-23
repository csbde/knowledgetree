<?php

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
