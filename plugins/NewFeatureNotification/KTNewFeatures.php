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

require_once(KT_LIB_DIR . '/security/Permission.inc');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

class KTNewFeatures {

	private $messageIds = array();
	protected $area_table = 'new_features_areas';
	protected $messages_table = 'new_features_messages';
	protected $users_table = 'new_features_users';

	public function getUsersNewFeatures()
	{
		global $default;

		$userID = $_SESSION['userID'];
		$section = $_SESSION['sSection'];
		$isAdmin = Permission::userIsSystemAdministrator($userID);
		$ktVersion = $default->systemVersion;
		$features = $this->getFeatures($userID, $section, $isAdmin, $ktVersion);
		$seenFeatures = $this->seenFeatures($features);
		$unseenFeatures = $this->unSeenFeatures($features, $seenFeatures);
		$this->saveSeenFeatures($unseenFeatures);

		return $unseenFeatures;
	}

	private function getFeatures($userID, $section, $isAdmin, $ktVersion)
	{
		$query = 'SELECT a.id as aid, a.name as aname, m.id as mid, m.message as mmessage, m.div as mdiv, m.area_id as marea_id, m.type as mtype, m.version as mversion FROM ' . $this->area_table . ' as a, ' . $this->messages_table . ' as m WHERE a.name = \'' .$section . '\' AND a.id = m.area_id AND (m.version = \'' . $ktVersion . '\' OR m.version = \'all\')';
		if($isAdmin)
		{
			$query .= ' AND (m.type=\'admin\' OR m.type = \'all\')';
		}
		else {
			$query .= ' AND (m.type=\'normal\' OR m.type = \'all\')';
		}

		return DBUtil::getResultArray($query);
	}

	private function seenFeatures($features)
	{
		if (empty($features)) {
			return array();
		}
		$userID = $_SESSION['userID'];
		$query = 'SELECT * FROM ' . $this->users_table . ' WHERE user_id = \'' . $userID .'\' AND message_id ';
		$i = 1;
		$numResults = count($features);
		$in = 'IN (';
		foreach ($features as $feature) {
			if($i == $numResults) {
				$in .= $feature['mid'];
			}
			else {
				$in .= $feature['mid'] . ',';
			}
			array_push($this->messageIds, $feature['mid']);
			$i++;
		}
		$in .= ')';
		$query .= $in;
		$results = DBUtil::getResultArray($query);

		return $results;
	}

	private function unSeenFeatures($features, $seenFeatures)
	{
		$unSeenFeatures = array();
		if(empty($seenFeatures)) {
			return $features;
		}
		else {
			foreach ($seenFeatures as $seenFeature) {
				if(!in_array($seenFeature['message_id'], $this->messageIds)) {
					$unSeenFeatures[] = $seenFeature;
				}
			}

			return $unSeenFeatures;
		}
	}

	private function saveSeenFeatures($seenFeatures)
	{
		if (empty($seenFeatures)) {
			return true;
		}
		$results = array();
		$addEntry = false;
		$userID = $_SESSION['userID'];
		$i = 1;
		$numResults = count($seenFeatures);
		$query = 'INSERT into ' . $this->users_table . ' (`user_id`, `message_id`) VALUES ';
		foreach ($seenFeatures as $seenFeature) {
			if(!$this->seenEntryExists($userID, $seenFeature['mid'])) {
				if($i == $numResults) {
					$query .= '(' . $userID . ', ' . $seenFeature['mid'] . ');';
				}
				else {
					$query .= '(' . $userID . ', ' . $seenFeature['mid'] . '),';
				}
				$addEntry = true;
			}
			$i++;
		}
		if($addEntry)
			DBUtil::runQuery($query);

		return true;
	}

	private function seenEntryExists($userID, $messageID)
	{
		$query = "SELECT * FROM {$this->users_table} WHERE user_id = '$userID' AND message_id = '$messageID'";
		$results = DBUtil::getResultArray($query);

		return (count($results) > 0);
	}
}