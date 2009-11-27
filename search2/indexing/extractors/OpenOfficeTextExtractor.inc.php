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

require_once(KT_DIR.'/thirdparty/peclzip/pclzip.lib.php');

class OpenOfficeTextExtractor extends ExternalDocumentExtractor
{
	public function __construct()
	{
	    /* *** Replaced unzip binary with pclzip ***
		$config = KTConfig::getSingleton();

		$this->unzip = KTUtil::findCommand("import/unzip", 'unzip');
		$this->unzip = str_replace('\\','/',$this->unzip);
		$this->unzip_params = $config->get('extractorParameters/unzip', '"{source}" "{part}" -d "{target_dir}"');
		*/
		parent::__construct();
	}

	/**
	 * Basic function setting the display name
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return _kt('Open Office Text Extractor');
	}

	public function getSupportedMimeTypes()
	{
		return array(
       		'application/vnd.oasis.opendocument.presentation',
       		'application/vnd.oasis.opendocument.presentation-template',
       		'application/vnd.oasis.opendocument.spreadsheet',
       		'application/vnd.oasis.opendocument.spreadsheet-template',
			'application/vnd.oasis.opendocument.text',
			'application/vnd.oasis.opendocument.text-template',
			'application/vnd.oasis.opendocument.text-master'
		);
	}

	public function needsIntermediateSourceFile()
	{
		return true;
	}

	protected function filter($text)
	{
		 return preg_replace ("@(</?[^>]*>)+@", " ", $text);
	}

	public function extractTextContent()
	{
		$config = KTConfig::getSingleton();
		$temp_dir = $config->get('urls/tmpDirectory');

		$docid = $this->document->getId();
		$time = 'ktindexer_openoffice_'. time() . '-' . $docid;
		$this->openxml_dir = $temp_dir . '/' . $time;

		$this->sourcefile = str_replace('\\','/',$this->sourcefile);
		$this->openxml_dir = str_replace('\\','/',$this->openxml_dir);

    	$archive = new PclZip($this->sourcefile);

    	if ($archive->extract(PCLZIP_OPT_PATH, $this->openxml_dir) == 0){
    		$this->output = _kt('Failed to extract content');
			return false;
    	}

		/* *** Original code using the unzip binary ***
		$cmd = '"' . $this->unzip . '"' . ' ' . str_replace(
			array('{source}','{part}', '{target_dir}'),
			array($this->sourcefile, 'content.xml',$this->openxml_dir), $this->unzip_params);

		$cmd = str_replace('\\','/', $cmd);

 		if (!$this->exec($cmd))
		{
			$this->output = _kt('Failed to execute command: ') . $cmd;
			return false;
		}
		*** End unzip code *** */

		$filename = $this->openxml_dir . '/content.xml';
		if (!file_exists($filename))
		{
			$this->output = _kt('Failed to find file: ') . $filename;
			return false;
		}

        $result = file_put_contents($this->targetfile, $this->filter(file_get_contents($filename)));

		return $result !== false;
	}

	/**
	 * Check that unzip is available
	 *
	 * @return boolean
	 */
	public function diagnose()
	{
	    return null;
		if (false === $this->unzip)
		{
			return sprintf(_kt("Cannot locate unzip: %s."), $this->unzip);
		}
		return null;
	}

}

?>