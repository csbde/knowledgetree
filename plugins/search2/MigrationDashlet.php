<?php

/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
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
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco, 
 * California 94120-7775, or email info@knowledgetree.com.
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

			// Cannot divide by zero so make it 1
			$divMigratedDocuments = ($migratedDocuments > 0) ? $migratedDocuments : 1;
			$timePerDocument = $migrationTime / $divMigratedDocuments;
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
