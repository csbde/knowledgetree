<?php

/*
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


require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');

class TagCloudDashlet extends KTBaseDashlet {
	var $oUser;
	var $sClass = 'ktBlock';

	/**
	 * Constructor method
	 *
	 * @return TagCloudDashlet
	 */
	function TagCloudDashlet(){
		$this->sTitle = _kt('Tag Cloud');
	}

	/**
	 * Check to see if user is active
	 *
	 * @param object $oUser
	 * @return boolean
	 */
	function is_active($oUser) {
		$this->oUser = $oUser;
		return true;
	}

	/**
	 * Render function for template
	 *
	 * @return unknown
	 */
	function render() {
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('TagCloud/dashlet');

		$aTags = $this->get_tag_weightings($aTags);

		$oRegistry =& KTPluginRegistry::getSingleton();
		$oPlugin =& $oRegistry->getPlugin('ktcore.tagcloud.plugin');
		$url = $oPlugin->getPagePath('TagCloudRedirection');

		$aTemplateData = array(
		'tags' => $aTags,
		'url'=>$url
		);
		return $oTemplate->render($aTemplateData);
    }

    /**
     * Builds the weightings for tags based on their frequency
     *
     * @param array $aTags
     * @return array
     */
    function get_tag_weightings($aTags)
	{
		$aTags = $this->get_relevant_tags();

		if($aTags === false || empty($aTags)){
		    return array();
		}

		if (count($aTags) == 0) $min_freq=$max_freq=0;
		else
		{
			$min_freq = min(array_values($aTags));
			$max_freq = max(array_values($aTags));
		}
		$min_size = 12;
		$max_size = 30;

		$distrib = $max_freq - $min_freq;
		if ($distrib == 0) $distrib=1;

		$step = ($max_size - $min_size)/($distrib);

		foreach($aTags as $tag=>$freq)
		{
			$size = ceil($min_size + (($freq - $min_freq) * $step));
			$aTags[$tag] = $size;
		}

		return $aTags;
	}

    /**
     * Returns the relevant tags for the current user
     *
     * @return array
     */
    function get_relevant_tags()
	{
		$aUserPermissions = KTSearchUtil::permissionToSQL($this->oUser, null);
		if(PEAR::isError($aUserPermissions)) {
            return false;
        }
		list($where, $params, $joins) = $aUserPermissions;
		$sql = "
    		SELECT
    			TW.tag, count(*) as freq
    		FROM
    			document_tags DT INNER JOIN tag_words TW ON DT.tag_id=TW.id
    		WHERE DT.document_id in (SELECT D.id FROM documents D $joins WHERE $where AND D.status_id = '1')  GROUP BY TW.tag";

		$tags = DBUtil::getResultArray(
		array($sql,$params)

		);
		$aTags = array();
		foreach($tags as $tag)
		{
			$word=$tag['tag'];
			$freq=$tag['freq'];
			$aTags[$word] = $freq;
		}
		return $aTags;

	}

}
?>
