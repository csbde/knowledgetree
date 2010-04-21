<?php
/**
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
/**
 * Load simple event modeling class queueEvent
 */
require_once(realpath(dirname(__FILE__) . '/../../queueEvent.php'));

class thumbEvent extends queueEvent {
	/**
	 * List of event dependencies
	 * @var array
	 */
	public $list_of_dependencies = array(
										'pdfEvent'
										);
	/**
	 * Parameters to be passed with event
	 * @var array
	 */
	public $list_of_parameters = array();
	/**
	 * Callbacks to be envoked
	 * @var array
	 */
	public $list_of_callbacks = array();
	
    /**
    * Construct thumbnail generator Event
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return none
    */
	function __construct() 
	{
		parent::setName('thumbEvent');
		parent::setMessage('ThumbGenerator.run');
	}
	
    /**
    * Create parameters needed by event
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return none
    */
	public function buildParameters() 
	{
		require_once(dirname(__FILE__) . '/../../../../../config/dmsDefaults.php');
		$this->addParameter('src_file', $this->getSrcFile());
		$this->addParameter('dest_file', $this->getDestFile());
	}
	
    /**
    * Get pdf destination url
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
	private function getSrcFile() 
	{
		$oStorage =& KTStorageManagerUtil::getSingleton();

		return $oStorage->getDocumentUrl($this->document, 'pdf');
	}
	
    /**
    * Get thumbnail destination url
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
	private function getDestFile() 
	{
		$oStorage =& KTStorageManagerUtil::getSingleton();

		return $oStorage->getDocumentUrl($this->document, 'thumbnail');
	}
}
?>