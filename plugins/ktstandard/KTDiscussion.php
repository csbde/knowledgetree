<?php

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . '/discussions/DiscussionThread.inc');
require_once(KT_LIB_DIR . '/discussions/DiscussionComment.inc');

class KTDiscussionPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.discussion.plugin";

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
    function render($context, $oComment, $oThread) {
        $this->oComment = $oComment;
        $oTemplate = $context->oValidator->validateTemplate('ktstandard/action/discussion_comment_list_item');
        $oCreator = User::get($oComment->getUserId());
        $oTemplate->setData(array(
            'comment' => $oComment,
            'state'   => $oThread->getState(),
            'creator' => $oCreator,
            'context' => $context,
        ));
        return $oTemplate->render();
    }
}

class KTDocumentDiscussionAction extends KTDocumentAction {
    var $sDisplayName = 'Discussion';
    var $sName = 'ktcore.actions.document.discussion';

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_("discussion"));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/discussion');

        // Fields for new thread creation
        $fields = array();
        $fields[] = new KTStringWidget(_("Subject"), _("The topic of discussion in this thread"), "subject", "", $this->oPage, true);
        $fields[] = new KTTextWidget(_("Body"), _("Your contribution to the discussion in this thread"), "body", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 10));

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

        $aErrorOptions['message'] = _("No subject provided");
        $sSubject = KTUtil::arrayGet($_REQUEST, 'subject');
        $sSubject = $this->oValidator->validateString($sSubject, $aErrorOptions);

        $aErrorOptions['message'] = _("No body provided");
        $sBody = KTUtil::arrayGet($_REQUEST, 'body');
        $sBody = $this->oValidator->validateString($sBody, $aErrorOptions);

        // Start the transaction around thread and comment creation
        $this->startTransaction();

        $oThread = DiscussionThread::createFromArray(array(
            'documentid' => $this->oDocument->getId(),
            'creatorid' => $this->oUser->getId(),
        ));
        $aErrorOptions['message'] = _("There was an error creating a new thread");
        $this->oValidator->notError($oThread, $aErrorOptions);

        $oComment = DiscussionComment::createFromArray(array(
            'threadid' => $oThread->getId(),
            'userid' => $this->oUser->getId(),
            'subject' => $sSubject,
            'body' => $sBody,
        ));
        $aErrorOptions['message'] = _("There was an error adding the comment to the thread");
        $this->oValidator->notError($oComment, $aErrorOptions);

        $oThread->setFirstCommentId($oComment->getId());
        $oThread->setLastCommentId($oComment->getId());
        $res = $oThread->update();
        $aErrorOptions['message'] = _("There was an error updating the thread with the new comment");
        $this->oValidator->notError($res, $aErrorOptions);

        // Thread and comment created correctly, commit to database
        $this->commitTransaction();

        $this->successRedirectToMain(_("New thread created"), sprintf('fDocumentId=%d', $this->oDocument->getId()));
        exit(0);
    }

    function do_viewthread() {
        $iThreadId = KTUtil::arrayGet($_REQUEST, 'fThreadId');
        $oThread = DiscussionThread::get($iThreadId);

        $iCommentId = $oThread->getFirstCommentId();
        $oComment = DiscussionComment::get($iCommentId);

        // breadcrumbs...
        $this->aBreadcrumbs[] = array(
            'name' => _('discussion'),
            'query' => sprintf('fDocumentId=%d', $this->oDocument->getId()),
        );
        $this->aBreadcrumbs[] = array(
            'name' => $oComment->getSubject(),
        );
        $this->oPage->setBreadcrumbDetails(_("viewing comments"));
        
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/action/discussion_thread');
        
        // Fields for new thread creation
        $replyFields = array();
        $replyFields[] = new KTStringWidget(_("Subject"), _("The topic of discussion in this thread"), "subject", "", $this->oPage, true);
        $replyFields[] = new KTTextWidget(_("Body"), _("Your contribution to the discussion in this thread"), "body", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 10));

        // Fields for closing thread (if user has write permission)
        $closeFields = array();

        $oPermission =& KTPermission::getByName('ktcore.permissions.write');
        if (PEAR::isError($oPermission) || 
            KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $closeFields[] = new KTTextWidget(_("Reason"), _("Describe the reason for closing this thread"), "reason", "", $this->oPage, true, null, null, array("cols" => 50, "rows" => 5));
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


        $aErrorOptions['message'] = _("No subject provided");
        $sSubject = KTUtil::arrayGet($_REQUEST, 'subject');
        $sSubject = $this->oValidator->validateString($sSubject, $aErrorOptions);

        $aErrorOptions['message'] = _("No body provided");
        $sBody = KTUtil::arrayGet($_REQUEST, 'body');
        $sBody = $this->oValidator->validateString($sBody, $aErrorOptions);

        // Start the transaction comment creation
        $this->startTransaction();

        // Create comment
        $oComment = DiscussionComment::createFromArray(array(
            'threadid' => $oThread->getId(),
            'userid' => $this->oUser->getId(),
            'subject' => $sSubject,
            'body' => $sBody,
        ));
        $aErrorOptions['message'] = _("There was an error adding the comment to the thread");
        $this->oValidator->notError($oComment, $aErrorOptions);

        // Update thread
        $oThread->setLastCommentId($oComment->getId());
        $oThread->incrementNumberOfReplies();

        $res = $oThread->update();
        
        $aErrorOptions['message'] = _("There was an error updating the thread with the new comment");
        $this->oValidator->notError($res, $aErrorOptions);



        // Thread and comment created correctly, commit to database
        $this->commitTransaction();

        $this->successRedirectTo('viewThread', _("Reply posted"), sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId()));
        exit(0);
    }

    function do_closethread() {
        $aErrorOptions = array(
            'redirect_to' => array('main', sprintf('fDocumentId=%d', $this->oDocument->getId())),
        );
        
        $iThreadId = KTUtil::arrayGet($_REQUEST, 'fThreadId');
        $oThread = DiscussionThread::get($iThreadId);

        $this->oValidator->notError($oThread, $aErrorOptions);

        $aErrorOptions = array(
            'redirect_to' => array('viewthread', sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId())),
        );

        $oPermission =& KTPermission::getByName('ktcore.permissions.write');
        
        if (PEAR::isError($oPermission)) {
            $this->errorRedirectTo(implode('&', $aErrorOptions['redirect_to']), _("Error getting permission"));
        }
        if (!KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oDocument)) {
            $this->errorRedirectTo(implode('&', $aErrorOptions['redirect_to']), _("You do not have permission to close this thread"));
        }

        $aErrorOptions['message'] = _("No reason provided");
        $sReason = KTUtil::arrayGet($_REQUEST, 'reason');
        $sReason = $this->oValidator->validateString($sReason, $aErrorOptions);

        // Start the transaction comment creation
        $this->startTransaction();

        $oThread->setState(1);
        $oThread->setCloseMetadataVersion($this->oDocument->getMetadataVersion());
        $oThread->setCloseReason($sReason);
        $res = $oThread->update();
        
        $aErrorOptions['message'] = _("There was an error updating the thread with the new comment");
        $this->oValidator->notError($res, $aErrorOptions);

        // Thread closed correctly, so commit
        $this->commitTransaction();

        $this->successRedirectTo('viewThread', _("Thread closed"), sprintf('fDocumentId=%d&fThreadId=%d', $this->oDocument->getId(), $oThread->getId()));
        exit(0);
    }

}
