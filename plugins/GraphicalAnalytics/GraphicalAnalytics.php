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
require_once('KTGraphicalAnalyticsSql.php');

class GraphicalAnalytics {

	private $table;
	
	public function __construct()
	{
		$this->KTGraphicalAnalyticsSql = new KTGraphicalAnalyticsSql();
	}

	public function getTop10DocumentsTemplate()
	{
		return $this->loadTemplate(array('data'=>$this->KTGraphicalAnalyticsSql->getTop10Documents()), 'top10documents');
	}

	public function getTop5DocumentsDashlet()
	{
		return $this->loadTemplate(array('context'=> $this, 'data'=>$this->KTGraphicalAnalyticsSql->getTop10Documents(5)), 'top5documents_dashlet');
	}

/******************************************************************************************************************/


	public function getDocumentsByRatingTemplate($dashlet = false)
	{
		$data = $this->KTGraphicalAnalyticsSql->getDocumentsByRating();

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

	public function getTop10UsersTemplate()
	{
		return $this->loadTemplate(array('data'=>$this->KTGraphicalAnalyticsSql->getTop10Users()), 'top10users');
	}

	public function getTop5UsersDashlet()
	{
		return $this->loadTemplate(array('data'=>$this->KTGraphicalAnalyticsSql->getTop10Users(5)), 'top5users_dashlet');
	}

	/******************************************************************************************************************/

	public function getDocumentViewsOverWeekTemplate()
	{
		$templateData = array('data'=>$this->KTGraphicalAnalyticsSql->getDocumentViewsOverWeek());

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

	public function getMostViewedDocumentsDashlet()
	{
		$templateData = array('data'=>$this->KTGraphicalAnalyticsSql->getMostViewedDocuments(), 'context'=>$this);

		return $this->loadTemplate($templateData, 'most_viewed_documents');
	}


	/******************************************************************************************************************/

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
		$data = $this->KTGraphicalAnalyticsSql->getUploadsPerWeekSql();

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
		$data = $this->KTGraphicalAnalyticsSql->getUserAccessPerWeekSql();

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

	public function getTransactionOverWeekTemplate()
	{
		$templateData = array('data'=>$this->KTGraphicalAnalyticsSql->getTransactionViewsSql());

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

	public function getDocumentCommentsPerWeekTemplate()
	{
		$templateData = array('data'=>$this->getDocumentCommentsPerWeekData());

		return $this->loadTemplate($templateData, 'comments_week');
	}



	private function getDocumentCommentsPerWeekData()
	{
		$data = $this->KTGraphicalAnalyticsSql->getDocumentCommentsSql();

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

	public function getDocumentLikesPerWeekTemplate()
	{
		$templateData = array('data'=>$this->getDocumentLikesPerWeekData());

		return $this->loadTemplate($templateData, 'likes_week');
	}



	private function getDocumentLikesPerWeekData()
	{
		$data = $this->KTGraphicalAnalyticsSql->getDocumentLikesSql();

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