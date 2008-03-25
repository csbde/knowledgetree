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

class IndexingStatusDashlet extends KTBaseDashlet
{
	var $indexerName;

    function IndexingStatusDashlet()
    {
        $this->sTitle = _kt('Document Indexer Status');
        $this->sClass = 'ktError';
    }

	function is_active($oUser)
	{
	    if (!Permission::userIsSystemAdministrator())
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

		if (empty($this->indexerDiagnosis) && empty($this->extractorDiagnosis))
		{
			return false;
		}

	    return true;
	}

	function render()
	{
	    $oTemplating =& KTTemplating::getSingleton();
	    $oTemplate = $oTemplating->loadTemplate('ktcore/search2/indexing_status');

		$url = KTUtil::kt_url();
		
	    $aTemplateData = array(
	    		'context' => $this,
	    		'indexerName' => $this->indexerName,
	    		'indexerDiagnosis' => $this->indexerDiagnosis,
	    		'extractorDiagnosis' => $this->extractorDiagnosis,
	    		'rootUrl' => $url
			);

        return $oTemplate->render($aTemplateData);
    }
}

?>
