<?php

/**
 *
 * $Id:
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
 */

/*
* Script to collect system information as part of a call home mechanism, no identifying information is stored.
*
* The following data is collected:
* Unique installation information: installation GUID, number of users in repository, number of documents in repository,
* operating system (platform, platform version, flavor if Linux), version and edition.
*/
chdir(realpath(dirname(__FILE__)));
require_once('../config/dmsDefaults.php');

global $default;
$default->log->debug('System information collection script starting...');

// Get installation guid
function getGuid()
{
    $guid = KTUtil::getSystemIdentifier();

    if(PEAR::isError($guid)){
        $guid = '';
    }
    return $guid;
}

// Get the number of users in the repository
function getUserCnt()
{
    $query = 'select count(*) as cnt, disabled from users where id > 0 group by disabled;';
    $result = DBUtil::getResultArray($query);

    if(empty($result) || PEAR::isError($result)){
        return '';
    }
    $users = '';

    foreach ($result as $row){
        $str = '';
        switch($row['disabled']){
            case 0: $str = 'Enabled'; break;
            case 1: $str = 'Disabled'; break;
            case 2: $str = 'Deleted'; break;
        }

        $str .= ': '.$row['cnt'];

        $users .= (!empty($users)) ? '; ' : '';
        $users .= $str;
    }
    return $users;
}

// Get the number of documents in the repository
function getDocCnt()
{
    $query = 'select count(*) as cnt, s.name from documents d, status_lookup s WHERE s.id = d.status_id group by d.status_id;';
    $result2 = DBUtil::getResultArray($query);

    if(empty($result2) || PEAR::isError($result2)){
        return '';
    }
    $docs = '';

    foreach ($result2 as $row){
        $docs .= (!empty($docs)) ? '; ' : '';
        $docs .= $row['name'].': '.$row['cnt'];
    }
    return $docs;
}

// Get the version of KT
function getKTVersion()
{
    $version = KTUtil::getSystemSetting('knowledgeTreeVersion');
    if(empty($version) || PEAR::isError($version)){
        $version = file_get_contents(KT_DIR . 'docs/VERSION.txt');
    }
    // remove newline that is in the version file
    $version = str_replace("\n", '', $version);
    return $version;
}

// Get the edition of KT
function getKTEdition()
{
    $edition = 'Community';
    if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
        $path = KTPluginUtil::getPluginPath('ktdms.wintools');
        require_once($path .  'baobabkeyutil.inc.php');
        $edition = BaobabKeyUtil::getName();

        // Remove the brackets around the name
        $edition = substr($edition, 1);
        $edition = substr($edition, 0, strlen($edition)-1);
    }
    return $edition;
}


// Get OS info - platform, version, linux flavour
function getOSInfo()
{
    $server = php_uname();

    if(strpos($server, 'Darwin') !== false){
        $os = 'Mac OS X';
    }else if(strpos($server, 'Win') !== false){
        $os = 'Windows';
    }else {
        $os = 'Linux';
    }

    return $os;
}

function sendForm($data)
{
    $url = 'http://ktnetwork.knowledgetree.com/call_home.php';
    //$url = 'http://10.33.20.250/knowledgetree/call_home.php';
    $data = http_build_query($data);

	$ch = curl_init($url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_exec($ch);
	curl_close($ch);
}

$post_str = getGuid() .'|'. getUserCnt() .'|'. getDocCnt() .'|'. getKTVersion() .'|'. getKTEdition() .'|'. getOSInfo();
$data['system_info'] = $post_str;

sendForm($data);

$default->log->debug('System information collection script finishing.');
exit(0);
?>
