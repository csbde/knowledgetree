<?php
/*
 * $Id: $
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

require_once(KT_LIB_DIR . '/render_helpers/sharedContent.inc');
require_once(KT_LIB_DIR . '/users/shareduserutil.inc.php');

/**
 * Trigger for document add (postValidate)
 *
 */
class KTAddSharedContentObjectTrigger
{
    public $namespace = 'ktcore.triggers.sharedcontent.add';
    public $aInfo = null;
    public $oUser = null;
    public $oObject = null;
    public $sType = null;

    /**
     * function to set the info for the trigger
     *
     * @param array $aInfo
     */
    function setInfo($aInfo)
    {
        if(isset($aInfo['document']) && !empty($aInfo['document'])) {
            $this->sType = 'document';
            $this->oObject = $aInfo['document'];
        } else if(isset($aInfo['folder']) && !empty($aInfo['folder'])) {
            $this->sType = 'folder';
            $this->oObject = $aInfo['folder'];
        }

    	$this->aInfo = $aInfo;
    }

    /**
     * postValidate method for trigger
     *
     * @return unknown
     */
    function postValidate() {
        // Trigger is only relevant for shared users
		if (!SharedUserUtil::isSharedUser()) {
			return false;
		}

		$iObjectId = $this->oObject->getId();

		$oSharedContent = new SharedContent($_SESSION['userID'], $_SESSION['userID'], $iObjectId, $this->sType, 1);
		if(!$oSharedContent->exists())
		{
			$res = $oSharedContent->create();
			if(!$res)
			{
				// TODO : Logging
			}
		}

		return $res;
    }
}


class KTAddSharedDocTrigger extends KTAddSharedContentObjectTrigger
{
    var $namespace = 'ktcore.triggers.sharedcontent.adddoc';
}

/**
 * Trigger for document add (postValidate)
 *
 */
class KTDeleteSharedContentObjectTrigger
{
    public $namespace = 'ktcore.triggers.sharedcontent.delete';
    public $aInfo = null;
    public $oUser = null;
    public $oDocument = null;
    public $sType = null;

    /**
     * function to set the info for the trigger
     *
     * @param array $aInfo
     */
    function setInfo($aInfo)
    {
//    	$this->aInfo = $aInfo;
//    	$this->oUser = $aInfo['oUser'];
//    	$this->oFolder = $aInfo['oDocument'];
//    	$this->sType = $aInfo['sType'];

        if(isset($aInfo['document']) && !empty($aInfo['document'])) {
            $this->sType = 'document';
            $this->oObject = $aInfo['document'];
        } else if(isset($aInfo['folder']) && !empty($aInfo['folder'])) {
            $this->sType = 'folder';
            $this->oObject = $aInfo['folder'];
        }

    	$this->aInfo = $aInfo;
    }

    /**
     * postValidate method for trigger
     *
     * @return unknown
     */
    function postValidate() {
//		if (!SharedUserUtil::isSharedUser()) {
//			return false;
//		}

		$iObjectId = $this->oObject->getId();

		$oSharedContent = new SharedContent($_SESSION['userID'], $_SESSION['userID'], $iObjectId, $this->sType, 1);
		if($oSharedContent->exists())
		{
			$res = $oSharedContent->delete();
			if(!$res)
			{
				// TODO : Logging
			}
		}

		return $res;
    }
}

class KTDeleteSharedDocTrigger extends KTDeleteSharedContentObjectTrigger
{
    var $namespace = 'ktcore.triggers.sharedcontent.deletedoc';
}
?>