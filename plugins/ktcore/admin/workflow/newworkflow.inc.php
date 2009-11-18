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
 */

// core
require_once(KT_LIB_DIR . "/dispatcher.inc.php");

// workflow
require_once(KT_LIB_DIR . '/workflow/workflow.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstate.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowtransition.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowstatepermissionsassignment.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowadminutil.inc.php');

// other
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/groups/Group.inc');
require_once(KT_LIB_DIR . '/roles/Role.inc');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');
require_once(KT_LIB_DIR . '/widgets/forms.inc.php');

class KTNewWorkflowWizard extends KTAdminDispatcher {
    function predispatch() {
        $this->persistParams(array('fWizardKey'));
    }

    function &form_step1() {
        $oForm = new KTForm;

        $oForm->setOptions(array(
            'action' => 'process_step1',
            'cancel_url' => KTUtil::addQueryStringSelf(''), // NBM:  is there a cleaner way to reference the parent?
            'fail_action' => 'main',
            'label' => _kt('Workflow Details'),
            'submit_label' => _kt('Next'),
            'description' => _kt('This first step requires that you provide basic details about the workflow: its name, etc.'),
            'context' => $this,
        ));
        $oForm->setWidgets(array(
            array('ktcore.widgets.string',array(
                'label' => _kt('Workflow Name'),
                'description' => _kt('Each workflow must have a unique name.'),
                'required' => true,
                'name' => 'workflow_name',
            )),
            array('ktcore.widgets.text',array(
                'label' => _kt('States'),
                'description' => _kt('As documents progress through their lifecycle, they pass through a number of <strong>states</strong>.  These states describe a step in the process the document must follow.  Examples of states include "reviewed","submitted" or "pending".  Please enter a list of states, one per line.  State names must be unique.'),
                'important_description' => _kt('Note that the first state you list is the one in which documents will start the workflow - this can be changed later on. '),
                'required' => true,
                'name' => 'states',
                'rows' => 15,
            )),
            array('ktcore.widgets.text',array(
                'label' => _kt('Transitions'),
                'description' => _kt('In order to move between states, users will cause "transitions" to occur.  These transitions represent processes followed, e.g. "review document", "distribute invoice" or "publish".  Please enter a list of transitions, one per line.  Transition names must be unique.  You\'ll assign transitions to states in the next step.'),
                'required' => false,
                'name' => 'transitions',
            )),
            array('ktcore.widgets.hidden',array(
                'required' => false,
                'name' => 'fWizardKey',
                'value' =>  KTUtil::randomString()
            )),
        ));

        $oForm->setValidators(array(
            array('ktcore.validators.string', array(
                'test' => 'workflow_name',
                'output' => 'workflow_name',
            )),
            array('ktcore.validators.string', array(
                'test' => 'fWizardKey',
                'output' => 'fWizardKey',
            )),
            array('ktcore.validators.string', array(
                'test' => 'states',
                'output' => 'states',
                'max_length' => 9999,
            )),
            array('ktcore.validators.string', array(
                'test' => 'transitions',
                'output' => 'transitions',
                'max_length' => 9999,
            )),
        ));

        return $oForm;
    }

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/workflow/admin/new_wizard_step1');

        $oForm =& $this->form_step1();

