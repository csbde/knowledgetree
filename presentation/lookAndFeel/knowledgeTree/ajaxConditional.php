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

require_once("../../../config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldset.inc.php");
require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');

/*
 * example code - tests the frontend behaviour.  remember to check ajaxConditional.php 
 * 
 */

class AjaxConditionalDispatcher extends KTStandardDispatcher {
    
    function do_main() {
        return "AJAX Error";
    }

    function handleOutput($data) {
        print $data;
    }

    function do_verifyAndUpdate() {
         header('Content-Type: text/xml');
         $oTemplating =& KTTemplating::getSingleton();

        $oTemplate = $oTemplating->loadTemplate("ktcore/conditional_ajax_verifyAndUpdate");
        $aTemplateData = array(
            
        );
        return $oTemplate->render($aTemplateData);
 
    }

    function do_updateFieldset() {
        global $main;
        $GLOBALS['default']->log->error(print_r($_REQUEST, true));
        header('Content-Type: application/xml');
        $oFieldset =& $this->oValidator->validateFieldset($_REQUEST['fieldset']);

        $matches = array();
        $aFields = array();
        foreach ($_REQUEST as $k => $v) {
            if (preg_match('/^metadata_(\d+)$/', $k, $matches)) {
                $aValues[$matches[1]] = $v;
            }
        }

        $aNextFieldValues =& KTMetadataUtil::getNext($oFieldset, $aValues);
        
        $sWidgets = '';
        // convert these into widgets using the ever-evil...
        // function getWidgetForMetadataField($field, $current_value, $page, $errors = null, $vocab = null) 
        foreach ($aNextFieldValues as $aFieldInfo) {
            $vocab = array();
            $vocab[''] = 'Unset';
            foreach ($aFieldInfo['values'] as $md_v) { $vocab[$md_v->getName()] = $md_v->getName(); }
            $req = $aFieldInfo['field']->getIsMandatory();
            $oWidget = getWidgetForMetadataField($aFieldInfo['field'], null, $main, null, $vocab, array('required' => $req)) ;
            $sWidgets .= $oWidget->render();
        }
        
        return $sWidgets;
    }
}

$oDispatcher = new AjaxConditionalDispatcher();
$oDispatcher->dispatch();

?>
