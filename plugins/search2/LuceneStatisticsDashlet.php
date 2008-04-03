<?php

/**
 * $Id:$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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

class LuceneStatisticsDashlet extends KTBaseDashlet
{
    function LuceneStatisticsDashlet()
    {
        $this->sTitle = _kt('Document Indexer Statistics');
    }

	function is_active($oUser)
	{
	    return Permission::userIsSystemAdministrator();
	}

	function render()
	{
	    $oTemplating =& KTTemplating::getSingleton();
	    $oTemplate = $oTemplating->loadTemplate('ktcore/search2/lucene_statistics');


		$check = true;
    	// check if we have a cached result
		if (isset($_SESSION['LuceneStats']))
		{
			// we will only do the check every 5 minutes
			if (time() - $_SESSION['LuceneStats']['time'] < 5 * 60)
			{
				$check = false;
				$stats = $_SESSION['LuceneStats']['stats'];
			}
		}

		// we will only check if the result is not cached, or after 5 minutes
		if ($check)
		{
			$optimisationDate = KTUtil::getSystemSetting('luceneOptimisationDate', '');

			$noOptimisation = false;
			if ($optimisationDate == '')
			{
				$optimisationDate = _kt('N/A');
				$optimisationPeriod = $optimisationDate;
			}
			else
			{
				$optimisationPeriod = KTUtil::computePeriodToDate($optimisationDate, null, true);
				$noOptimisation = $optimisationPeriod['days'] > 2;
				$optimisationPeriod = $optimisationPeriod['str'];
				$optimisationDate = date('Y-m-d H:i:s', $optimisationDate);
			}

			$indexingDate = KTUtil::getSystemSetting('luceneIndexingDate', '');
			if ($indexingDate == '')
			{
				$indexingDate = _kt('N/A');
				$indexingPeriod = $indexingDate;
			}
			else
			{
				$indexingPeriod = KTUtil::computePeriodToDate($indexingDate);
				$indexingDate = date('Y-m-d H:i:s', $indexingDate);
			}

			$index = Indexer::get();
			$docsInIndex = $index->getDocumentsInIndex();

			// we are only interested in documents that are active
			$docsInQueue = $index->getIndexingQueue(false);
			$docsInQueue = count($docsInQueue);

			$errorsInQueue = $index->getIndexingQueue(true);
			$errorsInQueue = count($errorsInQueue);

			$sql = "SELECT count(*) as docsInRepository FROM documents";
			$docsInRepository = DBUtil::getOneResultKey($sql, 'docsInRepository');

			if ($docsInRepository == 0)
			{
				$indexingCoverage = '0.00%';
				$queueCoverage = $indexingCoverage;
			}
			else
			{
				// compute indexing coverage
				$indexingCoverage = _kt('Not Available');
				if (is_numeric($docsInIndex))
				{
					$indexingCoverage = ($docsInIndex * 100) / $docsInRepository;
					$indexingCoverage = number_format($indexingCoverage, 2, '.',',') . '%';
				}

				// compute queue coverage
				$queueCoverage = _kt('Not Available');
				if (is_numeric($docsInQueue))
				{
					$queueCoverage = ($docsInQueue * 100) / $docsInRepository;
					$queueCoverage = number_format($queueCoverage, 2, '.',',') . '%';
				}
			}

			$stats = array(
				'optimisationDate'=>$optimisationDate,
	    		'optimisationPeriod'=>$optimisationPeriod,
	    		'indexingDate'=>$indexingDate,
	    		'indexingPeriod'=>$indexingPeriod,
	    		'docsInIndex'=>$docsInIndex,
	    		'docsInQueue'=>$docsInQueue,
	    		'errorsInQueue'=>$errorsInQueue,
	    		'docsInRepository'=>$docsInRepository,
	    		'indexingCoverage'=>$indexingCoverage,
	    		'queueCoverage'=>$queueCoverage,
	    		'noOptimisation'=>$noOptimisation
			);

    		$_SESSION['LuceneStats']['time'] = time();
    		$_SESSION['LuceneStats']['stats'] = $stats;
		}

	    $aTemplateData = array(
	    		'context' => $this,
	    		'stats'=>$stats

			);

        return $oTemplate->render($aTemplateData);
    }
}

?>
