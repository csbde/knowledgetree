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
require_once(KT_LIB_DIR . "/widgets/forms.inc.php");

require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");

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
    
	/**
	 * Returns the Metadata Fieldsets for the given DocumentId
	 * @return KTForm 
	 *
	 */
	
	function getTypeMetadataFieldsets($iDocumentTypeID) {
        //Creating the form
		$oForm = new KTForm;
		$oFReg =& KTFieldsetRegistry::getSingleton();
		$activesets = KTFieldset::getForDocumentType($iDocumentTypeID);
		
		foreach ($activesets as $oFieldset) {
			$widgets = kt_array_merge($widgets, $oFReg->widgetsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
			$validators = kt_array_merge($validators, $oFReg->validatorsForFieldset($oFieldset, 'fieldset_' . $oFieldset->getId(), $this->oDocument));
		}
		
		$oForm->setWidgets($widgets);
		$oForm->setValidators($validators);
		
		return $oForm->renderWidgets();
	}   
    
}

$f =& new GetTypeMetadataFieldsDispatcher;
$f->dispatch();


?>
