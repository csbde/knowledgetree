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
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

class GraphicalAnalytics {

	private $table;

	public function getTop10Documents($limit = 10)
    {
		$sql = '
		SELECT merged_table.document_id, document_metadata_version.name, SUM(documentscore) AS documentscore, mime_id FROM
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

		INNER JOIN documents D ON (merged_table.document_id = D.id)
		INNER JOIN document_metadata_version ON (D.metadata_version_id = document_metadata_version.id)
		INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)

                ' . $this->getPermissionsQuery() . '

		GROUP BY document_id

		ORDER BY documentscore DESC
		LIMIT 0, ' . $limit;

		$ratingContentEnable = false; // Fix Up

		if ($ratingContentEnable) {
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

		$sql = str_replace('[-COMMENT-SCORE-]', '4', $sql);
		$sql = str_replace('[-RATING-SCORE-]', '2', $sql);

        return DBUtil::getResultArray($sql);
    }

	public function getTop10DocumentsTemplate()
	{
		return $this->loadTemplate(array('data'=>$this->getTop10Documents()), 'top10documents');
	}

	public function getTop5DocumentsDashlet()
	{
		return $this->loadTemplate(array('context'=> $this, 'data'=>$this->getTop10Documents(5)), 'top5documents_dashlet');
	}

/******************************************************************************************************************/

	public function getDocumentsByRating()
	{
		$topDoc = $this->getTop10Documents();

		$topDoc = $topDoc[0]['documentscore'];

		$topDoc = round($topDoc, -1); // Round to the nearest 10th.
		$divider = $topDoc / 4; // We need to divide by this value to get things into groups of five

		$sql = '
		SELECT merged_twice.documentscore as scoregroup, COUNT(merged_twice.documentscore) as numitems FROM (
			SELECT merged_table.document_id, document_metadata_version.name, SUM(documentscore), ROUND((SUM(documentscore))/'.$divider.') AS documentscore FROM
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

			INNER JOIN documents D ON (merged_table.document_id = D.id)
			INNER JOIN document_metadata_version ON (D.metadata_version_id = document_metadata_version.id)
			INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)

                        ' . $this->getPermissionsQuery() . '

			GROUP BY document_id

			ORDER BY documentscore DESC
		) merged_twice
		GROUP BY documentscore
		ORDER BY documentscore DESC
		';

		$ratingContentEnable = false; // Fix Up

		if ($ratingContentEnable) {

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

		$sql = str_replace('[-COMMENT-SCORE-]', '4', $sql);
		$sql = str_replace('[-RATING-SCORE-]', '2', $sql);

        return DBUtil::getResultArray($sql);

	}

	public function getDocumentsByRatingTemplate($dashlet = false)
	{
		$data = $this->getDocumentsByRating();

		$pointScale = array('1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars');
		$score = array('point_1' => 0, 'point_2' => 0, 'point_3' => 0, 'point_4' => 0, 'point_5' => 0);

		foreach ($data as $item)
		{
			$point = $item['scoregroup'] + 1;
			$score['point_'.$point] = $item['numitems'];
		}

		$pointScale = '"'.implode('", "', $pointScale).'"';
		$score = implode(', ', $score);

		if ($dashlet) {
			return $this->loadTemplate(array('pointScale'=>$pointScale, 'score'=>$score), 'document_ratings_dashlet');
		} else {
			return $this->loadTemplate(array('pointScale'=>$pointScale, 'score'=>$score), 'document_ratings');
		}
	}

	/******************************************************************************************************************/

	public function getPointsOverWeeks()
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
		LIMIT 0, 10';

		$ratingContentEnable = false; // Fix Up

		if ($ratingContentEnable) {

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

		$sql = str_replace('[-COMMENT-SCORE-]', '4', $sql);
		$sql = str_replace('[-RATING-SCORE-]', '2', $sql);

        return DBUtil::getResultArray($sql);
    }
	/******************************************************************************************************************/

