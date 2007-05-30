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

require_once('../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');

class KTSimplePage {
    function requireJSResource() {
    }
}

class GetTypeMetadataFieldsDispatcher extends KTDispatcher {
    function do_main() {
        $this->oPage = new KTSimplePage;
		header('Content-type: text/html; charset=UTF-8');        
        return $this->getTypeMetadataFieldsets ($_REQUEST['fDocumentTypeID']);
    }

    function getTypeMetadataFieldsets ($iDocumentTypeID) {
        $fieldsets = array();
        $fieldsetDisplayReg =& KTFieldsetDisplayRegistry::getSingleton();
        $activesets = KTFieldset::getForDocumentType($iDocumentTypeID);
        foreach ($activesets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));
        }
        $aTemplateData = array(
            'fieldsets' => $fieldsets,
        );
        $oTemplating = KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/metadata/editable_metadata_fieldsets");
        return $oTemplate->render($aTemplateData);
    }
}

$f =& new GetTypeMetadataFieldsDispatcher;
$f->dispatch();


?>
