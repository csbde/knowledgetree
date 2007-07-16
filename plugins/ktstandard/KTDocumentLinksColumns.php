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

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentLink.inc');
require_once(KT_LIB_DIR . '/documentmanagement/LinkType.inc');

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");
require_once(KT_LIB_DIR . "/browse/browseutil.inc.php");

require_once(KT_LIB_DIR . "/browse/columnregistry.inc.php");


$oCR =& KTColumnRegistry::getSingleton();
$oCR->getColumn('ktcore.columns.title');

class KTDocumentLinkTitle extends AdvancedTitleColumn {
    var $namespace = 'ktdocumentlinks.columns.title';

    function renderDocumentLink($aDataRow) {
        $aOptions = $this->getOptions();
        $fParentDocId = KTUtil::arrayGet(KTUtil::arrayGet($aOptions, 'qs_params', array()),
                                         'fDocumentId', False);

        if ((int)$aDataRow["document"]->getId() === (int)$fParentDocId) {
            return htmlentities($aDataRow["document"]->getName(),ENT_QUOTES, 'UTF-8') .
                ' <span class="descriptiveText">(' . _kt('you cannot link to the source document') . ')';
        } else {
            return parent::renderDocumentLink($aDataRow);
        }
    }
}

?>