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

class ExternalResourceStatusDashlet extends KTBaseDashlet
{
	var $resources = array();

    function ExternalResourceStatusDashlet()
    {
        $this->sTitle = _kt('External Resource Dependancy Status');
        $this->sClass = 'ktError';
    }

    function addIssue($resource, $status)
    {
    	$this->resources[] = array(
    				'name'=>$resource,
    				'status'=>str_replace(

    						array("\n",_kt('Administrator Guide')),
    						array('<br>', sprintf("<a target='_blank' href=\"http://www.knowledgetree.com/go/ktAdminManual\">%s</a>", _kt('Administrator Guide'))), $status));
    }

    function checkResources()
    {
    	$check = true;
    	// check if we have a cached result
		if (isset($_SESSION['ExternalResourceStatus']))
		{
			// we will only do the check every 5 minutes
			if (time() - $_SESSION['ExternalResourceStatus']['time'] < 5 * 60)
			{
				$check = false;
				$this->resources = $_SESSION['ExternalResourceStatus']['resources'];
			}
		}

		// we will only check if the result is not cached, or after 5 minutes
		if ($check)
		{
	    	$this->checkOpenOffice();
    		$this->checkLucene();
    		$_SESSION['ExternalResourceStatus']['time'] = time();
    		$_SESSION['ExternalResourceStatus']['resources'] = $this->resources;
		}

    	return (count($this->resources) > 0);
    }

    function checkOpenOffice()
    {
		$diagnose = SearchHelper::checkOpenOfficeAvailablity();
		if (!is_null($diagnose))
		{
			$this->addIssue(_kt('Open Office Server'), $diagnose);
		}
    }

    function checkLucene()
    {
		$indexer = Indexer::get();
		$diagnose = $indexer->diagnose();
		if (!is_null($diagnose))
		{
			$this->addIssue(_kt('Lucene Indexer'), $diagnose);
		}
    }

	function is_active($oUser)
	{
	    if (!Permission::userIsSystemAdministrator($oUser))
	    {
	    	return false;
	    }

	    return $this->checkResources() > 0;
	}

	function render()
	{
	    $oTemplating =& KTTemplating::getSingleton();
	    $oTemplate = $oTemplating->loadTemplate('ktcore/search2/external_resources');

	    $aTemplateData = array(
	    		'context' => $this,
				'resources' => $this->resources
			);

        return $oTemplate->render($aTemplateData);
    }
}

?>
