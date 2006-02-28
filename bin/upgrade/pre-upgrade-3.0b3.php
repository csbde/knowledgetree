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
