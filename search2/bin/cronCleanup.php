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

chdir(dirname(__FILE__));
require_once(realpath('../../config/dmsDefaults.php'));

$config = KTConfig::getSingleton();
$temp_dir =$config->get("urls/tmpDirectory");

cleanupTempDirectory($temp_dir);

function cleanupTempDirectory($dir, $force = false)
{
    $dir = str_replace('\\','/', $dir);

    if (strpos($dir, '/tmp') === false) return;

    $dh = opendir($dir);
    while (($name = readdir($dh)) !== false)
    {
        if (substr($name, 0, 1) == '.') continue;

        $kti = (substr($name, 0, 3) == 'kti');   // remove files starting with kti (indexer temp files created by open office)

        $fullname = $dir . '/' . $name;

        if (!$kti && !$force)
        {
            $info = stat($fullname);
            if ($info['ctime'] >= time() - 24 * 60 * 60) continue; // remove files that have been accessed in the last 24 hours
        }

        if (is_dir($fullname))
        {
            cleanupTempDirectory($fullname, true);
            rmdir($fullname);
        }
        else
        {
            unlink($fullname);
        }

    }
    closedir($dh);
}


exit;
?>