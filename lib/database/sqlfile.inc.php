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

class SQLFile {
    function sqlFromFile($path) {
        return SQLFile::splitSQL(file_get_contents($path));
    }

    function splitSQL($sql) {
        $instring = false;
        $i = 0;
        $remaining = $sql;
        $query = "";
        $aQueries = array();

        $strlen = strlen($sql);

        for ($i = 0; $i < $strlen; $i++) {
            $c = $remaining[$i];
            if ($c === ";") {
                $query .= substr($remaining, 0, $i + 1);
                $aQueries[] = $query;
                $query = "";
                $remaining = trim(substr($remaining, $i + 1));
                $i = 0;
                $strlen = strlen($remaining);
                continue;
            }
            if ($c === "`") {
                $next = strpos($remaining, "`", $i);
                if ($next === false) {
                    $query .= $remaining;
                    $aQueries[] = $query;
                    return $aQueries;
                }
                $query .= substr($remaining, 0, $next);
                $remaining = substr($remaining, $next);
                $i = 0;
                $strlen = strlen($remaining);
                continue;
            }
            if (($c === "'") || ($c === '"')) {
                $stringchar = $c;
                $notfound = true;
                
                while ($notfound) {
                    $next = strpos($remaining, $stringchar, $i + 1);
                    if ($next === false) {
                        $query .= $remaining;
                        $aQueries[] = $query;
                        return $aQueries;
                    }
                    $i = $next + 1;
                    $quotes = true;
                    $b = 1;
                    while ($remaining[$next - $b] === "\\") {
                        $quotes = !$quotes;
                        $b++;
                    }
                    if ($quotes) {
                        $notfound = false;
                    }
                }
                $query .= substr($remaining, 0, $next);
                $remaining = substr($remaining, $next);
                $i = 0;
                $strlen = strlen($remaining);
                continue;
            }
            
            $nextdelim = SQLFile::_nextDelim($remaining);
            if ($nextdelim === false) {
                $query .= $remaining;
                $aQueries[] = $query;
                return $aQueries;
            }
            // $query .= substr($remaining, 0, $nextdelim);
        }
        return $aQueries;
    }

    function _nextDelim($string) {
        $q = strpos($string, "'");
        $d = strpos($string, '"');
        $b = strpos($string, "`");
        $s = strpos($string, ";");

        $min = false;
        foreach (array($q, $d, $b, $s) as $c) {
            if ($min === false) {
                $min = $c;
                continue;
            }
            if ($c === false) {
                continue;
            }
            if ($c < $min) {
                $min = $c;
                continue;
            }
        }
        return $min;
    }
}

?>
