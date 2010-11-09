<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
											'ktcore.viewlet.document.activityfeed',
											'ktcore.viewlets.document.workflow',
											'thumbnail.viewlets',
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