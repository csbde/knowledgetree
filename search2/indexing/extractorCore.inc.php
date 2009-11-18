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

/**
 * DocumentExtractor is the base class for all text extractors.
 *
 */
abstract class DocumentExtractor
{
	/**
	 * The source filename from which to extract text.
	 *
	 * @var string
	 */
	protected $sourcefile;

	/**
	 * The target filename, where the extracted text must be stored.
	 *
	 * @var string
	 */
	protected $targetfile;

	/**
	 * The mime type of the source file.
	 *
	 * @var string
	 */
	protected $mimetype;

	/**
	 * The extension of the source file.
	 *
	 * @var string
	 */
	protected $extension;

	/**
	 * Reference to the document being indexed.
	 *
	 * @var Document
	 */
	protected $document;

	/**
	 * Indicates if the extractor needs an intermediate file or not.
	 * Generally the source file will be a file within the respository itself. Some extractors may
	 * require the source file to have the correct extension. Setting this to true will result in
	 * a file being created with the extension of the file. It is ideal to disable this if possible.
	 *
	 * @var boolean
	 */
	protected $needsIntermediate;

	/**
	 * The status of the extraction. If null, the extraction has not been done yet.
	 *
	 * @var boolean
	 */
	protected $extractionStatus;

	/**
	 * The status of the indexing. If null, the indexing has not been done yet.
	 *
	 * @var boolean
	 */
	protected $indexStatus;

	/**
	 * If an error occurred, this is the output that was captured
	 *
	 * @var string
	 */
	public $output;


	public function __construct()
	{
		$this->needsIntermediate=false;
		$this->extractionStatus = null;
		$this->indexStatus = null;
	}

	/**
	 * Sets the status of the indexing.
	 *
	 * @param unknown_type $status
	 */
	public function setIndexingStatus($status)
	{
		$this->indexStatus = $status;
	}
	/**
	 * Returns the indexing status.
	 *
	 * @return boolean
	 */
	public function getIndexingStatus()
	{
		return $this->indexStatus;
	}

	/**
	 * Sets the extraction status.
	 *
	 * @param boolean $status
	 */
	public function setExtractionStatus($status)
	{
		$this->extractionStatus = $status;
	}
	/**
	 * Return the extraction status.
	 *
	 * @return boolean
	 */
	public function getExtractionStatus()
	{
		return $this->extractionStatus;
	}

	/**
	 * This associates all the mime types associated with the extractor class.
	 *
	 */
	public function registerMimeTypes()
	{
		$types = $this->getSupportedMimeTypes();
		if (empty($types))
		{
			return;
		}
		$classname=get_class($this);

		$sql = "select id as extractor_id from mime_extractors WHERE name='$classname'";
		$rs = DBUtil::getResultArray($sql);
		if (count($rs) == 0)
		{
			$extractor_id = DBUtil::autoInsert('mime_extractors', array('name'=>$classname, 'active'=>1));
		}
		else
		{
			$extractor_id = $rs[0]['extractor_id'];
		}


		foreach($types as $type)
		{
			$sql = "update mime_types set extractor_id=$extractor_id where mimetypes='$type' and extractor_id is null";
			$rs = DBUtil::runQuery($sql);
		}
	}

	/**
	 * Indicates if an intermediate file is required.
	 *
	 * @param $value boolean Optional. If set, we set the value.
	 * @return boolean
	 */
	public function needsIntermediateSourceFile($value = null)
	{
		if (!is_null($value))
		{
			$this->needsIntermediate = $value;
		}
		return $this->needsIntermediate;
	}

	/**
	 * Sets the source filename for the document extractor.
	 *
	 * @param string $sourcefile
	 */
	public function setSourceFile($sourcefile)
	{
		$this->sourcefile=$sourcefile;
	}

	/**
	 * Returns the source file name.
	 *
	 * @return string
	 */
	public function getSourceFile() { return $this->sourcefile; }

	/**
	 * Sets the source file's mime type.
	 *
	 * @param string $mimetype
	 */
	public function setMimeType($mimetype)
	{
		$this->mimetype=$mimetype;
	}
	/**
	 * Returns the mime type for the source file.
	 *
	 * @return string
	 */
	public function getMimeType() { return $this->mimetype; }

	/**
	 * Indicates the extension for the source file.
	 *
	 * @param string $extension
	 */
	public function setExtension($extension)
	{
		$this->extension=$extension;
	}
	/**
	 * Returns the extension of the source file.
	 *
	 * @return string
	 */
	public function getExtension() { return $this->extension; }

	/**
	 * Sets the file name of the target text file.
	 *
	 * @param string $targetfile
	 */
	public function setTargetFile($targetfile)
	{
		$this->targetfile=$targetfile;
	}

