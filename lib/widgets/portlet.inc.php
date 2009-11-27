<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 *
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
    var $btns = '';

    // current action is the one we are currently on.
    function setActions($actions, $currentaction) {
        foreach ($actions as $action) {
            $aInfo = $action->getInfo();

            if ($aInfo !== null && !empty($aInfo['name'])) {
                if ($aInfo["ns"] == $currentaction) {
                    unset($aInfo["url"]);
                    $aInfo['active'] = true;
                }
                $aBtn = $action->getButton();

                if($aBtn){
                    $this->btns[$aInfo['name']] = array_merge($aInfo, $aBtn);
                }else{
                    $this->actions[$aInfo['name']] = $aInfo;
                }
            }
        }
        ksort($this->actions);
        ksort($this->btns);
    }

    /**
     * Render a button for a given action
     *
     * @param string $text
     * @param string $link
     * @param string $class
     * @return unknown
     */
    function renderBtn($text, $link, $class) {

        // Create button html
        $button = "<div class='portlet_button'>
            <a href='{$link}'>
                <div class='big_btn_left'></div>
                <div class='big_btn_middle'>
                    <div class='btn_text'>{$text}</div>
                </div>
                <div class='big_btn_right {$class}'></div>
            </a>
        </div>";

        return $button;
    }

    /**
     * Render the specified actions as buttons
     */
    function showButtons() {
        if(empty($this->btns)){
            return '';
        }

        $rendered = '';
        foreach ($this->btns as $btn){
            $text = !empty($btn['display_text']) ? $btn['display_text'] : $btn['name'];
            $link = $btn['url'];
            $class = $btn['arrow_class'];
            $rendered .= $this->renderBtn($text, $link, $class);
        }

        return $rendered;
    }

    function render() {
        if (empty($this->actions)) {
            return null;
        }

        $btn = $this->showButtons();

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/portlets/actions_portlet');
        $aTemplateData = array(
            'context' => $this,
            'btn' => $btn
        );

        return $oTemplate->render($aTemplateData);
    }
}

?>
