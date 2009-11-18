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
 *
 */

class KTErrorViewerRegistry {
    var $aViewers = array();

    static function &getSingleton() {
    	static $singleton = null;
    	if (is_null($singleton))
    	{
    		$singleton = new KTErrorViewerRegistry();
    	}
    	return $singleton;
    }

    function register($sViewerClassName, $sHandledClass) {
        $this->aViewers[strtolower($sHandledClass)] = $sViewerClassName;
    }

    function getViewer($oError) {
        $sErrorClass = strtolower(get_class($oError));

        // Try for direct hit first
        $sClass = $sErrorClass;
        $sHandlerClass = KTUtil::arrayGet($this->aViewers, $sClass);
        if ($sHandlerClass) {
            return new $sHandlerClass($oError);
        }

        // PHP 4.0.5 added get_parent_class - this offers us the
        // best/first match
        if (function_exists('get_parent_class')) {
            while ($sClass = get_parent_class($sClass)) {
                $sHandlerClass = KTUtil::arrayGet($this->aViewers, $sClass);
                if ($sHandlerClass) {
                    return new $sHandlerClass($oError);
                }
            }
        }

        // Now try things the hard way - no best/first match (ick!)
        // Reverse, since hopefully the best/first match will be added
        // after the other matches.
        foreach (array_reverse($this->aViewers) as $sHandlerClass => $sClass) {
            if (is_a($oError, $sClass)) {
                return new $sHandlerClass($oError);
            }
        }

        // Just in case we have an unhandled i18n-friendly error
        if (method_exists($oError, 'geti18nMessage')) {
            return new KTStringErrorViewer($oError->getMessage());
        }

        // PEAR_Error should have caught things above, but just in case,
        // check if getMessage is there, and use that:
        if (method_exists($oError, 'getMessage')) {
            return new KTStringErrorViewer($oError->getMessage());
        }

        // Check if we are a string
        if (is_string($oError)) {
            return new KTStringErrorViewer($oError);
        }

        // Give up.
        return new KTStringErrorViewer(_kt("Unknown error"));
    }
}

$oEVRegistry =& KTErrorViewerRegistry::getSingleton();
class KTErrorViewer {
    function KTErrorViewer($oError) {
        $this->oError = $oError;
    }
    function view() {
        return $this->oError->getMessage();
    }

    function viewFull() {
        return $this->oError->toString();
    }

    function page() {
        $ret  = "<h2>Error</h2>\n\n";
        $ret .= "<dl>\n";
        $ret .= "\t<dt>Error type</dt>\n";
        $ret .= "\t<dd>" . $this->oError->getMessage() . "</dd>\n";
        $sInfo = $this->parseUserInfo();
        if ($sInfo) {
            $ret .= "\t<dt>Additional information</dt>\n";
            $ret .= "\t<dd>" . $sInfo . "</dd>\n";
        }
        $ret .= "</dl>\n";
        return $ret;
    }

    function parseUserInfo() {
        $sUserInfo = $this->oError->getUserInfo();
        return $sUserInfo;
    }
}
$oEVRegistry->register("KTErrorViewer", "PEAR_Error");

class KTDBErrorViewer extends KTErrorViewer {
    function view() {
        return _kt("Database error") . ": " . $this->oError->getMessage();
    }

    function page() {
        $ret  = "<h2>Database Error</h2>\n\n";
        $ret .= "<dl>\n";
        $ret .= "\t<dt>Error type</dt>\n";
        $ret .= "\t<dd>" . $this->oError->getMessage() . "</dd>\n";
        $sInfo = $this->parseUserInfo();

        if ($sInfo) {
            $ret .= "\t<dt>Additional information</dt>\n";
            $ret .= "\t<dd>" . $sInfo . "</dd>\n";
        }
        $ret .= "</dl>\n";
        return $ret;
    }

    function parseUserInfo() {
        $sUserInfo = $this->oError->getUserInfo();
        $aMatches = array();

        if (preg_match("#^ ?\[nativecode=(Can't connect to local.*) \(13\)#", $sUserInfo, $aMatches)) {
            return $aMatches[1];
        }
        return $sUserInfo;
    }
}
$oEVRegistry->register("KTDBErrorViewer", "DB_Error");

class KTStringErrorViewer extends KTErrorViewer {
    function view() {
        return $this->oError;
    }

    function viewFull() {
        return $this->oError;
    }
}
