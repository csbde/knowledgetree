<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