	/**
	 * Gets the file name of the target text file containing the extracted text.
	 *
	 * @return unknown
	 */
	public function getTargetFile() { return $this->targetfile; }

	/**
	 * Filter function that may be applied after extraction. This may be overridden.
	 *
	 * @param string $text
	 * @return string
	 */
	protected function filter($text)
	{
		return $text;
	}

	/**
	 * Set the document that will be indexed.
	 *
	 * @param Document $document
	 */
	public function setDocument($document)
	{
		$this->document = $document;
	}

	/**
	 * Returns a reference to the document.
	 *
	 * @return string
	 */
	public function getDocument()
	{
		return $this->document;
	}

	/**
	 * Returns an array of supported mime types.
	 * e.g. return array('plain/text');
	 *
	 *
	 * @return array
	 *
	 */
	public abstract function getSupportedMimeTypes();

	/**
	 * Extracts the content from the source file.
	 *
	 * @return boolean
	 */
	public abstract function extractTextContent();

	/**
	 * Returns a friendly name for the document text extractor.
	 *
	 * @return string
	 */
	public abstract function getDisplayName();

	/**
	 * Attempts to diagnose any problems with the indexing process.
	 *
	 * @return string
	 */
	public abstract function diagnose();

}

/**
 * This class extends the document extractor to execute some command line application.
 * The getCommandLine() method needs to be overridden.
 *
 */
abstract class ExternalDocumentExtractor extends DocumentExtractor
{
    protected $allowOutput = false;
    protected $pipeStdoutToDevNull = false;

	/**
	 * Initialise the extractor.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		putenv('LANG=en_US.UTF-8');

		$config = KTConfig::getSingleton();

		$default = realpath(str_replace('\\','/',KT_DIR . '/../openoffice/program'));

		putenv('ooProgramPath=' . $config->get('openoffice/programPath', $default));
	}

	public function setAllowOutput($allowOutput)
	{
	    $this->allowOutput = $allowOutput;
	}

	/**
	 * Executes a command. Returns true if successful.
	 *
	 * @param string $cmd A command line instruction.
	 * @return boolean
	 */
	protected  function exec($cmd)
	{
		$config = KTConfig::getSingleton();
		$temp_dir = $config->get('urls/tmpDirectory');
		$res = 0;

		$docid = $this->document->getId();

		$script_prefix = $temp_dir . '/' . time() . '-' . $docid;
		$script_out = $script_prefix . '.out';

		// define the scripts that we want

		if (OS_WINDOWS)
		{
			$script_name = $script_prefix . '.bat';

			$script = "rem This is an auto generated file. \n";
			$script .= $cmd . ' 2>"' . $script_out . "\"\r\n";
			$script .= "set er=%ERRORLEVEL%\r\n";
			$script .= "exit /B %er%\r\n";
		}
		else
		{
			$script_name = $script_prefix . '.sh';

			$script = "#!/bin/sh\n";
			$script .= "# This is an auto generated file. \n";
			$script .= $cmd . ' 2>>"' . $script_out . "\"";

			if ($this->pipeStdoutToDevNull)
			{
			    $script .= " >/dev/null";
			}

			$script .= "\n";

			$script .= "exit $?\n";
		}

		// write the script file
		if (file_put_contents($script_name, $script) === false)
		{
			$this->output = _kt('Could not create exec script: ') . $script_name;
			return false;
		}

		// execute the script file
		if (OS_WINDOWS)
		{
			$res = KTUtil::pexec("\"$script_name\"");
			$res = $res['ret'];
		}
		else
		{
			if (chmod($script_name, 0755) === false)
			{
				$this->output = _kt('Could change permission on exec script: ') . $script_name;
				return false;
			}
			system($script_name, $res);
		}

		// remote the script file and get the output if available
		@unlink($script_name);

		if (file_exists($script_out))
		{
			$this->output = file_get_contents($script_out);
			@unlink($script_out);
		}

		return ($res == 0) && (empty($this->output) || $this->allowOutput);
	}

	/**
	 * Returns the command line string to be executed.
	 * The command returned should include the target filename.
	 *
	 * @return string
	 */
	protected function getCommandLine()
	{
		throw new Exception(_kt('getCommandLine is not implemented'));
	}

	/**
	 * Executes the command that executes the command.
	 * Returns true if success.
	 *
	 * @return boolean
	 */
	public function extractTextContent()
	{
		global $default;

		$cmdline = $this->getCommandLine();

		$class = get_class($this);
		$default->log->debug("$class: "  . $cmdline);

		return $this->exec($cmdline);
	}

}

abstract class OOFallbackDocumentExtractor extends ExternalDocumentExtractor
{
    protected $cmd;
    protected $params;

    /**
     * Enter description here...
     *
     * @var StarOfficeExtractor
     */
    protected $oo;

