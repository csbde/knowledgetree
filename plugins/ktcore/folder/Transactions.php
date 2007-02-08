<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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
