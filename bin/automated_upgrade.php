<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once('../config/dmsDefaults.php');
require_once(KT_DIR . '/lib/upgrades/upgrade.inc.php');

if (!($default->dbAdminUser && $default->dbAdminPass)) {
    print "You need to set up the administrator user for your database.\n";
    print "Consult docs/UPGRADE.txt for more information\n";
    exit(1);
}

if (PEAR::isError($default->_admindb)) {
    print "Your database administrator user credentials can not login.\n";
    print "Consult docs/UPGRADE.txt for more information.\n";
    exit(1);
}

$query = sprintf('SELECT value FROM %s WHERE name = "knowledgeTreeVersion"', $default->system_settings_table);
$lastVersion = DBUtil::getOneResultKey($query, 'value');
$currentVersion = $default->systemVersion;

$action = $_SERVER['argv'][1];
if (empty($action)) {
    $action = 'show';
}

$upgrades = describeUpgrade($lastVersion, $currentVersion);

$i = 1;
foreach ($upgrades as $step) {
    print "Upgrade step $i: " . $step->getDescription();
    $bApplied = $step->isAlreadyApplied();
    $i++;
    if ($bApplied) {
        print " (already applied)\n";
        continue;
    }
    print "\n";
    if ($action == 'show') {
        continue;
    }
    $res = $step->performUpgrade();
    print "    RESULT: ";
    if ($res === true) {
        print "Success";
    }
    if (PEAR::isError($res)) {
        if (is_a($res, strtolower("Upgrade_Already_Applied"))) {
            print "Already applied";
        } else {
            print "ERROR\n";
            print $res->toString();
        }
    }
    print "\n";
}

?>
