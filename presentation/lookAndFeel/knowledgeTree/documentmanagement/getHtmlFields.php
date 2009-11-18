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

require_once('../../../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldset.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");


class KTSimplePage {
    function requireJSResource() {
    }
}

class GetHtmlFieldsDispatcher extends KTDispatcher {
    function do_main() {
        $this->oPage = new KTSimplePage;
		header('Content-type: text/html; charset=UTF-8');
		
		$type = $_REQUEST['type'];
		$docId = $_REQUEST['fDocumentTypeID'];
		
		switch ($type) {
			case 'html':
        		return $this->getHtmlFields ($docId);
			case 'generic':
        		return $this->getGenericFields ($docId);
			default:
				return $this->getHtmlFields ($docId);
		}
    }

	/**
	 * Returns a JSON object containing a list of HTML type fields for the given DocumentId
	 * @return JSON Object
	 *
	 */
	
	function getHtmlFields($iDocumentTypeID) {
		$oFReg =& KTFieldsetRegistry::getSingleton();
		$activesets = KTFieldset::getForDocumentType($iDocumentTypeID);
		
		foreach ($activesets as $oFieldset) {
			$htmlFieldIds = kt_array_merge($htmlFieldIds, $oFReg->getHtmlFields($oFieldset));
		}
		
		$jsOptions = '{ "htmlId" : {';
		
		foreach($htmlFieldIds as $fieldId) {
			$jsOptions .= "'$fieldId' : '$fieldId',";
		}
		$jsOptions = substr($jsOptions, 0, strlen($jsOptions) - 1);
		
		$jsOptions .= '}}';
		
		return $jsOptions;
	}

	/**
	 * Returns a JSON object containing a list of fields belonging to a generic fieldset 
	 * @return JSON Object
	 *
	 */
	function getGenericFields() {
		$oFReg =& KTFieldsetRegistry::getSingleton();
		$activesets = KTFieldset::getGenericFieldsets();
		
		$fields = array();
		foreach ($activesets as $oFieldset) {
			$fieldIds = kt_array_merge($fieldIds, $oFReg->getGenericFields($oFieldset));
		}
		
		$jsOptions = '{ "genericId" : {';
		
		foreach($fieldIds as $fieldId) {
			$jsOptions .= "'$fieldId' : '$fieldId',";
		}
		$jsOptions = substr($jsOptions, 0, strlen($jsOptions) - 1);
		
		$jsOptions .= '}}';
		
		return $jsOptions;
	}
    
	/**
	 * Returns a JSON object containing a list of fields belonging to a generic fieldset 
	 * for the given DocumentId
	 * @return JSON Object
	 *
	 */
	function getNonGenericFields($iDocumentTypeID) {
		$oFReg =& KTFieldsetRegistry::getSingleton();
		$activesets = KTFieldset::getForDocumentType($iDocumentTypeID);
		
		$fields = array();
		foreach ($activesets as $oFieldset) {
			$fieldIds = kt_array_merge($fields, $oFReg->getGenericFields($oFieldset));
		}
		
		$jsOptions = '{ "htmlId" : {';
		
		foreach($fieldIds as $fieldId) {
			$jsOptions .= "'$fieldId' : '$fieldId',";
		}
		$jsOptions = substr($jsOptions, 0, strlen($jsOptions) - 1);
		
		$jsOptions .= '}}';
		
		return $jsOptions;
	}	
	
}

$f =& new GetHtmlFieldsDispatcher;
$f->dispatch();
?>