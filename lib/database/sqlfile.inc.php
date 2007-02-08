<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
