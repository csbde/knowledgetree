<?php

/**
 * $Id:$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
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
		if (false === parent::extractTextContent())
		{
			return false;
		}

		if ($this->targetExtension != 'html')
		{
			return true;
		}
		$content = file_get_contents($this->targetfile);
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