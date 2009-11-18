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
 * 				Paul Barrett <paul@knowledgetree.com>
 *
 */


/**
 * Includes
 */
include_once(KT_ATOM_LIB_FOLDER.'KT_atom_serviceDoc.inc.php');
//include_once('KT_atom_baseDoc.inc.php');

class KT_cmis_atom_serviceDoc extends KT_atom_serviceDoc {

// override and extend as needed

    public $repositoryInfo = array();

    public function __construct($baseURI = NULL)
    {
        parent::__construct();

        // get repositoryInfo
        // NOTE currently we only support one repository, which will be the first one found in the repositories.xml config
        // TODO multiple repositories as individual workspaces

        include 'services/cmis/RepositoryService.inc.php';
        $RepositoryService = new RepositoryService();
        // TODO add auth requirement here, don't want to even supply service doc without auth
//        $RepositoryService->startSession();

        // fetch data for response
        $repositories = $RepositoryService->getRepositories();
        // fetch for default first repo;  NOTE that this will probably have to change at some point, quick and dirty for now
        $this->repositoryInfo = $RepositoryService->getRepositoryInfo($repositories[0]['repositoryId']);
    }

    protected function constructServiceDocumentHeaders()
    {
        $service = $this->newElement('service');
        $service->appendChild($this->newAttr('xmlns', 'http://www.w3.org/2007/app'));
        $service->appendChild($this->newAttr('xmlns:atom', 'http://www.w3.org/2005/Atom'));
        $service->appendChild($this->newAttr('xmlns:cmis', 'http://docs.oasis-open.org/ns/cmis/core/200901'));
        $this->service =& $service;
        $this->DOM->appendChild($this->service);
    }

    public function &newCollection($url = NULL, $title = NULL, $cmisCollectionType = NULL, $accept = null, &$ws = NULL)
    {
        $collection=$this->newElement('collection');
        $collection->appendChild($this->newAttr('href', $url));
        $collection->appendChild($this->newAttr('cmis:collectionType', $cmisCollectionType));
        $collection->appendChild($this->newElement('atom:title', $title));
        if (!is_null($accept)) {
            $collection->appendChild($this->newElement('accept', $accept));
        }
        if(isset($ws))$ws->appendChild($collection);
        return $collection;
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