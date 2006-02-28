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
            $oWidget = getWidgetForMetadataField($aFieldInfo['field'], null, $main, null, $vocab) ;
            $sWidgets .= $oWidget->render();
        }
        
        return $sWidgets;
    }
}

$oDispatcher = new AjaxConditionalDispatcher();
$oDispatcher->dispatch();

?>
