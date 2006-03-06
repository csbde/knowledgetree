<?php
/**
 * $Id$
 *
 * Represents a Group as per the database table groups.
 *
 * Copyright (c) 2006, 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.groups 
 */
 
require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc'); 
 
class Group extends KTEntity {
	
	/** primary key of current object */
	var $iId;
	/** group name */
	var $sName;
	/** is the group a unit admin */
	var $bIsUnitAdmin;
	/** is the group a sys admin */
	var $bIsSysAdmin;
    /** which unit the group belongs to */
	var $iUnitId;
    var $sAuthenticationDetails = null;
    var $sAuthenticationDetails2 = null;
    var $iAuthenticationSourceId = null;
	
	function Group($sNewName = null, $bNewIsUnitAdmin = false, $bNewIsSysAdmin = false) {
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->bIsUnitAdmin = $bNewIsUnitAdmin;
		$this->bIsSysAdmin = $bNewIsSysAdmin;
	}

	var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
        'bIsUnitAdmin' => 'is_unit_admin',
        'bIsSysAdmin' => 'is_sys_admin',
        'iUnitId' => 'unit_id',
        'sAuthenticationDetails' => 'authentication_details_s1',
        'sAuthenticationDetails2' => 'authentication_details_s2',
        'iAuthenticationSourceId' => 'authentication_source_id',
    );

    function _table () {
        global $default;
        return $default->groups_table;
    }
    // }}}

    // {{{ getters/setters
    // ---------------
    // Getters/setters
    // ---------------

    function getUnitAdmin() { return $this->bIsUnitAdmin; }
    function setUnitAdmin($bNewValue) { $this->bIsUnitAdmin = $bNewValue; }
    function getSysAdmin() { return $this->bIsSysAdmin; }
    function setSysAdmin($bNewValue) { $this->bIsSysAdmin = $bNewValue; }
    function getName() { return $this->sName; }
    function setName($sNewValue) { $this->sName = $sNewValue; }
    function getUnitId() { return $this->iUnitId; }
    function setUnitId($iNewValue) { $this->iUnitId = $iNewValue; }
    function getAuthenticationDetails() { return $this->sAuthenticationDetails; }
    function setAuthenticationDetails($mValue) { $this->sAuthenticationDetails = $mValue; }
    function getAuthenticationDetails2() { return $this->sAuthenticationDetails2; }
    function setAuthenticationDetails2($mValue) { $this->sAuthenticationDetails2 = $mValue; }
    function getAuthenticationSourceId() { return $this->iAuthenticationSourceId; }
    function setAuthenticationSourceId($mValue) { $this->iAuthenticationSourceId = $mValue; }
    // }}}

		
	/**
	 * Checks if this group has users mapped to it or not
	 */
	function hasUsers() {
		global $default;
		$sql = $default->db;
		$sql->query(array("SELECT id FROM $default->users_groups_table WHERE group_id = ?", $this->iId));/*ok*/
       	$rows = $sql->num_rows();
        if ($rows > 0) {
        	return true;
        } else {
        	return false;
        }
	}
	
	/**
	* Static function.
	* Given a groups primary key it will create a 
	* Group object and populate it with the 
	* corresponding database values
	*
	* @return Group populated Group object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iId) {
        return KTEntityUtil::get('Group', $iId);
	}

   /**
	* Static function
	* Get a list of web documents
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of Group objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null, $aOptions = null) {
	    if(!is_array($aOptions)) $aOptions = array($aOptions);
		$aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby', 'name');
        return KTEntityUtil::getList2('Group', $sWhereClause, $aOptions);
	}
	
	/**
	 * Returns an array of Users in this group.
	 */
	function &getUsers() {
	    // legacy api.
		return $this->getMembers();		
	}
	
    function &getMembers() {
        global $default;
        require_once(KT_LIB_DIR . '/users/User.inc');
        $sQuery = "SELECT user_id FROM $default->users_groups_table WHERE group_id = ?";
        $aParams = array($this->getId());
        $aUserIds = DBUtil::getResultArrayKey(array($sQuery, $aParams), "user_id");
        $aMembers = array();
        foreach ($aUserIds as $iUserId) {
            $oUser = User::get($iUserId);
            if ((!PEAR::isError($oUser)) && ($oUser !== false)) {
                $aMembers[] = $oUser;
            }
        }
        return $aMembers;
    }

    function &getMemberGroups() {
        global $default;
        $sQuery = "SELECT member_group_id FROM $default->groups_groups_table WHERE parent_group_id = ?";
        $aParams = array($this->getId());
        $aGroupIds = DBUtil::getResultArrayKey(array($sQuery, $aParams), "member_group_id");
        $aMembers = array();
        foreach ($aGroupIds as $iGroupId) {
            $oGroup = Group::get($iGroupId);
            if ((!PEAR::isError($oUser)) && ($oGroup !== false)) {
                $aMembers[] = $oGroup;
            }
        }
        return $aMembers;
    }


    function delete() {
        global $default;

        $sQuery = "DELETE FROM $default->users_groups_table WHERE group_id = ?";
        $aParams = array($this->getId());
        DBUtil::runQuery(array($sQuery, $aParams));

        $sQuery = "DELETE FROM $default->groups_units_table WHERE group_id = ?";
        $aParams = array($this->getId());
        DBUtil::runQuery(array($sQuery, $aParams));

        return parent::delete();
    }

    // {{{ hasMember
    function hasMember($oUser) {
        global $default;
        $iUserId = KTUtil::getId($oUser);

        $sQuery = "SELECT COUNT(*) AS number_of_entries FROM $default->users_groups_table
            WHERE group_id = ? AND user_id = ?";
        $aParams = array($this->getId(), $iUserId);
        $res = (int)DBUtil::getOneResultKey(array($sQuery, $aParams), "number_of_entries");
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($res === 1) {
            return true;
        }
        return false;
    }
    // }}}

    // {{{ setMembers
    function setMembers($aUsers) {
        $sTable = KTUtil::getTableName('users_groups');
        $aParams = array(
            "group_id" => $this->getId(),
        );
        $res = DBUtil::whereDelete($sTable, $aParams);
        foreach ($aUsers as $iUserId) {
            $iUserId = KTUtil::getId($iUserId);
            $this->addMember($iUserId);
        }
        return;
    }
    // }}}

    // {{{ addMember
    function addMember($oUser) {
        global $default;
        if ($this->hasMember($oUser)) {
            return true;
        }
        $aParams = array(
            "user_id" => KTUtil::getId($oUser),
            "group_id" => $this->getId(),
        );
        $res = DBUtil::autoInsert($default->users_groups_table, $aParams);
        if (PEAR::isError($res)) {
            return $res;
        }
        return true;
    }
    // }}}

    // {{{ removeMember
    function removeMember($oUser) {
        global $default;
        if (!$this->hasMember($oUser)) {
            return true;
        }
        $aParams = array(
            "user_id" => KTUtil::getId($oUser),
            "group_id" => $this->getId(),
        );
        $res = DBUtil::whereDelete($default->users_groups_table, $aParams);
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($this->hasMember($oUser)) {
            return PEAR::raiseError("Tried to remove member from database, apparently successfully, but hasMember thinks they're still members?");
        }
        return true;
    }
    // }}}

    // {{{ addMemberGroup
    function addMemberGroup($oGroup) {
        global $default;
        if ($this->hasMemberGroup($oGroup)) {
            return true;
        }
        $aParams = array(
            "parent_group_id" => $this->getId(),
            "member_group_id" => $oGroup->getId(),
        );
        $res = DBUtil::autoInsert($default->groups_groups_table, $aParams);
        if (PEAR::isError($res)) {
            return $res;
        }
        return true;
    }
    // }}}

    // {{{ removeMemberGroup
    function removeMemberGroup($oGroup) {
        global $default;
        if (!$this->hasMemberGroup($oGroup)) {
            return true;
        }
        $aParams = array(
            "parent_group_id" => $this->getId(),
            "member_group_id" => $oGroup->getId(),
        );
        $res = DBUtil::whereDelete($default->groups_groups_table, $aParams);
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($this->hasMember($oGroup)) {
            return PEAR::raiseError("Tried to remove member from database, apparently successfully, but hasMember thinks they're still members?");
        }
        return true;
    }
    // }}}

    // {{{ hasMemberGroup
    function hasMemberGroup($oGroup) {
        global $default;

        $sQuery = "SELECT COUNT(*) AS number_of_entries FROM $default->groups_groups_table
            WHERE parent_group_id = ? AND member_group_id = ?";
        $aParams = array($this->getId(), $oGroup->getId());
        $res = (int)DBUtil::getOneResultKey(array($sQuery, $aParams), "number_of_entries");
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($res === 1) {
            return true;
        }
        return false;
    }
    // }}}

    // getUnitAdministratorGroupsByUnit
    function getUnitAdministratorGroupsByUnit($oUnit) {
        $iUnitId = KTUtil::getId($oUnit);
        return KTEntityUtil::getByDict('Group', array(
            'is_unit_admin' => true,
            'unit_id' => $iUnitId,
        ), array(
            'multi' => 'true',
        ));
    }

    function getByAuthenticationSource($oSource, $aOptions = null) {
        $iSourceId = KTUtil::getId($oSource);
        $aOptions = KTUtil::meldOptions($aOptions, array(
            'multi' => true,
        ));
        return KTEntityUtil::getByDict('Group', array(
            'authentication_source_id' => $iSourceId,
        ), $aOptions);
    }

	function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('Group', $aOptions); }
}

/**
* Static function
*
* Creates a Group object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & groupCreateFromArray($aParameters) {
	$oGroup = & new Group($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oGroup;
}

?>
