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



require_once('KTGraphicalAnalytics.php');

class GraphAnalyticsPage extends KTStandardDispatcher {
    
    
    function __construct()
	{
		$this->permissions = array();
	    //$this->aBreadcrumbs = array(array('action' => 'browse', 'name' => _kt('Browse')));
	    return parent::KTStandardDispatcher();
	}
    
    public function do_main()
	{
	    global $default;
        
	    $templating =& KTTemplating::getSingleton();
	    $template = $templating->loadTemplate('graphspage');
        
        $ktAnalytics = new KTGraphicalAnalytics();
        
		global $main;
	    $templateData = array(
	           'context' => $this,
	           'topTenUsers' => $ktAnalytics->getTop10UsersTemplate(),
	           'topTenDocuments' => $ktAnalytics->getTop10DocumentsTemplate(),
	           'documentViews' => $ktAnalytics->getDocumentViewsOverWeekTemplate(),
	           //'transactionsPerWeek' => $ktAnalytics->getTransactionOverWeekTemplate(),
	           'commentsPerWeek' => $ktAnalytics->getDocumentCommentsPerWeekTemplate(),
	           
			   'likesPerWeek' => $ktAnalytics->getDocumentLikesPerWeekTemplate(),
			   'uploadsPerWeek' => $ktAnalytics->getUploadsPerWeekTemplate(),
			   'userAccessPerWeek' => $ktAnalytics->getUserAccessPerWeekTemplate(),
			   
			   
			   'documentRating' => $ktAnalytics->getDocumentsByRatingTemplate(),
			   
			   
			   
			   
			   
			   'commentsVsViewsPerWeek' => $ktAnalytics->getViewsVsCommentsOverWeekTemplate(),
        );
        
	    return $template->render($templateData);
	}
    
}

$oDispatcher = new GraphAnalyticsPage();
$oDispatcher->dispatch();