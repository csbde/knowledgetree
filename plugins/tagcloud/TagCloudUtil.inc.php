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

class TagCloudUtil {

    /**
     * Builds the weightings for tags based on their frequency.
     *
     * @param array $tags
     * @return array
     */
    public static function getTagWeightings($tags)
    {
        if (empty($tags)) {
            return array();
        }
        else {
            $minFreq = min(array_values($tags));
            $maxFreq = max(array_values($tags));
        }

        $minSize = 10;
        $maxSize = 20;

        $distrib = $maxFreq - $minFreq;
        if ($distrib == 0) {
            $distrib = 1;
        }

        $step = ($maxSize - $minSize) / $distrib;

        foreach($tags as $tag => $freq) {
            $size = ceil($minSize + (($freq - $minFreq) * $step));
            $tags[$tag] = $size;
        }

        return $tags;
    }

    /**
     * Returns the relevant tags for the current user.
     *
     * @return array
     */
    public static function getRelevantTags($user, $tag)
    {
        $tagList = isset($_SESSION['tagList']) ? $_SESSION['tagList'] : array();
        $tagTree = array();
        $documentList = '';

        // Get the previous tag info:
        //      the list of documents that contain the tag to search within.
        //      the tags that have already been filtered so they aren't displayed again.
        if (!empty($tagList)) {
            $previousTags = end($tagList);
            $prevTag = $previousTags['tag'];
            $documentList = $previousTags['docs'];
            $tagTree = $previousTags['tagTree'];
        }

        if (empty($tag)) {
            // If there is no tag specified then get all tags.
            $userPermissions = KTSearchUtil::permissionToSQL($user, 'ktcore.permissions.read');
            if (PEAR::isError($userPermissions)) {
                return array();
            }

            // Ensure the user has read permission on the documents.
            list($sWhere, $params, $sJoins) = $userPermissions;
            $sql = "SELECT TW.tag, count(*) as freq
                    FROM document_tags DT
                    INNER JOIN tag_words TW ON DT.tag_id=TW.id
                    WHERE DT.document_id in (SELECT D.id FROM documents D $sJoins WHERE $sWhere AND D.status_id = '1')
                    GROUP BY TW.tag";

            $existingTags = DBUtil::getResultArray(array($sql, $params));
        }
        else {
            // Create a new tag query to get the document id's associated with the tag.
            $queryObject = new TagQuery($user, $tag);
            $options = array();
            $options['select'] = 'DISTINCT DTS.document_id';

            $query = $queryObject->getQuery($options);
            $innerQuery = $query[0];
            $params = $query[1];

            $docIds = DBUtil::getResultArrayKey($query, 'document_id');
            $documents = implode(',', $docIds);
            // Make sure user not opening a new window on tag cloud filters.
            if (!$documents) {
                return array();
            }

            // Don't display tags that have already been selected.
            $tagTree[] = $tag;
            $cnt = count($tagTree);
            $ignoreTags = '?';
            for ($i = 1; $i < $cnt; $i++) {
                $ignoreTags .= ',?';
            }

            // Get the tags within the documents that haven't been selected before.
            $sQuery = "SELECT TW.tag, count(*) as freq
                    FROM document_tags DT INNER JOIN tag_words TW ON DT.tag_id=TW.id
                    WHERE DT.document_id in ($documents) AND TW.tag NOT IN ($ignoreTags)
                    GROUP BY TW.tag";

            $existingTags = DBUtil::getResultArray(array($sQuery, $tagTree));
            if (PEAR::isError($existingTags)) {
                echo $existingTags->getMessage();
            }

            // Add new tag to the session
            if ($prevTag != $tag) {
                $tagList[] = array('tag' => $tag, 'docs' => $documents, 'tagTree' => $tagTree);
                $_SESSION['tagList'] = $tagList;
            }
        }

        $tags = array();
        if ($existingTags) {
            foreach($existingTags as $tag)
            {
                $word = $tag['tag'];
                $freq = $tag['freq'];
                $tags[$word] = $freq;
            }
        }

        return $tags;
    }

}


class TagQuery extends PartialQuery {

    var $oUser;
    var $sTag;

    function TagQuery($user, $tag = '')
    {
        $this->sTag = $tag;
        $this->oUser = $user;
    }

    function getDocumentCount()
    {
        $options = array('select' => 'count(DISTINCT DTS.document_id) AS cnt');
        $query = $this->getQuery($options);
        if (PEAR::isError($query)) {
            return 0;
        }
        $ret = DBUtil::getOneResultKey($query, 'cnt');

        return $ret;
    }

    function getBaseQuery($options)
    {
        $criteriaSet = array(
            'join' => 'AND',
            'subgroup' => array(
                0 => array(
                        'join' => 'AND',
                        'values' => array(
                                        1 => array(
                                        'data' => array(
                                            'ktcore.criteria.tagcloud' => $this->sTag,
                                            'ktcore.criteria.tagcloud_not' => 0
                                        ),
                                        'type' => 'ktcore.criteria.tagcloud'
                                    )
                        ),
                )
            )
        );
        $query = KTSearchUtil::criteriaToQuery($criteriaSet, $this->oUser, 'ktcore.permissions.read', $options);

        return $query;
    }

    function getQuery($options = null)
    {
        $tagList = isset($_SESSION['tagList']) ? $_SESSION['tagList'] : array();
        if (!empty($tagList)) {
            $previousTags = end($tagList);
            $documentList = $previousTags['docs'];
        }

        // If the document list is empty then create internal query with read permissions for user and first tag.
        $queryObject = $this->getBaseQuery($options);
        if (empty($documentList)) {
            return $queryObject;
        }

        // TODO Figure out the purpose of this because it creates an edge case issue:
        //      1. Search for documents by tag, get 1 result.
        //      2. Add same tag to more documents.
        //      3. Access tag via tag cloud again as in point 1.
        //      4. Find that unless your session expired in between, you get the same results as in 1.
        //         None of the newly linked documents are in the results.
        $query = "{$queryObject[0]} AND DTS.document_id IN ($documentList)";
        $queryObject[0] = $query;

        return $queryObject;
    }

    function getDocuments($batchSize, $batchStart, $sortColumn, $sortOrder, $joinClause = null, $joinParams = null)
    {
        $options = array('select' => 'DISTINCT DTS.document_id AS id');
        $res = $this->getQuery($options);
        if (PEAR::isError($res)) {
            return array();
        }

        list($query, $params) = $res;
        $query .= " ORDER BY $sortColumn $sortOrder LIMIT $batchStart, $batchSize";

        $query = array($query, $params);
        $res = DBUtil::getResultArray($query);

        return $res;
    }

}


?>
