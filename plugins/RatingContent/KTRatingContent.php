<?php
/*
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009, 2010 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/security/Permission.inc');
require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');
require_once(KT_LIB_DIR . '/documentmanagement/Document.inc');

class KTRatingContent {

	private $table;

	public function __construct()
	{
		$this->table = 'ratingcontent_document';
	}

	public function doesUserLikeDocument($userId, $documentId)
	{
		$userLikesCount = $this->getUserLikesInCollectionQuery($userId, array($documentId));

		if (in_array($documentId, $userLikesCount)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function getNumDocumentLikes($documentId)
	{
		$documentLikesCount = $this->getLikesInCollectionQuery(array($documentId));

		if (array_key_exists('doc_'.$documentId, $documentLikesCount)) {
			return $documentLikesCount['doc_'.$documentId];
		} else {
			return 0;
		}
	}

	public function getLikesInCollection(&$listOfDocuments, $userId=NULL)
	{
		$documentIds = array();

		foreach ($listOfDocuments as $document)
		{
			$documentIds[] = $document['id'];
		}

		$documentLikesCount = $this->getLikesInCollectionQuery($documentIds);
		$userLikesCount = $this->getUserLikesInCollectionQuery($userId, $documentIds);

		foreach ($listOfDocuments as &$document)
		{
			if (array_key_exists('doc_'.$document['id'], $documentLikesCount)) {
				$document['like_count'] = $documentLikesCount['doc_'.$document['id']];
			} else {
				$document['like_count'] = 0;
			}

			if (in_array($document['id'], $userLikesCount)) {
				$document['user_likes_document'] = TRUE;
			} else {
				$document['user_likes_document'] = FALSE;
			}
		}

		// Relevant. List passed by reference
		return $listOfDocuments;
	}

	public function getLikesInCollectionQuery($documentIds)
	{
		$ids = implode(',', $documentIds);
		if(empty($ids)) return array();
		$sql = 'SELECT CONCAT("doc_", document_id) AS document_id, count( * ) AS likeCount FROM ratingcontent_document';

		$where = ' WHERE document_id IN (' . $ids . ') GROUP BY document_id';

		$results = DBUtil::getResultArray($sql.$where);

		$returnArray = array();

		foreach ($results as $item)
		{
			$returnArray[$item['document_id']] = $item['likeCount'];
		}

		return $returnArray;
	}

	public function getUserLikesInCollectionQuery($userId, $documentIds)
	{
		if ($userId == NULL || empty($documentIds)) {
			return array();
		}

		$sql = 'SELECT document_id FROM ratingcontent_document ';

		$where = ' WHERE user_id = "'.$userId.'" AND document_id IN ('.implode(',', $documentIds).')';

		$results = DBUtil::getResultArray($sql.$where);

		$returnArray = array();

		foreach ($results as $item)
		{
			$returnArray[] = $item['document_id'];
		}

		return $returnArray;
	}

	public function likeDocument($documentId, $userId)
	{
		// Todo: Check if User has access to document
		if ($this->likeDocumentExists($documentId, $userId)) {

		} else {
			$query = 'INSERT into ' . $this->table . ' (`document_id`, `user_id`, `date_time`)
			VALUES (' . $documentId . ', ' . $userId . ', "' . date('Y-m-d H:i:s') . '");';

			DBUtil::runQuery($query);
			
			$document = Document::get($documentId);
			
			// Create the document transaction
			$oDocumentTransaction = & new DocumentTransaction($document, 'Document liked', 'ratingcontent.transactions.like', array());
			$oDocumentTransaction->create();
			
		}

		return $this->getDocumentLikes($documentId);
	}

	public function unlikeDocument($documentId, $userId)
	{
		if ($this->likeDocumentExists($documentId, $userId)) {
			$query = "DELETE FROM {$this->table} WHERE user_id = '{$userId}' AND document_id = '{$documentId}';";

			DBUtil::runQuery($query);
			
			$document = Document::get($documentId);
			
			// Create the document transaction
			$oDocumentTransaction = & new DocumentTransaction($document, 'Document unliked', 'ratingcontent.transactions.unlike', array());
			$oDocumentTransaction->create();
		}

		return $this->getDocumentLikes($documentId);
	}

	public function getDocumentLikes($documentId)
	{
		$sql = "SELECT count(*) as numLikes FROM {$this->table} WHERE document_id = '$documentId' LIMIT 1";
        $res = DBUtil::getOneResultKey($sql, 'numLikes');

		return $res;

	}

	private function likeDocumentExists($documentId, $userId)
	{
		$query = "SELECT * FROM {$this->table} WHERE user_id = '$userId' AND document_id = '$documentId' LIMIT 1";
		$results = DBUtil::getResultArray($query);

		return (count($results) > 0);
	}

}