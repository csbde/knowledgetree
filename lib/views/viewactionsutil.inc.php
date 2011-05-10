<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
 * Contributor(s): ______________________________________
 */

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');

class ViewActionsUtil
{
	protected $actions;
	protected $list;
	
	public function __construct() {}
	
	/**
	 * Retrieve document actions
	 *
	 * @param object $oDocument
	 * @param object $oUser
	 */
	public function initActions($oDocument, $oUser) {
        $actions = KTDocumentActionUtil::getDocumentActionsForDocument($oDocument, $oUser);
        $info = KTDocumentActionUtil::getDocumentActionsForDocument($oDocument, $oUser, 'documentinfo');
        $this->actions = array_merge($actions, $info);
	}
	
    /**
     * Get the info for displaying the action buttons on the page
     *
     * @param array $actions
     * @return array
     */
    public function createButtons()
    {
        $menus = array();

        // Create the "more" button
        $btn = array('btn_position' => 'below', 'url' => '#', 'name' => _kt('More'), 'icon_class' => 'more', 'ns' => 'more');
        $this->list[$btn['btn_position']][$btn['ns']] = $btn;

        foreach ($this->actions as $oAction) {
            $info = $oAction->getInfo();

            // Skip if action is disabled
            if (is_null($info)) {
                continue;
            }

            // Skip if no name provided - action may be disabled for permissions reasons
            if (empty($info['name'])) {
                continue;
            }

            // Check whether the button has a parent i.e. is in the drop down menu of a split button
            if (!$info['parent_btn']) {
                // Determine the position of the button on the page
                $pos = $info['btn_position'];
                $this->list[$pos][$info['ns']] = $info;
            }
            else {
                $menus[$info['parent_btn']]['menu'][$info['ns']] = $info;
            }
        }

        if (!empty($menus)) {
            // Add the menu's to the correct buttons
            foreach ($this->list as $key => $item) {
                foreach ($menus as $subkey => $subitem) {
                    if (array_key_exists($subkey, $item)) {
                        // Order alphabetically
                        $submenu = $subitem['menu'];
                        uasort($submenu, array($this, 'sortMenus'));
                        $item[$subkey]['menu'] = $submenu;
                        $this->list[$key] = $item;
                    }
                }
            }
        }
        uasort($this->list['above'], array($this, 'sortBtns'));
    }
    
    /**
     * Retrieve top document actions
     *
     * @return array
     */
	protected function getTopActions() {
		return $this->list['above'];
	}
	
    /**
     * Retrieve bottom document actions
     *
     * @return array
     */
	protected function getBottomActions() {
		return $this->list['below'];
	}
	
    /**
     * Retrieve document link actions
     *
     * @return array
     */
	protected function getLinkActions() {
		return $this->list['links'];
	}
	
    protected function sortBtns($a, $b)
    {
        if ($a['btn_order'] < $b['btn_order']) return -1;
        if ($a['btn_order'] > $b['btn_order']) return 1;
        return 0;
    }

    protected function sortMenus($a, $b)
    {
        if ($a['name'] < $b['name']) return -1;
        if ($a['name'] > $b['name']) return 1;
        return 0;
    }
    
	/**
	 * Render HTML for top actions
	 *
	 * @return string
	 */
	public function renderTopActions() {
		$templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/document/view_top_actions');
        $templateData = array(
        						'actionBtns' => $this->getTopActions(),
        						);
        return $template->render($templateData);
	}
	
	/**
	 * Render HTML for bottom actions
	 *
	 * @return string
	 */
	public function renderBottomActions() {
        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/document/view_bottom_actions');
        $templateData = array(
        						'actionBtns' => $this->getBottomActions(),
        						'actionLinks' => $this->getLinkActions(),
        						);
        return $template->render($templateData);
	}
	
	
}
?>