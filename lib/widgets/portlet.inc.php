<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
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

/*  KT3 Basic Portlet
 *
 *  Very simple wrapper that establishes the absolutely basic API.
 *
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
        if (KTLOG_CACHE) $default->log->debug('portlet regging plugin: ' . $oPlugin->sNamespace);
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
    var $btn = '';

    // current action is the one we are currently on.
    function setActions($actions, $currentaction) {
        foreach ($actions as $action) {
            $aInfo = $action->getInfo();

            if ($aInfo !== null && !empty($aInfo['name'])) {
                if ($aInfo["ns"] == $currentaction) {
                    unset($aInfo["url"]);
                    $aInfo['active'] = true;
                }
                $this->actions[$aInfo['name']] = $aInfo;
            }
        }
        ksort($this->actions);
    }

    /**
     * Display a button for a given action
     *
     * @param array $action
     * @param string $btn
     * @return boolean
     */
    function setButton($action, $btn) {
        // Ensure action is set
        if(!isset($action[0])){
            return false;
        }

        $info = $action[0]->getInfo();

        // Ensure user has permission on / access to the action
        if(empty($info)){
            return false;
        }

        $link = $info['url'];
        $text = $info['name'];

        switch($btn){
            case 'document_checkin':
                $text = _kt('Checkin Document');
                $class = 'arrow_upload';
                break;
            case 'folder_upload':
                $text = _kt('Upload Document');
                $class = 'arrow_upload';
                break;
            case 'document_download':
                $text = _kt('Download Document');
                $class = 'arrow_download';
                break;
            default:
                return false;
        }

        // Create button html
        $button = "<div class='portlet_button'>
            <a href='$link'>
                <div class='big_btn_left'></div>
                <div class='big_btn_middle'>
                    <div class='btn_text'>{$text}
                    </div>
                    <div class='{$class}'>
                    </div>
                </div>
                <div class='big_btn_right'></div>
            </a>
        </div>";

        $this->btn = $button;
        return true;
    }

    function render() {
        if (empty($this->actions)) {
            return null;
        }
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/portlets/actions_portlet');
        $aTemplateData = array(
            'context' => $this,
        );

        // Display a button above the action list
        if(isset($this->btn) && !empty($this->btn)){
            $aTemplateData['showBtn'] = true;
            $aTemplateData['btn'] = $this->btn;
        }

        return $oTemplate->render($aTemplateData);
    }
}

?>
