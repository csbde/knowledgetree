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
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

class KTGraphicalAnalyticsSql {

	private $table;
	private $commentScore = 4;
	private $ratingScore = 2;
	private $ratingContentEnable = FALSE;
	
	public function __construct()
	{
		if (KTPluginUtil::pluginIsActive('actionableinsights.ratingcontent.plugin')) {
            $this->ratingContentEnabled = true;
		}
	}

	public function getTop10Documents($limit = 10)
    {
		$sql = '
		SELECT merged_table.document_id, document_content_version.filename, SUM(documentscore) AS documentscore, mime_id FROM
		(

			(
				SELECT document_id,
				SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)) = 0), score, score/ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)))) AS documentscore
				FROM document_transactions
				INNER JOIN graphicalanalysis_scoring ON (transaction_namespace = namespace)
				GROUP BY document_id
			)

			UNION ALL

			(
				SELECT document_id,
				SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)) != 0), [-COMMENT-SCORE-]/ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)), [-COMMENT-SCORE-])) AS documentscore
				FROM document_comments
				GROUP BY document_id
			)

			[-CONTENT-RATING-]

		) merged_table

		INNER JOIN documents D ON (merged_table.document_id = D.id AND D.status_id != 3 AND D.status_id != 4)
		INNER JOIN document_metadata_version ON (D.metadata_version_id = document_metadata_version.id)
		INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)

                ' . $this->getPermissionsQuery() . '

		GROUP BY document_id

		ORDER BY documentscore DESC
		LIMIT 0, ' . $limit;

		if ($this->ratingContentEnabled) {
			$sql = str_replace('[-CONTENT-RATING-]',
			'
			UNION ALL

			(
				SELECT document_id,
				SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time )) != 0), [-RATING-SCORE-]/ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time )), [-RATING-SCORE-])) AS documentscore
				FROM ratingcontent_document
				GROUP BY document_id
			) ', $sql);
		}
                else {
			$sql = str_replace('[-CONTENT-RATING-]', '', $sql);
		}

		$sql = str_replace('[-COMMENT-SCORE-]', $this->commentScore, $sql);
		$sql = str_replace('[-RATING-SCORE-]', $this->ratingScore, $sql);

        return DBUtil::getResultArray($sql);
    }


	public function getDocumentsByRating()
	{
		$topDoc = $this->getTop10Documents();

		$topDoc = $topDoc[0]['documentscore'];

		$topDoc = round($topDoc, -1); // Round to the nearest 10th.
		$divider = $topDoc / 4; // We need to divide by this value to get things into groups of five

		$sql = '
		SELECT merged_twice.documentscore as scoregroup, COUNT(merged_twice.documentscore) as numitems FROM (
			SELECT merged_table.document_id, document_content_version.filename, SUM(documentscore), ROUND((SUM(documentscore))/'.$divider.') AS documentscore FROM
			(

				(
					SELECT document_id,
					SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)) = 0), score, score/ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)))) AS documentscore
					FROM document_transactions
					INNER JOIN graphicalanalysis_scoring ON (transaction_namespace = namespace)
					GROUP BY document_id
				)

				UNION ALL

				(
					SELECT document_id,
					SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)) != 0), [-COMMENT-SCORE-]/ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)), [-COMMENT-SCORE-])) AS documentscore
					FROM document_comments
					GROUP BY document_id
				)

				[-CONTENT-RATING-]

			) merged_table

			INNER JOIN documents D ON (merged_table.document_id = D.id AND D.status_id != 3 AND D.status_id != 4)
			INNER JOIN document_metadata_version ON (D.metadata_version_id = document_metadata_version.id)
			INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)

                        ' . $this->getPermissionsQuery() . '

			GROUP BY document_id

			ORDER BY documentscore DESC
		) merged_twice
		GROUP BY documentscore
		ORDER BY documentscore DESC
		';

		if ($this->ratingContentEnabled) {

			$sql = str_replace('[-CONTENT-RATING-]',
			'
			UNION ALL

			(
				SELECT document_id,
				SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time )) != 0), [-RATING-SCORE-]/ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time )), [-RATING-SCORE-])) AS documentscore
				FROM ratingcontent_document
				GROUP BY document_id
			) ', $sql);
		} else {
			$sql = str_replace('[-CONTENT-RATING-]', '', $sql);
		}

		$sql = str_replace('[-COMMENT-SCORE-]', $this->commentScore, $sql);
		$sql = str_replace('[-RATING-SCORE-]', $this->ratingScore, $sql);

        return DBUtil::getResultArray($sql);

	}

	public function getPointsOverWeeks($weeksLimit=10)
    {
		$sql = '
		SELECT merged_table.document_id, document_content_version.filename, SUM(documentscore) AS documentscore FROM
		(

			(
				SELECT document_id,
				SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)) = 0), score, score/ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)))) AS documentscore
				FROM document_transactions
				INNER JOIN graphicalanalysis_scoring ON (transaction_namespace = namespace)
				GROUP BY document_id
			)

			UNION ALL

			(
				SELECT document_id,
				SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)) != 0), [-COMMENT-SCORE-]/ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)), [-COMMENT-SCORE-])) AS documentscore
				FROM document_comments
				GROUP BY document_id
			)

			[-CONTENT-RATING-]

		) merged_table

		INNER JOIN documents ON (merged_table.document_id = documents.id)
		INNER JOIN document_metadata_version ON (documents.metadata_version_id = document_metadata_version.id)
		INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)

		GROUP BY document_id

		ORDER BY documentscore DESC
		LIMIT 0, '.$weeksLimit;

		if ($this->ratingContentEnabled) {

			$sql = str_replace('[-CONTENT-RATING-]',
			'
			UNION ALL

			(
				SELECT document_id,
				SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time )) != 0), [-RATING-SCORE-]/ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time )), [-RATING-SCORE-])) AS documentscore
				FROM ratingcontent_document
				GROUP BY document_id
			) ', $sql);
		} else {
			$sql = str_replace('[-CONTENT-RATING-]', '', $sql);
		}

		$sql = str_replace('[-COMMENT-SCORE-]', $this->commentScore, $sql);
		$sql = str_replace('[-RATING-SCORE-]', $this->ratingScore, $sql);

        return DBUtil::getResultArray($sql);
    }
	

	public function getTopUsers($limit = 10)
	{
		// Needs to consider likes and comments
		$sql = '
        SELECT user_id, username, users.name, users.email,
		SUM(IF ((ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)) = 0), score, score/ABS(TIMESTAMPDIFF(WEEK,NOW(),datetime)))) AS userscore
		FROM document_transactions
		INNER JOIN graphicalanalysis_scoring ON (transaction_namespace = namespace)
		INNER JOIN documents ON (document_transactions.document_id = documents.id)
		INNER JOIN users ON (document_transactions.user_id = users.id)
		GROUP BY user_id
		ORDER BY userscore DESC
		LIMIT ' . $limit;

		return DBUtil::getResultArray($sql);
	}


	public function getDocumentViewsOverWeek($weeksLimit=10)
    {
        $permissionsQuery = $this->getPermissionsQuery();
        $sql = '
		SELECT COUNT(DT.document_id) AS count , transaction_namespace, ABS(TIMESTAMPDIFF(WEEK, NOW(), datetime)) AS week_number
		FROM document_transactions DT, documents D
                INNER JOIN document_metadata_version DMV ON DMV.id = D.metadata_version_id
                INNER JOIN document_content_version DCV ON DCV.id = DMV.content_version_id
                ' . (empty($permissionsQuery) ? 'WHERE' : "$permissionsQuery AND") . '
		DT.transaction_namespace = "ktcore.transactions.view"
                AND DT.document_id = D.id
		GROUP BY week_number
		ORDER BY week_number
		LIMIT 0, '.$weeksLimit;

        return DBUtil::getResultArray($sql);
    }

	public function getMostViewedDocuments()
    {
        $sql = '
		SELECT document_transactions.document_id, COUNT( document_transactions.document_id ) AS count, document_content_version.filename, mime_id
		FROM document_transactions
		INNER JOIN documents ON (document_transactions.document_id = documents.id AND documents.status_id != 3 AND documents.status_id != 4)
		INNER JOIN document_metadata_version ON (documents.metadata_version_id = document_metadata_version.id)
		INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)
		WHERE transaction_namespace = "ktcore.transactions.view"
		GROUP BY document_transactions.document_id
		ORDER BY count DESC
		LIMIT 0, 5
        ';

        return DBUtil::getResultArray($sql);
    }


	public function getUploadsPerWeekSql($weeksLimit=10)
    {
        $permissionsQuery = $this->getPermissionsQuery();
        $sql = '
		SELECT COUNT(DT.document_id) AS uploadcount, ABS(TIMESTAMPDIFF(WEEK, NOW(), datetime)) AS week_number
		FROM document_transactions DT, documents D
                INNER JOIN document_metadata_version DMV ON DMV.id = D.metadata_version_id
                INNER JOIN document_content_version DCV ON DCV.id = DMV.content_version_id
                ' . (empty($permissionsQuery) ? 'WHERE' : "$permissionsQuery AND") . '
		(DT.transaction_namespace = "ktcore.transactions.create" OR DT.transaction_namespace = "ktcore.transactions.check_in")
		AND ABS(TIMESTAMPDIFF(WEEK, NOW(), datetime)) < 10
                AND DT.document_id = D.id
		GROUP BY week_number
		ORDER BY week_number
		LIMIT 0, '.$weeksLimit;

        return DBUtil::getResultArray($sql);
    }


	public function getUserAccessPerWeekSql($weeksLimit=10)
    {
		// Decide whether to use document_transactions OR user_history
		$sql = '
		SELECT week_number, COUNT(uniqueDateUser) AS accessCount FROM
		(
			SELECT DISTINCT CONCAT(ABS(TIMESTAMPDIFF(WEEK, NOW(), datetime)), "_", user_id) AS uniqueDateUser,
				ABS( TIMESTAMPDIFF( WEEK, NOW(), datetime ) ) AS week_number FROM user_history
			WHERE ABS( TIMESTAMPDIFF( WEEK, NOW( ) , datetime ) ) < '.$weeksLimit.'
		) alias
		GROUP BY week_number
		ORDER BY week_number
		';

        return DBUtil::getResultArray($sql);
    }


	public function getTransactionViewsSql($weeksLimit=10)
    {
        $permissionsQuery = $this->getPermissionsQuery();
        $sql = '
		SELECT COUNT(DT.document_id) AS count , DT.transaction_namespace, ABS(TIMESTAMPDIFF(WEEK, NOW(), datetime)) AS week_number
		FROM document_transactions DT, documents D
                INNER JOIN document_metadata_version DMV ON DMV.id = D.metadata_version_id
                INNER JOIN document_content_version DCV ON DCV.id = DMV.content_version_id
                ' . (empty($permissionsQuery) ? 'WHERE' : "$permissionsQuery AND") . '
                DT.document_id = D.id
		GROUP BY week_number
		ORDER BY week_number
		LIMIT 0 , '.$weeksLimit;

        return DBUtil::getResultArray($sql);
    }


	public function getDocumentCommentsSql($weeksLimit=10)
    {
        $permissionsQuery = $this->getPermissionsQuery();
        $sql = '
		SELECT COUNT(c.document_id) as comment_count, ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)) AS week_number
		FROM document_comments c,
                documents D
                INNER JOIN document_metadata_version DMV ON DMV.id = D.metadata_version_id
                INNER JOIN document_content_version DCV ON DCV.id = DMV.content_version_id
                ' . (empty($permissionsQuery) ? 'WHERE' : "$permissionsQuery AND") . '
                ABS(TIMESTAMPDIFF(WEEK,NOW(),date_created)) < 10
                AND c.document_id = D.id
		GROUP BY week_number
		ORDER BY week_number
		LIMIT '.$weeksLimit;

        return DBUtil::getResultArray($sql);
    }


	public function getDocumentLikesSql($weeksLimit=10)
    {
        if ($this->ratingContentEnabled) {
			$sql = '
			SELECT COUNT(document_id) as like_count, ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time)) AS week_number
			FROM ratingcontent_document
			WHERE ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time)) < 10
			GROUP BY week_number
			ORDER BY week_number
			LIMIT '.$weeksLimit;
	
			return DBUtil::getResultArray($sql);
		} else {
			return array();
		}
    }

    // FIXME More duplication of this code - abstract to single library from which it can be called.
    private function getPermissionsQuery()
    {
        if ($this->inAdminMode()) {
            return '';
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
                WHERE PLA.permission_descriptor_id IN ($permissionDescriptors)";

            return $query;
        }
    }

    private function inAdminMode()
    {
        return isset($_SESSION['adminmode'])
            && ((int)$_SESSION['adminmode'])
            && Permission::adminIsInAdminMode();
    }

}
