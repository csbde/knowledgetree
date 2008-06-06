<?php

/**
 * $Id
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
 */

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

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
        $cacheDir = $config->get('cache/cacheDirectory');
        $cacheFile = $cacheDir . '/houseKeeper.folders';

        if (is_file($cacheFile))
        {
        	$this->folders = unserialize(file_get_contents($cacheFile));
        	return;
        }

        $tempDir = $config->get('urls/tmpDirectory');
        $logDir = $config->get('urls/logDirectory');
        $docsDir = $config->get('urls/documentRoot');

        $indexer = Indexer::get();
        $luceneDir = $indexer->getIndexDirectory();

        $systemDir = OS_UNIX?'/tmp':'c:/windows/temp';

        $this->folders = array(
        	array(
        		'name'=>_kt('Smarty Cache'),
        		'folder'=>$tempDir,
        		'pattern'=>'^%%.*',
        		'canClean'=>true
        	),
        	array(
        		'name'=>_kt('System Cache'),
        		'folder'=>$cacheDir,
        		'pattern'=>'',
        		'canClean'=>true
        	),
        	array(
        		'name'=>_kt('System Logs'),
        		'folder'=>$logDir,
        		'pattern'=>'.+\.txt$',
        		'canClean'=>true
        	));

        	$this->folders[] =
        	array(
        		'name'=>_kt('System Temporary Folder'),
        		'folder'=>$systemDir,
        		'pattern'=>'(sess_.+)?(.+\.log$)?',
        		'canClean'=>true
        	);

        if (is_dir($docsDir))
        {
        $this->folders[] =
        	array(
        		'name'=>_kt('Documents'),
        		'folder'=>$docsDir,
        		'pattern'=>'',
        		'canClean'=>false
        	);
        }

        if (is_dir($luceneDir))
        {
        $this->folders[] =
        	array(
        		'name'=>_kt('Document Index'),
        		'folder'=>$luceneDir,
        		'pattern'=>'',
        		'canClean'=>false
        	);

        	// lets only cache this once it has been resolved!
        	file_put_contents($cacheFile, serialize($this->folders));
        }



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
