<?php

/*
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
    			TW.tag, count(*) as freq 
    		FROM 
    			document_tags DT INNER JOIN tag_words TW ON DT.tag_id=TW.id
    		WHERE DT.document_id in (SELECT D.id FROM documents D $joins WHERE $where) GROUP BY TW.tag";


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
