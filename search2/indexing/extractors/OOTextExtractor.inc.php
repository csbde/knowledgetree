<?php

/**
 * $Id:$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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

class OOTextExtractor extends ExternalDocumentExtractor
{
	protected $python;
	protected $documentConverter;
	protected $ooHost;
	protected $ooPort;
	protected $targetExtension;

	public function __construct($targetExtension='html')
	{
		parent::__construct();
		$this->targetExtension = $targetExtension;
		$config =& KTConfig::getSingleton();

		$this->python = KTUtil::findCommand('externalBinary/python');
		$this->ooHost = $config->get('openoffice/host');
		$this->ooPort = $config->get('openoffice/port');

		$this->documentConverter = KT_DIR . '/bin/openoffice/DocumentConverter.py';
		if (!is_file($this->documentConverter))
		{
			$this->documentConverter = false;
		}
	}

	public function getDisplayName()
	{
		return _kt('OpenOffice Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(
			'application/msword',
			'application/vnd.sun.xml.writer',
			'application/vnd.sun.xml.writer.template',
			'application/vnd.sun.xml.writer.global',
			'application/vnd.oasis.opendocument.text',
			'application/vnd.oasis.opendocument.text-template',
			'application/vnd.oasis.opendocument.text-master'
		);
	}

	public function needsIntermediateSourceFile()
	{
		// we need the intermediate file because it
		// has the correct extension. documentConverter uses the extension to determine mimetype
		return true;
	}

	protected function getCommandLine()
	{
		$sourcefile = $this->sourcefile;

		unlink($this->targetfile);
		$this->targetfile .= '.' . $this->targetExtension;
		$targetfile = $this->targetfile;

		$escape = '"';

		$cmdline = "{$escape}{$this->python}{$escape} {$escape}{$this->documentConverter}{$escape} {$escape}{$sourcefile}{$escape} {$escape}{$targetfile}{$escape} {$this->ooHost} {$this->ooPort}";
		$cmdline = str_replace('\\','/',$cmdline);

		return $cmdline;
	}

	protected function filter($text)
	{
		 $text = preg_replace ("@(</?[^>]*>)+@", '', $text);

		 do
		 {
			 $old = $text;

			 $text= preg_replace("@([\r\n])[\s]+@",'\1', $text);

			 $text = preg_replace('@\ \ @',' ', $text);
			 $text = preg_replace("@\n\n@","\n", $text);
		 }
		 while ($old != $text);

		 return $text;
	}

	public function extractTextContent()
	{
	    global $default;

        $docId = $this->document->getId();

	    if (empty($this->extension))
	    {
	        $default->log->info("DocumentId: $docId - Document does not have an extension");
            Indexer::unqueueDocument($docId, sprintf(("Removing document from queue: documentId %d"),$docId));
	        return false;
	    }

	    // Open Office does not support the following files
	    if (in_array($this->extension, array('xlt')))
	    {
	        $default->log->info("DocumentId: $docId - Document does not have an extension");
	        Indexer::unqueueDocument($docId, sprintf(("Removing document from queue: documentId %d"),$docId));
	        return false;
	    }

        if (false === parent::extractTextContent())
		{
		    if (strpos($this->output, 'OpenOffice process not found or not listening') !== false)
		    {
		        $indexer = Indexer::get();
                $indexer->restartBatch();
                return false;
		    }
		    elseif (strpos($this->output, 'Unexpected connection closure') !== false
		    || strpos($this->output, '\'NoneType\' object has no attribute \'storeToURL\'') !== false
		    || strpos($this->output, 'The document could not be opened for conversion. This could indicate an unsupported mimetype.') !== false
		    || strpos($this->output, 'URL seems to be an unsupported one.') !== false
		    || strpos($this->output, '__main__.com.sun.star.task.ErrorCodeIOException') !== false)
		    {
                $default->log->info("DocumentId: $docId - Suspect the file cannot be indexed by Open Office.");
                file_put_contents($this->targetfile, '');
                $indexer = Indexer::get();
                $indexer->restartBatch();

                Indexer::unqueueDocument($docId, sprintf(_kt("Removing document from queue: documentId %d"),$docId));
	           return true;
		    }
			return false;
		}

		if ($this->targetExtension != 'html')
		{
		    file_put_contents($this->targetfile, '');
			return true;
		}
		$content = file_get_contents($this->targetfile);

        $this->setTargetFile($this->targetfile . '.txt');

		return file_put_contents($this->targetfile, $this->filter($content));

	}


	public function diagnose()
	{
		if (false === $this->python)
		{
			return _kt('Cannot locate python');
		}

		if (false === $this->documentConverter)
		{
			return _kt('Cannot locate DocumentConverter.py');
		}

		return SearchHelper::checkOpenOfficeAvailablity();
	}
}

?>