<?php

/**
 * $Id: $
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

class HouseKeeper
{
    public static
    function getDiskUsageStats($update = true)
    {
        $config = KTConfig::getSingleton();

        $cmd = KTUtil::findCommand('externalBinary/df','df');
        if ($cmd === false)
        {
            if ($update)
            KTUtil::setSystemSetting('DiskUsage','n/a');
            return false;
        }


        $warningPercent = $config->get('DiskUsage/warningThreshold', 15);
        $urgentPercent = $config->get('DiskUsage/urgentThreshold', 5);

        if (OS_WINDOWS)
        {
            $cmd = str_replace( '/','\\',$cmd);
            $res = KTUtil::pexec("\"$cmd\" -B 1 2>&1");
            $result = implode("\r\n",$res['out']);
        }
        else
        {
            if(strtolower(PHP_OS) == 'darwin'){
                $result = shell_exec($cmd." -k 2>&1");
            }else{
                $result = shell_exec($cmd." -B 1 2>&1");
            }
        }

        if (strpos($result, 'cannot read table of mounted file systems') !== false)
        {
            if ($update)
            KTUtil::setSystemSetting('DiskUsage','n/a');
            return false;
        }

        $result = explode("\n", $result);

        unset($result[0]); // gets rid of headings

        $usage=array();
        foreach($result as $line)
        {
            if (empty($line)) continue;
            preg_match('/(.*)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\%\s+(.*)/', $line, $matches);
            list($line, $filesystem, $size, $used, $avail, $usedp, $mount) = $matches;

            if ($size === 0 || empty($size)) continue;

            if(strtolower(PHP_OS) == 'darwin'){
                $size = $size * 1024;
                $used = $used * 1024;
                $avail = $avail * 1024;
            }

            $colour = '';
            if ($usedp >= 100 - $urgentPercent)
            {
                $colour = 'red';
            }
            elseif ($usedp >= 100 - $warningPercent)
            {
                $colour = 'orange';
            }

            $usage[] = array(
            'filesystem'=>trim($filesystem),
            'size'=>KTUtil::filesizeToString($size),
            'used'=>KTUtil::filesizeToString($used),
            'available'=>KTUtil::filesizeToString($avail),
            'usage'=>$usedp . '%',
            'mounted'=>trim($mount),
            'colour'=>$colour
            );
        }

        if ($update)
        KTUtil::setSystemSetting('DiskUsage',serialize($usage));

        return $usage;
    }

    private static
    function scanPath($path,$pattern)
    {
        $files=0;
        $filesize=0;

        if (is_dir($path) && ($dh = opendir($path)))
        {
            while (($file = readdir($dh)) !== false)
            {
                if (substr($file,0,1) == '.')
                {
                    continue;
                }

                $full = $path . '/' . $file;

                if (!is_readable($full) || !is_writable($full))
                {
                    continue;
                }

                if (is_dir($full))
                {
                    $result = self::scanPath($full,$pattern);
                    $files += $result['files'];
                    $filesize += $result['filesize'];
                    continue;
                }
                if ($pattern != '')
                {
                    if (preg_match('/' . $pattern . '/', $file) === false)
                    {
                        continue;
                    }
                }

                $files++;
                $filesize += filesize($full);
            }
            closedir($dh);
        }
        return array('files'=>$files,'filesize'=>$filesize,'dir'=>$path);
    }



    private static
    function getDirectories()
    {
        $config = KTConfig::getSingleton();
        $cacheDir = $config->get('cache/cacheDirectory');

        $tempDir = $config->get('urls/tmpDirectory');
        $logDir = $config->get('urls/logDirectory');
        $docsDir = $config->get('urls/documentRoot');

        $indexer = Indexer::get();
        $luceneDir = $indexer->getIndexDirectory();

        $systemDir = OS_UNIX?'/tmp':'c:/windows/temp';

        $folders = array(
        array(
        'name'=>_kt('Smarty Cache'),
        'folder'=>$tempDir,
        'pattern'=>'^%%.*',
        'canClean'=>true
        ),
        array(
        'name'=>_kt('System Cache'),
        'folder'=>$cacheDir,
        'pattern'=>'',
        'canClean'=>true
        ),
        array(
        'name'=>_kt('System Logs'),
        'folder'=>$logDir,
        'pattern'=>'.+\.txt$',
        'canClean'=>true
        ));

        $folders[] =
        array(
        'name'=>_kt('Temporary Folder'),
        'folder'=>$tempDir,
        'pattern'=>'',
        'canClean'=>true
        );

        $folders[] =
        array(
        'name'=>_kt('System Temporary Folder'),
        'folder'=>$systemDir,
        'pattern'=>'(sess_.+)?(.+\.log$)?',
        'canClean'=>true
        );

        if (is_dir($docsDir))
        {
            $folders[] =
            array(
            'name'=>_kt('Documents'),
            'folder'=>$docsDir,
            'pattern'=>'',
            'canClean'=>false
            );
        }

        if (is_dir($luceneDir))
        {
            $folders[] =
            array(
            'name'=>_kt('Document Index'),
            'folder'=>$luceneDir,
            'pattern'=>'',
            'canClean'=>false
            );

        }
        return $folders;
    }


    public static
    function getKTUsageStats($update = true)
    {
        $usage = array();

        $oRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oRegistry->getPlugin('ktcore.housekeeper.plugin');

        $folders = self::getDirectories();

        foreach($folders as $folder)
        {
            $directory 	= $folder['folder'];
            $pattern 	= $folder['pattern'];
            $canClean 	= $folder['canClean'];
            $name 		= $folder['name'];

            $temp = self::scanPath($directory,$pattern);

            $usage[] = array(
            'description'=>$name,
            'folder'=>$directory,
            'files'=>number_format($temp['files'],0,'.',','),
            'filesize'=>KTUtil::filesizeToString($temp['filesize']),
            'action'=>$i,
            'canClean'=>$canClean
            );
        }

        if ($update)
        KTUtil::setSystemSetting('KTUsage',serialize($usage));
        return $usage;
    }

    private static $folders = null;

    public static
    function getDirectory($folder)
    {
        if (is_null(self::$folders))
        {
            self::$folders = self::getDirectories();
        }
    	foreach(self::$folders as $dir)
    	{
    		if ($dir['folder'] == $folder)
    		{
    			return $dir;
    		}
    	}
    	return null;
    }


    public static
    function cleanDirectory($path, $pattern)
    {
        if (!is_readable($path))
        {
            return;
        }
        if ($dh = opendir($path))
        {
            while (($file = readdir($dh)) !== false)
            {
                if (substr($file,0,1) == '.')
                {
                    continue;
                }

                $full = $path . '/' . $file;
                if (is_dir($full))
                {
                    self::cleanDirectory($full,$pattern);
                    if (is_writable($full))
                    {
                        @rmdir($full);
                    }
                    continue;
                }

                if (!empty($pattern) && !preg_match('/' . $pattern . '/', $file))
                {
                    continue;
                }

                if (is_writable($full))
                {
                    @unlink($full);
                }

            }
            closedir($dh);
        }
    }

}


?>
