<?php

/**
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
 */

/**
 * API for the handling the KnowledgeTree trigger
 *
 * @author KnowledgeTree Team
 * @package KTAPI
 * @version 0.1
 */
class KTAPI_Trigger
{

	/**
	 * This is a reference to the ktapi object.
	 *
	 * @access protected
	 * @var KTAPI
	 */
    var $ktapi;

	/**
	 * Creates a new KTAPI_Trigger, sets up the internal variables.
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPI $ktapi Instance of the KTAPI object
	 * @return KTAPI_Trigger
	 */
	function KTAPI_Trigger(&$ktapi)
	{
		assert(!is_null($ktapi));
		assert(is_a($ktapi,'KTAPI'));

		$this->ktapi=&$ktapi;
	}
	
	
	/**
	 * Triggers a new KTAPI_Trigger
	 *
	 * @author KnowledgeTree Team
	 * @access public
	 * @param KTAPIDocument $document Document to trigger on
	 * @param string $action Action to trigger
	 * @param string $slot Slot the action falls in
	 */
	function doTrigger($document, $action, $slot)
	{					
		$oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
		$aTriggers = $oKTTriggerRegistry->getTriggers($action, $slot);
		
		foreach ($aTriggers as $aTrigger) {			
			$sTrigger = $aTrigger[0];			
            $oTrigger = new $sTrigger;			
			$aInfo = array(
				'document' => $document->document,
			);
			$oTrigger->setInfo($aInfo);
			$ret = $oTrigger->postValidate();
			if (PEAR::isError($ret)) {
				return $ret;
			}
		}
	}

}

?>