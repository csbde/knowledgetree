<?php

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class FolderIndexAction extends KTFolderAction
{
    var $sName = 'ktcore.search2.index.folder.action';
    var $_sShowPermission = "ktcore.permissions.write";

    function FolderIndexAction($oDocument = null, $oUser = null, $oPlugin = null)
    {
    	parent::KTFolderAction($oDocument, $oUser, $oPlugin);
    	$this->sDisplayName=_kt('Schedule Indexing');
    }

	function getName()
	{
		return _kt('Document Indexer');
	}

    function getDisplayName()
    {
    	if (Permission::userIsSystemAdministrator() && $_SESSION['adminmode'])
    	{
    	    if(!is_object($this->oFolder)){
    	        return '';
    	    }
    	       return _kt('Schedule Indexing');
    	}

    	return '';
    }

    function do_main()
    {
    	$folder=$this->oFolder;
   		$folderid=$folder->getId();
		if (Permission::userIsSystemAdministrator())
    	{
    	    if ($folderid == 1)
    	    {
    	        Indexer::indexAll();
    	    }
    	    else
    	    {
    	        Indexer::indexFolder($folder);
    	    }
    	}
    	$full_path = $folder->getFullPath();
    	$this->addInfoMessage(sprintf(_kt("All documents under the folder '%s' have been scheduled for indexing."), $full_path));

    	redirect("browse.php?fFolderId=$folderid");
    	exit;
    }
}

?>