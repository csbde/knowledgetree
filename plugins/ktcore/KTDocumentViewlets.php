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

require_once(KT_LIB_DIR . '/actions/documentviewlet.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/database/datetime.inc');
require_once(KT_DIR . '/plugins/comments/comments.php');

class KTWorkflowViewlet extends KTDocumentViewlet {

    public $sName = 'ktcore.viewlets.document.workflow';
    public $_sShowPermission = 'ktcore.permissions.write';
    public $bShowIfWriteShared = true;

    public function getInfo() {
        if ($this->_show() === false) {
            return null;
        }

        return true;
    }

    function displayViewlet()
    {
        $templating =& KTTemplating::getSingleton();
        $template =& $templating->loadTemplate('ktcore/document/viewlets/workflow');
        if (is_null($template)) { return ''; }

        $oWorkflowState = KTWorkflowState::get($this->oDocument->getWorkflowStateId());
        if (PEAR::isError($oWorkflowState)) { return ''; }

        $aDisplayTransitions = array();
        $aTransitions = KTWorkflowUtil::getTransitionsForDocumentUser($this->oDocument, $this->oUser);
        if (empty($aTransitions)) { return ''; }

        // Check if the document has been checked out
        $bIsCheckedOut = $this->oDocument->getIsCheckedOut();
        $iId = $this->oDocument->getId();
        if ($bIsCheckedOut) {
            // If document is checked out, don't link into the workflow.
            $aDisplayTransitions = array();
        }
        else {
            foreach ($aTransitions as $oTransition) {
                if (is_null($oTransition) || PEAR::isError($oTransition)) { continue; }

                $aDisplayTransitions[] = array(
                    'url' => KTUtil::ktLink('action.php', 'ktcore.actions.document.workflow', array('fDocumentId' => $iId, 'action' => 'quicktransition', 'fTransitionId' => $oTransition->getId())),
                    'name' => $oTransition->getName(),
                );
            }
        }

        //Retreive the comment for the previous transition
        $aCommentQuery = array(
            "SELECT comment FROM document_transactions
            where transaction_namespace='ktcore.transactions.workflow_state_transition'
            AND document_id = ?
            ORDER BY id DESC LIMIT 1;"
        );
        $aCommentQuery[] = array($iId);

        $aTransitionComments = DBUtil::getResultArray($aCommentQuery);
        $oLatestTransitionComment = null;

        if (!empty($aTransitionComments)) {
            $aRow = $aTransitionComments[0];
            $oLatestTransitionComment = $aRow['comment'];
            $iCommentPosition = strpos($oLatestTransitionComment,':'); //comment found after first colon in string

             // if comment found
            if ($iCommentPosition > 0) {
                $oLatestTransitionComment = substr($oLatestTransitionComment, $iCommentPosition+2, (strlen($oLatestTransitionComment)-$iCommentPosition));
            }
            // else first state in workflow
            else {
                $oLatestTransitionComment = null;
            }
        }

        $template->setData(array(
            'context' => $this,
            'bIsCheckedOut' => $bIsCheckedOut,
            'transitions' => $aDisplayTransitions,
            'state_name' => $oWorkflowState->getName(),
            'comment' => $oLatestTransitionComment,
        ));

        return $template->render();
    }

}


class KTDocumentActivityFeedAction extends KTDocumentViewlet {

    public $sName = 'ktcore.viewlet.document.activityfeed';
    public $bShowIfReadShared = true;
    public $bShowIfWriteShared = true;
    private $displayMax = 10;

    public function ajax_get_viewlet()
    {
        return $this->displayViewlet(true);
    }

    public function displayViewlet($onlyComments = false)
    {
        $documentId = $this->oDocument->getId();

        $activityFeed = $this->getActivityFeed($this->getDocumentTransactions($documentId));
        $versions = $this->getVersions($this->getMetadataVersions($documentId));
        $comments = $this->getDocumentComments($documentId);
        $activityFeed = array_merge($activityFeed, $versions, $comments);

        usort($activityFeed, array($this, 'sortTable'));

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/document/viewlets/activity_feed');

        $templateData = array(
              'context' => $this,
              'documentId' => $documentId,
              'versions' => $activityFeed,
              'displayMax' => $this->displayMax,
              'commentsCount' => count($activityFeed),
              'onlyComments' => $onlyComments,
        );

        return $template->render($templateData);
    }

