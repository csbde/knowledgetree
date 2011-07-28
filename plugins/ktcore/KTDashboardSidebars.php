<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
require_once(KT_LIB_DIR . "/actions/folderviewlet.inc.php");

class KTDashboardSidebar extends KTFolderViewlet {
    public $sName = 'ktcore.sidebars.folder';
	public $_sShowPermission = 'ktcore.permissions.read';
	public $order = 1;
	public $oUser;

	private $folderNamespaces = array('ktcore.sidebar.recent.folder');
	private $documentNamespaces = array('ktcore.sidebar.recent.document');
	private $dashboardNamespaces = array('ktcore.sidebars.dashboard.checkout');

	/**
	 * Get the class name of a sidebar item
	 *
	 */
	public function getCSSName() {}

	/**
	 * Get the ordering of a sidebar item
	 *
	 * @return int
	 */
	public function getOrder() { return $this->order; }

	/**
	 * Create a sidebar block
	 *
	 * @return string
	 */
	public function getSideBars()
	{
		$sidebars = $this->getDashboardSidebars();
		$ordered = $keys = array();
        foreach ($sidebars as $sidebar) {
        	// Skip info check
    		$order = $sidebar->getOrder();
    		// Sidebars cannot overwrite each other.
        	if(isset($ordered[$sidebar->getOrder()])) {
        		$order++;
        		$ordered[$order] = $sidebar;
        	}
        	else {
        		$ordered[$order] = $sidebar;
        	}
        	$keys[$order] = $order;
        }
        // Sort to rewrite keys.
        sort($keys);
		$oTemplating = KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/dashlets/sidebars/viewSidebar');
        $aTemplateData = array(
              'context' => $this,
              'sidebars' => $ordered,
              'keys' => $keys,
              'location' => 'dashboard',
        );

        return $oTemplate->render($aTemplateData);
	}

	private function getDashboardSidebars()
	{
		$sidebars = array();
		$this->oFolder = null;
		$this->oDocument = null;
		$folderSidebars = KTFolderActionUtil::getFolderActionsForFolder($this->oFolder, $this->oUser, 'foldersidebar');
		$documentSidebars = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentsidebar');
		$dashboardSidebars = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'dashboardsidebar');

		foreach ($folderSidebars as $sidebar) {
			if(in_array($sidebar->sName, $this->folderNamespaces)) {
				$sidebars[] = $sidebar;
			}
		}

		foreach ($documentSidebars as $sidebar) {
			if(in_array($sidebar->sName, $this->documentNamespaces)) {
				$sidebars[] = $sidebar;
			}
		}

		foreach ($dashboardSidebars as $sidebar) {
			if(in_array($sidebar->sName, $this->dashboardNamespaces)) {
				$sidebars[] = $sidebar;
			}
		}

		return $sidebars;
	}
}

// replace the old checked-out docs.
class KTCheckoutSidebar extends KTDashboardSidebar {
	public $sName = 'ktcore.sidebars.dashboard.checkout';
	public $_sShowPermission = 'ktcore.permissions.read';
	public $order = 1;
	public $bShowIfReadShared = true;
	public $bShowIfWriteShared = true;

	public function getCSSName()
	{
		return 'checked-out-documents';
	}

    public function displayViewlet()
    {
		$documents = Document::getList(array('checked_out_user_id = ?', $this->oUser->getId()));
        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/dashlets/sidebars/checkedout');
        $templateData = array(
            'documents' => $documents,
        );

        return $template->render($templateData);
    }
}

?>