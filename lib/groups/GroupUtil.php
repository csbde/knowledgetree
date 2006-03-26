<?php
/**
 * $Id$
 *
 * Utility functions regarding groups and membership
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . "/groups/Group.inc");

// {{{ GroupUtil
class GroupUtil {
    // {{{ filterCyclicalGroups
    /**
     * This utility function takes a group whose membership is being
     * considered, and a dictionary with group ids as keys and a list of
     * their member groups as values.
     *
     * $aGroupMembership = array(
     *      1 => array(2, 3, 4),
     *      2 => array(5, 3),
     *      3 => array(5),
     *  }
     *
     * This function returns a list of group ids from the group
     * membership array that may safely to added to the original group.
     */
    // STATIC
    function filterCyclicalGroups ($iTargetGroupID, $aGroupMemberships) {
        $aReturnGroupIDs = array();

        // PHP5: clone/copy
        $aLocalGroupMemberships = $aGroupMemberships;

        // In case we get given ourself, we know we can't add ourselves
        // to each other.
        unset($aLocalGroupMemberships[$iTargetGroupID]);

        // Groups that have no group members can safely be added to the
        // group.  Simplifies debugging of later code.
        foreach ($aLocalGroupMemberships as $k => $v) {
            if (is_null($v) || (!count($v))) {
                unset($aLocalGroupMemberships[$k]);
                $aReturnGroupIDs[] = $k;
            }
        }

        $aBadGroupIDs = GroupUtil::listBadGroups($iTargetGroupID, $aLocalGroupMemberships);

        foreach ($aLocalGroupMemberships as $k => $v) {
            if (!in_array($k, $aBadGroupIDs)) {
                $aReturnGroupIDs[] = $k;
            }
        }

        return $aReturnGroupIDs;
    }
    // }}}

    // {{{
    /**
     * This utility function takes a group whose membership is being
     * considered, and a dictionary with group ids as keys and a list of
     * their member groups as values.
     *
     * $aGroupMembership = array(
     *      1 => array(2, 3, 4),
     *      2 => array(5, 3),
     *      3 => array(5),
     *  }
     *
     * This function returns a list of group ids from the group
     * membership array that can't be safely to added to the original
     * group.
     */
    // STATIC
    function listBadGroups ($iTargetGroupID, $aGroupMemberships) {
        // PHP5: clone/copy
        $aLocalGroupMemberships = $aGroupMemberships;

        // Two ways to do this - either expand the list we're given of
        // immediate children to all children, OR mark group IDs as bad
        // (starting with the group we're planning to add the groups
        // into), and cycle while we're finding new bad groups.
        //
        // Marking bad group IDs seems like the easier-to-understand
        // option.

        $aBadGroupIDs = array($iTargetGroupID);
        $aLastBadGroupCount = 0;

        // While we've discovered new bad groups...
        while (count($aBadGroupIDs) > $aLastBadGroupCount) {
            $aLastBadGroupCount = count($aBadGroupIDs);
            foreach ($aLocalGroupMemberships as $iThisGroupID => $aGroupIDs) {

                // This check isn't strictly necessary, as the groups
                // should be removed from the local list of groups in
                // the later check, but who knows whether one can unset
                // array keys while iterating over the list.

                if (in_array($iThisGroupID, $aBadGroupIDs)) {
                    // print "Not considering $iThisGroupID, it is in bad group list: " . print_r($aBadGroupIDs, true);
                    unset($aLocalGroupMemberships[$iThisGroupID]);
                    continue;
                }

                foreach ($aGroupIDs as $k) {
                    if (in_array($k, $aBadGroupIDs)) {
                        // print "Adding $iThisGroupID to bad list, because it contains $k, which is in bad group list: " .  print_r($aBadGroupIDs, true);
                        unset($aLocalGroupMemberships[$iThisGroupID]);
                        $aBadGroupIDs[] = $iThisGroupID;
                        break;
                    }
                }
            }
        }
        return $aBadGroupIDs;
    }
    // }}}

    // {{{ addGroup
    function addGroup($aGroupDetails) {
        $aDefaultDetails = array(
            "is_unit_admin" => false,
            "is_system_admin" => false,
        );
        $aDetails = array_merge($aDefaultDetails, $aGroupDetails);
        if (is_null(KTUtil::arrayGet($aDetails, "name"))) {
            return PEAR::raiseError("Needed key name is not provided");
        }
        $oGroup = new Group($aDetails["name"],
                $aDetails["is_unit_admin"],
                $aDetails["is_system_admin"]);
        $ret = $oGroup->create();
        if ($ret === false) {
            return PEAR::raiseError("Legacy error creating group, may be: " . $_SESSION["errorMessage"]);
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if ($ret !== true) {
            return PEAR::raiseError("Non-true and non-error return value");
        }
        return $oGroup;
    }
    // }}}

    // {{{ list
    function listGroups($aGivenOptions = null) {
        if (is_null($aGivenOptions)) {
            $aGivenOptions = array();
        }
        $aDefaultOptions = array(
            //"active" => true,
        );
        $aOptions = array_merge($aDefaultOptions, $aGivenOptions);

        $aWhere = array();
        /* if ($aOptions["active"] === true) {
            $aWhere[] = array("active = ?", true);
        } */

        $sWhere = KTUtil::whereToString($aWhere);

        return Group::getList($sWhere);
    }
    // }}}

    // {{{
    function getNameForID($id) {
        global $default;
        $sName = lookupField($default->groups_table, "name", "id", $id);
        return $sName;
    }
    // }}}

    // {{{ listGroupsForUser
    function listGroupsForUser ($oUser, $aOptions = null) {
        global $default;
        $iUserId = KTUtil::getId($oUser);
        $ids = KTUtil::arrayGet($aOptions, 'ids', false);
        $sQuery = "SELECT group_id FROM $default->users_groups_table WHERE user_id = ?";
        $aParams = array($iUserId);
        $aGroupIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), "group_id");
        $aGroups = array();
        foreach ($aGroupIDs as $iGroupID) {
            if ($ids) {
                $aGroups[] = $iGroupID;
                continue;
            }
            $oGroup = Group::get($iGroupID);
            if (PEAR::isError($oGroup)) {
                continue;
            }
            if ($oGroup === false) {
                continue;
            }
            $aGroups[] = $oGroup;
        }
        return $aGroups;
    }
    // }}}

    function _invertGroupArray($aGroupArray) {
        $aRet = array();
        foreach ($aGroupArray as $k => $aArray) {
            foreach ($aArray as $v) {
                $aRet[$v] = KTUtil::arrayGet($aRet, $v, array());
                $aRet[$v][] = $k;
            }
        }
        return $aRet;
    }

    // {{{ _listGroupsIDsForUserExpand
    function _listGroupIDsForUserExpand ($oUser) {
        $iUserId = KTUtil::getId($oUser);
        global $default;
        $oCache = KTCache::getSingleton();
        $group = "groupidsforuser";
        list($bCached, $mCached) = $oCache->get($group, $oUser->getId());
        if ($bCached) {
            $default->log->debug(sprintf("Using group cache for _listGroupIDsForUserExpand %d", $iUserId));
            return $mCached;
        }
        $aGroupArray = GroupUtil::_invertGroupArray(GroupUtil::buildGroupArray());
        $aDirectGroups = GroupUtil::listGroupsForUser($oUser);
        $sQuery = "SELECT group_id FROM $default->users_groups_table WHERE user_id = ?";
        $aParams = array($iUserId);
        $aGroupIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), "group_id");
        foreach ($aGroupIDs as $iGroupID) {
            $aExtraIDs = KTUtil::arrayGet($aGroupArray, $iGroupID);
            if (is_array($aExtraIDs)) {
                $aGroupIDs = array_merge($aGroupIDs, $aExtraIDs);
            }
        }
        $aGroupIDs = array_unique($aGroupIDs);
        sort($aGroupIDs);
        $oCache->set($group, $oUser->getId(), $aGroupIDs);
        return $aGroupIDs;
    }
    // }}}

    // {{{ listGroupsForUserExpand
    function listGroupsForUserExpand ($oUser, $aOptions = null) {
        $ids = KTUtil::arrayGet($aOptions, 'ids', false);
        $aGroupIDs = GroupUtil::_listGroupIDsForUserExpand($oUser);
        $aGroups = array();
        foreach ($aGroupIDs as $iGroupID) {
            if ($ids) {
                $aGroups[] = $iGroupID;
            }
            $oGroup = Group::get($iGroupID);
            if (PEAR::isError($oGroup)) {
                continue;
            }
            if ($oGroup === false) {
                continue;
            }
            $aGroups[] = $oGroup;
        }
        return $aGroups;
    }
    // }}}

    // {{{
    function buildGroupArray() {
        global $default;
        $aDirectGroups = array();
        $aGroupMemberships = DBUtil::getResultArray("SELECT parent_group_id, member_group_id FROM $default->groups_groups_table");
        $aGroups =& Group::getList();
        foreach ($aGroups as $oGroup) {
            $aDirectGroups[$oGroup->getID()] = array();
        }
        foreach ($aGroupMemberships as $aRow) {
            $aList = KTUtil::arrayGet($aDirectGroups, $aRow['parent_group_id'], array());
            $aList[] = $aRow['member_group_id'];
            $aDirectGroups[$aRow['parent_group_id']] = $aList;
        }

        return GroupUtil::expandGroupArray($aDirectGroups);
    }
    // }}}

    // {{{ expandGroupArray
    function expandGroupArray($aDirectGroups) {
        // XXX: PHP5 clone
        $aExpandedGroups = $aDirectGroups;
        $iNum = 0;
        foreach ($aExpandedGroups as $k => $v) {
            $iNum += count($v);
        }
        $iLastNum = 0;
        while ($iNum !== $iLastNum) {
            $iLastNum = $iNum;

            foreach ($aExpandedGroups as $k => $v) {
                foreach ($v as $iGroupID) {
                    $aStuff = KTUtil::arrayGet($aExpandedGroups, $iGroupID, null);
                    if (is_null($aStuff)) {
                        continue;
                    }
                    $v = array_unique(array_merge($v, $aStuff));
                    sort($v);
                }
                $aExpandedGroups[$k] = $v;
            }
            
            $iNum = 0;
            foreach ($aExpandedGroups as $k => $v) {
                $iNum += count($v);
            }
        }
        return $aExpandedGroups;
    }
    // }}}
    
    // {{{ getMembershipReason
    function getMembershipReason($oUser, $oGroup) {
        $aGroupArray = GroupUtil::buildGroupArray();
        
        // short circuit
        
        if ($oGroup->hasMember($oUser)) { return sprintf(_kt('%s is a direct member.'), $oUser->getName()); }
        
        
        $aSubgroups = (array) $aGroupArray[$oGroup->getId()];
        if (empty($aSubgroups)) { 
            return null; // not a member, no subgroups. 
        }
        
        $sTable = KTUtil::getTableName('users_groups');
        $sQuery = 'SELECT group_id FROM ' . $sTable . ' WHERE user_id = ? AND group_id IN (' . DBUtil::paramArray($aSubgroups) . ')';
        $aParams = array($oUser->getId());
        $aParams = array_merge($aParams, $aSubgroups);

        $res = DBUtil::getOneResult(array($sQuery, $aParams));
        if (PEAR::isError($res)) {
            return $res;
        } else if (is_null($res)) {
            return null; // not a member
        } // else {
        
        $oSubgroup = Group::get($res['group_id']);
        if (PEAR::isError($oSubgroup)) { return $oSubgroup; }
        
        return sprintf(_kt('%s is a member of %s'), $oUser->getName(), $oSubgroup->getName()); // could be error, but errors are caught.
        
        // }
    }
    // }}}
}
// }}}

?>
