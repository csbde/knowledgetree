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
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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

    function KTWorkflowTrigger() {
        $this->_oTriggerState = null;  // initialise to initial state
        $this->sFriendlyName = _kt('Base class for workflow triggers');
        $this->sDescription = _kt('This is an abstract base class for the overall workflow trigger.  It should never actually be available for installation.');
    }
    
    function loadConfig($oTriggerInstance) {
        $this->oTriggerInstance = $oTriggerInstance;
    }
    
    function isLoaded() { return (!is_null($this->oTriggerInstance)); }
    
    // simple function to inform the UI/registration what kind of event this is
    function getCapabilities() {
        return array(
            'guard' => $this->bIsGuard,
            'action' => $this->bIsAction,
            'name' => $this->sFriendlyName,
            'description' => $this->sDescription,
        );
    }
    
    // return true for transition allowed on doc, false for transition not allowed on doc.
    function allowTransition($oDocument, $oUser) {
        return true;  // abstract base class
    }
    
    /*
    Multiple triggers can occur on a given transition.  If this trigger fails,
    return a PEAR::error (the overall system -will- roll the db back - 
    no need to do it yourself) with a -useful- human error message.
    
    IF YOU SUCCEED, return a $aRollbackInfo array.  This will be passed
    to $this->rollbackTransition IF NEEDED (e.g. a later trigger failed.)
    This is to do your best to roll back any external changes (e.g. emails
    sent.)
     */
    function performTransition($oDocument, $oUser) {
        $rollbackinfo = null;
        return $rollbackinfo;
    }
    
    // roll back the transition.  $aRollbackInfo was returned by you earlier
    // after ->performTransition.
    // 
    // throw a PEAR::error to -inform- users of a critical problem, NOT to 
    // cause the system to rollback (that's already happened.)
    function rollbackTransition($oDocument, $oUser, $aRollbackInfo = null) {
        return true;
        // return PEAR::raiseError(_kt('A follow-up email has been sent, informing the previous recipient that the step was cancelled.'));
    }
}

?>
