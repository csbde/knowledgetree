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

    function getDisplayName() {
        return _('Rename');
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
            $_SESSION["KTErrorMessage"][]= _("This document can't be renamed because it is checked out");
            controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
            exit(0);
        }
        return true;
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails("rename");
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/action/rename');
        $fields = array();
        $fields[] = new KTStringWidget(_('New file name'), _('The name to which the current file should be renamed.'), 'filename', "", $this->oPage, true);

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
            'message' => "No filename given",
        );
        $this->oValidator->validateString($sFilename, $aOptions);
        
        $res = KTDocumentUtil::rename($this->oDocument, $sFilename, $this->oUser);
        if (PEAR::isError($res)) {
            $_SESSION['KTErrorMessage'][] = $res->getMessage();
            controllerRedirect('viewDocument',sprintf('fDocumentId=%d', $this->oDocument->getId()));
        } else {
            $_SESSION['KTInfoMessage'][] = sprintf(_('Document "%s" renamed.'),$this->oDocument->getName());
        }
        
        controllerRedirect('viewDocument', sprintf('fDocumentId=%d', $this->oDocument->getId()));
        exit(0);
    }
}
// }}}

?>
