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

require_once(realpath(dirname(__FILE__) . '/../queueEvent.php'));

class pdfEvent extends queueEvent {
	public $list_of_dependencies = array();
	public $list_of_parameters = array();
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	function __construct() {
		parent::setName('pdfEvent');
		parent::setMessage('pdf.run');
		parent::setDependency($this->list_of_dependencies);
	}
	
	public function buildParameters() {
		require_once(dirname(__FILE__) . '/../../../../config/dmsDefaults.php');
		require_once(KT_DIR . '/search2/documentProcessor/documentProcessor.inc.php');
		require_once(KT_DIR . '/search2/indexing/lib/XmlRpcLucene.inc.php');
		$config =& KTConfig::getSingleton();
		$this->addParameter('document_id', $this->document->getId());
		$this->addParameter('javaServerUrl', $config->get('indexer/javaLuceneURL'));
		$this->addParameter('ooHost', $config->get('openoffice/host','127.0.0.1'));
		$this->addParameter('ooPort', $config->get('openoffice/port','8100'));
		$this->addParameter('ext', KTMime::getFileType($this->document->getMimeTypeID()));
	}
	
}
?>