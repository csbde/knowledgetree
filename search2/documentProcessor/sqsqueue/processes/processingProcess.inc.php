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

require_once(realpath(dirname(__FILE__) . '/../queueProcess.php'));

class processingProcess extends queueProcess {
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	function init() {
		parent::setName('processingProcess');
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	function addEvents() {
		$pdf = new queueEvent('pdf', 'pdfGenerator.run');
		$thumb = new queueEvent('thumb', 'thumbGenerator.run');
		$flash = new queueEvent('flash', 'flashGenerator.run');
		parent::addEvent($pdf);
		parent::addEvent($thumb);
		parent::addEvent($flash);
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	function addDependencies() {
		$pdf = new dependencyList('pdf');
		$pdf->addDependency('thumb');
		$pdf->addDependency('flash');
		parent::addDependencyList($pdf);
	}
}
?>