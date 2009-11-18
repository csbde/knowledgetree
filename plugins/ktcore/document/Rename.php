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
        $fields[] = new KTStringWidget(_kt('New file name'), _kt('The name to which the current file should be renamed.'), 'filename', $this->oDocument->getFileName(), $this->oPage, true);

        // Add an electronic signature
    	global $default;
    	if($default->enableESignatures){
    	    $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    	    $heading = _kt('You are attempting to rename the document');
    	    $submit['type'] = 'button';
    	    $submit['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.rename', 'document', 'document_rename_form', 'submit', {$this->oDocument->iId});";
    	}else{
    	    $submit['type'] = 'submit';
    	    $submit['onclick'] = '';
    	}

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
            'submit' => $submit
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
        $this->oValidator->validateIllegalCharacters($sFilename, $aOptions);

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
