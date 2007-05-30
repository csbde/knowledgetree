<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

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
        $action = trim($action, '/');
        if (empty($action)) {
            $this->error = true;
            $this->errorPage(_kt('No action given'));
        }
        $oRegistry =& KTActionRegistry::getSingleton();
        $aActionInfo = $oRegistry->getActionByNsname($action);
        if (empty($aActionInfo)) {
            $this->error = true;
            $this->errorPage(sprintf(_kt('No such action exists in %s'), APP_NAME));
        }
        $sFilename = $aActionInfo[1];
        if (!empty($sFilename)) {
            require_once($sFilename);
        }
        $oAction = new $aActionInfo[0];
        $oAction->dispatch();
    }

    function json_main() {
	return $this->do_main();
    }

    function do_bulkaction() {
        $act = (array) KTUtil::arrayGet($_REQUEST, 'submit',null);
        
        $targets = array_keys($act);
        if (!empty($targets)) {
            $target = $targets[0];
        } else {
            $this->errorRedirectToMain(_kt('No action selected.'));
            exit(0);
        }

        $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());        

        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', 1));
        if (PEAR::isError($oFolder)) { 
            $this->errorRedirectToMain(_kt('Invalid folder selected.'));
            exit(0);
        }

        if (empty($aFolderSelection) && empty($aDocumentSelection)) {
            $this->errorRedirectToMain(_kt('Please select documents or folders first.'), sprintf('fFolderId=%d', $oFolder->getId()));
            exit(0);
        }        
        
        // prepare for passing to bulk actions
        $oActionRegistry =& KTActionRegistry::getSingleton();
        $oAction =& $oActionRegistry->initializeAction($target, $this->oUser);
        
        if(!$oAction || PEAR::isError($oAction)) {
            $this->errorRedirectToMain(_kt('No such action.'));
            exit(0);
        }

        $oAction->oFolder = $oFolder;

        $oEntityList = new KTEntityList($aDocumentSelection, $aFolderSelection);
        $oAction->setEntityList($oEntityList);
        $oAction->redispatch('action', 'do_', $this);

        //        exit(0);
    }
        

    /**
     * Handle output from this dispatcher.
     *
     * If there's an error in _this_ dispatcher, use the standard
     * surroundings.  If not, don't put anything around the output - the
     * chained dispatcher will take care of that.
     */
    function handleOutput ($data) {
        if ($this->bJSONMode || $this->error) {
            parent::handleOutput($data);
        } else {
            print $data;
        }
    }
}
$d = new KTActionDispatcher();
$d->dispatch();
