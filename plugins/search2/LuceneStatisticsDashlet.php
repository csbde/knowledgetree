<?php

/**
 * $Id
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 *
 * The Original Code is: KnowledgeTree Open Source
 *
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

class LuceneStatisticsDashlet extends KTBaseDashlet
{
    function LuceneStatisticsDashlet()
    {
        $this->sTitle = _kt('Lucene Statistics');
    }

	function is_active($oUser)
	{
	    return Permission::userIsSystemAdministrator($oUser);
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

			$sql = "SELECT count(*) as docsInQueue FROM index_files";
			$docsInQueue  = DBUtil::getOneResultKey($sql, 'docsInQueue');

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
