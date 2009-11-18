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
 * Trigger for document add (postValidate)
 *
 */
class KTAddDocumentTrigger {
    var $aInfo = null;
    /**
     * function to set the info for the trigger
     *
     * @param array $aInfo
     */
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    /**
     * postValidate method for trigger
     *
     * @return unknown
     */
    function postValidate() {
        global $default;
        $oDocument =& $this->aInfo['document'];
        $aMeta = & $this->aInfo['aOptions'];

        $iDocId = $oDocument->getID();

        // get tag id from document_fields table where name = Tag
		$sQuery = 'SELECT df.id AS id FROM document_fields AS df ' .
				'WHERE df.name = \'Tag\'';

        $sTags = DBUtil::getOneResultKey(array($sQuery), 'id');
        if (PEAR::isError($sTags)) {
            // XXX: log error
            return false;
        }
        $tagString = '';
        // add tags
        if ($sTags) {
        	if (count($aMeta['metadata']) > 0)
        	{
        		foreach($aMeta['metadata'] as $aMetaData)
        		{

				$oProxy = $aMetaData[0];
				if($oProxy->iId == $sTags)
				{
					$tagString = $aMetaData[1];
				}
        		}
			}
			if($tagString != ''){
	        	$words_table = KTUtil::getTableName('tag_words');
	        	$tagString = str_replace('  ', ' ', $tagString);
		    	$tags = explode(',',$tagString);

		    	$aTagIds = array();

		    	foreach($tags as $sTag)
		    	{
		    		$sTag = trim($sTag);
		    	    if(mb_detect_encoding($sTag) == 'ASCII'){
		    	        $sTag = strtolower($sTag);
		    	    }

		    		$res = DBUtil::getOneResult(array("SELECT id FROM $words_table WHERE tag = ?", array($sTag)));

		    		if (PEAR::isError($res)) {
		            	return $res;
		        	}

		        	if (is_null($res))
		        	{
		        		$id = & DBUtil::autoInsert($words_table, array('tag'=>$sTag));
		        		$aTagIds[$sTag] = $id;
		        	}
		        	else
		        	{
		        		$aTagIds[$sTag] = $res['id'];
		        	}
		    	}

		    	$doc_tags = KTUtil::getTableName('document_tags');

		    	foreach($aTagIds as $sTag=>$tagid)
		    	{
		    		DBUtil::autoInsert($doc_tags, array(

		    			'document_id'=>$iDocId,
		    			'tag_id'=>$tagid),
		    			array('noid'=>true));
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
     * @param array $aInfo
     */
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    /**
     * postValidate method for trigger
     *
     * @return unknown
     */
    function postValidate() {
        global $default;
        $oDocument =& $this->aInfo['document'];
        $aMeta = & $this->aInfo['aOptions'];
      	// get document id
        $iDocId = $oDocument->getID();

        // get all tags that are linked to the document
		$sQuery = 'SELECT tw.id FROM tag_words AS tw, document_tags AS dt, documents AS d ' .
				'WHERE dt.tag_id = tw.id ' .
				'AND dt.document_id = d.id ' .
				'AND d.id = ?';
		$aParams = array($iDocId);
        $aTagId = DBUtil::getResultArray(array($sQuery, $aParams));
        if (PEAR::isError($aTagId)) {
            // XXX: log error
            return false;
        }
        // if there are any related tags proceed
        if ($aTagId) {
        	// delete all entries from document_tags table for the document
			$sQuery = 'DELETE FROM document_tags ' .
					'WHERE document_id = ?';
			$aParams = array($iDocId);
			$removed = DBUtil::runQuery(array($sQuery, $aParams));
			if (PEAR::isError($removed)) {
        		// XXX: log error
        		return false;
    		}
        }
        // proceed to add the tags as per normal
		$sQuery = 'SELECT df.id AS id FROM document_fields AS df ' .
		'WHERE df.name = \'Tag\'';

        $sTags = DBUtil::getOneResultKey(array($sQuery), 'id');
        if (PEAR::isError($sTags)) {
            // XXX: log error
            return false;
        }
        $tagString = '';
        if ($sTags) {
        	// it is actually correct using $aMeta. It is different to the add trigger above...
        	if (count($aMeta) > 0)
        	{
        		foreach($aMeta as $aMetaData)
        		{
        			$oProxy = $aMetaData[0];
        			if($oProxy->iId == $sTags)
        			{
        				$tagString = $aMetaData[1];
        				break;
					}
        		}
			}
			if($tagString != ''){
	        	$words_table = KTUtil::getTableName('tag_words');
	        	$tagString = str_replace('  ', ' ', $tagString);
		    	$tags = explode(',',$tagString);

		    	$aTagIds = array();

		    	foreach($tags as $sTag)
		    	{
		    	    $sTag = trim($sTag);
		    	    if(mb_detect_encoding($sTag) == 'ASCII'){
		    	        $sTag = strtolower($sTag);
		    	    }

		    		$res = DBUtil::getOneResult(array("SELECT id FROM $words_table WHERE tag = ?", array($sTag)));

		    		if (PEAR::isError($res)) {
		            	return $res;
		        	}

		        	if (is_null($res))
		        	{
		        		$id = & DBUtil::autoInsert($words_table, array('tag'=>$sTag));
		        		$aTagIds[$sTag] = $id;
		        	}
		        	else
		        	{
		        		$aTagIds[$sTag] = $res['id'];
		        	}
		    	}

		    	$doc_tags = KTUtil::getTableName('document_tags');

		    	foreach($aTagIds as $sTag=>$tagid)
		    	{
		    		DBUtil::autoInsert($doc_tags, array(
		    			'document_id'=>$iDocId,
		    			'tag_id'=>$tagid),
		    			array('noid'=>true));
		    	}
        	}
        }
    }
}
?>
