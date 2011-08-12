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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/util/sanitize.inc');
require_once(KT_LIB_DIR . '/render_helpers/sharedContent.inc');

class KTFolderAction extends KTStandardDispatcher {

    public $sName;
    public $sDescription;
    public $_sShowPermission = 'ktcore.permissions.folder_details';
    public $_sDisablePermission;
    public $sHelpPage = 'ktcore/browse.html';
    public $_bAdminAlwaysAvailable = false;
    public $sSection = 'browse';
	public $cssClass = '';
	public $parentBtn = 'more';

    /** Shared user mutators to deal with bypassing permissions */
	public $bShowIfReadShared = false;
	public $bShowIfWriteShared = false;

	/** Handle bulk action lock */
	protected $showIfBulkActions = array();
	protected $bulkActionInProgress = '';

    public function KTFolderAction($oFolder = null, $oUser = null, $oPlugin = null)
    {
        parent::KTStandardDispatcher();
        $this->oFolder =& $oFolder;
        $this->oUser =& $oUser;
        $this->oPlugin =& $oPlugin;
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _kt('Browse')),
        );
        $this->persistParams(array('fFolderId'));
    }

    public function setFolder(&$oFolder)
    {
        $this->oFolder =& $oFolder;
    }

    public function setUser(&$oUser)
    {
        $this->oUser =& $oUser;
    }

    public function _show()
    {
    	// If this is a shared user the object permissions are different.
    	if(SharedUserUtil::isSharedUser())
    	{
    		return $this->shareduser_show();
    	}
        if (is_null($this->_sShowPermission)) {
            return true;
        }
        $oPermission =& KTPermission::getByName($this->_sShowPermission);
        if (PEAR::isError($oPermission)) {
            return true;
        }

        if ($this->_bAdminAlwaysAvailable) {
            if (KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
                return true;
            }
        }

        return KTPermissionUtil::userHasPermissionOnItem($this->oUser, $oPermission, $this->oFolder);
    }

    public function getURL()
    {
        $oKTConfig =& KTConfig::getSingleton();
        $sExt = '.php';

        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = '';
        }

        if ($oKTConfig->get('KnowledgeTree/pathInfoSupport')) {
            return sprintf('%s/action%s/%s?fFolderId=%d', $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oFolder->getID());
        } else {
            return sprintf('%s/action%s?kt_path_info=%s&fFolderId=%d', $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oFolder->getID());
        }
    }

    public function getOnClick()
    {
        return '';
    }
    
    public function getInfo()
    {
    	if(!empty($this->bulkActionInProgress)) {
    		if(!in_array($this->bulkActionInProgress, $this->showIfBulkActions)) {
    			return '';
    		}
    	}
        $status = '';
        if ($this->_show() === false) {
            $status = 'disabled';
        }

        $aInfo = array(
            'description' => $this->sDescription,
            'name' => $this->getDisplayName(),
            'ns' => $this->sName,
            'url' => $this->getURL(),
            'onclick' => $this->getOnClick(),
            'class' => $this->cssClass,
            'parent' => $this->parentBtn,
            'status' => $status
        );

        return $this->customiseInfo($aInfo);
    }

    public function getName()
    {
        return sanitizeForSQLtoHTML($this->sName);
    }

    public function getDisplayName()
    {
        // This should be overridden by the i18nised display name
        // This implementation is only here for backwards compatibility
        return sanitizeForSQLtoHTML($this->sDisplayName);
    }

    public function getDescription()
    {
        return sanitizeForSQLtoHTML($this->sDescription);
    }

    public function getButton()
    {
        return false;
    }

    public function customiseInfo($aInfo)
    {
        return $aInfo;
    }

    public function check()
    {
        $this->oFolder =& $this->oValidator->validateFolder($_REQUEST['fFolderId']);

        if (!$this->_show()) { return false; }

        $aOptions = array(
            'final' => false,
            'documentaction' => 'viewDocument',
            'folderaction' => 'browse',
        );
        $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs,
            KTBrowseUtil::breadcrumbsForFolder($this->oFolder, $aOptions));

        $portlet = new KTActionPortlet(sprintf(_kt('About this folder')));
        $aActions = KTFolderActionUtil::getFolderInfoActionsForFolder($this->oFolder, $this->oUser);
        $portlet->setActions($aActions,$this->sName);
        $this->oPage->addPortlet($portlet);

        $portlet = new KTActionPortlet(sprintf(_kt('Actions on this folder')));
        $aActions = KTFolderActionUtil::getFolderActionsForFolder($this->oFolder, $this->oUser);
        $portlet->setActions($aActions,$this->sName);
        $this->oPage->addPortlet($portlet);

        if (KTPermissionUtil::userHasPermissionOnItem($this->oUser, 'ktcore.permissions.folder_details', $this->oFolder)) {
            $this->oPage->setSecondaryTitle($this->oFolder->getName());
        } else {
            if (KTBrowseUtil::inAdminMode($this->oUser, $this->oFolder)) {
                $this->oPage->setSecondaryTitle(sprintf('(%s)', $this->oFolder->getName()));
            } else {
                $this->oPage->setSecondaryTitle('...');
            }
        }

        return true;
    }

    public function do_main()
    {
        return _kt('Dispatcher component of action not implemented.');
    }

    /**
     * Check permissions on document for shared user
     *
     * @return unknown
     */
    public function shareduser_show()
    {
		// Check if actions display for both users
		if ($this->bShowIfReadShared && $this->bShowIfWriteShared)
		{
			return true;
		}
		// Check if action does not have to be displayed
		else if (!$this->bShowIfReadShared && !$this->bShowIfWriteShared)
		{
			return false;
		}
		// Check if action needs to be hidden for
		else if (!$this->bShowIfReadShared)
		{
			if($this->getPermission() == 1)
			{
				return true;
			}
		}

    	return false;
    }

    /**
     * Set the shared object permission
     *
     */
    public function getPermission()
    {
		$iUserId = $this->oUser->getID();
		$iFolderId = $this->oFolder->getID();
		$iParentId = $this->oFolder->getParentID();
		return SharedContent::getPermissions($iUserId, $iFolderId, $iParentId, 'folder');
    }

    public function setBulkAction($bulkActionInProgress) {
    	$this->bulkActionInProgress = $bulkActionInProgress;
    }

}

