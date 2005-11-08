<?php

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');

/*
 * Using KTStandardDispatcher for errorPage, overriding handleOutput as
 * the document action dispatcher will handle that.
 */

/**
 * Dispatcher for action.php/actionname
 *
 * This dispatcher looks up the action from the Action Registry, and
 * then chains onto that action's dispatcher.
 */
class KTActionDispatcher extends KTStandardDispatcher {
    /**
     * Default dispatch
     *
     * Find the action, and then use its dispatcher.  Error out nicely
     * if we aren't so lucky.
     */
    function do_main() {
        $this->error = false;
        $action = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
        $action = trim($action);
        $action = trim($action, "/");
        if (empty($action)) {
            $this->error = true;
            $this->errorPage("No action given");
        }
        $oRegistry =& KTActionRegistry::getSingleton();
        $aActionInfo = $oRegistry->getActionByNsname($action);
        if (empty($aActionInfo)) {
            $this->error = true;
            $this->errorPage("No such action exists in KnowledgeTree");
        }
        $oAction = new $aActionInfo[0];
        $oAction->dispatch();
    }

    /**
     * Handle output from this dispatcher.
     *
     * If there's an error in _this_ dispatcher, use the standard
     * surroundings.  If not, don't put anything around the output - the
     * chained dispatcher will take care of that.
     */
    function handleOutput ($data) {
        if ($this->error) {
            parent::handleOutput($data);
        } else {
            print $data;
        }
    }
}
$d = new KTActionDispatcher();
$d->dispatch();
