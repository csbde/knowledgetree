<?

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