    public function __construct($cmd, $params)
    {
        parent::__construct();
        $this->cmd = KTUtil::findCommand('externalBinary/' . $cmd, false);

        $config = KTConfig::getSingleton();
        $this->params = $config->get('indexer/' . $cmd . 'cmdline', $params);
        $this->useOO = false; //$config->get('indexer/useOpenOffice', true);
        if (!$config->get('indexer/use_' . $cmd, true) || OS_WINDOWS)
        {
            $this->cmd = false;
        }

        if ($this->useOO)
        {
//            require_once('extractors/StarOfficeExtractor.inc.php');
//            $this->oo = new StarOfficeExtractor();
        }
    }

	public function needsIntermediateSourceFile()
	{
		// we need the intermediate file because it
		// has the correct extension. documentConverter uses the extension to determine mimetype

		return ($this->useOO);
	}

	protected function getCommandLine()
	{
		$sourcefile = $this->sourcefile;
		$targetfile = $this->targetfile;
		$escape = '"';

        $cmd = $this->cmd;

		$cmdline = $this->params;
		$cmdline = eval("return \"$cmdline\";");

		$cmdline = str_replace('\\','/',$cmdline);

		return $cmdline;
	}


    public function extractTextContent()
    {
        if ($this->cmd !== false)
        {
            // so we have catppt or something
            $result = parent::extractTextContent();
            if ($result !== false)
            {
                // if it returns true, we can bail
                return true;
            }

            // if failure, fallthrough, and attempt OO
        }

        /*
        if ($this->useOO)
        {
            $this->oo->setSourceFile($this->sourcefile);
            $this->oo->setMimeType($this->mimetype);
            $this->oo->setExtension($this->extension);
            $this->oo->setTargetFile($this->targetfile);
            $this->oo->setDocument($this->document);
            $this->oo->setIndexingStatus(null);
            $this->oo->setExtractionStatus(null);

            $result = $this->oo->extractTextContent();

            $this->setIndexingStatus($this->oo->getIndexingStatus());
            $this->setExtractionStatus($this->oo->getExtractionStatus());
            $this->setTargetFile($this->oo->getTargetFile());

            return $result;
        }
        else
        {
        */
            global $default;
            $docId = $this->document->getId();
            $cmd = $this->cmd;
            $default->log->info("The document {$docId} cannot be indexed as {$cmd} is not available and OpenOffice is not in use.");
            file_put_contents($this->targetfile, '');
            return true;
        //}
    }

    public function diagnose()
    {
        if ($this->cmd !== false) // || !$this->useOO)
        {
            // cmd is found. we don't care about oo.
            // if we can't use oo, well, not much we can do....
            return null;
        }

        return false; //$this->oo->diagnose();
    }
}

/**
 * An extension to the extenal document extractor. A derived class simply needs
 * to implement a constructor and getSupportedMimeTypes().
 *
 */
abstract class ApplicationExtractor extends ExternalDocumentExtractor
{
	/**
	 * The full path to the application that will be run. This will be resolved from
	 * the path or using the config file.
	 *
	 * @var string
	 */
	private $application;
	/**
	 * The command name of the application that can be run.
	 *
	 * @var string
	 */
	private $command;
	/**
	 * This is the friendly name for the extractor.
	 *
	 * @var string
	 */
	private $displayname;
	/**
	 * The command line parameters for the application.
	 * This may include {source} and {target} where substitutions will be done.
	 *
	 * @var string
	 */
	private $params;

	/**
	 * Initialise the extractor.
	 *
	 * @param string $section The section in the config file.
	 * @param string $appname The application name in the config file.
	 * @param string $command The command that can be run.
	 * @param string $displayname
	 * @param string $params
	 */
	public function __construct($section, $appname, $command, $displayname, $params)
	{
		parent::__construct();

		$this->application = KTUtil::findCommand("$section/$appname", $command);
		$this->command = $command;
		$this->displayname = $displayname;
		$this->params = $params;
	}

	/**
	 * Return the display name.
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return sprintf(_kt('%s') , $this->displayname);
	}

	/**
	 * Returns the command line after performing substitutions.
	 *
	 * @return unknown
	 */
	protected function getCommandLine()
	{
		$sources = array('{source}','{target}');
		$target = array($this->sourcefile, $this->targetfile);
		$escape = OS_WINDOWS?'"':'\'';
		$cmdline = $escape . $this->application . $escape . ' ' . str_replace($sources,$target, $this->params);

		return $cmdline;
	}

	/**
	 * Identifies if there are any circumstances why the command can not run that could result in the text extraction process
	 * failing.
	 *
	 * @return mixed Returns string if there is a problem, null otherwise.
	 */
	public function diagnose()
	{
		if (false === $this->application)
		{
			return sprintf(_kt("Cannot locate binary for %s (%s)."), $this->displayname, $this->command);
		}

		return null;
	}
}

