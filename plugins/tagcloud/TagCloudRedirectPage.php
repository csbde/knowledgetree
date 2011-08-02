<?php
/*
* $Id$
*
* KnowledgeTree Open Source Edition
* Document Management Made Simple
* Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
* You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
* Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
*
*/

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/browse/Criteria.inc');
require_once(KT_LIB_DIR . '/search/savedsearch.inc.php');
require_once(KT_LIB_DIR . '/search/searchutil.inc.php');

require_once(KT_LIB_DIR . '/browse/DocumentCollection.inc.php');
require_once(KT_LIB_DIR . '/browse/BrowseColumns.inc.php');
require_once(KT_LIB_DIR . '/browse/PartialQuery.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/actions/bulkaction.php');

require_once(KT_DIR . '/plugins/tagcloud/TagCloudPortlet.php');
require_once(KT_DIR . '/plugins/tagcloud/TagCloudDashlet.php');

require_once(KT_LIB_DIR .'/render_helpers/browseView.helper.php');

class TagCloudRedirectPage extends KTStandardDispatcher {

    /**
     * Dispatcher main method
     *
     * @return unknown
     */
    public function do_main()
    {
        // Clear the session for a new search
        $url = isset($_REQUEST['tag']) ? ('tag=' . urlencode($_REQUEST['tag']) . '&decode=true') : '';
        $_SESSION['tagList'] = array();
        $this->redirectTo('search', $url);
    }

    /**
     * Recall a previous tag search and remove the tags that were selected after it.
     */
    public function do_recall()
    {
        $tag = $_REQUEST['tag'];
        $pos = $_REQUEST['pos'];

        // Delete all tags after and including the selected tag
        $tagList = $_SESSION['tagList'];
        $tagList = array_slice($tagList, 0, $pos);

        $_SESSION['tagList'] = $tagList;

        $url = 'tag=' . urlencode($tag) . '&decode=true';
        $this->redirectTo('search', $url);
    }

    // FIXME this looks horribly like it contains duplicated browse code,
    //       which is likely not being kept up to date with the current browse code.
    public function do_search()
    {
        // Get the tag to search for and create search query
        $tag = isset($_REQUEST['tag']) ? $_REQUEST['tag'] : '';
        $decode = isset($_REQUEST['decode']) ? $_REQUEST['decode'] : '';
        if ($decode == 'true') {
            $tag = urldecode($tag);
        }

        $userId = $_SESSION['userID'];
        $user = User::get($userId);

        // set page title
        $title =  _kt('Search Results - Tag:') . ' ' . $tag;
        $this->oPage->setBreadcrumbDetails($title);

        // Set tag cloud portlet
        $portlet = new TagCloudPortlet($user, $tag);
        $this->oPage->addPortlet($portlet);

        $browseViewRenderer = BrowseViewUtil::getBrowseView();
        // There are no bulk actions available to this section at the moment.
        // FIXME Add bulk actions menu.
        /*$templateData['bulkActionMenu'] = $browseViewRenderer->renderBulkActionMenu(null);*/
        $folderContentItems = $this->getTagContent($tag);
        $folderView = $preFolderView = array();

        foreach ($folderContentItems['documents'] as $item)
        {
            $item['id'] = $item['document_id'];
            if ($item['checked_out_date'] == 'n/a') {
                $item['checked_out_date'] = '';
            }
            
            $item['hidecheckbox'] = TRUE;

            $preFolderView[] = $browseViewRenderer->renderDocumentItem($item);
        }

        $pageCount = 1;
        $perPage = 15;
        $itemCount = count($preFolderView);
        $curItem = 0;

        $folderView[] = '<div class="page page_' . $pageCount . ' ">';
        foreach ($preFolderView as $item) {
            ++$curItem;
            if ($curItem > $perPage) {
                ++$pageCount;
                $curItem = 1;
                $folderView[] = '</div><div class="page page_' . $pageCount . ' ">';
            }
            $folderView[] = $item;
        }

        if ($itemCount <= 0) {
            $folderView[] = '<span class="notification" id="empty_message">There are currently no viewable items in this folder.</span>';
        }

        $folderView[] = '</div>';

        $this->oFolder = null;

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('kt3/browse');
        $templateData = array(
            'context' => $this,
            'collection' => $collection,
            'custom_title' => $title,
            'isEditable' => true,
            'boolean_search' => false,
            'bulkactions' => KTBulkActionUtil::getAllBulkActions(),
            'browseutil' => new KTBrowseUtil(),
            'tagcloudportlet' => $this->loadTagCloudPortlet($tag, $user),
            'returnaction' => $returnUrl,
        );

        $templateData['folderContents'] = join($folderView);
        $templateData['fragments'] = '';
        $templateData['fragments'] .= $browseViewRenderer->renderDocumentItem(null, true);
        $templateData['fragments'] .= $browseViewRenderer->renderFolderItem(null, true);
        $templateData['pagination'] = $browseViewRenderer->paginateByDiv($pageCount, 'page', 'paginate', 'item', "kt.pages.browse.viewPage('[page]');", "kt.pages.browse.prevPage();", "kt.pages.browse.nextPage();");
        $templateData['javascript'] = $browseViewRenderer->getJavaScript();

        return $template->render($templateData);
    }

    public function getTagContent($tag)
    {
        $userId = $_SESSION['userID'];
        $user = User::get($userId);

        $kt = new KTAPI();
        $session = $kt->start_system_session($user->getUsername());

        $results = $kt->get_tag_contents($tag);

        $ret = array('folders' => array(), 'documents' => $results['results'], 'shortcuts' => array());

        return $ret;
    }
    
    public function loadTagCloudPortlet($tag, $user)
    {
        $portlet = new TagCloudPortlet(NULL, $tag);
        
        $str = '<h2>'._kt('Related Tags').'</h2>'.$portlet->render();
        
        $dashlet = new TagCloudDashlet();
        
        if ($dashlet->is_active($user)) {
            $str .= '<div id="TagCloudDashlet"><h2>'._kt('All Tags').'</h2><div id="tagcloud">'.$dashlet->render().'</div></div>';
        }
        
        
        
        return $str;
    }

}
?>
