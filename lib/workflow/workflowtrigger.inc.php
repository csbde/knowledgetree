<?php
/**
 * $Id: workflowtrigger.inc.php 5268 2006-04-18 13:42:22Z nbm $
 *
 * Provides a base class for workflow triggers.  This includes
 * the ability to serialise configuration arrays into the db
 * and to be restored.
 *
 * This class will be subclassed - any configuration that is performed
 * should be saved through the associated KTWorkflowTriggerInstance.
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
 * @version $Revision: 5268 $
 * @author Brad Shuttleworth, Jam Warehouse (Pty) Ltd, South Africa
 */

//require_once(KT_LIB_DIR . '/workflow/workflowtriggerinstance');

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
    
    function getName() { return $this->sFriendlyName; }    
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