    private function getDocumentTransactions($documentId)
    {
        $query = 'SELECT DTT.name AS transaction_name, DT.transaction_namespace, U.name AS user_name, U.email as email,
            DT.version AS version, DT.comment AS comment, DT.datetime AS datetime
            FROM ' . KTUtil::getTableName('document_transactions') . ' AS DT
            INNER JOIN ' . KTUtil::getTableName('users') . ' AS U ON DT.user_id = U.id
            LEFT JOIN ' . KTUtil::getTableName('transaction_types') . ' AS DTT ON DTT.namespace = DT.transaction_namespace
            WHERE DT.document_id = ' . $documentId . '
            AND DT.transaction_namespace != \'ktcore.transactions.view\'
            ORDER BY DT.id DESC';
            // AND DT.transaction_namespace NOT LIKE \'ktcore.transactions.view\' - Not sure why this was a LIKE query.
            // ORDER BY DT.datetime DESC => replaced the order by so that they come out in the order they were added, reversed.

        return $this->getTransactionResult($query);
    }

    private function getTransactionResult($query)
    {
        $res = DBUtil::getResultArray($query);
        if (PEAR::isError($res)) {
            global $default;
            $default->log->error('Error getting the transactions - ' . $res->getMessage());
            $res = array();
        }

        return $res;
    }

    private function getActivityFeed($transactions)
    {
        // Set the namespaces where not in the transactions lookup
        $activityFeed = array();
        foreach($transactions as $key => $transaction) {
            if (empty($transaction['transaction_name'])) {
                $transactions[$key]['transaction_name'] = $this->_getActionNameForNamespace($transaction['transaction_namespace']);
            }

            $activityFeed[] = array(
                'document_name' => $transaction['document_name'],
                'document_link' => KTUtil::buildUrl('view.php', array('fDocumentId' => $transaction['document_id'])),
                'name' => $transaction['user_name'],
                'email' => md5(strtolower($transaction['email'])),
                'transaction_name' => $transaction['transaction_name'],
                'datetime' => getDateTimeDifference($transaction['datetime']),
                'actual_datetime' => $transaction['datetime'],
                'version' => $transaction['version'],
                'comment' => trim($transaction['comment']),
                'type' => 'transaction'
            );
        }

        return $activityFeed;
    }

    private function getMetadataVersions($documentId)
    {
        $metadataVersions = KTDocumentMetadataVersion::getByDocumentContent($documentId);
        if (PEAR::isError($metadataVersions)) {
            global $default;
            $default->log->error('Error getting the versions - ' . $metadataVersions->getMessage());
            $metadataVersions = array();
        }

        return $metadataVersions;
    }

    private function getVersions($metadataVersions)
    {
        $versions = array();
        $prevContentVersion = 0;

        foreach ($metadataVersions as $version) {
            // For each content version there can be multiple metadata versions
            // Allow the earliest metadata version to override the later ones
            $contentVersion = $version['content_version_id'];
            if ($contentVersion == $prevContentVersion) {
                continue;
            }

            $prevContentVersion = $contentVersion;

            $versions[] = array(
                'name' => $version['name'],
                'transaction_name' => _kt('New Document Version'),
                'datetime' => datetimeutil::getLocaleDate($version['datetime']),
                'actual_datetime' => $version['datetime'],
                'version' => $version['major_version'] . '.' . $version['minor_version'],
                'comment' => '',
                'type' => 'version'
            );
        }

        return array_reverse($versions);
    }

    private function getDocumentComments($documentId = null)
    {
        $comments = array();

        try {
            $comments = $this->formatCommentsResult(Comments::getDocumentComments($documentId));
        }
        catch (Exception $e) {
            global $default;
            $default->log->error('Error getting the comments - ' . $e->getMessage());
            $comments = array();
        }

        return $comments;
    }

    private function formatCommentsResult($result)
    {
        $comments = array();

        foreach ($result as $comment) {
            $comments[] = array(
                'document_name' => $comment['document_name'],
                'document_link' => KTUtil::buildUrl('view.php', array('fDocumentId' => $comment['document_id'])),
                'name' => $comment['user_name'],
                'email' => md5(strtolower($comment['email'])),
                'transaction_name' => _kt('Comment'),
                'datetime' => getDateTimeDifference($comment['date']),
                'actual_datetime' => $comment['date'],
                'version' => '',
                'comment' => $comment['comment'],
                'type' => 'comment'
            );
        }

        return $comments;
    }

