<?php

/*
 * $Id: $
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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


/**
* Builds the weightings for tags based on their frequency
*
* @param array $aTags
* @return array
*/
function get_tag_weightings($aTags)
{
    if (empty($aTags)){
        return array();
    }else{
        $min_freq = min(array_values($aTags));
        $max_freq = max(array_values($aTags));
    }
    $min_size = 10;
    $max_size = 20;

    $distrib = $max_freq - $min_freq;
    if ($distrib == 0){
        $distrib=1;
    }

    $step = ($max_size - $min_size)/($distrib);

    foreach($aTags as $tag => $freq)
    {
        $size = ceil($min_size + (($freq - $min_freq) * $step));
        $aTags[$tag] = $size;
    }

    return $aTags;
}

/**
* Returns the relevant tags for the current user
*
* @return array
*/
function get_relevant_tags($oUser, $sTag)
{
    $aTagList = isset($_SESSION['tagList']) ? $_SESSION['tagList'] : array();
    $tagTree = array();
    $documentList = '';

    // Get the previous tag info:
    // the list of documents that contain the tag to search within
    // the tags that have already been filtered so they aren't displayed again
    if(!empty($aTagList)){
        $aPrevTag = end($aTagList);
        $sPrevTag = $aPrevTag['tag'];
        $documentList = $aPrevTag['docs'];
        $tagTree = $aPrevTag['tagTree'];
    }

    if(empty($sTag)){
        // If there is no tag specified then get all tags.
        $aUserPermissions = KTSearchUtil::permissionToSQL($oUser, 'ktcore.permissions.read');
		if(PEAR::isError($aUserPermissions)) {
            return array();
        }
        // ensure the user has read permission on the documents
		list($sWhere, $aParams, $sJoins) = $aUserPermissions;

        $sql = "SELECT TW.tag, count(*) as freq
    		FROM document_tags DT
    		INNER JOIN tag_words TW ON DT.tag_id=TW.id
    		WHERE DT.document_id in (SELECT D.id FROM documents D $sJoins WHERE $sWhere AND D.status_id = '1')
    		GROUP BY TW.tag";

		$tags = DBUtil::getResultArray(array($sql, $aParams));
    }else{
        // Create a new tag query to get the document id's associated with the tag
        $oQuery = new TagQuery($oUser, $sTag);
        $aOptions = array();
        $aOptions['select'] = 'DISTINCT DTS.document_id';

        $aQuery = $oQuery->getQuery($aOptions);
        $sInnerQuery = $aQuery[0];
        $aParams = $aQuery[1];

        $aDocIds = DBUtil::getResultArrayKey($aQuery, 'document_id');
        $sDocs = implode(',', $aDocIds);
        // Make sure user not opening a new window on tag cloud filters
        if(!$sDocs) {
            return array();
        }
        // Don't display tags that have already been selected.
        $tagTree[] = $sTag;
        $cnt = count($tagTree);
        $sIgnoreTags = '?';
        for($i = 1; $i < $cnt; $i++){
            $sIgnoreTags .= ',?';
        }

        // Get the tags within the documents that haven't been selected before
        $sQuery = "SELECT TW.tag, count(*) as freq
        FROM document_tags DT INNER JOIN tag_words TW ON DT.tag_id=TW.id
        WHERE DT.document_id in ($sDocs) AND TW.tag NOT IN ($sIgnoreTags)
        GROUP BY TW.tag";

        $tags = DBUtil::getResultArray(array($sQuery, $tagTree));

        if(PEAR::isError($tags)){
            echo $tags->getMessage();
        }

        // Add new tag to the session
        if($sPrevTag != $sTag){
            $aTagList[] = array('tag' => $sTag, 'docs' => $sDocs, 'tagTree' => $tagTree);
            $_SESSION['tagList'] = $aTagList;
        }
    }

    $aTags = array();
    if($tags) {
        foreach($tags as $tag)
        {
            $word=$tag['tag'];
            $freq=$tag['freq'];
            $aTags[$word] = $freq;
        }
    }

    return $aTags;

}

class TagQuery extends PartialQuery
{
    var $oUser;
    var $sTag;

    function TagQuery($oUser, $sTag = '')
    {
        $this->sTag = $sTag;
        $this->oUser = $oUser;
    }

    function getDocumentCount()
    {
        $aOptions = array(
            'select' => 'count(DISTINCT DTS.document_id) AS cnt',
        );
        $aQuery = $this->getQuery($aOptions);
        if (PEAR::isError($aQuery)) { return 0; }
        $iRet = DBUtil::getOneResultKey($aQuery, 'cnt');
        return $iRet;
    }

    function getBaseQuery($aOptions)
    {
        $aCriteriaSet = array(
            'join'=>'AND',
                'subgroup'=>array(
                    0=>array(
                        'join'=>'AND',
                        'values'=>array(
                            1=>array(
                            'data'=>array(
                                'ktcore.criteria.tagcloud' => $this->sTag,
                                'ktcore.criteria.tagcloud_not'=>0
                                ),
                            'type'=>'ktcore.criteria.tagcloud'
                            )
                        ),
                    )
                )
            );

        $aQuery = KTSearchUtil::criteriaToQuery($aCriteriaSet, $this->oUser, 'ktcore.permissions.read', $aOptions);
        return $aQuery;
    }

    function getQuery($aOptions = null)
    {
        $aTagList = isset($_SESSION['tagList']) ? $_SESSION['tagList'] : array();
        if(!empty($aTagList)){
            $aPrevTag = end($aTagList);
            $documentList = $aPrevTag['docs'];
        }

        // If the document list is empty then create internal query with read permissions for the user and the first tag
        $aQuery = $this->getBaseQuery($aOptions);
        if(empty($documentList)){
            return $aQuery;
        }

        $sQuery = $aQuery[0];

        $sQuery .= " AND DTS.document_id IN ($documentList) ";
        $aQuery[0] = $sQuery;

        return $aQuery;
    }

    function getDocuments($iBatchSize, $iBatchStart, $sSortColumn, $sSortOrder, $sJoinClause = null, $aJoinParams = null)
    {
        $aOptions = array(
            'select' => 'DISTINCT DTS.document_id AS id'
        );
        $res = $this->getQuery($aOptions);
        if (PEAR::isError($res)) { return array(); }
        list($sQuery, $aParams) = $res;
        $sQuery .= ' ORDER BY ' . $sSortColumn . ' ' . $sSortOrder . ' ';
        $sQuery .= " LIMIT $iBatchStart, $iBatchSize";

        $q = array($sQuery, $aParams);
        $res = DBUtil::getResultArray($q);

        return $res;
    }
}


?>
