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
