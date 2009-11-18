<?php

/**
 * $Id:$
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
 *
 */

class StarOfficeExtractor extends ExternalDocumentExtractor
{
	protected $python;
	protected $documentConverter;
	protected $ooHost;
	protected $ooPort;
	protected $targetExtension;
	protected $useOO;

	public function __construct($targetExtension='html')
	{
		parent::__construct();
		$this->targetExtension = $targetExtension;
		$config =& KTConfig::getSingleton();

		$this->useOO = $config->get('indexer/useOpenOffice', true);
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
		return _kt('StarOffice Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
	    // disable
	    return array();
		$supported = array();

        if ($this->useOO)
        {
            $supported = array_merge($supported, array(
                'application/vnd.sun.xml.writer',
                'application/vnd.sun.xml.writer.template',
                'application/vnd.sun.xml.calc',
                'application/vnd.sun.xml.calc.template',
            ));
        }

        return $supported;
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
	        $default->log->info("DocumentId: $docId - Open Office does not support .xlt.");
	        Indexer::unqueueDocument($docId, sprintf(("Removing document from queue - Open Office does not support .xlt: documentId %d"),$docId));
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

        $content = $this->filter($content);
        if (empty($content))
        {
            return touch($this->targetfile);
        }

		return file_put_contents($this->targetfile, $content);

	}

	public function diagnose()
	{
	    if (!$this->useOO)
	    {
	        return true;
	    }
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