class JavascriptFolderAction extends KTFolderAction {

	/**
	 * This is an array of js files to be included for this action
	 *
	 * @var array
	 */
	public $js_paths = array();
	/**
	 * This is custom javascript that should be included
	 *
	 * @var array
	 */
	public $js = array();
	/**
	 * Indicates if a custom function should be provided, or if the function is part of an existing js file.
	 * If true
	 *
	 * @var boolean
	 */
	public $function_provided_by_action = true;

	/**
	 * Set the function name if you have a custom name you want to provide.
	 *
	 * @var string
	 */
	public $function_name = null;

	public function JavascriptFolderAction($oFolder = null, $oUser = null, $oPlugin = null)
	{
		parent::KTFolderAction($oFolder, $oUser, $oPlugin);
		$this->js_initialise();
	}

	public function js_initialise()
	{
		// this will be overridden
	}

	public function js_include($js)
	{
		$this->js[] = $js;
	}

	public function js_include_file($path)
	{
		global $AjaxDocumentJSPaths;

		if (!isset($AjaxDocumentJSPaths))
		{
			$AjaxDocumentJSPaths = array();
		}

		if (!in_array($AjaxDocumentJSPaths))
		{
			$AjaxDocumentJSPaths[] = $path;
			$this->js_paths [] = $path;
		}
	}

