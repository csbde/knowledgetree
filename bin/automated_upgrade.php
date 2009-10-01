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
    print '    RESULT: ';
    if ($res === true) {
        print 'Success';
    }
    if (PEAR::isError($res)) {
        if (is_a($res, strtolower('Upgrade_Already_Applied'))) {
            print 'Already applied';
        } else {
            print "ERROR\n";
            print $res->toString();
        }
    }
    print "\n";
}

?>
