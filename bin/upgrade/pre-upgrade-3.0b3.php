<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

$checkup = true;

require_once('../../config/dmsDefaults.php');

$s = array(
	"sql*2.99.5*0*2.99.5/dashlet_disabling.sql",
	"sql*2.99.5*0*2.99.5/role_allocations.sql",
	"sql*2.99.5*0*2.99.5/transaction_namespaces.sql",
	"sql*2.99.5*0*2.99.5/fieldset_field_descriptions.sql",
	"sql*2.99.5*0*2.99.5/role_changes.sql",
);

$sTable = KTUtil::getTableName('upgrades');

foreach ($s as $u) {
    var_dump($u);
    $f = array(
        'descriptor' => $u,
        'result' => true,
    );
    $res = DBUtil::autoInsert($sTable, $f);
    var_dump($res);
}
