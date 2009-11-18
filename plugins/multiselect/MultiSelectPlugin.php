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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class MultiSelectPlugin extends KTPlugin {
    var $sNamespace = "inet.multiselect.lookupvalue.plugin";
    var $autoRegister = true;
    var $showInAdmin = false;

	/**
	 * returns plugin name
	 * @param string.
	 * @return string.
	 *
	 * iNET Process
	 */
    function MultiSelectPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Multi-select Plugin');
        return $res;
    }

	/**
	 *  Register the action, location, call adminsetup function and sql function
	 *	iNET Process
	 */
	function setup() {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('Multiselect in metadata Part {lookup Value}', '/plugins/multiselect/templates');

		$dir = dirname(__FILE__);
		$this->applySQL(realpath($dir . '/sql/script.sql'));

		//For adding documents
		$this->registerAction('folderaction', 'MultiDocumentAddAction', 'inet.multiselect.actions.document.addDocument', 'addDocument.php');

		//For bulk upload
		$this->registerAction('folderaction', 'InetBulkUploadFolderAction', 'inet.actions.folder.bulkUpload', 'BulkUpload.php');
		/**
		 * Change Starts | iNET Process
		 * Code is Added 2009-03-04 :SL
		 * Reason : To Register "import from folder location" action for multiselect
		 */
		$this->registerAction('folderaction', 'InetBulkImportFolderMultiSelectAction', 'inet.actions.folder.bulkImport.multiselect', 'BulkImport.php');
		/**
		 * Change Ends | iNET Process
		 */
		$this->setupAdmin();
    }
	/**
	 * applies queries to the database
	 * @return
	 * @param $filename Object
	 */
	function applySQL($filename)
    {
		global $default;
		DBUtil::setupAdminDatabase();
		$db = $default->_admindb;

		$content = file_get_contents($filename);
		$aQueries = SQLFile::splitSQL($content);

		DBUtil::startTransaction();
        foreach($aQueries as $sQuery)
		{
			$res = DBUtil::runQuery($sQuery, $db);
			if (PEAR::isError($res)) {
				continue;
	        }
		}
		DBUtil::commit();
    }
	/**
	 * Sets up an admin
	 * @return
	 */
	function setupAdmin()
	{
		//FIXME: The kt_hideadminlink.js script hides the link on the client side. The faulty link/action
		//		should be de-registerred and removed at the server side. The function below breaks things
		//		so don't use.
		//		e.g. $this->deRegisterPluginHelper('documents/fieldmanagement2', 'KTDocumentFieldDispatcher');
		
		$js .= "<script src='resources/js/kt_hideadminlink.js' type='text/javascript'></script>";
		$this->registerAdminPage('ratpfieldset', 'InetDocumentFieldDispatcher', 'documents',
             $js._kt('Document Fieldsets'),
            _kt('Manage the different types of information with multiselect functionality that can be associated with classes of documents.'),
            'InetdocumentFieldsv2.php', null);
	}
}
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('MultiSelectPlugin', 'inet.multiselect.lookupvalue.plugin', __FILE__);
?>