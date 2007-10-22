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

class IndexingStatusDashlet extends KTBaseDashlet
{
	var $indexerName;

    function IndexingStatusDashlet()
    {
        $this->sTitle = _kt('document Indexer Status');
        $this->sClass = 'ktError';
    }

	function is_active($oUser)
	{
	    if (!Permission::userIsSystemAdministrator($oUser))
	    {
	    	return false;
	    }

		if (isset($_SESSION['IndexingStatus']))
		{
			$this->indexerName = $_SESSION['IndexingStatus']['indexerName'];
	    	$this->indexerDiagnosis = $_SESSION['IndexingStatus']['indexerDiagnosis'];
	    	$this->extractorDiagnosis = $_SESSION['IndexingStatus']['extractorDiagnosis'];
		}
		else
		{
			$indexer = Indexer::get();
	    	$this->indexerName = $indexer->getDisplayName();
	    	$this->indexerDiagnosis = $indexer->diagnose();
	    	$this->extractorDiagnosis = array();
	    	$extractorDiagnosis = $indexer->diagnoseExtractors();


	    	$result = array();
	    	foreach($extractorDiagnosis as $class=>$diagnosis)
	    	{
	    		$name=$diagnosis['name'];
	    		$diag = $diagnosis['diagnosis'];
				$result[$diag][] = $name;
	    	}

	    	foreach($result as $problem=>$indexers)
	    	{
	    		if (empty($problem)) continue;
	    		$this->extractorDiagnosis[] = array('problem'=>$problem, 'indexers'=>$indexers);
	    	}

	    	$this->indexerDiagnosis = str_replace(

    						array("\n",_kt('Administrator Guide')),
    						array('<br>', sprintf("<a target='_blank' href=\"http://www.knowledgetree.com/go/ktAdminManual\">%s</a>", _kt('Administrator Guide'))), $this->indexerDiagnosis);

	    	$_SESSION['IndexingStatus']['indexerName'] = $this->indexerName;
	    	$_SESSION['IndexingStatus']['indexerDiagnosis'] = $this->indexerDiagnosis;
	    	$_SESSION['IndexingStatus']['extractorDiagnosis'] = $this->extractorDiagnosis;
		}

		if (is_null($this->indexerDiagnosis) && empty($this->extractorDiagnosis))
		{
			return false;
		}


	    return true;
	}

	function render()
	{
	    $oTemplating =& KTTemplating::getSingleton();
	    $oTemplate = $oTemplating->loadTemplate('ktcore/search2/indexing_status');

	    $aTemplateData = array(
	    		'context' => $this,
	    		'indexerName' => $this->indexerName,
	    		'indexerDiagnosis' => $this->indexerDiagnosis,
	    		'extractorDiagnosis' => $this->extractorDiagnosis
			);

        return $oTemplate->render($aTemplateData);
    }
}

?>
