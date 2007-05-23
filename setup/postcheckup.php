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

$checkup = true;
error_reporting(E_ALL);
require_once('../config/dmsDefaults.php');

function writablePath($name, $path) {
    $ret = sprintf('<tr><td>%s (%s)</td><td>', $name, $path);
    if (is_writable($path)) {
        $ret .= sprintf('<font color="green"><b>Writeable</b></font>');
    } else {
        $ret .= sprintf('<font color="red"><b>Unwriteable</b></font>');
    }
    return $ret;
}

?>
<html>
  <head>
    <title><?php echo APP_NAME;?> Post-Configuration Checkup</title>
    <style>
th { text-align: left; }
    </style>
  </head>

  <body>

<h1><?php echo APP_NAME;?> post-configuration checkup</h1>

<p>This allows you to check that your <?php echo APP_NAME;?> configuration is set
up correctly.  You can run this at any time after configuration to check
that things are still set up correctly.</p>

<h2>Filesystem</h2>

<table width="50%">
  <tbody>
  <?php echo writablePath('Log directory', $default->logDirectory)?>
  <?php echo writablePath('Document directory', $default->documentRoot)?>
  </tbody>
</table>

<?php

if (substr($default->documentRoot, 0, strlen(KT_DIR)) == KT_DIR) {
    print '<p><strong><font color="orange">Your document directory is
    set to the default, which is inside the web root.  This may present
    a security problem if your documents can be accessed from the web,
    working around the permission system in
    '.APP_NAME.'.</font></strong></p>';
}

?>

<h2>Logging</h2>

<?php
if (PEAR::isError($loggingSupport)) {
    print '<p><font color="red">Logging support is not currently working.  Error is: ' .
        htmlentities($loggingSupport->toString()) . '</font></p>';
} else {
?>
<p>Logging support is operational.</p>
<?php
}
?>

<h2>Database connectivity</h2>

<?php
if (PEAR::isError($dbSupport)) {
    print '<p><font color="red">Database support is not currently working.  Error is: ' .
        htmlentities($dbSupport->toString()) . '</font></p>';
} else {
?>
<p>Database connectivity successful.</p>

<h3>Privileges</h3>
<?php
$selectPriv = DBUtil::runQuery('SELECT COUNT(id) FROM ' .  $default->documents_table);
if (PEAR::isError($selectPriv)) {
    print '<p><font color="red">Unable to do a basic database query.
    Error is: ' . htmlentities($selectPriv->toString()) .
    '</font></p>';
} else {
    print '<p>Basic database query successful.</p>';
}

$sTable = KTUtil::getTableName('system_settings');
DBUtil::startTransaction();
$res = DBUtil::autoInsert($sTable, array(
    'name' => 'transactionTest',
    'value' => 1,
));
DBUtil::rollback();
$res = DBUtil::getOneResultKey("SELECT id FROM $sTable WHERE name = 'transactionTest'", 'id');
if (!empty($res)) {
    print '<p><font color="red">Transaction support not available in database</font></p>';
} else {
    print '<p>Database has transaction support.</p>';
}
DBUtil::whereDelete($sTable, array('name' => 'transactionTest'));
?>

<?php
}
?>

  </body>
</html>
