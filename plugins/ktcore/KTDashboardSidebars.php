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
require_once(KT_LIB_DIR . "/actions/dashboardviewlet.inc.php");

class KTDashboardSidebar extends KTDashboardViewlet {
    public $sName = 'ktcore.sidebars.dashboard';
	public $_sShowPermission = 'ktcore.permissions.read';
	public $order = 1;
	public $oUser;

	private $folderNamespaces 		= array(	'ktcore.sidebar.recent.folder');
	private $documentNamespaces		= array(	'ktcore.sidebar.recent.document');

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
        	if(isset($ordered[$order])) {
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
		// The folde rand document objects are not available on the dashboard
		// as we are out of context. But reusing existing actions seems
		// better than copying and pasting.
		// TODO : Copy and paste if that makes more sense.
		$this->oFolder = null;
		$this->oDocument = null;
		$folderSidebars = KTFolderActionUtil::getFolderActionsForFolder($this->oFolder, $this->oUser, 'foldersidebar');
		$documentSidebars = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentsidebar');
		// Get dashboard actions that were never sidebars to begin with.
		$dashboardSidebars = KTDashboardActionUtil::getActionsForDashboard($this->oUser, 'dashboardsidebar');

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

		$sidebars = array_merge($sidebars, $dashboardSidebars);

		return $sidebars;
	}
}

// Replace the old checked-out docs.
class KTCheckoutSidebar extends KTDashboardSidebar {
	public $sName = 'ktcore.sidebars.dashboard.checkout';
	public $_sShowPermission = 'ktcore.permissions.read';
	public $order = 4;
	public $bShowIfReadShared = true;
	public $bShowIfWriteShared = true;

	public function getCSSName()
	{
		return 'checked-out-documents';
	}

    public function displayViewlet()
    {
    	$where = array(	'checked_out_user_id = ?', $this->oUser->getId(),
    					);
		$options = array(	'limit' => 5,
							'orderby' => 'created desc',
							);
		$documents = Document::getList($where, $options);
        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/dashlets/sidebars/checkedout');
        $templateData = array(
            'documents' => $documents,
            'browseUtil' => new KTBrowseUtil(),
        );

        return $template->render($templateData);
    }
}

class QuicklinksSidebar extends KTDashboardSidebar {
    public $sName = 'ktcore.sidebars.dashboard.quicklinks';
	public $_sShowPermission = 'ktcore.permissions.read';
	public $order = 4;
	public $bShowIfReadShared = true;
	public $bShowIfWriteShared = true;
	private $quicklinksMaxDisplay = 5;

   	public function getCSSName()
	{
		return 'quicklinks-documents';
	}

    public function displayViewlet() {
    	// TODO : Move to quicklinks plugin.
    	$quicklinks = KT_PLUGIN_DIR . '/commercial/network/quicklinks/Quicklink.inc.php';
    	if(!file_exists($quicklinks)) return '';
		require_once($quicklinks);
    	$quicklinks = Quicklink::getListForUser($this->oUser->getId());

	    $templating = KTTemplating::getSingleton();
	    $template = $templating->loadTemplate('ktcore/dashlets/sidebars/quicklinks');
	    $templateData = array(	'context' => $this,
				   				'quicklinks_items' => $quicklinks,
				   				'manage_url' => 'plugin.php?kt_path_info=bd.Quicklinks.plugin/quicklinksmanagement',
				   				'quicklinksMaxDisplay' => $this->quicklinksMaxDisplay,
				   				'quicklinksCount' => count($quicklinks),
				   				);

        return $template->render($templateData);
    }
}

?>