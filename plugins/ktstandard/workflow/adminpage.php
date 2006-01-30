<?php

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');


class WorkflowAllocationSelection extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;
    var $sSection = 'administration';

    function check() {
        $res = parent::check();
        if (!$res) { return false; }
        
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name'=> _('Automatic Workflow Assignments'));
        
        return true;
    }

    function do_main() { 
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('workflow', 'objectModification');

        $aFields = array();
        $aVocab = array();
        $aVocab[] = 'No automatic assignment';
        foreach ($aTriggers as $aTrigger) {
            $aVocab[$aTrigger[2]] = $aTrigger[0];
        }
        $aFields[] = new KTLookupWidget(_('Workflow Plugins'), _('Plugins providing workflow allocators.'),'selection_ns', $this->getHandler(), $this->oPage, true, null, null, array('vocab' => $aVocab));
        
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/workflow/allocator_selection');
        $oTemplate->setData(array(
            'context' => $this,
            'trigger_fields' => $aFields,
        ));
        return $oTemplate->render();        
    }
    
    function getHandler() {
        $sQuery = 'SELECT selection_ns FROM ' . KTUtil::getTableName('trigger_selection');
        $sQuery .= ' WHERE event_ns = ?';
        $aParams = array('ktstandard.workflowassociation.handler');
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'selection_ns');
        return $res;
        
    }
    
    function do_assign_handler() {
        $oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
        $aTriggers = $oKTTriggerRegistry->getTriggers('workflow', 'objectModification');
        
        $selection_ns = KTUtil::arrayGet($_REQUEST, 'selection_ns');
        if (empty($selection_ns)) {
            $sQuery = 'DELETE FROM ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' WHERE event_ns = ?';
            $aParams = array('ktstandard.workflowassociation.handler');
            DBUtil::runQuery(array($sQuery, $aParams));
            $this->successRedirectToMain(_('Handler removed.'));
        } else {
            if (!array_key_exists($selection_ns, $aTriggers)) {
                $this->errorRedirectToMain(_('Invalid assignment'));
            }
        
        
            // clear
            $sQuery = 'DELETE FROM ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' WHERE event_ns = ?';
            $aParams = array('ktstandard.workflowassociation.handler');
            DBUtil::runQuery(array($sQuery, $aParams));
            
            // set 
            $sQuery = 'INSERT INTO ' . KTUtil::getTableName('trigger_selection');
            $sQuery .= ' (event_ns, selection_ns)';
            $sQuery .= ' VALUES ("ktstandard.workflowassociation.handler",?)';
            $aParams = array($selection_ns);
            DBUtil::runQuery(array($sQuery, $aParams));
            $this->successRedirectToMain(_('Handler set.'));
        }
    }
}

?>
