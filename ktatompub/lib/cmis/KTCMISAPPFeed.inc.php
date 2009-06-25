<?php
/**
 * CMIS specific extension for AtomPub
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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
 * Contributor( s): ______________________________________
 *
 */


/**
 * Includes
 */
include_once('../KTAPDoc.inc.php');

/**
 * This class generates an AtomPub CMIS feed
 */

class KTCMISAPPFeed extends KTAPDoc {
	
	private $baseURI = NULL;
	private $id = NULL;
	private $title = NULL;
	private $feed = NULL;
			
	public function __construct($baseURI = NULL, $title = NULL, $link = NULL, $updated = NULL, $author = NULL, $id = NULL)
    {
		parent::__construct();
        
		$this->baseURI = $baseURI;
        $this->id = $id;
        $this->title = $title;
		$this->constructHeader();
	}
	
	private function constructHeader()
    {
		$feed = $this->newElement('feed');
		$feed->appendChild($this->newAttr('xmlns','http://www.w3.org/2007/app'));
		$feed->appendChild($this->newAttr('xmlns','http://www.w3.org/2005/Atom'));
		$feed->appendChild($this->newAttr('xmlns:cmis','http://www.cmis.org/2008/05'));
		$this->feed = &$feed;

        if (!is_null($this->id))
        {
            $this->newId($this->id, $this->feed);
        }

        $link = $this->newElement('link');
		$link->appendChild($this->newAttr('rel','self'));
		$link->appendChild($this->newAttr('href', $this->baseURI . trim($_SERVER['QUERY_STRING'], '/')));
		$feed->appendChild($link);
        
        if (!is_null($this->title))
        {
            $this->feed->appendChild($this->newElement('title', $this->title));
        }

        $this->DOM->appendChild($this->feed);
	}
	
	public function &newEntry()
    {
		$entry = $this->newElement('entry');
		$this->feed->appendChild($entry);
		return $entry;		
	}

    public function &newId($id, $entry = null)
    {
		$id = $this->newElement('id', $id);
        if(isset($entry))$entry->appendChild($id);
		return $id;
	}
	
	public function &newField($name = NULL, $value = NULL, &$entry = NULL)
    {
        $append = false;

        if(func_num_args() > 3)
        {
            $append = ((func_get_arg(3) === true) ? true : false);
		}

        $field = $this->newElement('cmis:' . $name,$value);

		if (isset($entry)) $entry->appendChild($field);
        else if ($append) $this->feed->appendChild($field);

		return $field;
	}
	
	public function getAPPdoc()
    {
		return $this->formatXmlString(trim($this->DOM->saveXML()));
	}
	
}

/*
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">

  <title>Example Feed</title>
  <link href="http://example.org/"/>
  <updated>2003-12-13T18:30:02Z</updated>
  <author>
    <name>John Doe</name>
  </author>
  <id>urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6</id>

  <entry>
    <title>Atom-Powered Robots Run Amok</title>
    <link href="http://example.org/2003/12/13/atom03"/>
    <id>urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a</id>
    <updated>2003-12-13T18:30:02Z</updated>
    <summary>Some text.</summary>
  </entry>

</feed>
*/

?>