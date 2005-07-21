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
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
    function add($aGroupDetails) {
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
        return true;
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

}
// }}}

?>
