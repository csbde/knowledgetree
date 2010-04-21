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

class queueEvent 
{
	/**
	 * Document object
	 * @var object
	 */
	public $document;
	/**
	 * Event name
	 * @var string
	 */
	public $name;
	/**
	 * Event message
	 * @var string
	 */
	public $message;
	/**
	 * List of event dependencies
	 * @var array
	 */
	public $list_of_dependencies;
	/**
	 * Parameters to be passed with event
	 * @var array
	 */
	public $list_of_parameters;
	/**
	 * Callbacks to be envoked
	 * @var array
	 */
	public $list_of_callbacks;
	
    /**
    * Constructor
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $name
    * @param string $message
    * @return
    */
	public function __construct($name = '', $message = '') 
	{
		$this->name = $name;
		$this->message = $message;
		$this->setDependency($this->list_of_dependencies);
		$this->setCallbacks($this->list_of_callbacks);
		$this->document = null;
	}
	
    /**
    * Set event name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $name
    * @return
    */
	public function setName($name) 
	{
		$this->name = $name;
	}
	
    /**
    * Set event message
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $message
    * @return
    */
	public function setMessage($message) 
	{
		$this->message = $message;
	}
	
    /**
    * Add event dependency
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $message
    * @return
    */
	public function setDependency($list_of_dependencies) 
	{
		$this->list_of_dependencies = $list_of_dependencies;
	}
	
    /**
    * Set the document
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function setDocument($document) 
	{
		$this->document = $document;
	}
	
    /**
    * Add event parameter
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $parameters
    * @return
    */
	function addParameter($key, $value) 
	{
		if(!in_array($key, $this->list_of_parameters))
		{
			$this->list_of_parameters[$key] = $value;
		}
	}
	
    /**
    * Get event name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getName() 
	{
		return $this->name;
	}
	
    /**
    * Get event message 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getMessage() 
	{
		return $this->message;
	}
	
    /**
    * Get list of event dependencies
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getDependencies() 
	{
		return $this->list_of_dependencies;
	}
	
    /**
    * Get list of event dependencies
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getParameters() 
	{
		return $this->list_of_parameters;
	}
	
    /**
    * Get list of event callbacks
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getCallbacks() 
	{
		return $this->list_of_callbacks;
	}
	
    /**
    * Create parameters needed by event
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function buildParameters() 
	{
		
	}

}

?>