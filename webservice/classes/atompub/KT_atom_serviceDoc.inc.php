<?php
/**
 * Framework for an Atom Publication Protocol Service
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
 * Contributor( s):
 * 				Mark Holtzhausen <mark@knowledgetree.com>
 *
 */


/**
 * Includes
 */
include_once('KT_atom_baseDoc.inc.php');


class KT_atom_serviceDoc extends KT_atom_baseDoc {

	protected $baseURI=NULL;
	protected $service=NULL;

	public function __construct($baseURI=NULL){
		parent::__construct();
		$this->constructServiceDocumentHeaders();
		$this->baseURI=$baseURI;
	}

	protected function constructServiceDocumentHeaders(){
		$service=$this->newElement('service');
		$service->appendChild($this->newAttr('xmlns','http://www.w3.org/2007/app'));
		$service->appendChild($this->newAttr('xmlns:atom','http://www.w3.org/2005/Atom'));
		$this->service=&$service;
		$this->DOM->appendChild($this->service);
	}

	public function &newWorkspace($title=NULL){
		$ws=$this->newElement('workspace');
		if($title)$ws->appendChild($this->newElement('atom:title',$title));
		$this->service->appendChild($ws);
		return $ws;
	}

	public function &newCollection($url=NULL,$title=NULL,&$ws=NULL){
		$collection=$this->newElement('collection');
		$collection->appendChild($this->newAttr('href',$url));
		$collection->appendChild($this->newElement('atom:title',$title));
		if(isset($ws))$ws->appendChild($collection);
		return $collection;
	}

	public function &newAccept($docType=NULL,&$collection=NULL){
		if($docType){
			$accept=$this->newElement('accept',$docType);
		}else{
			$accept=$this->newElement('accept');
		}
		if($collection)$collection->appendChild($accept);
		return $accept;
	}


	public function getAPPdoc(){
		return $this->formatXmlString(trim($this->DOM->saveXML()));
	}

}








/**
<?xml version="1.0" encoding="utf-8"?>
<service xmlns="http://www.w3.org/2007/app" xmlns:atom="http://www.w3.org/2005/Atom">
  <workspace>
    <atom:title>Main Site</atom:title>
    <collection href="http://example.org/blog/main" >
      <atom:title>My Blog Entries</atom:title>
      <categories href="http://example.com/cats/forMain.cats" />
    </collection>
    <collection href="http://example.org/blog/pic" >
      <atom:title>Pictures</atom:title>
      <accept>image/png</accept>
      <accept>image/jpeg</accept>
      <accept>image/gif</accept>
    </collection>
  </workspace>
  <workspace>
    <atom:title>Sidebar Blog</atom:title>
    <collection href="http://example.org/sidebar/list" >
      <atom:title>Remaindered Links</atom:title>
      <accept>application/atom+xml;type=entry</accept>
      <categories fixed="yes">
        <atom:category scheme="http://example.org/extra-cats/" term="joke" />
        <atom:category scheme="http://example.org/extra-cats/" term="serious" />
      </categories>
    </collection>
  </workspace>
</service>

 */


?>