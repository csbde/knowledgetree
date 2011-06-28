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

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

require_once(KT_LIB_DIR . '/plugins/KTAdminNavigation.php');

class AdminSettingsDispatcher extends KTAdminDispatcher {

    private $expandedSection = false;
    private $defaultCategory = '';

    public $sSection = 'settings';
    public $event_var = null;

    function __construct()
    {
        $this->aBreadcrumbs = array(
            array('url' => KTUtil::getRequestScriptName($_SERVER), 'name' => _kt('Settings')),
        );

        parent::KTAdminDispatcher();
    }

    public function do_main($viewCategory = false)
    {
        $registry = KTAdminNavigationRegistry::getSingleton();
        $categories = $registry->getCategories();
        reset($categories);
        $defaultCategory = current($categories);
        $this->defaultCategory = $defaultCategory['name'];

        $KTConfig = KTConfig::getSingleton();
        $condensedAdmin = $KTConfig->get('condensedAdminUI');

        // TODO Figure whether this is still relevant and remove if not.
        // We need to investigate sub_url solutions.
        $allItems = array();
        if ($condensedAdmin) {
            foreach ($categories as $category) {
                $items = $registry->getItemsForCategory($category['name']);
                $allItems[$category['name']] = $items;
            }
        }

        global $default;
        if (ACCOUNT_ROUTING_ENABLED && $default->tier == 'trial') {
            $this->includeOlark();
        }

        $templating = KTTemplating::getSingleton();
        
        if (KTUtil::arrayGet($_REQUEST, 'modal', null) == 'yes') {
            $template = $templating->loadTemplate('kt3/settings_ajax');
        } else {
            $template = $templating->loadTemplate('kt3/settings');
        }
        
        $templateData = array(
                            'context' => $this,
                            'categories' => $categories,
                            'all_items' => $allItems,
                            'items' => $this->getCategoryItems(),
                            'baseurl' => $_SERVER['PHP_SELF'],
        );

        return $template->render($templateData);
    }
    
    function handleOutput($data)
    {
        if (KTUtil::arrayGet($_REQUEST, 'modal', null) == 'yes') {
            echo $data;
            exit(0);
        } else {
            parent::handleOutput($data);
        }
    }

    private function getCategoryItems()
    {
        $urlParts = $this->parseSubUrl();
        $category = $this->getCategory($urlParts[0]);
        $subsection = $this->getSubsection($urlParts[1]);
        $expanded = $this->sectionExpanded();

        $page = $GLOBALS['main'];
        $javascript[] = 'resources/js/newui/hide_system_links.js';
        $page->requireJSResources($javascript);

        $registry = KTAdminNavigationRegistry::getSingleton();
        if (ACCOUNT_ROUTING_ENABLED && $category == 'contentIndexing') {
            $items = null;
            $message = 'Indexing of full-text content in KnowledgeTree is carried out through shared queue processes using SOLR. <br/>Content Indexing statistics coming soon!';
        }
        else {
            $categoryDetail = $registry->getCategory($category);
            $this->aBreadcrumbs[] = array('name' => $categoryDetail['title'], 'url' => KTUtil::ktLink('settings.php', '', 'fCategory='.$category));
            $this->oPage->title = _kt('Settings');
            $this->oPage->secondary_title = $categoryDetail['title'];
            $items = $registry->getItemsForCategory($category);
            $message = null;
        }

        if (count($items) == 1) {
            $items[0]['autoDisplay'] = true;
        }
        else {
            foreach ($items as $key => $item) {
                $items[$key]['autoDisplay'] = false;
                if ($subsection == $item['name'] && $expanded) {
                    $items[$key]['autoDisplay'] = true;
                }
            }
        }

        return $items;
    }

    private function parseSubUrl()
    {
        $parts = array(null, null);

        $subUrl = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
        $subUrl = trim(trim($subUrl), '/');

        $registry = KTAdminNavigationRegistry::getSingleton();
        if ($registry->isRegistered($subUrl)) {
            $this->expandedSection = true;
            $parts = explode('/', $subUrl);
        }

        return $parts;
    }

    private function getCategory($subUrlCategory = null)
    {
        $category = null;

        if (empty($subUrlCategory)) {
            $category = KTUtil::arrayGet($_REQUEST, 'fCategory', $this->defaultCategory);
        }
        else {
            $category = $subUrlCategory;
        }

        return $category;
    }

    private function getSubsection($subUrlsection = null)
    {
        $subsection = null;

        if (empty($subUrlsection)) {
            $subsection = KTUtil::arrayGet($_REQUEST, 'subsection', null);
        }
        else {
            $subsection = $subUrlsection;
        }

        return $subsection;
    }

    private function sectionExpanded()
    {
        return $this->expandedSection || KTUtil::arrayGet($_REQUEST, 'expanded', false);
    }

    private function includeOlark()
    {
        $user = User::get($_SESSION['userID']);
        $js = preg_replace('/.*[\/\\\\]plugins/', 'plugins', KT_LIVE_DIR) . '/resources/js/olark/olark.js';
        $this->oPage->requireJsResource($js);
        $this->oPage->setBodyOnload("javascript: ktOlark.setUserData('" . $user->getName() . "', '" . $user->getEmail() . "');");
    }

    public function loadSection($section)
    {
        $subUrl = $section['fullname'];
        $registry = KTAdminNavigationRegistry::getSingleton();
        if ($registry->isRegistered($subUrl)) {
            $dispatcher = $registry->getDispatcher($subUrl);
            $dispatcher->setCategoryDetail($subUrl);
            $dispatcher->setActiveStatus($section['autoDisplay']);

            return $dispatcher->dispatch();
        }
    }

    // This function is now just an alias for do_main.
    public function do_viewCategory()
    {
    	return $this->do_main();
    }

}

if ($default->enableAdminSignatures && ($_SESSION['electronic_signature_time'] < time())) {
    $baseUrl = KTUtil::kt_url();
    $url = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    $heading = _kt('You are attempting to access Settings');
    $main->setBodyOnload("javascript: showSignatureForm('{$url}', '{$heading}', 'dms.administration.administration_section_access', 'admin', '{$baseUrl}/browse.php', 'close');");
}

$dispatcher = new AdminSettingsDispatcher();
$dispatcher->dispatch();

?>
