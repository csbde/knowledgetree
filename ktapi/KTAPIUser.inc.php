<?php

/**
 *
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
 */

/**
 * Encapsulates functionality around a user.
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.9
 */
class KTAPI_User extends KTAPI_Member
{
    /**
     * Reference to the original User object.
     *
     * @access private
     * @var User
     */
    private $user;

    /**
     * Constructor for KTAPI_User. This is private, and can only be constructed by the static getByXXX() functions.
     *
     * @access private
     * @param User $user
     */
    private function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Using the id, the user can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param int $id
     * @return KTAPI_User Returns null if  there is no match.
     */
    public static function getById($id)
    {
        $user = User::get($id);

        if (PEAR::isError($user))
        {
            return $user;
        }

        return new KTAPI_User($user);
    }


    /**
     * Using the full name, the user can be resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $name
     * @return KTAPI_User Returns null if  there is no match.
     */
    public static function getByName($name)
    {
        $sql = 'SELECT username FROM users where name=?';
        $username = DBUtil::getOneResultKey(array($sql, array($name)), 'username');

        if (PEAR::isError($username))
        {
            return $username;
        }

        return self::getByUsername($username);
    }

    /**
     * Using the username, the user is resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $username
     * @return KTAPI_User  Returns null if  there is no match.
     */
    public static function getByUsername($username)
    {
        $user = User::getByUserName($username);

        if (PEAR::isError($user))
        {
            return $user;
        }

        return new KTAPI_User($user);
    }

    /**
     * Using the email, the user is resolved.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $email
     * @return KTAPI_User  Returns null if  there is no match.
     */
    public static function getByEmail($email)
    {
        $sql = 'SELECT username FROM users where email=?';
        $username = DBUtil::getOneResultKey(array($sql, $email), 'username');

        if (PEAR::isError($username))
        {
            return $username;
        }

        return self::getByUsername($username);
    }

    /**
     * Returns a list of users matching the filter criteria.
     *
     * @author KnowledgeTree Team
     * @access public
     * @static
     * @param string $filter
     * @param array $options
     * @return array of KTAPI_User
     */
    public static function getList($filter = null, $options = null)
    {
        $users = User::getList($filter, $options);

        if (PEAR::isError($users))
        {
            return $users;
        }

        $list = array();
        foreach($users as $user)
        {
            $list[] = new KTAPI_User($user);
        }

        return $list;
    }
    
    /**
     * Returns the most recent document owned by a user
     *
     * @author KnowledgeTree Team
     * @access public
     * @param int $limit
     * @return array of KTAPI_Document
     */
	public function mostRecentDocumentsOwned($limit = 10)
    {
    	$userID = $this->user->getId();
    	$GLOBALS['default']->log->debug("KTAPI_User mostRecentDocumentsOwned $userID, $limit");
    	
    	//only documents that user has permissions on
    	$res = KTSearchUtil::permissionToSQL($this->user, "ktcore.permissions.read");
        if (PEAR::isError($res)) {
            return $res;
        }

        list($sPermissionString, $aPermissionParams, $sPermissionJoin) = $res;

        // Create the "where" criteria
        $sWhere = "WHERE owner_id = {$userID} AND status_id = 1 AND {$sPermissionString}";
        $sOrderBy = "ORDER BY modified DESC LIMIT {$limit}";
        
    	$sql = "SELECT D.id as document_id FROM documents AS D $sPermissionJoin $sWhere $sOrderBy";
    	
    	$rows = DBUtil::getResultArrayKey(array($sql, $aPermissionParams), 'document_id');
		if (is_null($rows) || PEAR::isError($rows))
		{
			$results = new KTAPI_Error(KTAPI_ERROR_INTERNAL_ERROR, $rows);
		}
		else
		{
			$results = array();
			foreach($rows as $row)
			{
				$document = Document::get($row['document_id']);
				
				$results[] = $document;
			}
		}
		
		return $results;
    }

    /**
     * Return id property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return integer
     */
    public function getId() { return $this->user->getId(); }

    /**
     * Return username property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getUsername() { return $this->user->getUserName(); }

    /**
     * Return username property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getPassword() { return $this->user->getPassword(); }

    /**
     * Return display name property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getName() { return $this->user->getName(); }

    /**
     * Return email property. (readonly)
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getEmail() { return $this->user->getEmail(); }

}

?>