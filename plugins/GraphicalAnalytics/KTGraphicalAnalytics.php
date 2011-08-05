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

class KTGraphicalAnalytics {
	
	private $table;
	
	public function __construct() { }
	
	public function getTop10Documents()
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
	
	public function getTop10DocumentsTemplate()
	{
		return $this->loadTemplate(array('data'=>$this->getTop10Documents()), 'top10documents');
	}
	
	
	/******************************************************************************************************************/
	
	public function getTop10Users()
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
	
	public function getTop10UsersTemplate()
	{
		return $this->loadTemplate(array('data'=>$this->getTop10Users()), 'top10users');
	}
	
	/******************************************************************************************************************/
	
	public function getDocumentViewsOverWeek()
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
	
	
	public function getTransactionViewsSql()
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
		
		return $this->loadTemplate($templateData, 'views_vs_comments_week');
	}
	
	/******************************************************************************************************************/
	
	public function getDocumentCommentsSql()
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