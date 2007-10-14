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

class LuceneMigrationDashlet extends KTBaseDashlet
{
    function LuceneMigrationDashlet()
    {
        $this->sTitle = _kt('Lucene Migration Status');
    }

	function is_active($oUser)
	{
	    if (!Permission::userIsSystemAdministrator($oUser))
	    {
	    	return false;
	    }

	    $sql = "select count(*) as no from document_text";
	    $no = DBUtil::getOneResultKey($sql,'no');
		if ($no == 0)
		{
			return false;
		}
		$this->migratingDocuments = $no;

	    return true;
	}

	function render()
	{
	    $oTemplating =& KTTemplating::getSingleton();
	    $oTemplate = $oTemplating->loadTemplate('ktcore/search2/lucene_migration');

	    $config = KTConfig::getSingleton();
	    $batchDocuments = $config->get('indexer/batchMigrateDocuments');


		$migratedDocuments = KTUtil::getSystemSetting('migratedDocuments',0);
	    $migratingDocuments = $this->migratingDocuments;

	    $migrationStart = KTUtil::getSystemSetting('migrationStarted');
	    if (is_null($migrationStart))
	    {
			$migrationStartString = _kt('Not started');
			$migrationPeriod = _kt('N/A');
			$estimatedTime = _kt('Unknown');
			$estimatedPeriod = $estimatedTime;
	    }
	    else
	    {
			$migrationStartString = date('Y-m-d H:i:s', $migrationStart);
			$migrationTime = KTUtil::getSystemSetting('migrationTime',0);
			$migrationPeriod = KTUtil::computePeriod($migrationTime, '');
			$timePerDocument = $migrationTime / $migratedDocuments;
			$estimatedPeriod = $timePerDocument * $migratingDocuments;
			$estimatedTime = date('Y-m-d H:i:s', $migrationStart + $estimatedPeriod);
			$estimatedPeriod = KTUtil::computePeriod($estimatedPeriod, '');
	    }



	    $aTemplateData = array(
	    		'context' => $this,
	    		'batchDocuments'=>$batchDocuments,
	    		'batchPeriod'=>'Periodically',
	    		'migrationStart'=>$migrationStartString,
	    		'migrationPeriod'=>$migrationPeriod,
	    		'migratedDocuments'=>$migratedDocuments,
	    		'migratingDocuments'=>$migratingDocuments,
	    		'estimatedTime'=>$estimatedTime,
	    		'estimatedPeriod'=>$estimatedPeriod
			);

        return $oTemplate->render($aTemplateData);
    }
}

?>