    function sortTable($a, $b)
    {
        $date1 = new DateTime($a['actual_datetime']);
        $date2 = new DateTime($b['actual_datetime']);

        return $date1 < $date2 ? 1 : -1;
    }

    public function displayGlobalActivityFeed()
    {
        // You evil evil duplicator, you!

        $filter = array(
            'ktcore.transactions.create',
            'ktcore.transactions.delete',
            'ktcore.transactions.check_in'
        );
        $activityFeed = $this->getActivityFeed($this->getAllTransactions($filter));

        $comments = $this->getAllComments();
        $activityFeed = array_merge($activityFeed, $comments);

        usort($activityFeed, array($this, 'sortTable'));

        $templating =& KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/document/viewlets/global_activity_feed');

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
            {$this->getPermissionsQuery()}
            DT.transaction_namespace != 'ktcore.transactions.view'
            {$this->buildFilterQuery($filter)}
            AND DT.document_id = D.id
            ORDER BY DT.id DESC";

        return $this->getTransactionResult(array($query, $permissionParams));
    }

    // FIXME Lots of duplication, see comments plugin.
    private function getPermissionsQuery()
    {
        if ($this->inAdminMode()) {
            return 'WHERE';
        }
        else {
            $user = User::get($_SESSION['userID']);
            $permission = KTPermission::getByName('ktcore.permissions.read');
            $permId = $permission->getID();
            $permissionDescriptors = KTPermissionUtil::getPermissionDescriptorsForUser($user);
            $permissionDescriptors = empty($permissionDescriptors) ? -1 : implode(',', $permissionDescriptors);

            $query = "INNER JOIN permission_lookups AS PL ON D.permission_lookup_id = PL.id
                INNER JOIN permission_lookup_assignments AS PLA ON PL.id = PLA.permission_lookup_id
                AND PLA.permission_id = $permId
                WHERE PLA.permission_descriptor_id IN ($permissionDescriptors) AND";

            return $query;
        }
    }

    private function inAdminMode()
    {
        return isset($_SESSION['adminmode'])
            && ((int)$_SESSION['adminmode'])
            && Permission::adminIsInAdminMode();
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

    private function getAllComments()
    {
        $comments = array();

        try {
            $comments = $this->formatCommentsResult(Comments::getAllComments());
        }
        catch (Exception $e) {
            global $default;
            $default->log->error('Error getting the comments - ' . $e->getMessage());
            $comments = array();
        }

        return $comments;
    }

    function _getActionNameForNamespace($sNamespace)
    {
        $aNames = split('\.', $sNamespace);
        $sName = array_pop($aNames);
        $sName = str_replace('_', ' ', $sName);
        $sName = ucwords($sName);

        return $sName;
    }

    function getUserForId($iUserId)
    {
        $user = User::get($iUserId);
        if (PEAR::isError($user) || ($user == false)) { return _kt('User no longer exists'); }

        return $user->getName();
    }

    function getEmailForId($iUserId)
    {
        $user = User::get($iUserId);
        if (PEAR::isError($user) || ($user == false)) { return _kt('User no longer exists'); }

        return $user->getEmail();
    }

}

class KTInlineEditViewlet extends KTDocumentViewlet {

    public $sName = 'ktcore.viewlets.document.inline.edit';
    public $_sShowPermission = 'ktcore.permissions.write';
    public $bShowIfReadShared = true;
    public $bShowIfWriteShared = true;

    function displayViewlet()
    {
        $templating =& KTTemplating::getSingleton();
        $template =& $templating->loadTemplate("ktcore/document/viewlets/inline_edit");
        if (is_null($template)) { return ""; }
        // Get document fieldsets
        $fieldsets = array();
        $fieldsetDisplayReg = KTFieldsetDisplayRegistry::getSingleton();
        $aDocFieldsets = KTMetadataUtil::fieldsetsForDocument($this->oDocument);
        foreach ($aDocFieldsets as $oFieldset) {
            $displayClass = $fieldsetDisplayReg->getHandler($oFieldset->getNamespace());
            array_push($fieldsets, new $displayClass($oFieldset));
        }

        $template->setData(array(
            'context' => $this,
            'document' => $this->oDocument,
            'fieldsetDisplayHelper' => new KTFieldsetDisplay(),
        ));

        return $template->render();
    }

}

?>