	public function getTop10Users($limit = 10)
	{
		// Needs to consider likes and comments
		$sql = '
        SELECT user_id, username, users.name,
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

	public function getTop10UsersTemplate()
	{
		return $this->loadTemplate(array('data'=>$this->getTop10Users()), 'top10users');
	}

	public function getTop5UsersDashlet()
	{
		return $this->loadTemplate(array('data'=>$this->getTop10Users(5)), 'top5users_dashlet');
	}

	/******************************************************************************************************************/

	public function getDocumentViewsOverWeek()
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
		LIMIT 0, 10
        ';

        return DBUtil::getResultArray($sql);
    }

	public function getDocumentViewsOverWeekTemplate()
	{
		$templateData = array('data'=>$this->getDocumentViewsOverWeek());

		$templateData['graphdata'] = $this->generateDocViewsGraphData($templateData['data']);

		return $this->loadTemplate($templateData, 'documentviews_week');
	}

	private function generateDocViewsGraphData($data)
	{
		$weeks = array();
		$score = array();

		foreach($data as $item)
		{
			switch ($item['week_number'])
			{
				case 0: $str = 'This Week'; break;
				case 1: $str = 'Last Week'; break;
				default: $str = $item['week_number'].' Weeks Ago'; break;
			}

			$weeks[] = $str;
			$score[] = $item['count'];
		}

		$weeks = '"'.implode('", "', $weeks).'"';
		$score = implode(', ', $score);

		return array('weeks'=>$weeks, 'score'=>$score);
	}

	/******************************************************************************************************************/

	public function getMostViewedDocuments()
    {
        $sql = '
		SELECT document_transactions.document_id, COUNT( document_transactions.document_id ) AS count, document_metadata_version.name, mime_id
		FROM document_transactions
		INNER JOIN documents ON (document_transactions.document_id = documents.id)
		INNER JOIN document_metadata_version ON (documents.metadata_version_id = document_metadata_version.id)
		INNER JOIN document_content_version ON (document_metadata_version.content_version_id = document_content_version.id)
		WHERE transaction_namespace = "ktcore.transactions.view"
		GROUP BY document_transactions.document_id
		ORDER BY count DESC
		LIMIT 0, 5
        ';

        return DBUtil::getResultArray($sql);
    }

	public function getMostViewedDocumentsDashlet()
	{
		$templateData = array('data'=>$this->getMostViewedDocuments(), 'context'=>$this);

		return $this->loadTemplate($templateData, 'most_viewed_documents');
	}


	/******************************************************************************************************************/

	public function getUploadsPerWeekSql()
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
		LIMIT 0, 10
        ';

