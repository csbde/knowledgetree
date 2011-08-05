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

class GraphicalAnalytics {

	private $table;

	public function __construct() { }

	public static function getTop10Documents()
    {
        $sql = '
        SELECT documents.id AS document_id, document_content_version.filename,
		SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)) = 0), score, score/ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)))) AS documentscore
		FROM document_transactions
		INNER JOIN graphicalanalysis_scoring ON (transaction_namespace = namespace)
		INNER JOIN documents ON (document_transactions.document_id = documents.id)
		INNER JOIN document_metadata_version ON (documents.metadata_version_id = document_metadata_version.id)
		INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)
		GROUP BY document_id
		ORDER BY documentscore DESC
		LIMIT 10';

        return DBUtil::getResultArray($sql);
    }

	public static function getTop10Users()
	{
		$sql = '
        SELECT user_id, username, users.name,
		SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)) = 0), score, score/ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)))) AS userscore
		FROM document_transactions
		INNER JOIN graphicalanalysis_scoring ON (transaction_namespace = namespace)
		INNER JOIN documents ON (document_transactions.document_id = documents.id)
		INNER JOIN users ON (document_transactions.user_id = users.id)
		GROUP BY user_id
		ORDER BY userscore DESC
		LIMIT 10';

		return DBUtil::getResultArray($sql);
	}

	public static function getDocumentViewsOverWeek()
    {
        $sql = '
		SELECT COUNT( document_id ) AS count , transaction_namespace, ABS( TIMESTAMPDIFF( WEEK, NOW( ) , datetime ) ) AS week_number
		FROM document_transactions
		WHERE transaction_namespace = "ktcore.transactions.view"
		GROUP BY week_number
		ORDER BY week_number
		LIMIT 0, 10
        ';

        return DBUtil::getResultArray($sql);
    }

	public static function getTransactionViewsSql()
    {
        $sql = '
		SELECT COUNT( document_id ) AS count , transaction_namespace, ABS( TIMESTAMPDIFF( WEEK, NOW( ) , datetime ) ) AS week_number
		FROM document_transactions
		GROUP BY week_number
		ORDER BY week_number
		LIMIT 0 , 10
        ';

        return DBUtil::getResultArray($sql);
    }

	public static function getDocumentCommentsSql()
    {
        $sql = '
		SELECT COUNT(document_id) as comment_count, ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)) AS week_number
		FROM document_comments
		WHERE ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)) < 10
		GROUP BY week_number
		ORDER BY week_number
		LIMIT 10
        ';

        return DBUtil::getResultArray($sql);
    }

}