        $oTemplate->setData(array(
            'context' => $this,
            'form' => $oForm,
        ));
        return $oTemplate->render();
    }

    function do_process_step1() {

    	 $fWizardKey = KTUtil::arrayGet($_REQUEST, 'fWizardKey');
    	if (!empty($fWizardKey))
    	{
    		 $this->errorRedirectToMain(_kt("Could not create workflow.") );
    		 exit;
    	}

        $oForm =& $this->form_step1();
        $res = $oForm->validate();
        $data = $res['results'];
        // perform additional validation.
        $extra_errors = array();

        $data['workflow_name'] = str_replace(array('   ', '  '), array(' ', ' '), $data['workflow_name']);
        $oWorkflow = KTWorkflow::getByName($data['workflow_name']);
        if (!PEAR::isError($oWorkflow)) {
            $extra_errors['workflow_name'][] = _kt("A workflow with that name already exists.  Please choose a different name for this workflow.");
        }

        $initial_states = (array) explode("\n", $data['states']);   // must be there, we validated it.
        $failed = array();
        $states = array();
        $is_first = true;
        $initial_state = '';
        foreach ($initial_states as $sInitialStateName) {
            $state_name = trim($sInitialStateName);
            if (empty($state_name)) {
                continue;
            }

            if ($states[$state_name]) {
                $failed[] = $state_name;
                continue;
            }
            if ($is_first) {
                $is_first = false;
                $initial_state = $state_name;
            }
            $states[$state_name] = $state_name;
        }
        if (empty($states)) {
            $extra_errors['states'][] = _kt('You must provide at least one state name.');
        }
        if (!empty($failed)) {
            $extra_errors['states'] = sprintf(_kt("You cannot have duplicate state names: %s"), implode(', ', $failed));
        }
        $data['states'] = $states;
        $data['initial_state'] = $initial_state;

        $initial_transitions = (array) explode("\n", $data['transitions']);   // must be there, we validated it.
        $failed = array();
        $transitions = array();
        foreach ($initial_transitions as $sInitialTransitionName) {
            $transition_name = trim($sInitialTransitionName);
            if (empty($transition_name)) {
                continue;
            }

            if ($transitions[$transition_name]) {
                $failed[] = $transition_name;
                continue;
            }

            $transitions[$transition_name] = $transition_name;
        }

        if (!empty($failed)) {
            $extra_errors['transitions'] = sprintf(_kt("You cannot have duplicate transition names: %s"), implode(', ', $failed));
        }
        $data['transitions'] = $transitions;

        // handle errors.
        if (!empty($res['errors']) || !empty($extra_errors)) {
            $oForm->handleError(null, $extra_errors);
        }

        // store the data for a while.

        $wiz_data = (array) $_SESSION['_wiz_data'];
        $wiz_data[$fWizardKey] = $data;
        $_SESSION['_wiz_data'] =& $wiz_data;

        if (empty($data['transitions'])) {
            return $this->finalise();   // finish and go.
        }

        $this->successRedirectTo("step2",_kt("Initial data stored."));
    }

    function do_step2() {
    	 $fWizardKey = KTUtil::arrayGet($_REQUEST, 'fWizardKey');
    	if (!empty($fWizardKey))
    	{
    		 $this->errorRedirectToMain(_kt("Could not create workflow.") );
    		 exit;
    	}
        $wiz_data = (array) $_SESSION['_wiz_data'][$fWizardKey];

        if (empty($wiz_data)) {
            $this->errorRedirectToMain(_kt("Unable to find previous value.  Please try again."));
        }

        $oTemplate = $this->oValidator->validateTemplate('ktcore/workflow/admin/new_wizard_step2');

        $transitions = (array) $wiz_data['transitions'];
        $args = $this->meldPersistQuery("", "process_step2", true);

        $oTemplate->setData(array(
            'context' => $this,
            'fWizardKey'=>$fWizardKey,
            'args' => $args,
            'transitions' => $wiz_data['transitions'],
            'states' => $wiz_data['states'],
        ));
        return $oTemplate->render();
    }

    function do_process_step2() {
        $fWizardKey = KTUtil::arrayGet($_REQUEST, 'fWizardKey');
        if (!empty($fWizardKey))
    	{
    		 $this->errorRedirectToMain(_kt("Could not create workflow.") );
    		 exit;
    	}
        $wiz_data = $_SESSION['_wiz_data'][$fWizardKey];
        if (empty($wiz_data)) {
            $this->errorRedirectToMain(_kt("Unable to locate stored data.  Please try again."));
        }

        // we can't use the form "stuff" here since we don't have a grid widget yet
        // and hopefully never will.

        $fToData = (array) KTUtil::arrayGet($_REQUEST, 'fTo');
        $fFromData = (array) KTUtil::arrayGet($_REQUEST, 'fFrom');

        // these are data[transition][state] = true

        $fTo = array();
        $initial_state = $wiz_data['initial_state'];
        foreach ($wiz_data['transitions'] as $transition) {
            $candidate = $fToData[$transition];
            if (empty($wiz_data['states'][$candidate])) {
                $candidate = $initial_state;
            }
            $fTo[$transition] = $candidate;
        }

        $fFrom = array();
        foreach ($wiz_data['transitions'] as $transition) {
            $d = (array) KTUtil::arrayGet($fFromData, $transition);
            $final = array();
            foreach ($d as $state => $discard) {
                if (!empty($wiz_data['states'][$state])) {
                    $final[] = $state;
                }
            }
            $fFrom[$transition] = $final;
        }

        $wiz_data['from'] = $fFrom;
        $wiz_data['to'] = $fTo;

        $_SESSION['_wiz_data'][$fWizardKey] = $wiz_data;

        return $this->finalise();
    }

    function finalise() {
        $fWizardKey = KTUtil::arrayGet($_REQUEST, 'fWizardKey');
        if (!empty($fWizardKey))
    	{
    		 $this->errorRedirectToMain(_kt("Could not create workflow.") );
    		 exit;
    	}
        $wiz_data = $_SESSION['_wiz_data'][$fWizardKey];

        // gather all our data.  we're sure this is all good and healthy.

        $states = $wiz_data['states'];
        $transitions = $wiz_data['transitions'];
        $from = $wiz_data['from'];
        $to = $wiz_data['to'];
        $initial_state = $wiz_data['initial_state'];
        $workflow_name = $wiz_data['workflow_name'];

        $this->startTransaction();
        // create the initial workflow
        $oWorkflow = KTWorkflow::createFromArray(array(
            'name' => $workflow_name,
            'humanname' => $workflow_name,
            'enabled' => true,
        ));
        if (PEAR::isError($oWorkflow)) {
            $this->errorRedirectToMain(sprintf(_kt("Failed to create workflow: %s"), $oWorkflow->getMessage()));
        }
        $iWorkflowId = $oWorkflow->getId();
        // create the states.
        $aStates = array();
        foreach ($states as $state_name) {
            $oState = KTWorkflowState::createFromArray(array(
                'workflowid' => $iWorkflowId,
                'name' => $state_name,
                'humanname' => $state_name,
            ));
            if (PEAR::isError($oState)) {
                $this->errorRedirectToMain(sprintf(_kt("Failed to create state: %s"), $oState->getMessage()));
            }
            $aStates[$state_name] = $oState;
        }

        // update the initial state on workflow
        $oInitialState = $aStates[$initial_state];
        $oWorkflow->setStartStateId($oInitialState->getId());
        $res = $oWorkflow->update();
        if (PEAR::isError($res)) {
            $this->errorRedirectToMain(sprintf(_kt("Failed to update workflow: %s"), $res->getMessage()));
        }

        // next, we create and hook up the transitions.
        $aTransitions = array();
        foreach ($transitions as $transition) {
            $dest_name = $to[$transition];
            $oDestState = $aStates[$dest_name];
            $oTransition = KTWorkflowTransition::createFromArray(array(
                "WorkflowId" => $iWorkflowId,
                "Name" => $transition,
                "HumanName" => $transition,
                "TargetStateId" => $oDestState->getId(),
                // FIXME we need to deprecate these, eventually
                "GuardPermissionId" => null,
                "GuardGroupId" => null,
                "GuardRoleId" => null,
                "GuardConditionId" => null,
            ));
            if (PEAR::isError($oTransition)) {
                $this->errorRedirectToMain(sprintf(_kt("Failed to create transition: %s"), $oTransition->getMessage()));
            }

            // hook up source states.
            $state_ids = array();
            $sources = (array) $from[$transition];
            foreach ($sources as $state_name) {

                // must exist.
                $oState = $aStates[$state_name];
                $state_ids[] = $oState->getId();
            }

            $res = KTWorkflowAdminUtil::saveTransitionSources($oTransition, $state_ids);
            if (PEAR::isError($res)) {
                $this->errorRedirectToMain(sprintf(_kt("Failed to set transition origins: %s"), $res->getMessage()));
            }
        }

        $this->commitTransaction();

        // finally, we want to redirect the user to the parent dispatcher somehow.
        // FIXME nbm:  how do you recommend we do this?

        $base = $_SERVER['PHP_SELF'];
        $qs = sprintf("action=view&fWorkflowId=%d",$oWorkflow->getId());
        $url = KTUtil::addQueryString($base, $qs);
        $this->addInfoMessage(_kt("Your new workflow has been created.  You may want to configure security and notifications from the menu on the left."));
        redirect($url);
    }
}

?>
