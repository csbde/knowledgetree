<?php

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/discussions/DiscussionThread.inc');
require_once(KT_LIB_DIR . '/discussions/DiscussionComment.inc');

$oKTActionRegistry =& KTActionRegistry::getSingleton();

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
    function render($context, $oComment) {
        $this->oComment = $oComment;
        $oTemplate = $context->oValidator->validateTemplate('ktstandard/action/discussion_comment_list_item');
        $oCreator = User::get($oComment->getUserId());
        $oTemplate->setData(array(
            'comment' => $oComment,
            'creator' => $oCreator,
            'context' => $context,
        ));
        return $oTemplate->render();
    }
}

class KTDocumentDiscussionAction extends KTDocumentAction {
    var $sBuiltInAction = 'viewDiscussion';
    var $sDisplayName = 'Discussion';
    var $sName = 'ktcore.actions.document.discussion';

    function do_main() {
        $this->oPage->setBreadcrumbDetails("discussion");
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/discussion');

        // Fields for new thread creation
        $fields = array();
        $fields[] = new KTStringWidget("Subject", "The topic of discussion in this thread", "subject", "", $this->oPage, true);
        $fields[] = new KTTextWidget("Body", "Your contribution to the discussion in this thread", "body", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 10));

        $threads = DiscussionThread::getList();

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

        $aErrorOptions['message'] = "No subject provided";
        $sSubject = KTUtil::arrayGet($_REQUEST, 'subject');
        $sSubject = $this->oValidator->validateString($sSubject, $aErrorOptions);

        $aErrorOptions['message'] = "No body provided";
        $sBody = KTUtil::arrayGet($_REQUEST, 'body');
        $sBody = $this->oValidator->validateString($sBody, $aErrorOptions);

        // Start the transaction around thread and comment creation
        $this->startTransaction();

        $oThread = DiscussionThread::createFromArray(array(
            'documentid' => $this->oDocument->getId(),
            'creatorid' => $this->oUser->getId(),
        ));
        $aErrorOptions['message'] = "There was an error creating a new thread";
        $this->oValidator->notError($oThread);

        $oComment = DiscussionComment::createFromArray(array(
            'threadid' => $oThread->getId(),
            'userid' => $this->oUser->getId(),
            'subject' => $sSubject,
            'body' => $sBody,
        ));
        $aErrorOptions['message'] = "There was an error adding the comment to the thread";
        $this->oValidator->notError($oComment);

        $oThread->setFirstCommentId($oComment->getId());
        $oThread->setLastCommentId($oComment->getId());
        $res = $oThread->update();
        $aErrorOptions['message'] = "There was an error updating the thread with the new comment";
        $this->oValidator->notError($res);

        // Thread and comment created correctly, commit to database
        $this->commitTransaction();

        $this->successRedirectToMain("New thread created", sprintf('fDocumentId=%d', $this->oDocument->getId()));
        exit(0);
    }

    function do_viewthread() {
        $iThreadId = KTUtil::arrayGet($_REQUEST, 'fThreadId');
        $oThread = DiscussionThread::get($iThreadId);

        $iCommentId = $oThread->getFirstCommentId();
        $oComment = DiscussionComment::get($iCommentId);

        $this->aBreadcrumbs[] = array(
            'name' => 'discussion',
            'url' => $_SERVER['PHP_SELF'] . sprintf('?fDocumentId=%d', $this->oDocument->getId()),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $oComment->getSubject(),
        );
        $this->oPage->setBreadcrumbDetails("viewing comments");
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/discussion_thread');
        // Fields for new thread creation
        $fields = array();
        $fields[] = new KTStringWidget("Subject", "The topic of discussion in this thread", "subject", "", $this->oPage, true);
        $fields[] = new KTTextWidget("Body", "Your contribution to the discussion in this thread", "body", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 10));

        $aTemplateData = array(
            'context' => &$this,
            'fields' => $fields,
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


        $aErrorOptions['message'] = "No subject provided";
        $sSubject = KTUtil::arrayGet($_REQUEST, 'subject');
        $sSubject = $this->oValidator->validateString($sSubject, $aErrorOptions);

        $aErrorOptions['message'] = "No body provided";
        $sBody = KTUtil::arrayGet($_REQUEST, 'body');
        $sBody = $this->oValidator->validateString($sBody, $aErrorOptions);

        // Start the transaction comment creation
        $this->startTransaction();

        $oComment = DiscussionComment::createFromArray(array(
            'threadid' => $oThread->getId(),
            'userid' => $this->oUser->getId(),
            'subject' => $sSubject,
            'body' => $sBody,
        ));
        $aErrorOptions['message'] = "There was an error adding the comment to the thread";
        $this->oValidator->notError($oComment, $aErrorOptions);

        $oThread->setLastCommentId($oComment->getId());
        $res = $oThread->update();
        $aErrorOptions['message'] = "There was an error updating the thread with the new comment";
        $this->oValidator->notError($res, $aErrorOptions);

        // Thread and comment created correctly, commit to database
        $this->commitTransaction();

        $this->successRedirectTo('viewThread', "Reply posted", sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId()));
        exit(0);
    }
}
$oKTActionRegistry->registerAction('documentaction', 'KTDocumentDiscussionAction', 'ktcore.actions.document.discussion');

