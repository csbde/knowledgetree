<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
        $sFilename = $aActionInfo[1];
        if (!empty($sFilename)) {
            require_once($sFilename);
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
