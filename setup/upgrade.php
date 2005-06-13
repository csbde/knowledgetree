<?php

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
        return $res->toString();
    }
    return $res;
}

function performAllUpgrades () {
    global $default;
    $query = sprintf('SELECT value FROM %s WHERE name = "knowledgeTreeVersion"', $default->system_settings_table);
    $lastVersion = DBUtil::getOneResultKey($query, 'value');
    $currentVersion = $default->systemVersion;

    $upgrades = performUpgrade($lastVersion, $currentVersion);

    $ret = "<table>\n";
    $ret .= "<tr><th>Code</th><th>Description<th></tr>\n";
    foreach ($upgrades as $upgrade) {
        $ret .= sprintf('<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
            htmlspecialchars($upgrade->getDescriptor()),
            htmlspecialchars($upgrade->getDescription()),
            htmlspecialchars(showResult($upgrade->getResult())));
    }
    $ret .= '</table>';
    return $ret;
}

$upgradeTable = generateUpgradeTable();
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

if ($upgradeTable) {
    print "
    <p>The table below describes the upgrades that need to occur to
    upgrade your KnowledgeTree installation to <strong>$default->systemVersion</strong>.
    Click on the button below the table to perform the upgrades.</p>
    ";

    print $upgradeTable;

    print '<form><input type="submit" name="go" value="Upgrade" /></form>';
} else {
    
}
?>
  </body>
</html>
