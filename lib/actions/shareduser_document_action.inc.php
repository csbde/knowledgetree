<?php
require_once(KT_LIB_DIR . "/actions/documentaction.inc.php");

class SharedUserDocumentActionUtil extends KTDocumentActionUtil 
{
	// TODO : Where can I store this array?
	private $shared_user_actions = array(	'ktcore.actions.document.displaydetails', 
											'ktcore.actions.document.transactionhistory',
											'ktcore.actions.document.versionhistory',
											'instaview.processor.link',
											'ktcore.actions.document.cancelcheckout',
											'ktcore.actions.document.checkin',
											'ktcore.actions.document.checkout',
											'ktcore.actions.document.edit',
											'ktcore.actions.document.rename',
											'ktcore.actions.document.view',
											'ktcore.actions.document.workflow',
											'zoho.edit.document',
											);
	
    public function getDocumentActionInfo($slot = 'documentaction')
    {
        $oRegistry = KTActionRegistry::getSingleton();
        $actions = $oRegistry->getActions($slot);
        foreach ($actions as $key=>$action)
        {
        	if(!in_array($key, $this->shared_user_actions))
        	{
        		unset($actions[$key]);
        	}
        }
        return $actions;
    }
    
    public function getDocumentActionsForDocument($oDocument, $oUser, $slot = 'documentaction') 
    {
        $aObjects = array();
        foreach (SharedUserDocumentActionUtil::getDocumentActionInfo($slot) as $aAction) 
        {
            list($sClassName, $sPath, $sPlugin) = $aAction;
            $oRegistry = KTPluginRegistry::getSingleton();
            $oPlugin = $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) 
            {
                require_once($sPath);
            }
            $aObjects[] = new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }
    
}
?>