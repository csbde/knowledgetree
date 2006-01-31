<?php

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
