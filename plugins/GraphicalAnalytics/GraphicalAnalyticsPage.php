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
 */

// main library routines and defaults
require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');

require_once('GraphicalAnalytics.php');

class GraphAnalyticsPage extends KTStandardDispatcher {

	public $tierCantView = array('starter', 'professional', 'company', 'team');

    function __construct()
	{
		$this->permissions = array();
	    //$this->aBreadcrumbs = array(array('action' => 'browse', 'name' => _kt('Browse')));
	    return parent::KTStandardDispatcher();
	}

    public function do_main()
	{
		global $main;
		$this->oPage->title = _kt('Analytics');

	    $templating =& KTTemplating::getSingleton();
	    $template = $templating->loadTemplate('graphspage');
        $ktAnalytics = new GraphicalAnalytics();

	    $templateData = array(
	           'context' => $this,

			   // Dashlets
			   'userAccessPerWeek' => $ktAnalytics->getUserAccessPerWeekDashlet(),
			   'uploadsPerWeek' => $ktAnalytics->getUploadsPerWeekDashlet(),
			   'documentRating' => $ktAnalytics->getDocumentsByRatingTemplate(TRUE), // TRUE for Dashlet
			   'topFiveDocuments' => $ktAnalytics->getTop5DocumentsDashlet(),
	           'topFiveUsers' => $ktAnalytics->getTop5UsersDashlet(),
	           'mostViewedDocuments' => $ktAnalytics->getMostViewedDocumentsDashlet(),

			   // For the Page
			   'top10Documents' => $ktAnalytics->getTop10DocumentsTemplate(),
			   'documentsByRating' => $ktAnalytics->getDocumentsByRatingTemplate(),
			   'top10Users' => $ktAnalytics->getTop10UsersTemplate(),
			   'uploadsPerWeek' => $ktAnalytics->getUploadsPerWeekTemplate(),
			   'userAccessPerWeek' => $ktAnalytics->getUserAccessPerWeekTemplate(),
			   'transactionPerWeek' => $ktAnalytics->getTransactionOverWeekTemplate(),
			   'viewsVsComments' => $ktAnalytics->getViewsVsCommentsOverWeekTemplate(),
			   'commentsPerWeek' => $ktAnalytics->getDocumentCommentsPerWeekTemplate(),
			   'likesPerWeek' => $ktAnalytics->getDocumentLikesPerWeekTemplate(),

			   'checkInsVsCheckouts' => $ktAnalytics->getTransactionTypesPerWeekTemplate(),
			   'sharing_per_week' => $ktAnalytics->getSharingPerWeekTemplate(),

        );

	    return $template->render($templateData);
	}

    public function planDenied()
    {
    	global $default;
	    $templating =& KTTemplating::getSingleton();
	    $template = $templating->loadTemplate('tier');
        // Page title
        $this->oPage->title = _kt('Blocked');
        // Don't sanitize the info, as we would like to display a link
        $this->oPage->allowHTML = true;
        // Empty content
        $this->oPage->setPageContents('<div></div>');
        // Remove all js
        $this->oPage->js_resources = array();
        $this->oPage->js_standalone = array();
        $this->oPage->setUser($this->oUser);
        $this->oPage->hideSection();
        $this->oPage->contents = $template->render();
        $this->oPage->render();

        exit(0);
    }

}

$oDispatcher = new GraphAnalyticsPage();
$oDispatcher->dispatch();