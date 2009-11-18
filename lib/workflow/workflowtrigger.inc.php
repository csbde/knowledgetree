<?php
/**
 * $Id$
 *
 * Provides a base class for workflow triggers.  This includes
 * the ability to serialise configuration arrays into the db
 * and to be restored.
 *
 * This class will be subclassed - any configuration that is performed
 * should be saved through the associated KTWorkflowTriggerInstance.
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
 */

//require_once(KT_LIB_DIR . '/workflow/workflowtriggerinstance');
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTWorkflowTrigger {
    var $sNamespace = 'ktcore.workflowtriggers.abstractbase';
    var $sFriendlyName;
    var $sDescription;
    var $oTriggerInstance;
    var $aConfig = array();
    
    // generic requirements - both can be true
    var $bIsGuard = false;
    var $bIsAction = false;
    var $bIsConfigurable = true;

    function KTWorkflowTrigger() {
        $this->_oTriggerState = null;  // initialise to initial state
        $this->sFriendlyName = _kt('Base class for workflow triggers');
        $this->sDescription = _kt('This is an abstract base class for the overall workflow trigger.  It should never actually be available for installation.');
    }
    
    function loadConfig($oTriggerInstance) {
        $this->oTriggerInstance = $oTriggerInstance;
        $this->aConfig = $oTriggerInstance->getConfig();
    }
    
    function isLoaded() { return (!is_null($this->oTriggerInstance)); }
    
    // simple function to inform the UI/registration what kind of event this is
    function getInfo() {
        return array(
            'guard' => $this->bIsGuard,
            'action' => $this->bIsAction,
            'name' => $this->sFriendlyName,
            'description' => $this->sDescription,
        );
    }
    
    function getName() { return sanitizeForSQLtoHTML($this->sFriendlyName); }    
    function getNamespace() { return $this->sNamespace; }    
    function getConfigId() { return $this->oTriggerInstance->getId(); }    
    
    // return true for transition allowed on doc, false for transition not allowed on doc.
    function allowTransition($oDocument, $oUser) {
        return true;  // abstract base class
    }
    
    // perform more expensive checks -before- performTransition.
    function precheckTransition($oDocument, $oUser) {
        return true;
    }
    
    /*
    Multiple triggers can occur on a given transition.  If this trigger fails,
    return a PEAR::error (the overall system -will- roll the db back - 
    no need to do it yourself) with a -useful- human error message.
    
    Any other return is simply discarded.
     */
    function performTransition($oDocument, $oUser) {
        return true;
    }
    
    // display the configuration page for this plugin
    // will be called -after- loadConfig, so it can prepopulate the options.
    function displayConfiguration($args) {
        return _kt('No configuration has been implemented for this plugin.');
    }    
    
    // dispatched - again, after loadConfig, so it can set the config.
    // throw an error to redispatch displayConfiguration, or return true to cause a db commit (probably).
    function saveConfiguration() {
        return true;
    }
    
    // give a brief, friendly description of what we are and what we do.
    function getConfigDescription() {
        return '';    
    }
}

?>
