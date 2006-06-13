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

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/observers.inc.php");

require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");

class KTFolderTransactionsAction extends KTFolderAction {
    var $sName = 'ktcore.actions.folder.transactions';
    var $_sShowPermission = "ktcore.permissions.folder_details";

    function getDisplayName() {
        return _kt('Folder transactions');
    }
    
    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("transactions"));
        $this->oPage->setTitle(_kt('Folder transactions'));

        // $oTemplate =& $this->oValidator->validateTemplate('ktcore/folder/transactions');

        $folder_data = array();
        $folder_data["folder_id"] = $this->oFolder->getId();

        $this->oPage->setSecondaryTitle($this->oFolder->getName());

        $aTransactions = array();
        // FIXME do we really need to use a raw db-access here?  probably...
        $sQuery = "SELECT DTT.name AS transaction_name, U.name AS user_name, FT.comment AS comment, FT.datetime AS datetime " .
            "FROM " . KTUtil::getTableName("folder_transactions") . " AS FT LEFT JOIN " . KTUtil::getTableName("users") . " AS U ON FT.user_id = U.id " .
            "LEFT JOIN " . KTUtil::getTableName("transaction_types") . " AS DTT ON DTT.namespace = FT.transaction_namespace " .
            "WHERE FT.folder_id = ? ORDER BY FT.datetime DESC";
        $aParams = array($this->oFolder->getId());
        $res = DBUtil::getResultArray(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
           var_dump($res); // FIXME be graceful on failure.
           exit(0);
        }

        // FIXME roll up view transactions
        $aTransactions = $res;

        // render pass.
        $this->oPage->title = _kt("Folder History");
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/view_folder_history");
        $aTemplateData = array(
              "context" => $this,
              "folder_id" => $folder_id,
              "folder" => $this->oFolder,
              "transactions" => $aTransactions,
        );
        return $oTemplate->render($aTemplateData);
    }


}

?>