	public function customiseInfo($aInfo)
	{
		$js = '';
		foreach($this->js_paths as $path)
		{
			$js .= "<script language=\"javascript\" src=\"$path\"></script>\n";
		}

		$js .= '<script language="javascript">'. "\n";
		foreach($this->js as $js2)
		{
			$js .= $js2 . "\n";
		}

		$js .= $this->getScript() . '</script>'. "\n";

		$js .= '<div onclick="' . $this->getScriptActivation() . '"><a>' . $this->getDisplayName() . '</a></div>'. "\n";


		$aInfo['js'] = $js;

        return $aInfo;
    }

    public function getScript()
    {
    	if ($this->function_provided_by_action === false)
    	{
    		return '';
    	}

    	return "function " . $this->getScriptActivation() . '{'.$this->getFunctionScript().'}';
    }

	public function getFunctionScript()
	{
		return 'alert(\''. $this->getDisplayName()  .' not implemented!\');';
	}

    public function getScriptActivation()
    {
    	if (!is_null($this->function_name))
    	{
    		return $this->function_name;
    	}

    	global $AjaxDocumentActions;
    	$class = get_class($this);

    	return 'js' .  $class. 'Dispatcher()';
    }

}

class KTFolderActionUtil {

    public function getFolderActions($slot)
    {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions($slot);
    }

    public function getFolderInfoActions()
    {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions('folderinfo');
    }

    public static function getFolderActionsForFolder($folder, $user, $slot = 'folderaction')
    {
        $objects = array();

        $folderActions = KTFolderActionUtil::getFolderActions($slot);
        foreach ($folderActions as $action) {
            list($class, $path, $namespace, $pluginName) = $action;
            $pluginRegistry = KTPluginRegistry::getSingleton();
            $plugin = $pluginRegistry->getPlugin($pluginName);

            if (!empty($path)) {
                require_once($path);

                $objects[] = new $class($folder, $user, $plugin);
            }
        }

        return $objects;
    }

    public function &getFolderInfoActionsForFolder($oFolder, $oUser)
    {
        $objects = array();

        $folderInfoActions = KTFolderActionUtil::getFolderInfoActions();
        foreach ($folderInfoActions as $action) {
            list($className, $path, $namespace, $pluginName) = $action;
            $registry = KTPluginRegistry::getSingleton();
            $plugin = $registry->getPlugin($pluginName);
            
            if (!empty($path)) {
                require_once($path);
                
                $objects[] =new $className($oFolder, $oUser, $plugin);
            }
        }

        return $objects;
    }

    public static function checkForBackgroundedAction($folderId = '', $action = '')
    {
        if (!empty($action)) {
            // "document" action refers to the zoho plugin
            $blockedActions = array('document', 'addDocument', 'addFolder', 'rename', 'roles', 'copy', 'move', 'delete', 'archive', 'checkin', 'checkout');
            
            $action = explode('.', $action);
            $action = array_pop($action);
            
            if (!in_array($action, $blockedActions)) {
                return false;
            }
        }
        
        // todo: make neat 
        $redirect = '';
        if (empty($folderId)) {
            $folderId = $_REQUEST['fFolderId'];
            $redirect = KTUtil::kt_clean_folder_url($folderId);
            
            if (empty($folderId)) {
                $documentId = $_REQUEST['fDocumentId'];
                
                if (!empty($documentId)) {
                    $document = Document::get($documentId);
                    $folderId = $document->getFolderId();
                    $redirect = KTUtil::kt_clean_document_url($documentId);
                } 
                else {
                    $folderId = 1;
                }
            }
        }
        
        // todo: refactor to check bulk actions ...
        include_once(KT_LIB_DIR . '/permissions/BackgroundPermissions.php');
        $accountName = (defined('ACCOUNT_NAME')) ? ACCOUNT_NAME : '';
        
        $backgroundPerms = new BackgroundPermissions($folderId, $accountName);
        $check = $backgroundPerms->checkIfFolderAffected();
        $message = '';
        
        if ($check) {
            $message = 'This action cannot be performed as a permissions update is currently in progress. Please try again later.';
        }
        
        $response = array('check' => $check, 'message' => $message, 'redirect' => $redirect);
        
        return $response;
    }
}

?>