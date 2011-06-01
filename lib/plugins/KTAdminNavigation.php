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

// FIXME should we refactor this into a separate file?  Do we gain anything?

class KTAdminNavigationRegistry {

    private $aResources = array();
    private $aCategorisation = array();
    private $aCategories = array();
    private $sorted = array('categories' => false, 'items' => false);

    public static function &getSingleton ()
    {
        if (!KTUtil::arrayGet($GLOBALS['_KT_PLUGIN'], 'oKTAdminNavigationRegistry')) {
            $GLOBALS['_KT_PLUGIN']['oKTAdminNavigationRegistry'] = new KTAdminNavigationRegistry;
        }

        return $GLOBALS['_KT_PLUGIN']['oKTAdminNavigationRegistry'];
    }

    // name is the suburl below admin
    // namespace, class, category, title, description
    // if category is specified, it looks for an item with THAT NAME for its details.
    public function registerLocation($sName, $sClass, $sCategory, $sTitle, $sDescription, $sDispatcherFilePath = null, $sURL = null, $sNamespace = null, $iOrder = 0)
    {
        $sFullname = $sCategory . '/' . $sName;
        $aInfo = array(
            'name' => $sName,
            'class' => $sClass,
            'title' => $sTitle,
            'description'=> $sDescription,
            'filepath' => $sDispatcherFilePath,
            'url' => $sURL,
            'fullname' => $sFullname,
            'order' => $iOrder
        );

        $this->aResources[$sFullname] = $aInfo;

        // is this a toplevel item?
        if ($sCategory != null) {
            if (!array_key_exists($sCategory, $this->aCategories)) {
                $this->registerCategory($sCategory, $sCategory, '');
            }
            $this->aCategorisation[$sCategory][] = $aInfo;
        }
    }

    public function isRegistered($name)
    {
	return KTUtil::arrayGet($this->aResources, $name);
    }

    public function registerCategory($name, $title, $description, $order)
    {
        $this->aCategories[$name] = array(
					'title' => $title,
					'description' => $description,
					'name' => $name,
					'order' => $order
				    );
    }

    public function getCategories()
    {
	$this->sortCategories();
	return $this->aCategories;
    }

    private function sortCategories()
    {
	if ($this->sorted['categories']) {
            return true;
        }

        uasort($this->aCategories, 'order_compare');

	$this->sorted['categories'] = true;
    }

    public function getCategory($category)
    {
	return $this->aCategories[$category];
    }

    public function getItemsForCategory($category)
    {
        $this->sortItems($category);
        return $this->aCategorisation[$category];
    }

    private function sortItems($sCategory)
    {
        if ($this->sorted['items'][$sCategory]) {
            return true;
        }

        usort($this->aCategorisation[$sCategory], 'order_compare');

        $this->sorted['items'][$sCategory] = true;
    }

    public function getDispatcher($sName)
    {
        $aInfo = $this->aResources[$sName];
        if ($aInfo['filepath'] !== null) {
	    require_once($aInfo['filepath']);
	}

        if (!empty($aInfo['url'])) {
           return new RedirectingDispatcher($aInfo['url']);
        }

        return new $aInfo['class'];
    }

}

function order_compare($a, $b)
{
    if ($a['order'] > $b['order']) {
        return -1;
    }

    if ($a['order'] < $b['order']) {
        return 1;
    }

    return 0;
}

class RedirectingDispatcher {

    var $url = '';

    function RedirectingDispatcher($sURL)
    {
        $this->url = $sURL;
    }

    function dispatch()
    {
        redirect($this->url);
    }

}

?>
