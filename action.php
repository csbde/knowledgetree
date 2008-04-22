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
            $this->errorRedirectToBrowse(_kt('No action selected.'));
            exit(0);
        }

        $aFolderSelection = KTUtil::arrayGet($_REQUEST, 'selection_f' , array());
        $aDocumentSelection = KTUtil::arrayGet($_REQUEST, 'selection_d' , array());

        $oFolder = Folder::get(KTUtil::arrayGet($_REQUEST, 'fFolderId', 1));
        if (PEAR::isError($oFolder)) {
            $this->errorRedirectToBrowse(_kt('Invalid folder selected.'));
            exit(0);
        }

        if (empty($aFolderSelection) && empty($aDocumentSelection)) {
            $this->errorRedirectToBrowse(_kt('Please select documents or folders first.'), sprintf('fFolderId=%d', $oFolder->getId()));
            exit(0);
        }

        // prepare for passing to bulk actions
        $oActionRegistry =& KTActionRegistry::getSingleton();
        $oAction =& $oActionRegistry->initializeAction($target, $this->oUser);

        if(!$oAction || PEAR::isError($oAction)) {
            $this->errorRedirectToBrowse(_kt('No such action.'));
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
