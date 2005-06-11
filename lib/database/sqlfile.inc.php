<?php

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
