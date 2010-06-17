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

class Comments
{
    /**
     * Get the list of comments on a document ordered by the date created
     *
     * @param int $document_id
     * @return array
     */
    static public function get_comments($document_id, $order = 'DESC')
    {
        global $default;
        $list = array();
        if(!is_numeric($document_id)){
            $default->log->error('COMMENTS|get: Document ID must be numeric');
            throw new Exception('Document ID must be numeric', 1);
        }

        $sql = "SELECT c.id, c.user_id, c.comment, c.date_created AS date, u.name AS user_name FROM document_comments c
            INNER JOIN users u on u.id = c.user_id
            WHERE document_id = {$document_id}
            ORDER BY date_created {$order}";

        $list = DBUtil::getResultArray($sql);

        if(PEAR::isError($list)){
            $default->log->error("COMMENTS|get: Error fetching document comments: {$list->getMessage()}");
            throw new Exception("Error fetching document comments: {$list->getMessage()}", 1);
        }

        $formatted_list = array();
        foreach ($list as $item){
            $item['action'] = '';
            $item['version'] = '';
            $formatted_list[] = $item;
        }

        return $formatted_list;
    }

    /**
     * Add a comment on a document
     *
     * @param int $document_id
     * @param string $comment
     */
    static public function add_comment($document_id, $comment)
    {
        global $default;
        if(!is_numeric($document_id)){
            $default->log->error('COMMENTS|add: Document ID must be numeric');
            throw new Exception('Document ID must be numeric', 1);
        }

        if(empty($comment)){
            $default->log->warn('COMMENTS|add: Comment can\'t be empty');
            throw new Exception('Comment can\'t be empty', 1);
        }

        $date = date('Y-m-d H:i:s');
        $user_id = $_SESSION['userID'];

        $fields = array();
        $fields['document_id'] = $document_id;
        $fields['user_id'] = $user_id;
        $fields['date_created'] = $date;
        $fields['comment'] = $comment;

        $res = DBUtil::autoInsert('document_comments', $fields);

        if(PEAR::isError($res)){
            $default->log->error("COMMENTS|add: Error saving comment: {$res->getMessage()}");
            throw new Exception("Error saving comment: {$res->getMessage()}", 1);
        }

        return $res;
    }

    /**
     * Not Used!
     * Allow the user to edit the comment within a few minutes of posting it.
     *
     * @param int $comment_id The id of the comment being edited
     * @param string $comment The updated comment
     */
    static public function update_comment($comment_id, $comment)
    {
        global $default;
        if(!is_numeric($comment_id)){
            $default->log->error('COMMENTS|update: Comment ID must be numeric');
            throw new Exception('Comment ID must be numeric', 1);
        }

        if(empty($comment)){
            $default->log->warn('COMMENTS|update: Comment can\'t be empty');
            throw new Exception('Comment can\'t be empty', 1);
        }

        $fields = array();
        $fields['comment'] = $comment;

        $res = DBUtil::autoUpdate('document_comments', $fields, $comment_id);

        if(PEAR::isError($res)){
            $default->log->error("COMMENTS|update: Error updating comment: {$res->getMessage()}");
            throw new Exception("Error updating comment: {$res->getMessage()}", 1);
        }

        return $res;
    }

    /**
     * Not Used!
     * Allow the user to delete the comment within a few minutes of posting it.
     * Or use as a moderator tool
     *
     * @param int $comment_id The id of the comment being deleted
     */
    static public function delete_comment($comment_id)
    {
        global $default;
        if(!is_numeric($comment_id)){
            $default->log->error('COMMENTS|delete: Comment ID must be numeric');
            throw new Exception('Comment ID must be numeric', 1);
        }

        $res = DBUtil::autoDelete('document_comments', $comment_id);

        if(PEAR::isError($res)){
            $default->log->error("COMMENTS|delete: Error deleting comment: {$res->getMessage()}");
            throw new Exception("Error deleting comment: {$res->getMessage()}", 1);
        }

        return $res;
    }
}

?>