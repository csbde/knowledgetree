<?php

/**
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

require_once(KT_LIB_DIR . '/actions/dashboardviewlet.inc.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_DIR . '/plugins/ktcore/KTDocumentViewlets.php');
require_once('KTGraphicalAnalytics.php');

class KTDashboardActivityFeedAction extends KTDashboardViewlet {

    public $sName = 'ktcore.viewlet.dashboard.activityfeed';
    public $bShowIfReadShared = true;
    public $bShowIfWriteShared = true;
    public $order = 2;
    private $displayMax = 10;
    private $documentActivityFeedAction;

    public function __construct()
    {
    	parent::KTDashboardViewlet();
    	$this->documentActivityFeedAction = new KTDocumentActivityFeedAction();
    }

    public function getCSSName()
    {
    	return 'activityfeed';
    }

    public function displayViewlet()
    {
        $page = $GLOBALS['main'];
        $page->requireCSSResource('resources/css/newui/browseView.css');

        // FIXME There is some duplication here.
        //       The mime icon stuff for instance can be abstracted to
        //       a third file and used both here and in the browse view.

        $filter = array(
            'ktcore.transactions.create',
            'ktcore.transactions.delete',
            'ktcore.transactions.check_in'
        );
        $activityFeed = $this->documentActivityFeedAction->getActivityFeed($this->getAllTransactions($filter));

        $comments = $this->documentActivityFeedAction->getAllComments();
        $activityFeed = array_merge($activityFeed, $comments);
        $activityFeed = $this->setMimeIcons($activityFeed);

        usort($activityFeed, array($this, 'sortTable'));

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/dashboard/viewlets/global_activity_feed');

        $templateData = array(
              'context' => $this,
              'documentId' => $documentId,
              'versions' => $activityFeed,
              'displayMax' => $this->displayMax,
              'commentsCount' => count($activityFeed)
        );

        return $template->render($templateData);
    }

    private function getAllTransactions($filter = array())
    {
        $query = "SELECT D.id as document_id, DMV.name as document_name,
            DCV.mime_id,
            DTT.name AS transaction_name, DT.transaction_namespace,
            U.name AS user_name, U.email as email,
            DT.version AS version, DT.comment AS comment, DT.datetime AS datetime
            FROM " . KTUtil::getTableName('document_transactions') . " AS DT
            INNER JOIN " . KTUtil::getTableName('users') . " AS U ON DT.user_id = U.id
            LEFT JOIN " . KTUtil::getTableName('transaction_types') . "
            AS DTT ON DTT.namespace = DT.transaction_namespace,
            documents D
            INNER JOIN document_metadata_version DMV ON DMV.id = D.metadata_version_id
            INNER JOIN document_content_version DCV ON DCV.id = DMV.content_version_id
            {$this->documentActivityFeedAction->getPermissionsQuery()}
            DT.transaction_namespace != 'ktcore.transactions.view'
            {$this->buildFilterQuery($filter)}
            AND DT.document_id = D.id
            ORDER BY DT.id DESC";

        return $this->documentActivityFeedAction->getTransactionResult(array($query, $permissionParams));
    }

	private function buildFilterQuery($filter = array())
    {
        $filterQuery = '';

        if (!empty($filter)) {
            foreach ($filter as $namespace) {
                $filterQueries[] = "DT.transaction_namespace = '$namespace'";
            }
            $filterQuery = 'AND (' . implode(' OR ', $filterQueries) . ')';
        }

        return $filterQuery;
    }

	private function setMimeIcons($activityFeed)
    {
        foreach ($activityFeed as $key => $item) {
            $iconFile = 'resources/mimetypes/newui/' . KTMime::getIconPath($item['mime_id']) . '.png';
            $item['icon_exists'] = file_exists(KT_DIR . '/' . $iconFile);
            $item['icon_file'] = $iconFile;

            if ($item['icon_exists']) {
                $item['mimeicon'] = str_replace('\\', '/', $GLOBALS['default']->rootUrl . '/' . $iconFile);
                $item['mimeicon'] = 'background-image: url(' . $item['mimeicon'] . ')';
            }
            else {
                $item['mimeicon'] = '';
            }

            $activityFeed[$key] = $item;
        }

        return $activityFeed;
    }
}

class KTGraphicalAnalytics extends KTDashboardViewlet
{
    public $sName = 'ktcore.viewlet.dashboard.analytics';
    public $bShowIfReadShared = true;
    public $bShowIfWriteShared = true;
	public $order = 1;

    public function getCSSName()
    {
    	return 'graphicalanalytics';
    }

	public function displayViewlet()
	{
        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/dashboard/viewlets/graphical_analytics');

        $templateData = array(
              'context' => $this,
        );

        return $template->render($templateData);
	}
}
?>
