<?php

/**
 * Index page for CMIS AtomPub services
 */

include_once('lib/cmis/KTCMISAPPServiceDoc.inc.php');
include_once('lib/cmis/KTCMISAPPFeed.inc.php');

define ('CMIS_BASE_URI', trim(KT_APP_BASE_URI, '/') . 'cmis/');
// hack for links not yet working in KT, use Alfresco installation to move things forward
//define ('CMIS_BASE_URI_ALF', 'http://127.0.0.1:8080/alfresco/service/api/');

// fetch username and password for auth;  note that this apparently only works when PHP is run as an apache module
// TODO method to fetch username and password when running PHP as CGI, if possible
// HTTP Basic Auth:
$username = $_SERVER['PHP_AUTH_USER'];
$password = $_SERVER['PHP_AUTH_PW'];

// TODO response if failed auth, need generic response which can be used by all code

$arg = (isset($query[1]) ? $query[1] : '');

switch($arg)
{
    case 'checkedout':
        include('services/cmis/checkedout.inc.php');
        break;
    case 'document':
        include('services/cmis/document.inc.php');
        break;
    case 'folder':
        include('services/cmis/folder.inc.php');
        break;
    case 'type':
    case 'types':
        include('services/cmis/types.inc.php');
        break;
    case 'repository':
    default:
        include('services/cmis/servicedocument.inc.php');
        break;
}

?>
