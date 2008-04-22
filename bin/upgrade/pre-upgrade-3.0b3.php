<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
