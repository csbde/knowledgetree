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
 */

// unfortunately the autovalidation stuff requires that link to validation here.

require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");

class KTWidget {
    var $sNamespace = 'kt.abstract.basewidget';
    
    var $sLabel = '';
    var $sDescription = '';
    var $sName = '';
    var $sOrigname;
    var $sBasename = '';
    var $sId = null;
    var $bRequired = false;
    var $aOptions = null;
    var $aErrors = null;
    var $value = null;
    
    var $bAutoValidate;
    
    var $aJavascript = array(); // what javascript do we need added.
    var $aCSS = array();
    
    // allow very quick overrides.
    var $sTemplate = "ktcore/widgets/base";
        
    function configure($aOptions) {
        $this->sLabel = KTUtil::arrayGet($aOptions, 'label');
        $this->sDescription = KTUtil::arrayGet($aOptions, 'description');
        $this->sName = KTUtil::arrayGet($aOptions, 'name');
        $this->sOrigname = $this->sName;
        $this->sBasename = $this->sName; // we need to be able to get the "old" value after wrapping.
        $this->value = KTUtil::arrayGet($aOptions, 'value');
        
        $this->bRequired = (KTUtil::arrayGet($aOptions, 'required') == true);
        $this->sId = KTUtil::arrayGet($aOptions, 'id');
        $this->aOptions = $aOptions;        // there may be additional options
        $this->aErrors = array();
        
        $this->bAutoValidate = KTUtil::arrayGet($aOptions, 'autovalidate', true, false); // false is a valid answer here

        $this->aOptions['width'] = KTUtil::arrayGet($this->aOptions, 'width', '45');
    }
    
    function getDefault() { return $this->value; }
    function setDefault($mValue) { $this->value = $mValue; }
    function getBasename() { return $this->sBasename; }
    function wrapName($sOuter) { 
        // wrap the name.  we *require* that something extract up to the level
        // at which basename is accurate.
        $this->sName = sprintf('%s[%s]', $sOuter, $this->sBasename);
    }
    
    function setErrors($aErrors = null) {
        if (is_array($aErrors)) {
            $this->aErrors = $aErrors;
        }
    }

    function requireJSResource($sResourceURL) {
        $this->aJavascript[] = $sResourceURL;
    }

    function render() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $bHasErrors = false;       
        if (count($this->aErrors) != 0) { $bHasErrors = true; }
        //var_dump($this->aErrors);
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate('ktcore/forms/widgets/base');
        
        if (!empty($this->aJavascript)) {
            // grab our inner page.
            $oPage =& $GLOBALS['main'];            
            $oPage->requireJSResources($this->aJavascript);
        }
        if (!empty($this->aCSS)) {
            // grab our inner page.
            $oPage =& $GLOBALS['main'];            
            $oPage->requireCSSResources($this->aCSS);
        }        
        
        $widget_content = $this->getWidget();
        
        $aTemplateData = array(
            "context" => $this,
            "label" => $this->sLabel,
            "description" => $this->sDescription,
            "name" => $this->sName,
            "required" => $this->bRequired,
            "has_id" => ($this->sId !== null),
            "id" => $this->sId,
            "has_value" => ($this->value !== null),
            "value" => $this->value,
            "has_errors" => $bHasErrors,
            "errors" => $this->aErrors,
            "options" => $this->aOptions,
            "widget" => $widget_content,
        );
        return $oTemplate->render($aTemplateData);   
    }
    
    function getWidget() {
        $oTemplating =& KTTemplating::getSingleton();        
        $oTemplate = $oTemplating->loadTemplate($this->sTemplate);    
        
        $aTemplateData = array(
            "context" => $this,
            "label" => $this->sLabel,
            "description" => $this->sDescription,
            "name" => $this->sName,
            "required" => $this->bRequired,
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
    
    function getValidators() {
        if (!$this->bAutoValidate) {
            return null;   
        }
        
        // the base widget handles only the simplest possible case - required
        // fields
        
        if (!$this->bRequired) {
            return null;
        }
        
        $oVF =& KTValidatorFactory::getSingleton();
        return $oVF->get('ktcore.validators.required', array(
            'test' => $this->sOrigname,
            'basename' => $this->sBasename
        ));
    }
    
    function process($raw_data) {
       return array($this->sBasename => KTUtil::arrayGet($raw_data, $this->sOrigname));
    }
}

?>
