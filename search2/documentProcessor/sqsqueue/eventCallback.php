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

class eventCallback 
{
	/**
	 * Name of callback
	 * @var object
	 */
	public $name;
	/**
	 * A url to callback to.
	 * @var string
	 */
	public $url;
	/**
	 * List of event dependencies
	 * @var array
	 */
	public $dependencies;
	
    /**
    * Constructor
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $name
    * @param string $url
    * @param array $dependencies
    * @return none
    */
	public function __construct($name, $url, $dependencies) 
	{
		$this->name = $name;
		$this->url = $url;
		$this->dependencies = $dependencies;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string
    * @return none
    */
	public function getName() 
	{
		return $this->name;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string
    * @return none
    */
	public function getUrl()
	{
		return $this->url;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string
    * @return none
    */
	public function getDependencies()
	{
		return $this->dependencies;
	}
}

?>