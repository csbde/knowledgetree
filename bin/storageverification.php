<?php
/**
 * $Id$
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
 * Contributor( s): ______________________________________
 */

require_once(dirname(__FILE__) . '/../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/storage/storagemanager.inc.php');

// TODO: this does not verify files that are in the storage system, but not on the database. It is better that the storage system
// be in sync with the database. However, we should have a better way to query the storage driver to give us a list of files.

class StorageVerification
{
    private $count;
    private $lineCount;
    private $doc;
    const DOCS_PER_DOT = 100;
    const DOTS_PER_LINE = 80;
    private $nl;
    private $tab;

    private
    function error($msg)
    {
        $doc = $this->doc;
        $documentId = $doc->getId();
        $path = $doc->getFullPath();
        $filename = $doc->getFileName();
        $storagePath = $doc->getStoragePath();

        print "{$this->nl}{$this->nl}";
        print "Problem with Document ID: {$documentId}{$this->nl}";
        print "{$this->tab}Path: {$path}{$this->nl}";
        print "{$this->tab}Filename: {$filename}{$this->nl}";
        print "{$this->tab}StoragePath: {$storagePath}{$this->nl}";
        print "{$this->tab}Problem: {$msg}{$this->nl}{$this->nl}";
        flush();
        $this->count = 0;
        $this->lineCount = 0;
        $this->clearCache();
    }

    private
    function progress()
    {
        if ($this->count++ % StorageVerification::DOCS_PER_DOT == 0)
        {
            $this->lineCount++;
            print '.';
            flush();
        }

        if ($this->lineCount == StorageVerification::DOTS_PER_LINE )
        {
            print "{$this->nl}";
            flush();
            $this->lineCount = 0;
        }
        $this->clearCache();
    }

    private
    function clearCache()
    {
        $metadataid = $this->doc->getMetadataVersionId();
        $contentid = $this->doc->getContentVersionId();
        $iId = $this->doc->getId();
        $cache = KTCache::getSingleton();
        $cache->remove('KTDocumentMetadataVersion/id', $metadataid);
        $cache->remove('KTDocumentContentVersion/id', $contentid);
        $cache->remove('KTDocumentCore/id', $iId);
        $cache->remove('Document/id', $iId);
        unset($GLOBALS['_OBJECTCACHE']['KTDocumentMetadataVersion'][$metadataid]);
        unset($GLOBALS['_OBJECTCACHE']['KTDocumentContentVersion'][$contentid]);
        unset($GLOBALS['_OBJECTCACHE']['KTDocumentCore'][$iId]);

        unset($this->doc);
    }

    public
    function run()
    {
        global $argc;

        if (isset($argc))
        {
            $this->nl = "\n";
            $this->tab = "\t";
            print "Storage Verification{$this->nl}";
            print "===================={$this->nl}";
        }
        else
        {
            $this->nl = '<br>';
            $this->tab = '&nbsp;&nbsp;&nbsp;&nbsp;';
            print "<b>Storage Verification</b>{$this->nl}";

        }



        $sql = "SELECT
                    dmv.id as metadata_version_id, dcv.document_id, dcv.md5hash, dcv.size
               FROM
                    document_content_version dcv
                    INNER JOIN document_metadata_version dmv ON dcv.id=dmv.content_version_id";
        $rows = DBUtil::getResultArray($sql);
        $this->count = 0;
        $this->lineCount = 0;

        $storage =& KTStorageManagerUtil::getSingleton();
        foreach($rows as $row)
        {
            $doc = Document::get($row['document_id'], $row['metadata_version_id']);

            if (PEAR::isError($doc))
            {
                $msg = $doc->getMessage();
                $this->error($doc, "Error with document: {$msg}");
                continue;
            }
            $this->doc = $doc;

            $tmpPath = $storage->temporaryFile($doc);
            if (!file_exists($tmpPath))
            {
                $this->error("Temporary file could not be resolved: {$tmpPath}");
                continue;
            }

            $expectedSize = $row['size'];
            $currentSize = filesize($tmpPath);
            if ($expectedSize != $currentSize)
            {
                $this->error("Filesize does not match. Expected: {$expectedSize} Current: {$currentSize}");
                continue;
            }

            $expectedHash = $row['md5hash'];
            $currentHash = md5_file($tmpPath);
            if ($expectedHash != $currentHash)
            {
                $this->error("Hash does not match. Expected: {$expectedHash} Current: {$currentHash}");
                continue;
            }
            $this->progress();
        }

        print "{$this->nl}Done.{$this->nl}{$this->nl}";
    }

}


$verification = new StorageVerification();
$verification->run();

?>
