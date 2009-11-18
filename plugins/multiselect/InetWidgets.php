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

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/widgets/basewidget.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');

class InetMultiselectWidget extends KTBaseWidget 
{
	var $sTemplate = "multiselect/selection";
	
	
	/**
	 * assign the class variables
	 * @return 
	 * @param $sLabel Object
	 * @param $sDescription Object
	 * @param $sName Object
	 * @param $value Object
	 * @param $oPage Object
	 * @param $bRequired Object[optional]
	 * @param $sId Object[optional]
	 * @param $aErrors Object[optional]
	 * @param $aOptions Object[optional]
	 * 
	 * iNET Process
	 */
	function InetMultiselectWidget($sLabel, $sDescription, $sName, $value, &$oPage, $bRequired = false, $sId = null, $aErrors = null, $aOptions = null)
	{
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
		if($this->aOptions['lookup_type'] == "multiwithcheckboxes")
		{
			$this->sTemplate = "multiselect/simple_selection";
		}
		
	}
	
	
	/**
	 * returns the rendered templates
	 * @return 
	 * 
	 * iNET Process
	 */
	function render() {
        // very simple, general purpose passthrough.  Chances are this is sufficient,
        // just override the template being used.
        $bHasErrors = false;       
        if (count($this->aErrors) != 0) { $bHasErrors = true; }
        
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
			"vocab" => $this->aOptions['vocab'],
        );
        return $oTemplate->render($aTemplateData);   
    }
	
	
	/**
	 * returns the selected lookup value
	 * @return 
	 * @param $lookup Object
	 * 
	 * iNET Process
	 */
	function selected($lookup) {
        if ($this->bMulti) {
            return $this->_valuesearch[$lookup];
        } else {
            return ($this->value == $lookup);
        }
    }
	
	/**
	 * 
	 * @return array
	 * @param $raw_data array
	 * 
	 * iNET Process
	 */
    function process($raw_data) {
        return array($this->sBasename => $raw_data[$this->sBasename]);
    }
}

?>