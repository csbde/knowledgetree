<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * File::Gettext
 *
 * PHP versions 4 and 5
 *
 * @category   FileFormats
 * @package    File_Gettext
 * @author     Michael Wallner <mike@php.net>
 * @copyright  2004-2005 Michael Wallner
 * @license    BSD, revised
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/File_Gettext
 */

/**
 * Requires File_Gettext
 */
require_once 'File/Gettext.php';

/**
 * File_Gettext_PO
 *
 * GNU PO file reader and writer.
 *
 * @author      Michael Wallner <mike@php.net>
 * @version     $Revision$
 * @access      public
 */
class File_Gettext_PO extends File_Gettext
{
    /**
     * Constructor
     *
     * @access  public
     * @return  object      File_Gettext_PO
     * @param   string      path to GNU PO file
     */
    function File_Gettext_PO($file = '')
    {
        $this->file = $file;
    }

    /**
     * Load PO file
     *
     * @access  public
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     * @param   string  $file
     */
    function load($file = null)
    {
        if (!isset($file)) {
            $file = $this->file;
        }

        // load file
        if (!$contents = @file($file)) {
            return parent::raiseError($php_errormsg . ' ' . $file);
        }

        $msgid = null;
        $aMatches = array();

        foreach ($contents as $line) {
            /*
            Replaced the regular expressions to get translations working on windows.
            */
            if (preg_match('/^msgid(.*)$/', $line, $aMatches)) {
                if ($msgid) {
                    $this->strings[parent::prepare($msgid)] = parent::prepare($msgstr);
                }
                $msgid = trim($aMatches[1]);
				$msgid = substr($msgid, 1, strlen($msgid) - 2);
                $msgstr = "";
                $msgstr_started = false;
            }
			//#^msgstr "(.*)"$#
            if (preg_match('/^msgstr(.*)$/', $line, $aMatches)) {
                $msgstr = trim($aMatches[1]);
				$msgstr = substr($msgstr, 1, strlen($msgstr) - 2);
                $msgstr_started = true;
            }
			//#^"(.*)"$#
            if (preg_match('/^"(.*)"$/', $line, $aMatches)) {
                if ($msgstr_started) {
                    $tmp = trim($aMatches[1]);
					$msgstr .= substr($tmp, 1, strlen($tmp) - 2);
                } else {
                    $tmp = trim($aMatches[1]);
					$msgid .= substr($tmp, 1, strlen($tmp) - 2);
                }
            }
            /* Original code
            if (preg_match('#^msgid "(.*)"$#', $line, $aMatches)) {
                if ($msgid) {
                    $this->strings[parent::prepare($msgid)] = parent::prepare($msgstr);
                }
                $msgid = $aMatches[1];
                $msgstr = "";
                $msgstr_started = false;
            }
            if (preg_match('#^msgstr "(.*)"$#', $line, $aMatches)) {
                $msgstr = $aMatches[1];
                $msgstr_started = true;
            }
            if (preg_match('#^"(.*)"$#', $line, $aMatches)) {
                if ($msgstr_started) {
                    $msgstr .= $aMatches[1];
                } else {
                    $msgid .= $aMatches[1];
                }
            }
            */
        }
        if ($msgid) {
            $this->strings[parent::prepare($msgid)] = parent::prepare($msgstr);
        }

        // check for meta info
        if (isset($this->strings[''])) {
            $this->meta = parent::meta2array($this->strings['']);
            unset($this->strings['']);
        }

        return true;
    }

    /**
     * Save PO file
     *
     * @access  public
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     * @param   string  $file
     */
    function save($file = null)
    {
        if (!isset($file)) {
            $file = $this->file;
        }

        // open PO file
        if (!is_resource($fh = @fopen($file, 'w'))) {
            return parent::raiseError($php_errormsg . ' ' . $file);
        }
        // lock PO file exclusively
        if (!@flock($fh, LOCK_EX)) {
            @fclose($fh);
            return parent::raiseError($php_errmsg . ' ' . $file);
        }

        // write meta info
        if (count($this->meta)) {
            $meta = 'msgid ""' . "\nmsgstr " . '""' . "\n";
            foreach ($this->meta as $k => $v) {
                $meta .= '"' . $k . ': ' . $v . '\n"' . "\n";
            }
            fwrite($fh, $meta . "\n");
        }
        // write strings
        foreach ($this->strings as $o => $t) {
            fwrite($fh,
                'msgid "'  . parent::prepare($o, true) . '"' . "\n" .
                'msgstr "' . parent::prepare($t, true) . '"' . "\n\n"
            );
        }

        //done
        @flock($fh, LOCK_UN);
        @fclose($fh);
        return true;
    }
}
?>
