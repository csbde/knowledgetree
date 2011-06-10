<?php
/*
* $Id: $
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

// FIXME Looks like some duplication or very similar functionality between classes.

/**
 * Trigger for document add (postValidate)
 */
class KTAddDocumentTrigger {

    var $aInfo = null;
    /**
     * function to set the info for the trigger
     *
     * @param array $info
     */
    function setInfo(&$info)
    {
        $this->aInfo =& $info;
    }

    /**
     * postValidate method for trigger
     *
     * @return unknown
     */
    function postValidate()
    {
        global $default;

        $document =& $this->aInfo['document'];
        $metadata = & $this->aInfo['aOptions'];
        $docId = $document->getID();

        // get tag id from document_fields table where name = Tag
        $query = 'SELECT df.id AS id FROM document_fields AS df WHERE df.name = \'Tag\'';

        $tags = DBUtil::getOneResultKey(array($query), 'id');
        if (PEAR::isError($tags)) {
            // XXX: log error
            return false;
        }

        $tagString = '';
        // add tags
        if ($tags) {
            if (count($metadata['metadata']) > 0) {
                foreach($metadata['metadata'] as $metadataData) {
                    $proxy = $metadataData[0];
                    if ($proxy->iId == $tags) {
                        $tagString = $metadataData[1];
                    }
                }
            }

            if ($tagString != '') {
                $wordsTable = KTUtil::getTableName('tag_words');
                $tagString = str_replace('  ', ' ', $tagString);
                $tags = explode(',', $tagString);

                $tagIds = array();
                foreach($tags as $tag) {
                    $tag = trim($tag);
                    if (mb_detect_encoding($tag) == 'ASCII') {
                        $tag = strtolower($tag);
                    }

                    $res = DBUtil::getOneResult(array("SELECT id FROM $wordsTable WHERE tag = ?", array($tag)));
                    if (PEAR::isError($res)) {
                        return $res;
                    }

                    if (is_null($res)) {
                        $id = & DBUtil::autoInsert($wordsTable, array('tag' => $tag));
                        $tagIds[$tag] = $id;
                    }
                    else {
                        $tagIds[$tag] = $res['id'];
                    }
                }

                $docTags = KTUtil::getTableName('document_tags');

                foreach($tagIds as $tag => $tagid) {
                    DBUtil::autoInsert(
                        $docTags,
                        array(
                            'document_id' => $docId,
                            'tag_id' => $tagid),
                        array('noid' => true)
                    );
                }
            }
        }
    }

}


/**
 * Trigger for document edit (postValidate)
 *
 */
class KTEditDocumentTrigger {

    var $aInfo = null;
    /**
     * function to set the info for the trigger
     *
     * @param array $info
     */
    function setInfo(&$info) {
        $this->aInfo =& $info;
    }

    /**
     * postValidate method for trigger
     *
     * @return unknown
     */
    function postValidate() {
        global $default;

        $document =& $this->aInfo['document'];
        $metadata = & $this->aInfo['aOptions'];
        $docId = $document->getID();
        $params = array($docId);

        // get all tags that are linked to the document
        $query = 'SELECT tw.id FROM tag_words AS tw, document_tags AS dt, documents AS d ' .
                'WHERE dt.tag_id = tw.id ' .
                'AND dt.document_id = d.id ' .
                'AND d.id = ?';
        $tagId = DBUtil::getResultArray(array($query, $params));
        if (PEAR::isError($tagId)) {
            // XXX: log error
            return false;
        }

        // if there are any related tags proceed
        if ($tagId) {
            // delete all entries from document_tags table for the document
            $query = 'DELETE FROM document_tags WHERE document_id = ?';
            $removed = DBUtil::runQuery(array($query, $params));
            if (PEAR::isError($removed)) {
                // XXX: log error
                return false;
            }
        }
        // proceed to add the tags as per normal
        $query = 'SELECT df.id AS id FROM document_fields AS df WHERE df.name = \'Tag\'';
        $tags = DBUtil::getOneResultKey(array($query), 'id');
        if (PEAR::isError($tags)) {
            // XXX: log error
            return false;
        }

        $tagString = '';
        if ($tags) {
            // it is actually correct using $metadata. It is different to the add trigger above...
            if (count($metadata) > 0) {
                foreach($metadata as $metadataData) {
                    $proxy = $metadataData[0];
                    if ($proxy->iId == $tags) {
                        $tagString = $metadataData[1];
                        break;
                    }
                }
            }

            if ($tagString != '') {
                $wordsTable = KTUtil::getTableName('tag_words');
                $tagString = str_replace('  ', ' ', $tagString);
                $tags = explode(',',$tagString);

                $tagIds = array();
                foreach($tags as $tag) {
                    $tag = trim($tag);
                    if (mb_detect_encoding($tag) == 'ASCII') {
                        $tag = strtolower($tag);
                    }

                    $res = DBUtil::getOneResult(array("SELECT id FROM $wordsTable WHERE tag = ?", array($tag)));
                    if (PEAR::isError($res)) {
                        return $res;
                    }

                    if (is_null($res)) {
                        $id = & DBUtil::autoInsert($wordsTable, array('tag' => $tag));
                        $tagIds[$tag] = $id;
                    }
                    else {
                        $tagIds[$tag] = $res['id'];
                    }
                }

                $docTags = KTUtil::getTableName('document_tags');
                foreach($tagIds as $tag => $tagid)
                {
                    DBUtil::autoInsert(
                        $docTags,
                        array(
                            'document_id' => $docId,
                            'tag_id' => $tagid),
                        array('noid' => true)
                    );
                }
            }
        }
    }

}
?>
