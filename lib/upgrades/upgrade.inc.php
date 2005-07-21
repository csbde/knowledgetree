<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/**
 * $Id$
 *
 * Assists in discovering what needs to be done to upgrade one version
 * of KnowledgeTree to another.
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
 * @author Neil Blakey-Milner <nbm@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

require_once(KT_LIB_DIR . '/upgrades/UpgradeItems.inc.php');

function setupAdminDatabase() {
    global $default;
    $dsn = array(
        'phptype'  => $default->dbType,
        'username' => $default->dbAdminUser,
        'password' => $default->dbAdminPass,
        'hostspec' => $default->dbHost,
        'database' => $default->dbName,
    );

    $options = array(
        'debug'       => 2,
        'portability' => DB_PORTABILITY_ERRORS,
        'seqname_format' => 'zseq_%s',
    );

    $default->_admindb = &DB::connect($dsn, $options);
    if (PEAR::isError($default->_admindb)) {
        die($default->_admindb->toString());
    }
    $default->_admindb->setFetchMode(DB_FETCHMODE_ASSOC);
    return; 
}
setupAdminDatabase();

// {{{ Format of the descriptor
/**
 * Format of the descriptor
 *
 * type*version*phase*simple description for uniqueness
 *
 * type is: sql, function, subupgrade, upgrade
 * version is: 1.2.4, 2.0.0rc5
 * phase is: 0, 1, 0pre.  Phase is _only_ evaluated by describeUpgrades.
 * description is: anything, unique in terms of version and type.
 */
// }}}

// {{{ describeUpgrade
/**
 * Describe the upgrade path between two versions of KnowledgeTree.
 *
 * @param string Original version (e.g., "1.2.4")
 * @param string Current version (e.g., "2.0.2")
 *
 * @return array Array of UpgradeItem describing steps to be taken
 */
function &describeUpgrade ($origVersion, $currVersion) {
    // How to figure out what upgrades to do:
    //
    // 1. Get all SQL upgrades >= origVersion and <= currVersion
    // 2. Get all Function upgrades >= origVersion and <= currVersion
    // 3. Categorise each into version they upgrade to
    // 4. Sort each version subgroup into correct order
    // 5. Add "recordSubUpgrade" for each version there.
    // 6. Add back into one big list again
    // 7. Add "recordUpgrade" for whole thing

    // $recordUpgrade =  array('upgrade*' . $currVersion, 'Upgrade to ' .  $currVersion, null);
    
    $steps = array();
    foreach (array('SQLUpgradeItem', 'FunctionUpgradeItem') as $itemgen) {
        $f = array($itemgen, 'getUpgrades');
        $ssteps =& call_user_func($f, $origVersion, $currVersion);
        $scount = count($ssteps);
        for ($i = 0; $i < $scount; $i++) {
            $steps[] =& $ssteps[$i];
        }
    }
    $upgradestep =& new RecordUpgradeItem($currVersion, $origVersion);
    $steps[] =& $upgradestep;
    $stepcount = count($steps);
    for ($i = 0; $i < $stepcount; $i++) {
        $step =& $steps[$i];
        $step->setParent($upgradestep);
    }
    usort($steps, 'step_sort_func');

    return $steps;
}
// }}}

// {{{ step_sort_func
function step_sort_func ($obj1, $obj2) {
    // Ugly hack to ensure that upgrade table is made first...
    if ($obj1->name === "2.0.6/create_upgrade_table.sql") {
        return -1;
    }
    if ($obj2->name === "2.0.6/create_upgrade_table.sql") {
        return 1;
    }
    $res = compare_version($obj1->getVersion(), $obj2->getVersion());
    if ($res !== 0) {
        return $res;
    }
    if ($obj1->getPhase() > $obj2->getPhase()) {
        return 1;
    }
    if ($obj1->getPhase() < $obj2->getPhase()) {
        return -1;
    }
    return 0;
}
// }}}

// {{{ compare_version
/**
 * Compares two version numbers and returns a value based on this comparison
 *
 * Using standard software version rules, such as 2.0.5rc1 comes before
 * 2.0.5, and 2.0.5rc1 comes after 2.0.5alpha1, compare two version
 * numbers, and determine which is the higher.
 *
 * XXX: Actually, just does $version1 < $version2
 *
 * @param string First version number
 * @param string Second version number
 *
 * @return int -1, 0, 1
 */
function compare_version($version1, $version2) {
    // XXX: Version comparisons should be better.
    if ($version1 < $version2) {
        return -1;
    }
    if ($version1 > $version2) {
        return 1;
    }
    return 0;
}
// }}}

// {{{ lte_version
/**
 * Quick-hand for checking if a version number is lower-than-or-equal-to
 */
function lte_version($version1, $version2) {
    if (in_array(compare_version($version1, $version2), array(-1, 0))) {
            return true;
    } 
    return false;
}
// }}

// {{ gte_version
/**
 * Quick-hand for checking if a version number is greater-than-or-equal-to
 */
function gte_version($version1, $version2) {
    if (in_array(compare_version($version1, $version2), array(0, 1))) {
            return true;
    } 
    return false;
}
// }}}

?>
