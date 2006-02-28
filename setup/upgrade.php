<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
            return "Already applied";
        }
        return $res->toString();
    }
    if ($res === true) {
        return "Success";
    }
    if ($res === false) {
        return "Failure";
    }
    return $res;
}

function performAllUpgrades () {
    global $default;
    $query = sprintf('SELECT value FROM %s WHERE name = "knowledgeTreeVersion"', $default->system_settings_table);
    $lastVersion = DBUtil::getOneResultKey($query, 'value');
    $currentVersion = $default->systemVersion;

    $upgrades = describeUpgrade($lastVersion, $currentVersion);

    print "<table width=\"100%\">\n";
    print "<tr><th>Description</th><th>Result</th></tr>\n";
    foreach ($upgrades as $upgrade) {
        $res = $upgrade->performUpgrade();
         printf("<tr><td>%s</td><td>%s</td></tr>\n",
            htmlspecialchars($upgrade->getDescription()),
            htmlspecialchars(showResult($res)));
    }
    print '</table>';
    return $ret;
}

if ($_REQUEST["go"] === "Upgrade") {
    $performingUpgrade = true;
} else {
    $upgradeTable = generateUpgradeTable();
}
?>
<html>
  <head>
    <title>KnowledgeTree Checkup</title>
    <style>
th { text-align: left; }
td { vertical-align: top; }
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
    } else {
        print "
        <p>The table below describes the upgrades that have occurred to
        upgrade your KnowledgeTree installation to <strong>$default->systemVersion</strong>.
        ";

        $upgradeTable = performAllUpgrades();
    }

    print $upgradeTable;

    if (!$performingUpgrade) {
        print '<form><input type="submit" name="go" value="Upgrade" /></form>';
    } else {
        print '<form><input type="submit" name="go" value="ShowUpgrades" /></form>';
    }
?>
  </body>
</html>
