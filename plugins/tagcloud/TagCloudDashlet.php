<?php

/**
 * $Id: TagCloudDashlet.php,v 1.1 2006/02/28 16:53:49 nbm Exp $
 *
 * Copyright (c) 2007 Jam Warehouse http://www.jamwarehouse.com
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
 *         http://www.knowledgetree.com/
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

		$aTags = & $this->get_relevant_tags();
		$aTags = & $this->get_tag_weightings($aTags);

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
    function & get_tag_weightings(&$aTags)
	{
		$aTags = $this->get_relevant_tags();

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
    function & get_relevant_tags()
	{

		list($where, $params, $joins) = KTSearchUtil::permissionToSQL($this->oUser, null);


		$sql = "
    		SELECT 
    			tw.tag, count(*) as freq 
    		FROM 
    			document_tags dt INNER JOIN tag_words tw ON dt.tag_id=tw.id
    		WHERE dt.document_id in (SELECT d.id FROM documents d $joins WHERE $where) GROUP BY tw.tag";


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
