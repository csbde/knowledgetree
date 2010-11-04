<?php
/**
 * $Id$
 *
 * Represents a user as per the users table in the database.
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
 */

require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class SharedContent extends KTEntity {
    public $iUserId; // user id of owner
    public $iObjectId; // object id of document or folder
    public $sType; // object type if folder or document

    public $_aFieldToSelect = array(
        "iUserId" => "user_id",
        "iObjectId" => "object_id",
        "sType" => "type",
    );
    
    public $_bUsePearError = true;
    
	public function __construct() 
    {
		$this->iUserId = '';
		$this->iObjectId = '';
		$this->sType = '';
    }
    
    /**
    * Retrieve shared content objects database table name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
    public function _table () { return KTUtil::getTableName('shared_content'); }
    
    // ---------------
    // Getters/setters
    // ---------------
    /**
    * Retrieve a list of SharedContent objects
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $sWhereClause - string
    * @param $aOptions - array
    * @return SharedContent objects - array
    */
    public function getList($sWhereClause = null, $aOptions = null) 
    {
        if (is_null($aOptions)) { $aOptions = array(); }
        $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby','name');
        return KTEntityUtil::getList2('SharedContent', $sWhereClause, $aOptions);
    }
    
    // ---------------
    // Getters/setters
    // ---------------
    /**
    * Retrieve a list of SharedContent objects
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $sWhereClause - string
    * @param $aOptions - array
    * @return SharedContent objects - array
    */
    public function getUsersObjects($iUserId = null, $aOptions = null) 
    {
        if (is_null($aOptions)) { $aOptions = array(); }
        $sWhereClause = "user_id = '$iUserId'";
        $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby','type');
        return KTEntityUtil::getList2('SharedContent', $sWhereClause, $aOptions);
    }
}
?>