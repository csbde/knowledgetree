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

require_once('GraphicalAnalytics.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

class GraphicalAnalyticsTemplates {
	protected $graphs;

	public function __construct() {	}

	public function getTop10DocumentsTemplate()
	{
		return $this->loadTemplate(array('data'=>GraphicalAnalytics::getTop10Documents()), 'top10documents');
	}

	public function getTop10UsersTemplate()
	{
		return $this->loadTemplate(array('data'=>GraphicalAnalytics::getTop10Users()), 'top10users');
	}

	public function getDocumentViewsOverWeekTemplate()
	{
		$templateData = array('data'=>GraphicalAnalytics::getDocumentViewsOverWeek());

		$templateData['graphdata'] = $this->generateDocViewsGraphData($templateData['data']);

		return $this->loadTemplate($templateData, 'documentviews_week');
	}

	public function getTransactionOverWeekTemplate()
	{
		$templateData = array('data'=>GraphicalAnalytics::getTransactionViewsSql());

		$templateData['graphdata'] = $this->getTransactionOverWeekData($templateData['data']);

		return $this->loadTemplate($templateData, 'transactions_week');
	}

	public function getTransactionsVsViewsOverWeekTemplate()
	{
		$templateData = array();

		$templateData['transactions'] = $this->getTransactionOverWeekData(GraphicalAnalytics::getTransactionViewsSql());
		$templateData['document_views'] = $this->generateDocViewsGraphData(GraphicalAnalytics::getDocumentViewsOverWeek());

		return $this->loadTemplate($templateData, 'transactions_vs_comments_week');
	}

	public function getViewsVsCommentsOverWeekTemplate()
	{
		$templateData = array();

		$templateData['comments'] = $this->getDocumentCommentsPerWeekData();
		$templateData['document_views'] = $this->generateDocViewsGraphData(GraphicalAnalytics::getDocumentViewsOverWeek());

		return $this->loadTemplate($templateData, 'views_vs_comments_week');
	}

	public function getDocumentCommentsPerWeekTemplate()
	{
		$templateData = array('data'=>$this->getDocumentCommentsPerWeekData());

		return $this->loadTemplate($templateData, 'comments_week');
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

	private function getDocumentCommentsPerWeekData()
	{
		$data = GraphicalAnalytics::getDocumentCommentsSql();

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

}