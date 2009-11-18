<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 *
 */

$checkup = true;
error_reporting(E_ALL);
//require_once('../config/dmsDefaults.php');

function writablePath($name, $path) {
    $ret = sprintf('<tr><td>%s (%s)</td><td>', $name, $path);

    // Ensure the path is a full/absolute path
    $path = KTUtil::isAbsolutePath($path) ? $path : KT_DIR . $path;

    // Check if the directory exists and create it if it doesn't
    if(!file_exists($path)){
        mkdir($path, 0755);
    }

    // Check if directory is writable
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
  <?php
      echo writablePath('Log directory', $default->logDirectory);
      echo writablePath('Document directory', $default->documentRoot);
      echo writablePath('Webservice uploads directory', $default->uploadDirectory);
  ?>
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
