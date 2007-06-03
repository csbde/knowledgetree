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

/* Field Widgets 
 *
 * Useful (common) widgets to handle creating, editing, extending items, etc.
 *
 */
 
require_once(KT_LIB_DIR . "/templating/templating.inc.php"); 

class KTBaseWidget {
    var $sLabel = '';
    var $sDescription = '';
    var $sName = '';
    var $oPage = null;
    var $sId = null;
    var $bRequired = false;
    var $aOptions = null;
    var $aErrors = null;
    
    var $value = null;
    
    
    // very quick overrides.
    var $sTemplate = "kt3/fields/base";
    
    function KTBaseWidget($sLabel, $sDescription, $sName, $value, &$oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) {
        $this->sLabel = $sLabel;
        $this->sDescription = $sDescription;
        $this->sName = $sName;
        $this->value = $value;
        $this->oPage =& $oPage;
        $this->bRequired = $bRequired;
        $this->sId = $sId;
        $this->aOptions = $aOptions;
        $this->aErrors = $aErrors;
        
        if (is_null($this->aOptions)) { $this->aOptions = array(); }
        // default to being a bit bigger.
        $this->aOptions['width'] = KTUtil::arrayGet($this->aOptions, 'width', '45');
    }
    
    function render() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $bHasErrors = false;       
        if (count($this->aErrors) != 0) { $bHasErrors = true; }
        //var_dump($this->aErrors);
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate($this->sTemplate);
        
        $aTemplateData = array(
            "context" => $this,
            "label" => $this->sLabel,
            "description" => $this->sDescription,
            "name" => $this->sName,
            "required" => $this->bRequired,
            "page" => $this->oPage,
            "has_id" => ($this->sId !== null),
            "id" => $this->sId,
            "has_value" => ($this->value !== null),
            "value" => $this->value,
            "has_errors" => $bHasErrors,
            "errors" => $this->aErrors,
            "options" => $this->aOptions,
        );
        return $oTemplate->render($aTemplateData);   
    }
}

/* Ultra simple items, could be extended later (e.g. JS)*/
class KTStringWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/base"; }
class KTPasswordWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/password"; }
class KTIntegerWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/base"; }
class KTTextWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/text"; }

class KTCheckboxWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/checkbox"; }

class KTFileUploadWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/fileupload"; }
class KTStaticTextWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/statictext"; }

/* lookup widget */
// EXPECTS $aOptions["vocab"] => key, item
class KTLookupWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/lookup"; }
// EXPECTS $aOptions["tree"] => inner widget.
class KTTreeWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/tree"; }

// TODO KTTransferWidget
// TODO KTDateWidget
// TODO KTDateRangeWidget

// Expects $aOptions['action'] => dispatcher action to load from
//         $aOptions['assigned'] => currently assigned values
//         $aOptions['bind_add'] (opt) => name of js method to call on add
//         $aOptions['bind_remove'] (opt) => name of js method to call on remove
class KTJSONLookupWidget extends KTBaseWidget { var $sTemplate = "kt3/fields/jsonlookup"; }

?>
