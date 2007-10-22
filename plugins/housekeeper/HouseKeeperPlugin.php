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
 */

class HouseKeeperPlugin extends KTPlugin
 {
	var $autoRegister = true;
 	var $sNamespace = 'ktcore.housekeeper.plugin';

 	var $folders = array();

 	function HouseKeeperPlugin($sFilename = null)
 	{
	 	parent::KTPlugin($sFilename);

        $this->sFriendlyName = _kt('Housekeeper');

        $config = KTConfig::getSingleton();
        $tempDir = $config->get('urls/tmpDirectory');
        $cacheDir = $config->get('cache/cacheDirectory');
        $logDir = $config->get('urls/logDirectory');
        $docsDir = $config->get('urls/documentRoot');
        $luceneDir = $config->get('indexer/luceneDirectory');

        $systemDir = OS_UNIX?'/tmp':'c:/windows/temp';

        $this->folders = array(
        	array(
        		'name'=>_kt('Smarty Cache'),
        		'folder'=>$tempDir,
        		'pattern'=>'^%%.*',
        		'canClean'=>true
        	),
        	array(
        		'name'=>_kt('KnowledgeTree Cache'),
        		'folder'=>$cacheDir,
        		'pattern'=>'',
        		'canClean'=>true
        	),
        	array(
        		'name'=>_kt('KnowledgeTree Logs'),
        		'folder'=>$logDir,
        		'pattern'=>'.+\.txt$',
        		'canClean'=>true
        	),
        	array(
        		'name'=>_kt('System Temporary Folder'),
        		'folder'=>$systemDir,
        		'pattern'=>'(sess_.+)?(.+\.log$)?',
        		'canClean'=>true
        	),
        	array(
        		'name'=>_kt('KnowledgeTree Documents'),
        		'folder'=>$docsDir,
        		'pattern'=>'',
        		'canClean'=>false
        	),
        	array(
        		'name'=>_kt('KnowledgeTree Lucene Indexes'),
        		'folder'=>$luceneDir,
        		'pattern'=>'',
        		'canClean'=>false
        	),
        );
    }

 	function getDirectories()
 	{
 		return $this->folders;
 	}

    function getDirectory($folder)
    {
    	foreach($this->folders as $dir)
    	{
    		if ($dir['folder'] == $folder)
    		{
    			return $dir;
    		}
    	}
    	return null;
    }

    function setup()
    {
		$this->registerDashlet('DiskUsageDashlet', 'ktcore.diskusage.dashlet', 'DiskUsageDashlet.inc.php');
		$this->registerDashlet('FolderUsageDashlet', 'ktcore.folderusage.dashlet', 'FolderUsageDashlet.inc.php');

        $oTemplating =& KTTemplating::getSingleton();
  	 	$oTemplating->addLocation('housekeeper', '/plugins/housekeeper/templates');
    }

}

$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('HouseKeeperPlugin', 'ktcore.housekeeper.plugin', __FILE__);

?>
