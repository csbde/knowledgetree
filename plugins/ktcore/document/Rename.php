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

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/PhysicalDocumentManager.inc');

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

// {{{ KTDocumentRenameAction
class KTDocumentRenameAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.rename';

    var $_sShowPermission = "ktcore.permissions.write";
    var $_bMutator = true;

    function getDisplayName() {
        return _kt('Rename');
    }

    function getInfo() {
        if ($this->oDocument->getIsCheckedOut()) {
            return null;
        }
        return parent::getInfo();
    }

    function check() {
        $res = parent::check();
        if ($res !== true) {
            return $res;
        }
        if ($this->oDocument->getIsCheckedOut()) {
            $_SESSION["KTErrorMessage"][]= _kt("This document can't be renamed because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("Rename"));
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/rename');
        $fields = array();

        $fields[] = new KTStaticTextWidget(_kt('Current file name'), _kt('The current file name is shown below:'), 'oldfilename', $this->oDocument->getFileName(), $this->oPage, false);
        $fields[] = new KTStringWidget(_kt('New file name'), _kt('The name to which the current file should be renamed.'), 'filename', "", $this->oPage, true);

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
        ));
        return $oTemplate->render();
    }

    function do_rename() {
        global $default;
        $sFilename = KTUtil::arrayGet($_REQUEST, 'filename');
        $aOptions = array(
            'redirect_to' => array('', sprintf('fDocumentId=%d', $this->oDocument->getId())),
            'message' => _kt("No filename given"),
            'max_str_len' => 255,
        );
        $this->oValidator->validateString($sFilename, $aOptions);
        
        $res = KTDocumentUtil::rename($this->oDocument, $sFilename, $this->oUser);
        if (PEAR::isError($res)) {
            $_SESSION['KTErrorMessage'][] = $res->getMessage();
            controllerRedirect('viewDocument',sprintf('fDocumentId=%d', $this->oDocument->getId()));
        } else {
            $_SESSION['KTInfoMessage'][] = sprintf(_kt('Document "%s" renamed.'),$this->oDocument->getName());
        }
        
        controllerRedirect('viewDocument', sprintf('fDocumentId=%d', $this->oDocument->getId()));
        exit(0);
    }
}
// }}}

?>
