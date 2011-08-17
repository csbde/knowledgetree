<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

require_once('NewFeatureCache.php');
require_once(KT_LIB_DIR . '/security/Permission.inc');
require_once(KT_LIB_DIR . '/database/dbutil.inc');

class NewFeatures {

	private $messageIds = array();
	protected $area_table = 'new_features_areas';
	protected $messages_table = 'new_features_messages';
	protected $users_table = 'new_features_users';

	public function getUsersNewFeatures($location)
	{
		global $default;
		$unseenFeatures = array();
		$userId = $_SESSION['userID'];

		NewFeatureCache::init();

		$section = $this->determinePageLocation($location);
		$isAdmin = Permission::userIsSystemAdministrator($userId);
		$ktVersion = $default->systemVersion;
		$cachedVersion = NewFeatureCache::getCachedVersion($userId);

		if($cachedVersion == $ktVersion) {
			$features = NewFeatureCache::getCached($userId, 'all', $section);
			$seenFeatures = NewFeatureCache::getCached($userId, 'seen', $section);
			$unseenFeatures = $this->unSeenFeatures($features, $seenFeatures);
			$updatedSeen = array_merge($seenFeatures, $unseenFeatures);
			NewFeatureCache::saveToCache($updatedSeen, $userId, 'seen', $section);
		}
		else {
			// Get new features for user.
			$features = $this->getFeatures($userId, $section, $isAdmin, $ktVersion);
			$seenFeatures = $this->seenFeatures($features);
			$unseenFeatures = $this->unSeenFeatures($features, $seenFeatures);
			// Cache results.
			NewFeatureCache::saveToCache($features, $userId, 'all', $section);
			NewFeatureCache::saveToCache($unseenFeatures, $userId, 'seen', $section);
			NewFeatureCache::saveVersion($userId, $ktVersion);
		}
		$this->saveSeenFeatures($unseenFeatures);

		return $unseenFeatures;
	}

	private function determinePageLocation($location)
	{
		if (preg_match('/settings\.php/', $location)) {
			return 'settings';
		}

		if (preg_match('/dashboard\.php/', $location)) {
			return 'dashboard';
		}

		// Not in use??
		if (preg_match('/action\.php/', $location)) {
			return 'action';
		}

		if (preg_match('%/01\d*%', $location)) {
			return 'view_details';
		}

		return 'browse';
	}

	private function getFeatures($userId, $section, $isAdmin, $ktVersion)
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
		if (empty($features)) { return array(); }
		$userId = $_SESSION['userID'];
		$query = 'SELECT * FROM ' . $this->users_table . ' WHERE user_id = \'' . $userId .'\' AND message_id ';
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

		// If all features have not been seen yet
		if(empty($seenFeatures)) {
			// Only return the first three
			return array_slice($features, 0, 3);
		}
		else {

			$seenIds = array();

			foreach ($seenFeatures as $seenFeature)
			{
				// Because we cache the results, it might be of two diffent queries.
				// Check both the seen and unseen results.
				$seenIds[] = isset($seenFeature['message_id']) ? $seenFeature['message_id'] : $seenFeature['mid'];
			}

			$counter = 0;

			foreach ($features as $unSeenFeatureItem) {

				if(!in_array($unSeenFeatureItem['mid'], $seenIds) && $counter < 3) {
					$unSeenFeatures[] = $unSeenFeatureItem;
					$counter++;
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
		$userId = $_SESSION['userID'];
		$i = 1;
		$numResults = count($seenFeatures);
		$query = 'INSERT into ' . $this->users_table . ' (`user_id`, `message_id`) VALUES ';
		foreach ($seenFeatures as $seenFeature) {
			if(!$this->seenEntryExists($userId, $seenFeature['mid'])) {
				if($i == $numResults) {
					$query .= '(' . $userId . ', ' . $seenFeature['mid'] . ');';
				}
				else {
					$query .= '(' . $userId . ', ' . $seenFeature['mid'] . '),';
				}
				$addEntry = true;
			}
			$i++;
		}
		if($addEntry) {
			DBUtil::runQuery($query);
		}

		return true;
	}

	private function seenEntryExists($userId, $messageID)
	{
		$query = "SELECT * FROM {$this->users_table} WHERE user_id = '$userId' AND message_id = '$messageID'";
		$results = DBUtil::getResultArray($query);

		return (count($results) > 0);
	}
}