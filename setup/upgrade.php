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

$GLOBALS["checkup"] = true;

require_once('../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/upgrades/upgrade.inc.php');

function generateUpgradeTable () {
    global $default;
    $query = sprintf('SELECT value FROM %s WHERE name = "knowledgeTreeVersion"', $default->system_settings_table);
    $lastVersion = DBUtil::getOneResultKey($query, 'value');
    $currentVersion = $default->systemVersion;

    $upgrades = describeUpgrade($lastVersion, $currentVersion);

    $ret = "<table>\n";
    $ret .= "<tr><th>Code</th><th>Description</th><th>Applied</th></tr>\n";
    foreach ($upgrades as $upgrade) {
        $ret .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>\n",
            htmlspecialchars($upgrade->getDescriptor()),
            htmlspecialchars($upgrade->getDescription()),
            $upgrade->isAlreadyApplied() ? "Yes" : "No"
            );
    }
    $ret .= '</table>';
    return $ret;
}

function showResult($res) {
    if (PEAR::isError($res)) {
        if (is_a($res, 'Upgrade_Already_Applied')) {
            return '<span style="color: orange">Already applied</span>';
        }
        return sprintf('<span style="color: red">%s</span>', htmlspecialchars($res->toString()));
    }
    if ($res === true) {
        return '<span style="color: green">Success</span>';
    }
    if ($res === false) {
        return '<span style="color: red">Failure</span>';
    }
    return $res;
}

$GLOBALS['row'] = 1;

function performAllUpgrades () {
    global $default;
    $query = sprintf('SELECT value FROM %s WHERE name = "knowledgeTreeVersion"', $default->system_settings_table);
    $lastVersion = DBUtil::getOneResultKey($query, 'value');
    $currentVersion = $default->systemVersion;

    $upgrades = describeUpgrade($lastVersion, $currentVersion);

    foreach ($upgrades as $upgrade) {
        if (($GLOBALS['row'] % 2) == 1) {
            $class = "odd";
        } else {
            $class = "even";
        }
        printf('<div class="row %s"><div class="foo">%s</div>' . "\n", $class, htmlspecialchars($upgrade->getDescription()));
        $GLOBALS['row']++;
        ob_flush();
        flush();
        $res = $upgrade->performUpgrade();
        printf('<div class="bar">%s</div>', showResult($res));
        print '<br style="clear: both">' . "\n";
        ob_flush();
        flush();
        print "</div>\n";
        if (PEAR::isError($res)) {
            if (!is_a($res, 'Upgrade_Already_Applied')) {
                break;
            } else {
                $res = true;
            }
        }
        if ($res === false) {
            $res = PEAR::raiseError("Upgrade returned false");
            break;
        }
    }
    return $res;
}

function failWritablePath($name, $path) {
    if (!is_writable($path)) {
        sprintf("The path for setting %s, which is set to %s, can not be written to.  Correct this situation before continuing.", $name, $path);
        exit(1);
    }
}

failWritablePath('Log directory', $default->logDirectory);
failWritablePath('Document directory', $default->documentRoot);

if (PEAR::isError($loggingSupport)) {
    print '<p><font color="red">Logging support is not currently working.  Check post-installation checkup.</font></p>';
    exit(1);
}

if (PEAR::isError($dbSupport)) {
    print '<p><font color="red">Database support is not currently working.  Check post-installation checkup.</font></p>';
    exit(1);
}

if ($_REQUEST["go"] === "Upgrade") {
    $performingUpgrade = true;
} else {
    $upgradeTable = generateUpgradeTable();
}
?>
<html>
  <head>
    <title>KnowledgeTree Upgrade</title>
    <style>
th { text-align: left; }
td { vertical-align: top; }
.foo { float: left; }
.bar { padding-left: 2em; float: right; }
.odd { background-color: #eeeeee; }
.even { background-color: #dddddd; }
.row { padding: 0.5em 1em; }
    </style>
  </head>

  <body>
    <h1>KnowledgeTree Upgrades</h1>

<?php

    if (!$performingUpgrade) {
        print "
        <p>The table below describes the upgrades that need to occur to
        upgrade your KnowledgeTree installation to <strong>$default->systemVersion</strong>.
        Click on the button below the table to perform the upgrades.</p>
        ";
        $upgradeTable = generateUpgradeTable();
        print $upgradeTable;
        print '<form><input type="submit" name="go" value="Upgrade" /></form>';
        print '</body></html>';

    } else {
        print "
        <p>The table below describes the upgrades that have occurred to
        upgrade your KnowledgeTree installation to <strong>$default->systemVersion</strong>.
        ";

        $res = performAllUpgrades();
        if (PEAR::isError($res)) {
            print '<font color="red">Upgrade failed.</font>' . "\n";
        } else {
            $sUrl = generateLink("");
            print sprintf('<font color="green">Upgrade succeeded.  Now
            try <a href="%s">log in and use the system</a>.</font>',
            $sUrl) . "\n";
        }
        print '</body></html>';
    }
?>
