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

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/documentmanagement/documentutil.inc.php');

class documentViewletServices extends client_service {
	
	public function run_action($params) {
		$classaction = $params['action'];
		$classname = $params['name'];
		$classpath = $params['class'];
		$classpath = str_replace('/./', '/', $classpath);
		if(file_exists($classpath)) {
			require_once($classpath);
			$class = new $classname();
			$class->$classaction($params);
		}
		
	}
	
    public function comments($params) {
    	$action = array();
		$documentId = $params['documentId'];
		$document = Document::get($documentId);
		$user = User::get($_SESSION['userID']);
    	$action[] = 'ktcore.viewlet.document.activityfeed';
    	$actions = KTDocumentActionUtil::getDocumentActionsByNames($action, 'documentviewlet', $document, $user);
    	if(count($actions) > 0) {
			$commentsAction = $actions[0];
			if($commentsAction instanceof KTDocumentActivityFeedAction)
				$comments = $commentsAction->ajax_get_viewlet();
				$this->addResponse('success', $comments);
    	}
    	
    	return true;
    }
	
	public function versionAndFileName($params) {
    	$action = array();
		$documentId = $params['documentId'];
		$document = Document::get($documentId);
    	$this->addResponse('filename', $document->getFileName());
    	$this->addResponse('version', $document->getVersion());
    	return true;
    }
    
}
?>