        return DBUtil::getResultArray($sql);
    }

	public function getUploadsPerWeekTemplate()
	{
		return $this->loadTemplate($this->generateUploadsPerWeekGraphData(), 'uploads_week');
	}

	public function getUploadsPerWeekDashlet()
	{
		return $this->loadTemplate($this->generateUploadsPerWeekGraphData(), 'uploads_week_dashlet');
	}

	private function generateUploadsPerWeekGraphData()
	{
		$data = $this->getUploadsPerWeekSql();

		$rowCounter = 0;

		$weeks = array();
		$uploadsCounter = array();
		$uploadsArray = array();

		for ($i=0; $i<10;$i++) {
			$week = $this->formatWeekStr($i);

			if ($data[$rowCounter]['week_number'] == $i) {
				$num = $data[$rowCounter]['uploadcount'];
				$rowCounter++;
			} else {
				$num = 0;
			}

			$uploadsCounter[] = $num;
			$uploadsArray[] = array('week_number'=>$i, 'count'=>$num, 'week_str'=>$this->formatWeekStr($i));
		}

		$weeks = '"'.implode('", "', $weeks).'"';
		$uploadsCounter = implode(', ', $uploadsCounter);

		return array('weeks'=>$weeks, 'uploadsCounter'=>$uploadsCounter, 'uploadsArray'=>$uploadsArray);
	}

	/******************************************************************************************************************/



	public function getUserAccessPerWeekSql()
    {
		// Decide whether to use document_transactions OR user_history
		$sql = '
		SELECT week_number, COUNT(uniqueDateUser) AS accessCount FROM
		(
			SELECT DISTINCT CONCAT(ABS(TIMESTAMPDIFF(WEEK, NOW(), datetime)), "_", user_id) AS uniqueDateUser,
				ABS( TIMESTAMPDIFF( WEEK, NOW(), datetime ) ) AS week_number FROM user_history
			WHERE ABS( TIMESTAMPDIFF( WEEK, NOW( ) , datetime ) ) < 10
		) alias
		GROUP BY week_number
		ORDER BY week_number
		';

        return DBUtil::getResultArray($sql);
    }

	public function getUserAccessPerWeekTemplate()
	{
		return $this->loadTemplate($this->generateUserAccessPerWeekGraphData(), 'user_access_week');
	}

	public function getUserAccessPerWeekDashlet()
	{
		return $this->loadTemplate($this->generateUserAccessPerWeekGraphData(), 'user_access_week_dashlet');
	}

	private function generateUserAccessPerWeekGraphData()
	{
		$data = $this->getUserAccessPerWeekSql();

		$rowCounter = 0;

		$weeks = array();
		$accessCounter = array();
		$accessArray = array();

		for ($i=0; $i<10;$i++) {
			$week = $this->formatWeekStr($i);

			if ($data[$rowCounter]['week_number'] == $i) {
				$num = $data[$rowCounter]['accessCount'];
				$rowCounter++;
			} else {
				$num = 0;
			}

			$accessCounter[] = $num;
			$accessArray[] = array('week_number'=>$i, 'count'=>$num, 'week_str'=>$this->formatWeekStr($i));
		}

		$weeks = '"'.implode('", "', $weeks).'"';
		$accessCounter = implode(', ', $accessCounter);

		return array('weeks'=>$weeks, 'accessCounter'=>$accessCounter, 'accessArray'=>$accessArray);
	}

	/******************************************************************************************************************/

	public function getTransactionViewsSql()
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
		LIMIT 0 , 10
        ';

        return DBUtil::getResultArray($sql);switch ($item['week_number'])
			{
				case 0: $str = 'This Week'; break;
				case 1: $str = 'Last Week'; break;
				default: $str = $item['week_number'].' Weeks Ago'; break;
			}
    }

	public function getTransactionOverWeekTemplate()
	{
		$templateData = array('data'=>$this->getTransactionViewsSql());

		$templateData['graphdata'] = $this->getTransactionOverWeekData($templateData['data']);

		return $this->loadTemplate($templateData, 'transactions_week');
	}

	private function getTransactionOverWeekData($data)
	{
		$weeks = array();
		$score = array();

		foreach($data as $item)
		{
			switch ($item['week_number'])
			{
				case 0: $str = 'This Week'; break;
				case 1: $str = 'Last Week'; break;
				default: $str = $item['week_number'].' Weeks Ago'; break;
			}

			$weeks[] = $str;
			$score[] = $item['count'];
		}

		$weeks = '"'.implode('", "', $weeks).'"';
		$score = implode(', ', $score);

		return array('weeks'=>$weeks, 'score'=>$score);
	}


	/******************************************************************************************************************/

	public function getTransactionsVsViewsOverWeekTemplate()
	{
		$templateData = array();

		$templateData['transactions'] = $this->getTransactionOverWeekData($this->getTransactionViewsSql());
		$templateData['document_views'] = $this->generateDocViewsGraphData($this->getDocumentViewsOverWeek());

		return $this->loadTemplate($templateData, 'transactions_vs_comments_week');
	}


	/******************************************************************************************************************/

	public function getViewsVsCommentsOverWeekTemplate()
	{
		$templateData = array();

		$templateData['comments'] = $this->getDocumentCommentsPerWeekData();
		$templateData['document_views'] = $this->generateDocViewsGraphData($this->getDocumentViewsOverWeek());
		$templateData['document_likes'] = $this->getDocumentLikesPerWeekData();

		return $this->loadTemplate($templateData, 'views_vs_comments_week');
	}

	/******************************************************************************************************************/

	public function getDocumentCommentsSql()
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
		LIMIT 10
        ';

        return DBUtil::getResultArray($sql);
    }

	public function getDocumentCommentsPerWeekTemplate()
	{
		$templateData = array('data'=>$this->getDocumentCommentsPerWeekData());

		return $this->loadTemplate($templateData, 'comments_week');
	}



	private function getDocumentCommentsPerWeekData()
	{
		$data = $this->getDocumentCommentsSql();

		$rowCounter = 0;

		$weeks = array();
		$commentsCounter = array();
		$commentsArray = array();

		for ($i=0; $i<10;$i++) {
			$week = $this->formatWeekStr($i);

			if ($data[$rowCounter]['week_number'] == $i) {
				$num = $data[$rowCounter]['comment_count'];
				$rowCounter++;
			} else {
				$num = 0;
			}

			$commentsCounter[] = $num;
			$commentsArray[] = array('week_number'=>$i, 'count'=>$num);
		}

		$weeks = '"'.implode('", "', $weeks).'"';
		$commentsCounter = implode(', ', $commentsCounter);

		return array('weeks'=>$weeks, 'counter'=>$commentsCounter, 'comments'=>$commentsArray);
	}


	/******************************************************************************************************************/

	public function getDocumentLikesSql()
    {
        $sql = '
		SELECT COUNT(document_id) as like_count, ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time)) AS week_number
		FROM ratingcontent_document
		WHERE ABS(TIMESTAMPDIFF(WEEK,NOW(),date_time)) < 10
		GROUP BY week_number
		ORDER BY week_number
		LIMIT 10
        ';

        return DBUtil::getResultArray($sql);
    }

	public function getDocumentLikesPerWeekTemplate()
	{
		$templateData = array('data'=>$this->getDocumentLikesPerWeekData());

		return $this->loadTemplate($templateData, 'likes_week');
	}



	private function getDocumentLikesPerWeekData()
	{
		$data = $this->getDocumentLikesSql();

		$rowCounter = 0;

		$weeks = array();
		$likesCounter = array();
		$likesArray = array();

		for ($i=0; $i<10;$i++) {
			$week = $this->formatWeekStr($i);

			if ($data[$rowCounter]['week_number'] == $i) {
				$num = $data[$rowCounter]['like_count'];
				$rowCounter++;
			} else {
				$num = 0;
			}

			$likesCounter[] = $num;
			$likesArray[] = array('week_number'=>$i, 'count'=>$num);
		}

		$weeks = '"'.implode('", "', $weeks).'"';
		$likesCounter = implode(', ', $likesCounter);

		return array('weeks'=>$weeks, 'counter'=>$likesCounter, 'likesArray'=>$likesArray);
	}


	/******************************************************************************************************************/


	private function loadTemplate($templateData, $template)
	{
		$templating =& KTTemplating::getSingleton();
	    $template = $templating->loadTemplate($template);

		$GLOBALS['page_js_resources'][] = 'thirdpartyjs/highcharts/highcharts.js';

	    return $template->render($templateData);
	}

	private function formatWeekStr($week)
	{
		switch ($week)
		{
			case 0: $str = 'This Week'; break;
			case 1: $str = 'Last Week'; break;
			default: $str = $week.' Weeks Ago'; break;
		}

		return $str;
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

	public function getMimeIcon($mimeId)
	{
		$iconFile = 'resources/mimetypes/' . KTMime::getIconPath($mimeId) . '.gif';


        if (file_exists(KT_DIR . '/' . $iconFile)) {
			return '<img src="/'.$iconFile.'" />';
		} else {
			return '&nbsp;';
		}
	}

	public function cleanUrl($documentId)
	{
		return KTUtil::kt_clean_document_url($documentId);
	}

}
?>