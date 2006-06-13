<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/discussions/DiscussionThread.inc');
require_once(KT_LIB_DIR . '/discussions/DiscussionComment.inc');


define('DISCUSSION_NEW', 0);
define('DISCUSSION_OPEN', 1);
define('DISCUSSION_FINALISED', 2);
define('DISCUSSION_IMPLEMENTED', 3);
define('DISCUSSION_CLOSED', 4);


class KTDiscussionPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.discussion.plugin";
    var $autoRegister = true;

    function KTDiscussionPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Document Discussions Plugin');
        return $res;
    }            

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentDiscussionAction', 'ktcore.actions.document.discussion');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTDiscussionPlugin', 'ktstandard.discussion.plugin', __FILE__);

class KTDiscussionThreadListRenderer {
    function render($context, $oThread) {
        $this->oThread = $oThread;
        $oTemplate = $context->oValidator->validateTemplate('ktstandard/action/discussion_thread_list_item');
        $oFirstComment = DiscussionComment::get($oThread->getFirstCommentId());
        if (PEAR::isError($oFirstComment)) {
            return null;
        }
        $oLastComment = DiscussionComment::get($oThread->getLastCommentId());
        if (PEAR::isError($oLastComment)) {
            return null;
        }
        $oCreator = User::get($oThread->getCreatorId());
        $oTemplate->setData(array(
            'thread' => $this->oThread,
            'first_comment' => $oFirstComment,
            'last_comment' => $oLastComment,
            'creator' => $oCreator,
            'context' => $context,
        ));
        return $oTemplate->render();
    }
}

class KTCommentListRenderer {
    var $bCycle = false;
    
    function render($context, $oComment, $oThread) {
        $this->oComment = $oComment;
        $this->bCycle = !$this->bCycle;

        $oTemplate = $context->oValidator->validateTemplate('ktstandard/action/discussion_comment_list_item');
        $oCreator = User::get($oComment->getUserId());
        
        $oTemplate->setData(array(
            'comment' => $oComment,
            'state'   => $oThread->getState(),
            'creator' => $oCreator,
            'context' => $context,
            'cycle'   => $this->bCycle,
        ));
        return $oTemplate->render();
    }
}

class KTDocumentDiscussionAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.discussion';
    var $aTransitions = array(DISCUSSION_NEW => array(DISCUSSION_OPEN),
			      DISCUSSION_OPEN => array(DISCUSSION_FINALISED),
			      DISCUSSION_FINALISED => array(DISCUSSION_OPEN, DISCUSSION_IMPLEMENTED),
			      DISCUSSION_IMPLEMENTED => array(DISCUSSION_CLOSED, DISCUSSION_OPEN),
			      DISCUSSION_CLOSED => array());

    var $aStateNames = array(DISCUSSION_NEW => 'New',
			     DISCUSSION_OPEN => 'Under discussion',
			     DISCUSSION_FINALISED => 'Finalised - To be implemented',
			     DISCUSSION_IMPLEMENTED => 'Finalised - Implemented',
			     DISCUSSION_CLOSED => 'Closed');
    

    function getDisplayName() {
        return _kt('Discussion');
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt("discussion"));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/discussion');

        // Fields for new thread creation
        $fields = array();
        $fields[] = new KTStringWidget(_kt("Subject"), _kt("The topic of discussion in this thread"), "subject", "", $this->oPage, true);
        $fields[] = new KTTextWidget(_kt("Body"), _kt("Your contribution to the discussion in this thread"), "body", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 10));

	$bIncludeClosed = KTUtil::arrayGet($_REQUEST, 'fIncludeClosed', false);

	$sQuery = sprintf('document_id = %d', $this->oDocument->getId());
	if(!$bIncludeClosed) {
	    $sQuery .= sprintf(' AND state != %d', DISCUSSION_CLOSED);
	}

        $threads = DiscussionThread::getList($sQuery);

        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
            'threads' => $threads,
            'threadrenderer' => new KTDiscussionThreadListRenderer(),
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_newthread() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fDocumentId=%d', $this->oDocument->getId())),
        );

        $aErrorOptions['message'] = _kt("No subject provided");
        $sSubject = KTUtil::arrayGet($_REQUEST, 'subject');
        $sSubject = $this->oValidator->validateString($sSubject, $aErrorOptions);

        $aErrorOptions['message'] = _kt("No body provided");
        $sBody = KTUtil::arrayGet($_REQUEST, 'body');
        $sBody = $this->oValidator->validateString($sBody, $aErrorOptions);

        // Start the transaction around thread and comment creation
        $this->startTransaction();

        $oThread = DiscussionThread::createFromArray(array(
            'documentid' => $this->oDocument->getId(),
            'creatorid' => $this->oUser->getId(),
        ));
        $aErrorOptions['message'] = _kt("There was an error creating a new thread");
        $this->oValidator->notError($oThread, $aErrorOptions);

        $oComment = DiscussionComment::createFromArray(array(
            'threadid' => $oThread->getId(),
            'userid' => $this->oUser->getId(),
            'subject' => $sSubject,
            'body' => KTUtil::formatPlainText($sBody),
        ));
        $aErrorOptions['message'] = _kt("There was an error adding the comment to the thread");
        $this->oValidator->notError($oComment, $aErrorOptions);

        $oThread->setFirstCommentId($oComment->getId());
        $oThread->setLastCommentId($oComment->getId());
        
        // add to searchable_text.
        $sTable = KTUtil::getTableName('comment_searchable_text');
        $aSearch = array(
            'comment_id' => $oComment->getId(),
            'document_id' => $this->oDocument->getId(),
            'body' => sprintf("%s %s", KTUtil::formatPlainText($sBody), $sSubject),
        );
        DBUtil::autoInsert($sTable,
            $aSearch,
            array('noid' => true));
        
        $res = $oThread->update();
        $aErrorOptions['message'] = _kt("There was an error updating the thread with the new comment");
        $this->oValidator->notError($res, $aErrorOptions);

        // Thread and comment created correctly, commit to database
        $this->commitTransaction();

        $this->successRedirectToMain(_kt("New thread created"), sprintf('fDocumentId=%d', $this->oDocument->getId()));
        exit(0);
    }

    function do_viewthread() {
        $iThreadId = KTUtil::arrayGet($_REQUEST, 'fThreadId');
        $oThread = DiscussionThread::get($iThreadId);

        $iCommentId = $oThread->getFirstCommentId();
        $oComment = DiscussionComment::get($iCommentId);

        // breadcrumbs...
        $this->aBreadcrumbs[] = array(
            'name' => _kt('discussion'),
            'query' => sprintf('fDocumentId=%d', $this->oDocument->getId()),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $oComment->getSubject(),
        );
        $this->oPage->setBreadcrumbDetails(_kt("viewing comments"));
        
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/discussion_thread');
        
        // Fields for new thread creation
        $replyFields = array();
        $replyFields[] = new KTStringWidget(_kt("Subject"), _kt("The topic of discussion in this thread"), "subject", "", $this->oPage, true);
        $replyFields[] = new KTTextWidget(_kt("Body"), _kt("Your contribution to the discussion in this thread"), "body", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 10));

        // Fields for closing thread (if user has workflow permission)
        $closeFields = array();
        $oPermission =& KTPermission::getByName('ktcore.permissions.workflow');

        if (!PEAR::isError($oPermission) && KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument) && $oThread->getState() != DISCUSSION_CLOSED) {
	    $aOptions = array('vocab' => $this->_buildStates($oThread));
	    $closeFields[] = new KTLookupWidget(_kt("State"), _kt("Select the state to move this discussion into"), "state", "", $this->oPage, true, null, null, $aOptions);
	    $closeFields[] = new KTTextWidget(_kt("Reason"), _kt("Describe the reason for the state change, or the conclusion reached through discussion"), "reason", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 5));
        }
        
        // increment views
        $oThread->incrementNumberOfViews();
        $oThread->update();

        $aTemplateData = array(
            'context' => &$this,
            'replyfields' => $replyFields,
            'closefields' => $closeFields,
            'thread' => $oThread,
            'commentrenderer' => new KTCommentListRenderer(),
        );
        
        return $oTemplate->render($aTemplateData);
    }

    function do_postreply() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fDocumentId=%d', $this->oDocument->getId())),
        );
        $iThreadId = KTUtil::arrayGet($_REQUEST, 'fThreadId');
        $oThread = DiscussionThread::get($iThreadId);

        $this->oValidator->notError($oThread, $aErrorOptions);

        $aErrorOptions = array(
            'redirect_to' => array('viewthread', sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId())),
        );


        $aErrorOptions['message'] = _kt("No subject provided");
        $sSubject = KTUtil::arrayGet($_REQUEST, 'subject');
        $sSubject = $this->oValidator->validateString($sSubject, $aErrorOptions);

        $aErrorOptions['message'] = _kt("No body provided");
        $sBody = KTUtil::arrayGet($_REQUEST, 'body');
        $sBody = $this->oValidator->validateString($sBody, $aErrorOptions);

        // Start the transaction comment creation
        $this->startTransaction();

        // Create comment
        $oComment = DiscussionComment::createFromArray(array(
            'threadid' => $oThread->getId(),
            'userid' => $this->oUser->getId(),
            'subject' => $sSubject,
            'body' => KTUtil::formatPlainText($sBody),
        ));
        $aErrorOptions['message'] = _kt("There was an error adding the comment to the thread");
        $this->oValidator->notError($oComment, $aErrorOptions);

        // Update thread
        $oThread->setLastCommentId($oComment->getId());
        $oThread->incrementNumberOfReplies();

        $res = $oThread->update();
        
        // add to searchable_text.
        $sTable = KTUtil::getTableName('comment_searchable_text');
        $aSearch = array(
            'comment_id' => $oComment->getId(),
            'document_id' => $this->oDocument->getId(),
            'body' => sprintf("%s %s", KTUtil::formatPlainText($sBody), $sSubject),
        );
        DBUtil::autoInsert($sTable,
            $aSearch,
            array('noid' => true));
        
        $aErrorOptions['message'] = _kt("There was an error updating the thread with the new comment");
        $this->oValidator->notError($res, $aErrorOptions);



        // Thread and comment created correctly, commit to database
        $this->commitTransaction();

        $this->successRedirectTo('viewThread', _kt("Reply posted"), sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId()));
        exit(0);
    }

    function do_changestate() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fDocumentId=%d', $this->oDocument->getId())),
        );
        
        $iThreadId = KTUtil::arrayGet($_REQUEST, 'fThreadId');
        $oThread = DiscussionThread::get($iThreadId);
        $this->oValidator->notError($oThread, $aErrorOptions);

        $aErrorOptions = array(
            'redirect_to' => array('viewthread', sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId())),
        );

        $oPermission =& KTPermission::getByName('ktcore.permissions.workflow');
	$sRedirectTo = implode('&', $aErrorOptions['redirect_to']);
        
        if (PEAR::isError($oPermission)) {
            $this->errorRedirectTo($sRedirectTo, _kt("Error getting permission"));
	    exit(0);
        }
        if (!KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $this->errorRedirectTo($sRedirectTo, _kt("You do not have permission to close this thread"));
	    exit(0);
        }

	$iStateId = KTUtil::arrayGet($_REQUEST, 'state');
	if(!in_array($iStateId, $this->aTransitions[$oThread->getState()])) {
	    $this->errorRedirectTo($sRedirectTo, _kt("Invalid transition"));	
	    exit(0);
	}
	
	$aErrorOptions['message'] = _kt("No reason provided");
	$sReason = $this->oValidator->validateString(KTUtil::arrayGet($_REQUEST, 'reason'), $aErrorOptions);
	
	if($iStateId > $oThread->getState()) {
	    $sTransactionNamespace = 'ktcore.transactions.collaboration_step_approve';
	} else {
	    $sTransactionNamespace = 'ktcore.transactions.collaboration_step_rollback';
	}

        // Start the transaction comment creation
        $this->startTransaction();

        $oThread->setState($iStateId);
	if($iStateId == DISCUSSION_CLOSED) {
	    $oThread->setCloseMetadataVersion($this->oDocument->getMetadataVersion());
	} else if($iStateId == DISCUSSION_FINALISED) {	
	    $oThread->setCloseReason($sReason);
	}

        $oDocumentTransaction = & new DocumentTransaction($this->oDocument, $sReason, $sTransactionNamespace);
        $oDocumentTransaction->create();

        $res = $oThread->update();
        
        $aErrorOptions['message'] = _kt("There was an error updating the thread with the new comment");
        $this->oValidator->notError($res, $aErrorOptions);

        // Thread closed correctly, so commit
        $this->commitTransaction();

        $this->successRedirectTo('viewThread', _kt("Thread state changed"), sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId()));
        exit(0);
    }




    function &_buildStates(&$oThread) {
	$iCurState = $oThread->getState();
	$aTransitions = $this->aTransitions[$iCurState];

	$aVocab = array();

	foreach($aTransitions as $iTransition) {
	    $aVocab[$iTransition] = $this->aStateNames[$iTransition];
	}
	return $aVocab;
    }

    function _getStateName($iStateId) {
	return KTUtil::arrayGet($this->aStateNames, $iStateId, 'Unnamed state');
    }


}
