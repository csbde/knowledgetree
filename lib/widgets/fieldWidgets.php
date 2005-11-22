<?php

/* Field Widgets 
 *
 * Useful (common) widgets to handle creating, editing, extending items, etc.
 *
 * Author: Brad Shuttleworth (brad@jamwarehouse.com) 
 * Copyright (c) 2005 the Jam Warehouse Software (Pty) Ltd. 
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
    
    function KTBaseWidget($sLabel, $sDescription, $sName, $value, $oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null) {
        $this->sLabel = $sLabel;
        $this->sDescription = $sDescription;
        $this->sName = $sName;
        $this->value = $value;
        $this->oPage = $oPage;
        $this->bRequired = $bRequired;
        $this->sId = $sId;
        $this->aOptions = $aOptions;
        $this->aErrors = $aErrors;
    }
    
    function render() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $bHasErrors = false;       
        if (count($this->aErrors) != 0) { $bHasErrors = true; }
        //var_dump($this->aErrors);
        $oTemplating = new KTTemplating;        
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

// TODO KTTreeWidget
// TODO KTTransferWidget
// TODO KTDateWidget
// TODO KTDateRangeWidget

?>
