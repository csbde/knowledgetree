<?php
/*
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009, 2010 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . '/security/permissions/Permission.inc');

class KTNewFeatures {

	protected $area_table = 'new_features_area';
	protected $messages_table = 'new_features_messages';
	protected $users_table = 'new_features_users';

	static public function getUsersNewFeatures()
	{
		$user_id = $_SESSION['userID'];
		$section = $_SESSION['sSection'];
		$isAdmin = Permission::userIsSystemAdministrator($user_id);
		//$query = 'SELECT m.id, m.message, m.div, m.area, m.type, m.enabled FROM ' . self::messages_table . ' WHERE ';
		$query = 'SELECT * FROM ' . self::area_table . 'as a, ' . self::messages_table . ' as m WHERE a.name = \'' .$section . '\' AND a.id = m.area_id';
		if($isAdmin)
		{

		}
		else {

		}
		$results = DBUtil::getResultArray($query);

	}
}