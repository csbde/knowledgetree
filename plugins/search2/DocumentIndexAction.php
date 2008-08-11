<?php

require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

class DocumentIndexAction extends KTDocumentAction
{
    var $sName = 'ktcore.search2.index.action';
    var $_sShowPermission = "ktcore.permissions.write";

    function DocumentIndexAction($oDocument = null, $oUser = null, $oPlugin = null)
    {
    	parent::KTDocumentAction($oDocument, $oUser, $oPlugin);
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
    	    if(!is_object($this->oDocument)){
    	        return '';
    	    }
    		if (Indexer::isDocumentScheduled($this->oDocument->getId()))
    		{
    			return _kt('Unschedule Indexing');
    		}
    		else
    		{
    			return _kt('Schedule Indexing');
    		}
    	}

    	return '';
    }

    function do_main()
    {
    	$doc=$this->oDocument;
   		$docid=$doc->getId();
		if (Permission::userIsSystemAdministrator())
    	{
    	    $full_path = $doc->getFullPath();
    		if (Indexer::isDocumentScheduled($docid))
    		{
    			Indexer::unqueueDocument($docid);
    		    $this->addInfoMessage(sprintf(_kt("Document '%s' has been removed from the indexing queue."), $full_path));
    		}
    		else
    		{
    			Indexer::index($doc, 'A');
    		    $this->addInfoMessage(sprintf(_kt("Document '%s' has been added to the indexing queue."), $full_path));
    		}
    	}
    	redirect("view.php?fDocumentId=$docid");
    	exit;
    }
}

?>