abstract class TextExtractor extends DocumentExtractor
{
	/**
	 * This extracts the text from the document.
	 *
	 * @return boolean
	 */
	public function extractTextContent()
	{
	    $config = KTConfig::getSingleton();
		$maxTextSize = $config->get('indexer/maxTextSize', 1024 * 1024 * 10); // we'll only take 10 meg by default
		$content = file_get_contents($this->sourcefile, null, null, null, $maxTextSize);
		if (false === $content)
		{
			return false;
		}

		$result = file_put_contents($this->targetfile, $this->filter($content));

		return false !== $result;
	}

	/**
	 * There are no external dependancies to diagnose.
	 *
	 * @return null
	 */
	public function diagnose()
	{
		return null;
	}

}

/**
 * The composite extractor implies that a conversion is done to an intermediate form before another extractor is run.
 *
 */
abstract class CompositeExtractor extends DocumentExtractor
{
	/**
	 * The initial extractor
	 *
	 * @var DocumentExtractor
	 */
	private $sourceExtractor;
	/**
	 * The text extractor
	 *
	 * @var DocumentExtractor
	 */
	private $targetExtractor;
	/**
	 * The extension for the initial extraction
	 *
	 * @var string
	 */
	private $targetExtension;
	/**
	 * The mime type of the initial extraction.
	 *
	 * @var string
	 */
	private $targetMimeType;

	public function __construct($sourceExtractor, $targetExtension, $targetMimeType, $targetExtractor, $needsIntermediate)
	{
		$this->sourceExtractor = $sourceExtractor;
		$this->targetExtractor = $targetExtractor;
		$this->targetExtension = $targetExtension;
		$this->targetMimeType = $targetMimeType;
		$this->needsIntermediateSourceFile($needsIntermediate);
	}

	/**
	 * Extracts the content of the document
	 *
	 * @return string
	 */
	public function extractTextContent()
	{
		$intermediateFile = $this->targetfile . '.' . $this->targetExtension;
		touch($intermediateFile);

		$this->sourceExtractor->setSourceFile($this->sourcefile);
		$this->sourceExtractor->setTargetFile($intermediateFile);
		$this->sourceExtractor->setDocument($this->getDocument());
		$this->sourceExtractor->setMimeType($this->mimetype);
		$this->sourceExtractor->setExtension($this->extension);
		if (!$this->sourceExtractor->extractTextContent())
		{
			$this->output = $this->sourceExtractor->output;
			@unlink($intermediateFile);
			return false;
		}
		$intermediateFile = $this->sourceExtractor->getTargetFile();

		$this->targetExtractor->setSourceFile($intermediateFile);
		$this->targetExtractor->setTargetFile($this->targetfile);
		$this->targetExtractor->setDocument($this->getDocument());
		$this->targetExtractor->setMimeType($this->targetMimeType);
		$this->targetExtractor->setExtension($this->targetExtension);
		$result = $this->targetExtractor->extractTextContent();
		if (!$result)
		{
			$this->output = $this->targetExtractor->output;
		}

		@unlink($intermediateFile);
		$this->setTargetFile($this->targetExtractor->getTargetFile());

		return $result;
	}

	/**
	 * Diagnose the extractors
	 *
	 * @return mixed
	 */
	public function diagnose()
	{
		$diagnosis = $this->sourceExtractor->diagnose();
		if (!empty($diagnosis))
		{
			return $diagnosis;
		}

		$diagnosis = $this->targetExtractor->diagnose();
		if (!empty($diagnosis))
		{
			return $diagnosis;
		}

		return null;
	}
}


/**
 * The purpose of an extractor hook is to effect the
 *
 */
abstract class ExtractorHook
{
	/**
	 * Returns an array of supported mime types.
	 * e.g. return array('plain/text');
	 *
	 *
	 * @return array
	 *
	 */
	public abstract function getSupportedMimeTypes();

	/**
	 * Returns the friendly name for the hook.
	 *
	 * @return string
	 */
	public abstract function getDisplayName();

	/**
	 * This does a basic diagnosis on the hook.
	 *
	 * @return string
	 */
	public function diagnose()
	{
		return null;
	}

	/**
	 * Perform any pre extraction activities.
	 *
	 * @param DocumentExtractor $extractor
	 */
	public function pre_extract($extractor)
	{
	}

	/**
	 * Perform any post extraction activities.
	 *
	 * @param DocumentExtractor $extractor
	 */
	public function post_extract($extractor)
	{

	}

	/**
	 * Perform any pre indexing activities.
	 *
	 * @param DocumentExtractor $extractor
	 */
	public function pre_index($extractor)
	{

	}

	/**
	 * Perform any post indexing activities.
	 *
	 * @param DocumentExtractor $extractor
	 */
	public function post_index($extractor)
	{

	}
}

?>
