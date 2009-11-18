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

require_once 'HTTP/WebDAV/Server.php'; // thirdparty PEAR
require_once 'Config.php';             // thirdparty PEAR
require_once 'Log.php';                // thirdparty PEAR

$userAgentValue = $_SERVER['HTTP_USER_AGENT'];
if (stristr($userAgentValue, "Microsoft Data Access Internet Publishing Provider DAV")) {
    // Fix for Novell Netdrive
    chdir(realpath(dirname(__FILE__)));
    require_once '../../config/dmsDefaults.php'; // This is our plug into KT.
}else{
    require_once '../config/dmsDefaults.php'; // This is our plug into KT.
}

DEFINE('STATUS_WEBDAV', 5);  // Status code to handle 0 byte PUT    FIXME: Do we still need this!

/**
 * KnowledgeTree access using WebDAV protocol
 *
 * @access public
 */
class KTWebDAVServer extends HTTP_WebDAV_Server
{
    /**
     * String to be used in "X-Dav-Powered-By" header
     *
     * @var string
     */
    var $dav_powered_by = 'KTWebDAV (1.0.0)';

    /**
     * Realm string to be used in authentication
     *
     * @var string
     */
    var $http_auth_realm = 'KTWebDAV Server';

    /**
     * Path to KT install root
     *
     * @var string
     */
    var $ktdmsPath = '';

    /**
     * Debug Info Toggle
     *
     * @var string
     */
    var $debugInfo = 'off';

    /**
     * Safe Mode Toggle
     *
     * @var string
     */
    var $safeMode = 'on';

    /**
     * Configuration Array
     *
     * @var array
     */
    var $config = array();

    /**
     * Settings Section Configuration Array
     *
     * @var array
     */
    var $settings = array();

    /**
     * Current User ID
     *
     * @var int
     */
    var $userID;

    /**
     * Current Method
     *
     * @var string
     */
    var $currentMethod;

    /**
     * Last Created Folder ID
     *
     * @var string
     */
    var $lastFolderID;

    /**
     * DAV Client
     *
     * @var String
     */
    var $dav_client;

    /**
     * Root Folder Name
     *
     * @var String
     */
    var $rootFolder = 'Root Folder';

    /**
     * Last Message
     *
     * @var String
     */
    var $lastMsg = '';

    /**
     * Constructor
     *
     * @param void
     * @return void
     */
    function KTWebDAVServer() {
        // CGI compatible auth setup
        $altinfo = KTUtil::arrayGet( $_SERVER, 'kt_auth', KTUtil::arrayGet( $_SERVER, 'REDIRECT_kt_auth'));
        if ( !empty( $altinfo) && !isset( $_SERVER['PHP_AUTH_USER'])) {
            $val = $altinfo;
            $pieces = explode( ' ', $val);   // bad.
            if ( $pieces[0] == 'Basic') {
                $chunk = $pieces[1];
                $decoded = base64_decode( $chunk);
                $credential_info = explode( ':', $decoded);
                if ( count( $credential_info) == 2) {
                    $_SERVER['PHP_AUTH_USER'] = $credential_info[0];
                    $_SERVER['PHP_AUTH_PW'] = $credential_info[1];
                    $_SERVER["AUTH_TYPE"] = 'Basic';
                }
            }
        }

        // Let the base class do it's thing
        parent::HTTP_WebDAV_Server();

        // Load KTWebDAV config
        if (!$this->initConfig()) {
            $this->ktwebdavLog('Could not load configuration.', 'error');
            exit(0);
        }

        if ($this->debugInfo == 'on') {

            $this->ktwebdavLog('=====================');
            $this->ktwebdavLog('  Debug Info is : ' . $this->debugInfo);
            $this->ktwebdavLog('    SafeMode is : ' . $this->safeMode);
            $this->ktwebdavLog(' Root Folder is : ' . $this->rootFolder);
            $this->ktwebdavLog('=====================');
        }

    }

    /**
     * Load KTWebDAV configuration from conf file
     *
     * @param void
     * @return bool	true on success
     */
    function initConfig() {

        global $default;
        $oConfig =& KTConfig::getSingleton();

        // Assign Content
        $this->debugInfo = $oConfig->get('KTWebDAVSettings/debug', 'off');
        $this->safeMode = $oConfig->get('KTWebDAVSettings/safemode', 'on');
        $this->rootFolder = $oConfig->get('KTWebDAVSettings/rootfolder', 'Root Folder');
        $this->kt_version = $default->systemVersion;

        return true;
    }

    /**
     * Log to the KTWebDAV logfile
     *
     * @todo Add other log levels for warning, profile, etc
     * @param string    log message
     * @param bool    debug only?
     * @return bool	true on success
     */
    function ktwebdavLog($entry, $type = 'info', $debug_only = false) {

        if ($debug_only && $this->debugInfo != 'on') return false;

        $ident = 'KTWEBDAV';
        $conf = array('mode' => 0644, 'timeFormat' => '%X %x');
        $logger = &Log::singleton('file', '../../var/log/ktwebdav-' . date('Y-m-d') . '.txt', $ident, $conf);
        if ($type == 'error') $logger->log($entry, PEAR_LOG_ERR);
        else $logger->log($entry, PEAR_LOG_INFO);
        return true;
    }

    /**
     * Get the current UserID
     *
     * @access private
     * @param  void
     * @return int userID
     */
    function _getUserID() {
        return $this->userID;
    }

    /**
     * Set the current UserID
     *
     * @access private
     * @param  void
     * @return int UserID
     */
    function _setUserID($iUserID) {
        return $this->userID = $iUserID;
    }

    /**
     * Serve a webdav request
     *
     * @access public
     * @param  void
     * @return void
     */
    function ServeRequest()	{

        global $default;

        if ($this->debugInfo == 'on') {

            $this->ktwebdavLog('_SERVER is ' . print_r($_SERVER, true), 'info', true);
        }

        // Check for electronic signatures - if enabled exit
        $oConfig =& KTConfig::getSingleton();
        $enabled = $oConfig->get('e_signatures/enableApiSignatures', false);
        if($enabled){
            $this->ktwebdavLog('Electronic Signatures have been enabled, disabling WebDAV.', 'info');

            $data = "<html><head><title>KTWebDAV - The KnowledgeTree WebDAV Server</title></head>";
            $data .= "<body>";
            $data .= "<div align=\"center\"><IMG src=\"../resources/graphics/ktlogo-topbar_base.png\" width=\"308\" height=\"61\" border=\"0\"></div><br>";
            $data .= "<div align=\"center\"><h2><strong>Welcome to KnowledgeTree WebDAV Server</strong></h2></div><br><br>";
            $data .= "<div align=\"center\">The WebDAV Server has been disabled!</div><br><br>";
            $data .= "<div align=\"center\">Electronic Signatures are enabled.</div><br><br>";
            $data .= "</body>";

            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/html; charset="utf-8"');
            echo $data;

            exit(0);
        }

        // Get the client info
        $this->checkSafeMode();

        // identify ourselves
        $this->ktwebdavLog('WebDAV Server : ' . $this->dav_powered_by . ' [KT:'.$default->systemVersion."]", 'info', true);
        header('X-Dav-Powered-By: '.$this->dav_powered_by . ' [KT:'.$default->systemVersion.']');

        // check authentication
        if (!$this->_check_auth()) {
            $this->ktwebdavLog('401 Unauthorized - Authorisation failed.' .$this->lastMsg, 'info', true);
            $this->ktwebdavLog('----------------------------------------', 'info', true);
            $this->http_status('401 Unauthorized - Authorisation failed. ' .$this->lastMsg);

            // RFC2518 says we must use Digest instead of Basic
            // but Microsoft Clients do not support Digest
            // and we don't support NTLM and Kerberos
            // so we are stuck with Basic here
            header('WWW-Authenticate: Basic realm="'.($this->http_auth_realm).'"');

            return;
        }

        // check
        if(! $this->_check_if_header_conditions()) {
            $this->http_status('412 Precondition failed');
            return;
        }

        // set path
        $request_uri = $this->_urldecode(!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
        $this->path = str_replace($_SERVER['SCRIPT_NAME'], '', $request_uri);
        if(ini_get('magic_quotes_gpc')) {
            $this->path = stripslashes($this->path);
        }

        $this->ktwebdavLog('PATH_INFO is ' . $_SERVER['PATH_INFO'], 'info', true);
        $this->ktwebdavLog('REQUEST_URI is ' . $_SERVER['REQUEST_URI'], 'info', true);
        $this->ktwebdavLog('SCRIPT_NAME is ' . $_SERVER['SCRIPT_NAME'], 'info', true);
        $this->ktwebdavLog('PHP_SELF is ' . $_SERVER['PHP_SELF'], 'info', true);
        $this->ktwebdavLog('path set to ' . $this->path, 'info', true);

        // detect requested method names
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $wrapper = 'http_'.$method;

        $this->currentMethod = $method;
        // activate HEAD emulation by GET if no HEAD method found
        if ($method == 'head' && !method_exists($this, 'head')) {
            // rfc2068 Sec: 10.2.1
            //HEAD the entity-header fields corresponding to the requested resource
            //     are sent in the response without any message-body
            $method = 'get';
        }
        $this->ktwebdavLog("Entering $method request", 'info', true);

        if (method_exists($this, $wrapper) && ($method == 'options' || method_exists($this, $method))) {
            $this->$wrapper();  // call method by name
        } else { // method not found/implemented
            if ($_SERVER['REQUEST_METHOD'] == 'LOCK') {
                $this->http_status('412 Precondition failed');
            } else {
                $this->http_status('405 Method not allowed');
                header('Allow: '.join(', ', $this->_allow()));  // tell client what's allowed
            }
        }

        $this->ktwebdavLog("Exiting $method request", 'info', true);
    }

    /**
     * check authentication if check is implemented
     *
     * @param  void
     * @return bool  true if authentication succeded or not necessary
     */
    function _check_auth()
    {
        $this->ktwebdavLog('Entering _check_auth...', 'info', true);

        // Workaround for mod_auth when running php cgi
        if(!isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['HTTP_AUTHORIZATION'])){
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        }

        if (method_exists($this, 'checkAuth')) {
            // PEAR style method name
            return $this->checkAuth(@$_SERVER['AUTH_TYPE'],
                    @$_SERVER['PHP_AUTH_USER'],
                    @$_SERVER['PHP_AUTH_PW']);
        } else if (method_exists($this, 'check_auth')) {
            // old (pre 1.0) method name
            return $this->check_auth(@$_SERVER['AUTH_TYPE'],
                    @$_SERVER['PHP_AUTH_USER'],
                    @$_SERVER['PHP_AUTH_PW']);
        } else {
            // no method found -> no authentication required
            return true;
        }
    }

    /**
     * Authenticate user
     *
     * @access private
     * @param  string  HTTP Authentication type (Basic, Digest, ...)
     * @param  string  Username
     * @param  string  Password
     * @return bool    true on successful authentication
     */
    function checkAuth($sType, $sUser, $sPass) {

        $this->ktwebdavLog('Entering checkAuth params are: ', 'info', true);
        $this->ktwebdavLog('sType: ' . $sType, 'info', true);
        $this->ktwebdavLog('sUser: ' . $sUser, 'info', true);
        $this->ktwebdavLog('sPass: ' . $sPass, 'info', true);

        // Authenticate user

        require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

        if ( empty($sUser) ) {
            $this->ktwebdavLog('sUser is empty, returning false.', 'info', true);
            return false;
        }

        if ( empty($sPass) ) {
            $this->ktwebdavLog('sPass is empty, returning false.', 'info', true);
            return false;
        }

        $sUser = iconv('ISO-8859-1', 'UTF-8', $sUser);
        $sPass = iconv('ISO-8859-1', 'UTF-8', $sPass);
        $oUser =& User::getByUsername($sUser);
        if (PEAR::isError($oUser) || ($oUser === false)) {
            $this->ktwebdavLog('User not found: ' . $sUser . '.', 'error');
            $this->lastMsg = 'User not found: ' . $sUser . '.';
            return false;
        }
        $authenticated = KTAuthenticationUtil::checkPassword($oUser, $sPass);

        if ($authenticated === false) {
            $this->ktwebdavLog('Password incorrect for ' . $sUser . '.', 'error');
            $this->lastMsg = 'Password incorrect for ' . $sUser . '.';
            return false;
        }

        if (PEAR::isError($authenticated)) {
            $this->ktwebdavLog('Password incorrect for ' . $sUser . '.', 'error');
            $this->lastMsg = 'Password incorrect for ' . $sUser . '.';
            return false;
        }

        $oUser->setLastLogin(date('Y-m-d H:i:s'));
        $oUser->update();

        $this->ktwebdavLog('Session ID is: '.$sessionID, 'info', true);
        $this->ktwebdavLog('UserID is: ' . $oUser->getId(), 'info', true );
        $this->_setUserID($oUser->getId());
        $_SESSION['userID'] = $this->_getUserID();
        $this->ktwebdavLog('SESSION UserID is: ' . $_SESSION['userID'], 'info', true );

        $this->ktwebdavLog("Authentication Success.", 'info', true);

        return true;
    }

    /**
     * PROPFIND method handler
     *
     * @param  array  options
     * @param  array  return array for file properties
     * @return bool   true on success
     */
    function PROPFIND(&$options, &$files) {

        $this->ktwebdavLog("Entering PROPFIND. options are " . print_r($options, true), 'info', true);

        global $default;

        $fspath = $default->documentRoot . "/" . $this->rootFolder . $options["path"];
        $this->ktwebdavLog("fspath is " . $fspath, 'info', true);

        $path = $options["path"];

        // Fix for the Mac Goliath Client
        // Mac adds DS_Store files when folders are added and ._filename files when files are added
        // The PUT function doesn't add these files to the dms but PROPFIND still looks for the .DS_Store file,
        // and returns an error if not found. We emulate its existence by returning a positive result.
        if($this->dav_client == 'MG'){
            // Remove filename from path
            $aPath = explode('/', $path);
            $fileName = $aPath[count($aPath)-1];

            if(strtolower($fileName) == '.ds_store'){
                $this->ktwebdavLog("Using a Mac client. Filename is .DS_Store so we emulate a positive result.", 'info', true);
                // ignore
                return true;
            }
            if($fileName[0] == '.' && $fileName[1] == '_'){
                $this->ktwebdavLog("Using a Mac client. Filename is ._Filename so we emulate a positive result.", 'info', true);
                // ignore
                return true;
            }
        }

        list($iFolderID, $iDocumentID) = $this->_folderOrDocument($path);
        $this->ktwebdavLog("Folder/Doc is " . print_r(array($iFolderID, $iDocumentID), true), 'info', true);

        // Folder does not exist
        if($iFolderID == '') return false;

        if (is_null($iDocumentID)) {
            return $this->_PROPFINDFolder($options, $files, $iFolderID);
        }
        return $this->_PROPFINDDocument($options, $files, $iDocumentID);

    }

    /**
     * PROPFIND helper for Folders
     *
     * @param array  options
     * @param array  Return array for file props
     * @param int  Folder ID
     * @return bool   true on success
     */
    function _PROPFINDFolder(&$options, &$files, $iFolderID) {

        global $default;

        $this->ktwebdavLog("Entering PROPFINDFolder. options are " . print_r($options, true), 'info', true);

        $folder_path = $options["path"];
        if (substr($folder_path, -1) != "/") {
            $folder_path .= "/";
        }
        $options["path"] = $folder_path;

        $files["files"] = array();
        $files["files"][] = $this->_fileinfoForFolderID($iFolderID, $folder_path);

        $oPerm =& KTPermission::getByName('ktcore.permissions.read');
        $oUser =& User::get($this->userID);

        if (!empty($options["depth"])) {
            $aChildren = Folder::getList(array('parent_id = ?', $iFolderID));
            // FIXME: Truncation Time Workaround
            //foreach (array_slice($aChildren, 0, 50) as $oChildFolder) {
            foreach ($aChildren as $oChildFolder) {
                // Check if the user has permissions to view this folder
                $oFolderDetailsPerm =& KTPermission::getByName('ktcore.permissions.folder_details');

                if(KTPermissionUtil::userHasPermissionOnItem($oUser, $oFolderDetailsPerm, $oChildFolder))
                {
                    $this->ktwebdavLog("Folder Details permissions GRANTED for user ". $_SESSION["userID"] ." on folder " . $oChildFolder->getName(), 'info', true);
                    $files["files"][] = $this->_fileinfoForFolder($oChildFolder, $folder_path . $oChildFolder->getName());
                }
                else
                {
                    $this->ktwebdavLog("Folder Details permissions DENIED for ". $_SESSION["userID"] ." on folder " . $oChildFolder->getName(), 'info', true);
                }
            }
            $aDocumentChildren = Document::getList(array('folder_id = ? AND status_id = 1', $iFolderID));
            // FIXME: Truncation Time Workaround
            //foreach (array_slice($aDocumentChildren, 0, 50) as $oChildDocument) {
            foreach ($aDocumentChildren as $oChildDocument) {
                // Check if the user has permissions to view this document
                if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oChildDocument)) {
                    $this->ktwebdavLog("Read permissions GRANTED for ". $_SESSION["userID"] ." on document " . $oChildDocument->getName(), 'info', true);
                    $files["files"][] = $this->_fileinfoForDocument($oChildDocument, $folder_path . $oChildDocument->getFileName());
                } else $this->ktwebdavLog("Read permissions DENIED for ". $_SESSION["userID"] ." on document " . $oChildDocument->getName(), 'info', true);
            }
        }
        return true;
        }

        /**
         * PROPFIND helper for Documents
         *
         * @param array  options
         * @param array  Return array for file props
         * @param int  Document ID
         * @return bool   true on success
         */
        function _PROPFINDDocument(&$options, &$files, $iDocumentID) {

            global $default;

            $this->ktwebdavLog("Entering PROPFINDDocument. files are " . print_r($files, true), 'info', true);

            $res = $this->_fileinfoForDocumentID($iDocumentID, $options["path"]);
            $this->ktwebdavLog("_fileinfoForDocumentID result is " . print_r($res, true), 'info', true);
            if ($res === false) {
                return false;
            }
            $files["files"] = array();
            $files["files"][] = $res;
            return true;
        }

        /**
         * PROPFIND helper for Document Info
         *
         * @param Document  Document Object
         * @param string    Path
         * @return array    Doc info array
         */
        function _fileinfoForDocument(&$oDocument, $path) {

            global $default;

            $this->ktwebdavLog("Entering _fileinfoForDocument. Document is " . print_r($oDocument, true), 'info', true);

            $fspath = $default->documentRoot . "/" . $this->rootFolder . $path;
            $this->ktwebdavLog("fspath is " . $fspath, 'info', true);

            // create result array
            // Modified - 25/10/07 - spaces prevent files displaying in finder
            if($this->dav_client == 'MC'){
                $path = str_replace('%2F', '/', urlencode($path));
            }
            $path = str_replace('&', '%26', $path);

            $info = array();
            $info["path"]  = $path;
            $info["props"] = array();

            // no special beautified displayname here ...
            $info["props"][] = $this->mkprop("displayname", $oDocument->getName());

            // creation and modification time
            $info["props"][] = $this->mkprop("creationdate", strtotime($oDocument->getCreatedDateTime()));
            $info["props"][] = $this->mkprop("getlastmodified", strtotime($oDocument->getVersionCreated()));

            // plain file (WebDAV resource)
            $info["props"][] = $this->mkprop("resourcetype", '');
            // FIXME: Direct database access
            $sQuery = array("SELECT mimetypes FROM $default->mimetypes_table WHERE id = ?", array($oDocument->getMimeTypeID()));
            $res = DBUtil::getOneResultKey($sQuery, 'mimetypes');
            $info["props"][] = $this->mkprop("getcontenttype", $res);

            $info["props"][] = $this->mkprop("getcontentlength", $oDocument->getFileSize());

            // explorer wants these?
            $info["props"][] = $this->mkprop("name", '');
            $info["props"][] = $this->mkprop("parentname", '');
            $info["props"][] = $this->mkprop("href", '');
            $info["props"][] = $this->mkprop("ishidden", '');
            $info["props"][] = $this->mkprop("iscollection", '');
            $info["props"][] = $this->mkprop("isreadonly", '');
            $info["props"][] = $this->mkprop("contentclass", '');
            $info["props"][] = $this->mkprop("getcontentlanguage", '');
            $info["props"][] = $this->mkprop("lastaccessed", '');
            $info["props"][] = $this->mkprop("isstructureddocument", '');
            $info["props"][] = $this->mkprop("defaultdocument", '');
            $info["props"][] = $this->mkprop("isroot", '');

            return $info;
        }


        /**
         * PROPFIND helper for Document Info
         *
         * @param int  Document ID
         * @param string  path
         * @return array   Doc info array
         */
        function _fileinfoForDocumentID($iDocumentID, $path) {

            global $default;

            $this->ktwebdavLog("Entering _fileinfoForDocumentID. DocumentID is " . print_r($iDocumentID, true), 'info', true);

            if ($iDocumentID == '') return false;

            $oDocument =& Document::get($iDocumentID);

            if (is_null($oDocument) || ($oDocument === false) || PEAR::isError($oDocument)) {
                return false;
            }

            return $this->_fileinfoForDocument($oDocument, $path);

        }

        /**
         * PROPFIND helper for Folder Info
         *
         * @param Folder  Folder Object
         * @param string  $path
         * @return array  Folder info array
         */
        function _fileinfoForFolder($oFolder, $path) {

            global $default;

            $this->ktwebdavLog("Entering _fileinfoForFolder. Folder is " . print_r($oFolder, true), 'info', true);

            // Fix for Mac
            // Modified - 25/10/07 - spaces prevent files displaying in finder
            if($this->dav_client == 'MC'){
                $path = str_replace('%2F', '/', urlencode(utf8_encode($path)));
            }
            $path = str_replace('&', '%26', $path);

            // create result array
            $info = array();
            $info["path"] = $path;
            $fspath = $default->documentRoot . "/" . $this->rootFolder . $path;
            //$fspath = $default->documentRoot . '/' . $oFolder->generateFolderPath($oFolder->getID());

            $info["props"] = array();
            // no special beautified displayname here ...
            $info["props"][] = $this->mkprop("displayname", $oFolder->getName());

            // creation and modification time
            //$info["props"][] = $this->mkprop("creationdate", strtotime($oFolder->getCreatedDateTime()));
            //$info["props"][] = $this->mkprop("getlastmodified", strtotime($oFolder->getVersionCreated()));

            // directory (WebDAV collection)
            $info["props"][] = $this->mkprop("resourcetype", "collection");
            $info["props"][] = $this->mkprop("getcontenttype", "httpd/unix-directory");
            $info["props"][] = $this->mkprop("getcontentlength", 0);

            return $info;
        }

        /**
         * PROPFIND method handler
         *
         * @param  void
         * @return void
         */
        function http_PROPFIND()
        {
            $options = Array();
            $options["path"] = $this->path;

            // search depth from header (default is "infinity)
            if (isset($_SERVER['HTTP_DEPTH'])) {
                $options["depth"] = $_SERVER["HTTP_DEPTH"];
            } else {
                $options["depth"] = "infinity";
            }

            // analyze request payload
            $propinfo = new _parse_propfind("php://input");
            if (!$propinfo->success) {
                $this->http_status("400 Error");
                return;
            }
            $options['props'] = $propinfo->props;

            // call user handler
            if (!$this->PROPFIND($options, $files)) {
                $this->http_status("404 Not Found");
                return;
            }

            // collect namespaces here
            $ns_hash = array();

            // Microsoft Clients need this special namespace for date and time values
            $ns_defs = "xmlns:ns0=\"urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/\"";

            // now we loop over all returned file entries
            foreach($files["files"] as $filekey => $file) {

                // nothing to do if no properties were returned for a file
                if (!isset($file["props"]) || !is_array($file["props"])) {
                    continue;
                }

                // now loop over all returned properties
                foreach($file["props"] as $key => $prop) {
                    // as a convenience feature we do not require that user handlers
                    // restrict returned properties to the requested ones
                    // here we strip all unrequested entries out of the response

                    switch($options['props']) {
                        case "all":
                            // nothing to remove
                            break;

                        case "names":
                            // only the names of all existing properties were requested
                            // so we remove all values
                            unset($files["files"][$filekey]["props"][$key]["val"]);
                        break;

                        default:
                        $found = false;

                        // search property name in requested properties
                        foreach((array)$options["props"] as $reqprop) {
                            if (   $reqprop["name"]  == $prop["name"]
                                    && $reqprop["xmlns"] == $prop["ns"]) {
                                $found = true;
                                break;
                            }
                        }

                        // unset property and continue with next one if not found/requested
                        if (!$found) {
                            $files["files"][$filekey]["props"][$key]='';
                            continue(2);
                        }
                        break;
                    }

                    // namespace handling
                    if (empty($prop["ns"])) continue; // no namespace
                    $ns = $prop["ns"];
                    if ($ns == "DAV:") continue; // default namespace
                    if (isset($ns_hash[$ns])) continue; // already known

                    // register namespace
                    $ns_name = "ns".(count($ns_hash) + 1);
                    $ns_hash[$ns] = $ns_name;
                    $ns_defs .= " xmlns:$ns_name=\"$ns\"";
                }

                // we also need to add empty entries for properties that were requested
                // but for which no values where returned by the user handler
                if (is_array($options['props'])) {
                    foreach($options["props"] as $reqprop) {
                        if($reqprop['name']=='') continue; // skip empty entries

                        $found = false;

                        // check if property exists in result
                        foreach($file["props"] as $prop) {
                            if (   $reqprop["name"]  == $prop["name"]
                                    && $reqprop["xmlns"] == $prop["ns"]) {
                                $found = true;
                                break;
                            }
                        }

                        if (!$found) {
                            if($reqprop["xmlns"]==="DAV:" && $reqprop["name"]==="lockdiscovery") {
                                // lockdiscovery is handled by the base class
                                $files["files"][$filekey]["props"][]
                                    = $this->mkprop("DAV:",
                                            "lockdiscovery" ,
                                            $this->lockdiscovery($files["files"][$filekey]['path']));
                            } else {
                                // add empty value for this property
                                $files["files"][$filekey]["noprops"][] = $this->mkprop($reqprop["xmlns"], $reqprop["name"], '');

                                // register property namespace if not known yet
                                if ($reqprop["xmlns"] != "DAV:" && !isset($ns_hash[$reqprop["xmlns"]])) {
                                    $ns_name = "ns".(count($ns_hash) + 1);
                                    $ns_hash[$reqprop["xmlns"]] = $ns_name;
                                    $ns_defs .= " xmlns:$ns_name=\"$reqprop[xmlns]\"";
                                }
                            }
                        }
                    }
                }
            }

            // now we generate the reply header ...
            $this->http_status("207 Multi-Status");
            header('Content-Type: text/xml; charset="utf-8"');

            // ... and payload
            echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            echo "<D:multistatus xmlns:D=\"DAV:\">\n";

            foreach($files["files"] as $file) {
                // ignore empty or incomplete entries
                if(!is_array($file) || empty($file) || !isset($file["path"])) continue;
                $path = $file['path'];
                if(!is_string($path) || $path==='') continue;

                echo " <D:response $ns_defs>\n";

                $tempHref = $this->_mergePathes($_SERVER['SCRIPT_NAME'], $path);

                // Ensure collections end in a slash
                if(isset($file['props'])){
                    foreach($file['props'] as $v){
                        if($v['name'] == 'resourcetype'){
                            if($v['val'] == 'collection'){
                                $tempHref = $this->_slashify($tempHref);
                                continue;
                            }
                        }
                    }
                }

                $href = htmlspecialchars($tempHref);

                echo "  <D:href>$href</D:href>\n";

                $this->ktwebdavLog("\nfile is: " . print_r($file, true), 'info', true);

                // report all found properties and their values (if any)
                if (isset($file["props"]) && is_array($file["props"])) {
                    echo "   <D:propstat>\n";
                    echo "    <D:prop>\n";

                    foreach($file["props"] as $key => $prop) {

                        if (!is_array($prop)) continue;
                        if (!isset($prop["name"])) continue;

                        $this->ktwebdavLog("Namespace is " . $prop["ns"], 'info', true);

                        if (!isset($prop["val"]) || $prop["val"] === '' || $prop["val"] === false) {
                            // empty properties (cannot use empty() for check as "0" is a legal value here)
                            if($prop["ns"]=="DAV:") {
                                echo "     <D:$prop[name]/>\n";
                            } else if(!empty($prop["ns"])) {
                                echo "     <".$ns_hash[$prop["ns"]].":$prop[name]/>\n";
                            } else {
                                echo "     <$prop[name] xmlns=\"\"/>";
                            }
                        } else if ($prop["ns"] == "DAV:") {
                            $this->ktwebdavLog("Getting DAV: Properties...", 'info', true);
                            // some WebDAV properties need special treatment
                            switch ($prop["name"]) {
                                case "creationdate":
                                    $this->ktwebdavLog("Getting creationdate...", 'info', true);
                                echo "     <D:creationdate ns0:dt=\"dateTime.tz\">"
                                    . gmdate("Y-m-d\\TH:i:s\\Z",$prop['val'])
                                    . "</D:creationdate>\n";
                                break;
                                case "getlastmodified":
                                    $this->ktwebdavLog("Getting getlastmodified...", 'info', true);
                                echo "     <D:getlastmodified ns0:dt=\"dateTime.rfc1123\">"
                                    . gmdate("D, d M Y H:i:s ", $prop['val'])
                                    . "GMT</D:getlastmodified>\n";
                                break;
                                case "resourcetype":
                                    $this->ktwebdavLog("Getting resourcetype...", 'info', true);
                                echo "     <D:resourcetype><D:$prop[val]/></D:resourcetype>\n";
                                break;
                                case "supportedlock":
                                    $this->ktwebdavLog("Getting supportedlock...", 'info', true);
                                echo "     <D:supportedlock>$prop[val]</D:supportedlock>\n";
                                break;
                                case "lockdiscovery":
                                    $this->ktwebdavLog("Getting lockdiscovery...", 'info', true);
                                echo "     <D:lockdiscovery>\n";
                                echo $prop["val"];
                                echo "     </D:lockdiscovery>\n";
                                break;
                                default:
                                $this->ktwebdavLog("Getting default...", 'info', true);
                                $this->ktwebdavLog("name is: " . $prop['name'], 'info', true);
                                $this->ktwebdavLog("val is: " . $this->_prop_encode(htmlspecialchars($prop['val'])), 'info', true);
                                echo "     <D:" . $prop['name'] .">"
                                    . $this->_prop_encode(htmlspecialchars($prop['val']))
                                    .     "</D:" . $prop['name'] . ">\n";
                                break;
                            }
                        } else {
                            // properties from namespaces != "DAV:" or without any namespace
                            $this->ktwebdavLog('Getting != "DAV:" or without any namespace Properties...', 'info', true);
                            if ($prop["ns"]) {
                                echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]>"
                                    . $this->_prop_encode(htmlspecialchars($prop['val']))
                                    . "</" . $ns_hash[$prop["ns"]] . ":$prop[name]>\n";
                            } else {
                                echo "     <$prop[name] xmlns=\"\">"
                                    . $this->_prop_encode(htmlspecialchars($prop['val']))
                                    . "</$prop[name]>\n";
                            }
                        }
                    }

                    echo "   </D:prop>\n";
                    echo "   <D:status>HTTP/1.1 200 OK</D:status>\n";
                    echo "  </D:propstat>\n";
                }

                // now report all properties requested but not found
                $this->ktwebdavLog('Getting all properties requested but not found...', 'info', true);
                if (isset($file["noprops"])) {
                    echo "   <D:propstat>\n";
                    echo "    <D:prop>\n";

                    foreach($file["noprops"] as $key => $prop) {
                        if ($prop["ns"] == "DAV:") {
                            echo "     <D:$prop[name]/>\n";
                        } else if ($prop["ns"] == '') {
                            echo "     <$prop[name] xmlns=\"\"/>\n";
                        } else {
                            echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]/>\n";
                        }
                    }

                    echo "   </D:prop>\n";
                    echo "   <D:status>HTTP/1.1 404 Not Found</D:status>\n";
                    echo "  </D:propstat>\n";
                }

                echo " </D:response>\n";
            }

            echo "</D:multistatus>\n";
        }

        /**
         * PROPFIND helper for Folder Info
         *
         * @param int  Folder ID
         * @param string path
         * @return array   Folder info array
         */
        function _fileinfoForFolderID($iFolderID, $path) {

            global $default;

            $this->ktwebdavLog("Entering _fileinfoForFolderID. FolderID is " . $iFolderID, 'info', true);

            if($iFolderID == '') return false;

            $oFolder =& Folder::get($iFolderID);

            if (is_null($oFolder) || ($oFolder === false)) {
                $this->ktwebdavLog("oFolderID error. ", 'info', true);
                return false;
            }

            return $this->_fileinfoForFolder($oFolder, $path);
        }

        /**
         * GET method handler
         *
         * @param  array  parameter passing array
         * @return bool   true on success
         */
        function GET(&$options)
        {
            // required for KT
            global $default;

            $this->ktwebdavLog("Entering GET. options are " .  print_r($options, true), 'info', true);

            // Get the client info
            $this->checkSafeMode();

            // get path to requested resource
            $path = $options["path"];

            // Fix for Mac Clients
            // Mac adds DS_Store files when folders are added and ._filename files when files are added
            // The PUT function doesn't add these files to the dms but PROPFIND still looks for the .DS_Store file,
            // and returns an error if not found. We emulate its existence by returning a positive result.
            if($this->dav_client == 'MC' || $this->dav_client == 'MG'){
                // Remove filename from path
                $aPath = explode('/', $path);
                $fileName = $aPath[count($aPath)-1];

                if(strtolower($fileName) == '.ds_store'){
                    $this->ktwebdavLog("Using a Mac client. Filename is .DS_Store so we emulate a positive result.", 'info', true);
                    // ignore
                    return true;
                }
                if($fileName[0] == '.' && $fileName[1] == '_'){
                    $this->ktwebdavLog("Using a Mac client. Filename is ._Filename so we emulate a positive result.", 'info', true);
                    // ignore
                    return true;
                }
            }

            list($iFolderID, $iDocumentID) = $this->_folderOrDocument($path);

            if ($iDocumentID === false) {
                $this->ktwebdavLog("Document not found.", 'info', true);
                return "404 Not found - Document not found.";
            }

            if (is_null($iDocumentID)) {
                return $this->_GETFolder($options, $iFolderID);
            }
            return $this->_GETDocument($options, $iDocumentID);

        }

        /**
         * GET method helper
         *
         * @param  array  parameter passing array
         * @param  int    MainFolder ID
         * @return bool   true on success
         */
        function _GETFolder(&$options, $iMainFolderID) {

            global $default;

            $this->ktwebdavLog("Entering _GETFolder. options are " . print_r($options, true), 'info', true);

            $oMainFolder =& Folder::get($iMainFolderID);
            $aFolderID = array();
            $aChildren = Folder::getList(array('parent_id = ?', $iMainFolderID));
            //        $sFolderName = $oMainFolder->getName();

            if (is_writeable("../var") && is_writeable("../var/log")) {
                $writeperms = "<font color=\"green\"><b>OK</b></font>";
            }else {
                $writeperms = "<font color=\"red\"><b>NOT SET</b></font>";
            }

            if ($this->ktdmsPath != '') {
                $ktdir = $this->ktdmsPath;
            }

            $srv_proto = split('/', $_SERVER['SERVER_PROTOCOL']);
            $proto = strtolower($srv_proto[0]);

            // check if ssl enabled
            if($proto == 'http' && $default->sslEnabled){
                $proto = 'https';
            }

            $dataSafe = '';
            if($this->safeMode != 'off'){
                $dataSafe = "<div style=\"color: orange;\" align=\"center\">NOTE: Safe mode is currently enabled, only viewing and downloading of documents will be allowed.</div><br><br>";
            }

            $data = "<html><head><title>KTWebDAV - The KnowledgeTree WebDAV Server</title></head>";
            $data .= "<body>";
            $data .= "<div align=\"center\"><IMG src=\"../resources/graphics/ktlogo-topbar_base.png\" width=\"308\" height=\"61\" border=\"0\"></div><br>";
            $data .= "<div align=\"center\"><h2><strong>Welcome to KnowledgeTree WebDAV Server</strong></h2></div><br><br>";
            $data .= "<div align=\"center\">To access KTWebDAV copy the following URL and paste it into your WebDAV enabled client...</div><br><br>";
            $data .= $dataSafe;
            $data .= "<div align=\"center\"><strong>" . $proto . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . "</strong></div>";
            $data .= "</body>";

            $options['mimetype'] = 'text/html';
            $options["data"] = $data;
            return true;
        }

        /**
         * GET method helper
         *
         * @param  array  parameter passing array
         * @param  int  Document ID
         * @return bool   true on success
         */
        function _GETDocument(&$options, $iDocumentID) {
            global $default;

            $oDocument =& Document::get($iDocumentID);

            // get a temp file, and read.  NOTE: NEVER WRITE TO THIS
            $oStorage =& KTStorageManagerUtil::getSingleton();
            $fspath = $oStorage->temporaryFile($oDocument);

            $this->ktwebdavLog("Filesystem Path is " . $fspath, 'info', true );

            // detect resource type
            $mimetype = KTMime::getMimeTypeName($oDocument->getMimeTypeID());
            $options['mimetype'] = KTMime::getFriendlyNameForString($mimetype);
            // detect modification time
            // see rfc2518, section 13.7
            // some clients seem to treat this as a reverse rule
            // requiering a Last-Modified header if the getlastmodified header was set

            $options['mtime'] = $oDocument->getVersionCreated();

            // detect resource size
            $options['size'] = $oDocument->getFileSize();

            // no need to check result here, it is handled by the base class
            $options['stream'] = fopen($fspath, "r");

            $this->ktwebdavLog("Method is " . $this->currentMethod, 'info', true );

            if ($this->currentMethod == "get") {

                // create the document transaction record
                include_once(KT_LIB_DIR . '/documentmanagement/DocumentTransaction.inc');
                $oDocumentTransaction = & new DocumentTransaction($oDocument, "Document viewed via KTWebDAV", 'ktcore.transactions.view');
                $oDocumentTransaction->iUserID = $this->userID;
                $oDocumentTransaction->create();

            }
            return true;
        }

        /**
         * GET method helper
         * Method takes a directory path and checks whether it refers to a document or folder. The relevant folder and/or document id is returned.
         *
         * @param $path string The directory path
         * @return array or bool Either returns an array of folder/document id's or false if an error occurred
         */
        function _folderOrDocument($path) {

            global $default;

            $this->ktwebdavLog("Entering _folderOrDocument. path is " . $path, 'info', true);

            /* ** Get the directory path and the folder/document being acted on ** */
            $sFileName = basename($path);
            // for windows replace backslash with forwardslash
            $sFolderPath = str_replace("\\", '/', dirname($path) );

            /* ** Get the starting point for recursing through the directory structure
                FolderId = 0 if we're in the root folder
                FolderId = 1 the starting point for locating any other folder ** */
            if ($sFolderPath == "/" || $sFolderPath == "/ktwebdav") {
                $this->ktwebdavLog("This is the root folder.", 'info', true);
                $sFolderPath = $this->rootFolder;
                $iFolderID = 0;
            } else $iFolderID = 1;
            if ($sFileName == "ktwebdav.php") {
                $this->ktwebdavLog("This is the root folder file.", 'info', true);
                $sFileName = '';
            }

            $this->ktwebdavLog("sFileName is " . $sFileName, 'info', true);
            $this->ktwebdavLog("sFolderName is " . $sFolderPath, 'info', true);
            $this->ktwebdavLog("iFolderID is " . $iFolderID, 'info', true);

            /* ** Break up the directory path into its component directory's,
                recurse through the directory's to find the correct id of the current directory.
                Avoids situations where several directory's have the same name. ** */
            $aFolderNames = split('/', $sFolderPath);

            $this->ktwebdavLog("aFolderNames are: " . print_r($aFolderNames, true), 'info', true);
            $aRemaining = $aFolderNames;
            while (count($aRemaining)) {
                $sFolderName = $aRemaining[0];
                $aRemaining = array_slice($aRemaining, 1);
                if (empty($sFolderName)) {
                    continue;
                }
                // FIXME: Direct database access
                if($iFolderID == 0){
                    $sQuery = "SELECT id FROM folders WHERE parent_id is null AND name = ?";
                    $aParams = array($sFolderName);
                }else{
                    $sQuery = "SELECT id FROM folders WHERE parent_id = ? AND name = ?";
                    $aParams = array($iFolderID, $sFolderName);
                }
                $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');
                if (PEAR::isError($id)) {
                    $this->ktwebdavLog("A DB error occurred in _folderOrDocument", 'info', true);
                    return false;
                }
                if (is_null($id)) {
                    // Some intermediary folder path doesn't exist
                    $this->ktwebdavLog("Some intermediary folder does not exist in _folderOrDocument", 'error', true);
                    return array(false, false);
                }
                $iFolderID = (int)$id;
                $this->ktwebdavLog("iFolderID set to " . $iFolderID, 'info', true);
            }

            /* ** Get the document id using the basename and parent folder id as parameters.
                If an id is obtained then the path refers to a document.
                If no id is returned then the path refers to a folder or a non-existing document. ** */
            // FIXME: Direct database access
            //        $sQuery = "SELECT id FROM documents WHERE folder_id = ? AND filename = ? AND status_id = 1";
            $sQuery = "SELECT D.id ";
            $sQuery .= "FROM documents AS D ";
            $sQuery .= "LEFT JOIN document_metadata_version AS DM ";
            $sQuery .= "ON D.metadata_version_id = DM.id ";
            $sQuery .= "LEFT JOIN document_content_version AS DC ";
            $sQuery .= "ON DM.content_version_id = DC.id ";
            $sQuery .= "WHERE D.folder_id = ? AND DC.filename = ? AND D.status_id=1";

            $aParams = array($iFolderID, $sFileName);
            $iDocumentID = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

            if (PEAR::isError($iDocumentID)) {
                $this->ktwebdavLog("iDocumentID error in _folderOrDocument", 'info', true);
                return false;
            }

            /* ** If the path refers to a folder or a non-existing document,
                Get the folder id using the basename and parent folder id as parameters.
                If an id is obtained then the path refers to an existing folder.
                If no id is returned and the basename is empty then path refers to the root folder.
                If no id is returned and the basename is not empty, then the path refers to either a non-existing folder or document. ** */
            if ($iDocumentID === null) {
                $this->ktwebdavLog("iDocumentID is null", 'info', true);
                // FIXME: Direct database access
                $sQuery = "SELECT id FROM folders WHERE parent_id = ? AND name = ?";
                $aParams = array($iFolderID, $sFileName);
                $id = DBUtil::getOneResultKey(array($sQuery, $aParams), 'id');

                if (PEAR::isError($id)) {
                    $this->ktwebdavLog("A DB(2) error occurred in _folderOrDocument", 'info', true);
                    return false;
                }
                if (is_null($id)) {
                    if ($sFileName == '') {
                        return array($iFolderID, null);
                    }
                    $this->ktwebdavLog("id is null in _folderOrDocument", 'info', true);
                    return array($iFolderID, false);
                }
                if (substr($path, -1) !== "/") {
                    $this->ktwebdavLog("Setting Location Header to " . "Location: " . $_SERVER["PHP_SELF"] . "/", 'info', true);
                    header("Location: " . $_SERVER["PHP_SELF"] . "/");
                }
                $this->ktwebdavLog("DEBUG: return id ".$id, 'info', true);
                return array($id, null);
            }

            return array($iFolderID, (int)$iDocumentID);
        }

        /**
         *  PUT method handler
         *
         * @param  array  parameter passing array
         * @return string  HTTP status code or false
         */
        function PUT(&$options)
        {
            global $default;

            if ($this->checkSafeMode()) {

                $this->ktwebdavLog("Entering PUT. options are " .  print_r($options, true), 'info', true);
                $this->ktwebdavLog("dav_client is: " .  $this->dav_client, 'info', true);

                $path = $options["path"];

                // Fix for Mac
                // Modified - 22/10/07
                // Mac adds DS_Store files when folders are added and ._filename files when files are added
                // we want to ignore them.
                if($this->dav_client == 'MC' || $this->dav_client == 'MG'){
                    // Remove filename from path
                    $aPath = explode('/', $path);
                    $fileName = $aPath[count($aPath)-1];

                    if(strtolower($fileName) == '.ds_store'){
                        $this->ktwebdavLog("Using a mac client. Ignore the .DS_Store files created with every folder.", 'info', true);
                        // ignore
                        return "204 No Content";
                    }

                    if($fileName[0] == '.' && $fileName[1] == '_'){
                        $fileName = substr($fileName, 2);
                        $this->ktwebdavLog("Using a mac client. Ignore the ._filename files created with every file.", 'info', true);
                        // ignore
                        return "204 No Content";
                    }
                }

                $res = $this->_folderOrDocument($path);
                list($iFolderID, $iDocumentID) = $res;

                if ($iDocumentID === false && $iFolderID === false) {
                    // Couldn't find intermediary paths
                    /*
                     * RFC2518: 8.7.1 PUT for Non-Collection Resources
                     *
                     * 409 (Conflict) - A PUT that would result in the creation
                     * of a resource without an appropriately scoped parent collection
                     * MUST fail with a 409 (Conflict).
                     */
                    return "409 Conflict - Couldn't find intermediary paths";
                }

                $oParentFolder =& Folder::get($iFolderID);
                // Check if the user has permissions to write in this folder
                $oPerm =& KTPermission::getByName('ktcore.permissions.write');
                $oUser =& User::get($this->userID);
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oParentFolder)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }

                $this->ktwebdavLog("iDocumentID is " . $iDocumentID, 'info', true);

                if (is_null($iDocumentID)) {
                    // This means there is a folder with the given path
                    $this->ktwebdavLog("405 Method not allowed", 'info', true);
                    return "405 Method not allowed - There is a folder with the given path";
                }

                if ($iDocumentID == false) {
                    $this->ktwebdavLog("iDocumentID is false", 'info', true);
                }

                if ($iDocumentID !== false) {
                    // This means there is a document with the given path
                    $oDocument = Document::get($iDocumentID);

                    $this->ktwebdavLog("oDocument is " .  print_r($oDocument, true), 'info', true);
                    $this->ktwebdavLog("oDocument statusid is " .  print_r($oDocument->getStatusID(), true), 'info', true);

                    if ( ( (int)$oDocument->getStatusID() != STATUS_WEBDAV ) && ( (int)$oDocument->getStatusID() != DELETED )) {
                        $this->ktwebdavLog("Trying to PUT to an existing document", 'info', true);
                        if (!$this->dav_client == "MS" && !$this->dav_client == "MC") return "409 Conflict - There is a document with the given path";
                    }

                    // FIXME: Direct filesystem access
                    $fh = $options["stream"];
                    $sTempFilename = tempnam('/tmp', 'ktwebdav_dav_put');
                    $ofh = fopen($sTempFilename, 'w');

                    $contents = '';
                    while (!feof($fh)) {
                        $contents .= fread($fh, 8192);
                    }
                    $fres = fwrite($ofh, $contents);
                    $this->ktwebdavLog("A DELETED or CHECKEDOUT document exists. Overwriting...", 'info', true);
                    $this->ktwebdavLog("Temp Filename is: " . $sTempFilename, 'info', true );
                    $this->ktwebdavLog("File write result size was: " . $fres, 'info', true );

                    fflush($fh);
                    fclose($fh);
                    fflush($ofh);
                    fclose($ofh);
                    $this->ktwebdavLog("Files have been flushed and closed.", 'info', true );

                    $name = basename($path);
                    $aFileArray = array(
                            "name" => $name,
                            "size" => filesize($sTempFilename),
                            "type" => false,
                            "userID" => $this->_getUserID(),
                            );
                    $this->ktwebdavLog("aFileArray is " .  print_r($aFileArray, true), 'info', true);

                    //include_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
                    $aOptions = array(
                            //'contents' => new KTFSFileLike($sTempFilename),
                            'temp_file' => $sTempFilename,
                            'metadata' => array(),
                            'novalidate' => true,
                            );
                    $this->ktwebdavLog("DEBUG: overwriting file. Options: ".print_r($aOptions, true));
                    $this->ktwebdavLog("DEBUG: overwriting file. Temp name: ".$sTempFilename.' '.print_r($sTempFilename, true));
                    $this->ktwebdavLog("DEBUG: overwriting file. Name: ".$name.' '.print_r($name, true));

                    // Modified - 25/10/07 - changed add to overwrite
                    //$oDocument =& KTDocumentUtil::add($oParentFolder, $name, $oUser, $aOptions);
                    $oDocument =& KTDocumentUtil::overwrite($oDocument, $name, $sTempFilename, $oUser, $aOptions);

                    if(PEAR::isError($oDocument)) {
                        $this->ktwebdavLog("oDocument ERROR: " .  $oDocument->getMessage(), 'info', true);
		                unlink($sTempFilename);
                        return "409 Conflict - " . $oDocument->getMessage();
                    }

                    $this->ktwebdavLog("oDocument is " .  print_r($oDocument, true), 'info', true);

                    unlink($sTempFilename);
                    return "201 Created";
                }

                $options["new"] = true;
                // FIXME: Direct filesystem access
                $fh = $options["stream"];
                $sTempFilename = tempnam('/tmp', 'ktwebdav_dav_put');
                $ofh = fopen($sTempFilename, 'w');

                $contents = '';
                while (!feof($fh)) {
                    $contents .= fread($fh, 8192);
                }
                $fres = fwrite( $ofh, $contents);
                $this->ktwebdavLog("Content length was not 0, doing the whole thing.", 'info', true );
                $this->ktwebdavLog("Temp Filename is: " . $sTempFilename, 'info', true );
                $this->ktwebdavLog("File write result size was: " . $fres, 'info', true );

                fflush($fh);
                fclose($fh);
                fflush($ofh);
                fclose($ofh);
                $this->ktwebdavLog("Files have been flushed and closed.", 'info', true );

                $name = basename($path);
                $aFileArray = array(
                        "name" => $name,
                        "size" => filesize($sTempFilename),
                        "type" => false,
                        "userID" => $this->_getUserID(),
                        );
                $this->ktwebdavLog("aFileArray is " .  print_r($aFileArray, true), 'info', true);

                //include_once(KT_LIB_DIR . '/filelike/fsfilelike.inc.php');
                $aOptions = array(
                        //'contents' => new KTFSFileLike($sTempFilename),
                        'temp_file' => $sTempFilename,
                        'metadata' => array(),
                        'novalidate' => true,
                        );
                $oDocument =& KTDocumentUtil::add($oParentFolder, $name, $oUser, $aOptions);

                if(PEAR::isError($oDocument)) {
                    $this->ktwebdavLog("oDocument ERROR: " .  $oDocument->getMessage(), 'info', true);
                    unlink($sTempFilename);
                    return "409 Conflict - " . $oDocument->getMessage();
                }

                $this->ktwebdavLog("oDocument is " .  print_r($oDocument, true), 'info', true);

                unlink($sTempFilename);
                return "201 Created";

            }  else return "423 Locked - KTWebDAV is in SafeMode";

        }

        /**
         * MKCOL method handler
         *
         * @param  array  parameter passing array
         * @return string  HTTP status code or false
         */
        function MKCOL($options)
        {
            $this->ktwebdavLog("Entering MKCOL. options are " .  print_r($options, true), 'info', true);

            if ($this->checkSafeMode()) {

                global $default;

                if (!empty($_SERVER["CONTENT_LENGTH"])) {
                    /*
                     * RFC2518: 8.3.2 MKCOL status codes
                     *
                     * 415 (Unsupported Media Type)- The server does not support
                     * the request type of the body.
                     */
                    return "415 Unsupported media type";
                }

                // Take Windows's escapes out
                $path = str_replace('\\', '' , $options['path']);


                $res = $this->_folderOrDocument($path);
                list($iFolderID, $iDocumentID) = $res;

                if ($iDocumentID === false && $iFolderID === false) {
                    // Couldn't find intermediary paths
                    /*
                     * RFC2518: 8.3.2 MKCOL status codes
                     *
                     * 409 (Conflict) - A collection cannot be made at the
                     * Request-URI until one or more intermediate collections
                     * have been created.
                     */
                    $this->ktwebdavLog("409 Conflict in MKCOL", 'info', true);
                    return "409 Conflict - Couldn't find intermediary paths";
                }


                if (is_null($iDocumentID)) {
                    // This means there is a folder with the given path
                    /*
                     * RFC2518: 8.3.2 MKCOL status codes
                     *
                     * 405 (Method Not Allowed) - MKCOL can only be executed on
                     * a deleted/non-existent resource.
                     */
                    $this->ktwebdavLog("405 Method not allowed - There is a folder with the given path", 'info', true);
                    return "405 Method not allowed - There is a folder with the given path";
                }
                if ($iDocumentID !== false) {
                    // This means there is a document with the given path
                    /*
                     * RFC2518: 8.3.2 MKCOL status codes
                     *
                     * 405 (Method Not Allowed) - MKCOL can only be executed on
                     * a deleted/non-existent resource.
                     */
                    $this->ktwebdavLog("405 Method not allowed - There is a document with the given path", 'info', true);
                    return "405 Method not allowed - There is a document with the given path";
                }

                $sFolderName = basename($path);
                $sFolderPath = dirname($path);

                $dest_fspath = $default->documentRoot . "/" . $this->rootFolder . $path;
                $this->ktwebdavLog("Will create a physical path of " .  $dest_fspath, 'info', true);

                $oParentFolder =& Folder::get($iFolderID);
                $this->ktwebdavLog("Got an oParentFolder of " .  print_r($oParentFolder, true), 'info', true);

                // Check if the user has permissions to write in this folder
                $oPerm =& KTPermission::getByName('ktcore.permissions.addFolder');
                $oUser =& User::get($this->userID);

                $this->ktwebdavLog("oPerm is " .  print_r($oPerm, true), 'info', true);
                $this->ktwebdavLog("oUser is " .  print_r($oUser, true), 'info', true);
                $this->ktwebdavLog("oFolder is " .  print_r($oParentFolder, true), 'info', true);

                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oParentFolder)) {
                    $this->ktwebdavLog("Permission denied.", 'info', true);
                    return "403 Forbidden - User does not have sufficient permissions";
                } else $this->ktwebdavLog("Permission granted.", 'info', true);


                include_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

                KTFolderUtil::add($oParentFolder, $sFolderName, $oUser);
                /*
                 * RFC 2518: 8.3.2 MKCOL status codes
                 *
                 * 201 (Created) - The collection or structured resource was
                 * created in its entirety.
                 */
                $this->ktwebdavLog("201 Created", 'info', true);
                return "201 Created";

            } else return "423 Locked - KTWebDAV is in SafeMode";
        }


        /**
         * DELETE method handler
         *
         * @param  array  parameter passing array
         * @return string  HTTP status code or false
         */
        function DELETE($options)
        {
            $this->ktwebdavLog("Entering DELETE. options are " . print_r($options, true), 'info', true);

            if ($this->checkSafeMode()) {

                $path = $options["path"];
                $res = $this->_folderOrDocument($path);
                $this->ktwebdavLog("DELETE res is " . print_r($res, true), 'info', true);
                if ($res === false) {
                    $this->ktwebdavLog("404 Not found - The Document was not found.", 'info', true);
                    return "404 Not found - The Document was not found.";
                }
                list($iFolderID, $iDocumentID) = $res;

                if ($iDocumentID === false) {
                    $this->ktwebdavLog("404 Not found - The Folder was not found.", 'info', true);
                    return "404 Not found - The Folder was not found.";
                }

                if (is_null($iDocumentID)) {
                    return $this->_DELETEFolder($options, $iFolderID);
                }
                return $this->_DELETEDocument($options, $iFolderID, $iDocumentID);

            } else return "423 Locked - KTWebDAV is in SafeMode";
        }

        /**
         * DELETE method helper for Documents
         *
         * @param  array  parameter passing array
         * @param  int    Folder ID
         * @param  int    Document ID
         * @return string  HTTP status code or false
         */
        function _DELETEDocument($options, $iFolderID, $iDocumentID) {

            $this->ktwebdavLog("Entering _DELETEDocument. options are " . print_r($options, true), 'info', true);

            global $default;

            $oDocument =& Document::get($iDocumentID);

            // Check if the user has permissions to delete this document
            $oPerm =& KTPermission::getByName('ktcore.permissions.delete');
            $oUser =& User::get($this->userID);
            if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDocument)) {
                return "403 Forbidden - The user does not have sufficient permissions";
            }

            $res = KTDocumentUtil::delete($oDocument, $_SERVER['HTTP_REASON']);

            if (PEAR::isError($res)) {
                $this->ktwebdavLog("404 Not Found - " . $res->getMessage(), 'info', true);
                return "404 Not Found - " . $res->getMessage();
            }

            $this->ktwebdavLog("204 No Content", 'info', true);
            return "204 No Content";
        }

        /**
         * DELETE method helper for Folders
         *
         * @param  array  paramter passing array
         * @param  int  Folder ID
         * @return string  HTTP status code or false
         */
        function _DELETEFolder($options, $iFolderID) {

            $this->ktwebdavLog("Entering _DELETEFolder. options are " . print_r($options, true), 'info', true);

            global $default;

            require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");

            // Check if the user has permissions to delete this folder
            $oFolder =& Folder::get($iFolderID);
            $oPerm =& KTPermission::getByName('ktcore.permissions.delete');
            $oUser =& User::get($this->userID);
            if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oFolder)) {
                return "403 Forbidden - The user does not have sufficient permissions";
            }
            $this->ktwebdavLog("Got an oFolder of " . print_r($oFolder, true), 'info', true);
            $this->ktwebdavLog("Got an oUser of " . print_r($oUser, true), 'info', true);
            $res = KTFolderUtil::delete($oFolder, $oUser, 'KTWebDAV Delete');

            if (PEAR::isError($res)) {
                $this->ktwebdavLog("Delete Result error " . print_r($res, true), 'info', true);
                return "403 Forbidden - ".$res->getMessage();
            }

            return "204 No Content";
        }

        /**
         * MOVE method handler
         * Method checks if the source path refers to a document / folder then calls the appropriate method handler.
         *
         * @param $options array  parameter passing array
         * @return string  HTTP status code or false
         */
        function MOVE($options)
        {
            $this->ktwebdavLog("Entering MOVE. options are " . print_r($options, true), 'info', true);

            /* ** Check that write is allowed ** */
            if ($this->checkSafeMode()) {

                if (!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
                    $this->ktwebdavLog("415 Unsupported media type", 'info', true);
                    return "415 Unsupported media type";
                }

                /*		// no moving to different WebDAV Servers yet
                        if (isset($options["dest_url"])) {
                        $this->ktwebdavLog("502 bad gateway - No moving to different WebDAV Servers yet", 'info', true);
                        return "502 bad gateway - No moving to different WebDAV Servers yet";
                        }
                 */

                /* ** Get the path to the document/folder to be copied.
                    Call function to check if the path refers to a document or a folder.
                    Return 404 error if the path is invalid. ** */
                $source_path = $options["path"];

                // Fix for Mac Goliath
                // Modified - 30/10/07
                // Mac adds ._filename files when files are added / copied / moved
                // we want to ignore them.
                if($this->dav_client == 'MG'){
                    // Remove filename from path
                    $aPath = explode('/', $source_path);
                    $fileName = $aPath[count($aPath)-1];

//                    if(strtolower($fileName) == '.ds_store'){
//                        $this->ktwebdavLog("Using a mac client. Ignore the .DS_Store files created with every folder.", 'info', true);
//                        // ignore
//                        return "204 No Content";
//                    }

                    if($fileName[0] == '.' && $fileName[1] == '_'){
                        $fileName = substr($fileName, 2);
                        $this->ktwebdavLog("Using a mac client. Ignore the ._filename files created with every file.", 'info', true);
                        // ignore
                        return "204 No Content";
                    }
                }

                $source_res = $this->_folderOrDocument($source_path);
                if ($source_res === false) {
                    $this->ktwebdavLog("404 Not found - Document was not found.", 'info', true);
                    return "404 Not found - Document was not found.";
                }

                /* ** Get the returned parent folder id and document/folder id.
                    If the parent folder id is false, return 404 error.
                    If the document id is either false or null, then the source is a folder.
                    If the document id exists then the source is a document.
                    If the source is a folder then call _MOVEFolder.
                    If the source is a document then check if its checked out and call _MOVEDocument. ** */
                list($iFolderID, $iDocumentID) = $source_res;
                if ($iFolderID === false && ($iDocumentID === false || is_null($iDocumentID))) {
                    $this->ktwebdavLog("404 Not found - Folder was not found.", 'info', true);
                    return "404 Not found - Folder was not found.";
                }

                if (is_null($iDocumentID) || $iDocumentID === false) {
                    // Source is a folder
                    $this->ktwebdavLog("Source is a Folder.", 'info', true);
                    $movestat = $this->_MOVEFolder($options, $iFolderID);

                } else {
                	 // Source is a document
                	 $this->ktwebdavLog("Source is a Document.", 'info', true);
                	if ($this->canCopyMoveRenameDocument($iDocumentID)) {
						$movestat = $this->_MOVEDocument($options, $iFolderID, $iDocumentID);
					} else {
						return "423 Locked - Cannot MOVE document because it is checked out by another user.";
					}
                }

                $this->ktwebdavLog("Final movestat result is: " . $movestat, 'info', true);
                return $movestat;

            } else return "423 Locked - KTWebDAV is in SafeMode";

        }

        /**
         * MOVE method helper for Documents
         *
         * @param  array  parameter passing array
         * @param  int    Folder ID
         * @param  int    Document ID
         * @return string  HTTP status code or false
         */
        function _MOVEDocument($options, $iFolderID, $iDocumentID) {

            /* ** Ensure that the destination path exists ** */
            if ($options['dest'] == '') $options["dest"] = substr($options["dest_url"], strlen($_SERVER["SCRIPT_NAME"]));
            $this->ktwebdavLog("Entering _MOVEDocument. options are " . print_r($options, true), 'info', true);

            // Fix for Mac Goliath
            // Modified - 25/10/07 - remove ktwebdav from document path
            if($this->dav_client == 'MG' || $this->dav_client == 'MS'){
                $this->ktwebdavLog("Remove ktwebdav from destination path: ".$options['dest'], 'info', true);
                if(!(strpos($options['dest'], 'ktwebdav/ktwebdav.php/') === FALSE)){
                    $options['dest'] = substr($options['dest'], 22);
                }
                if($options['dest'][0] != '/'){
                   $options['dest'] = '/'.$options['dest'];
                }
            }

            global $default;
            $new = true;

            /* ** Get the relevant paths. Get the basename of the destination path as the destination filename.
                Check whether the destination path refers to a folder / document. ** */
            $oDocument = Document::get($iDocumentID);
            $oSrcFolder = Folder::get($iFolderID);
            $oUser =& User::get($this->userID);

            $source_path = $options["path"];
            $dest_path = urldecode($options["dest"]);

            /* ** Get the source folder object.
                If the destination document is null, then the destination is a folder, continue.
                If the destination document returns an id, then the document exists. Check overwrite.
                If overwrite is true, then check permissions and delete the document, continue.
                If the destination document is false, then continue. ** */
            list($iDestFolder, $iDestDoc) = $this->_folderOrDocument($dest_path);

            if (is_null($iDestDoc)) {
                // the dest is a folder
                $this->ktwebdavLog("Destination is a folder.", 'info', true);
            } else if ($iDestDoc !== false) {
                // Document exists
                $this->ktwebdavLog("Destination Document exists.", 'info', true);
                $oReplaceDoc = Document::get($iDestDoc);
                if ($options['overwrite'] != 'T') {
                    $this->ktwebdavLog("Overwrite needs to be TRUE.", 'info', true);
                    return "412 Precondition Failed - Destination Document exists. Overwrite needs to be TRUE.";
                }
                $this->ktwebdavLog("Overwrite is TRUE, deleting Destination Document.", 'info', true);

                // Check if the user has permissions to delete this document
                $oPerm =& KTPermission::getByName('ktcore.permissions.delete');
                $oUser =& User::get($this->userID);
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oReplaceDoc)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }
                KTDocumentUtil::delete($oReplaceDoc, 'KTWebDAV move overwrites target.');
                $new = false;
            }

            /* ** Check if the source and destination directories are the same and the destination is not a folder.
                Then action is probably a rename.
                Check if user has permission to write to the document and folder.
                Rename the document. ** */
            if ((dirname($source_path) == dirname($dest_path)) && !is_null($iDestDoc)) {
                // This is a rename
                $this->ktwebdavLog("This is a rename.", 'info', true);
                $this->ktwebdavLog("Got an oDocument of " . print_r($oDocument, true), 'info', true);
                $this->ktwebdavLog("Got a new name of " . basename($dest_path), 'info', true);

                // Check if the user has permissions to write this document
                $oPerm =& KTPermission::getByName('ktcore.permissions.write');
                $oUser =& User::get($this->userID);
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDocument)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }

                // Perform rename
                $res = KTDocumentUtil::rename($oDocument, basename($dest_path), $oUser);
                if (PEAR::isError($res) || is_null($res) || ($res === false)) {
                    return "404 Not Found - " . $res->getMessage();
                } else if($new) {
                    $this->ktwebdavLog("201 Created", 'info', true);
                    return "201 Created";
                }else {
                    $this->ktwebdavLog("204 No Content", 'info', true);
                    return "204 No Content";
                }
            }

            /* ** Get the destination folder object and the source document object.
                Check if user has permission to write to the document and folder.
                Move the document. ** */
            $oDestFolder = Folder::get($iDestFolder);
            $this->ktwebdavLog("Got a destination folder of " . print_r($oDestFolder, true), 'info', true);

            // Check if the user has permissions to write in this folder
            $oPerm =& KTPermission::getByName('ktcore.permissions.write');
            $oUser =& User::get($this->userID);
            if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDestFolder)) {
                return "403 Forbidden - User does not have sufficient permissions";
            }

            $reason = (isset($_SERVER['HTTP_REASON']) && !empty($_SERVER['HTTP_REASON'])) ? $_SERVER['HTTP_REASON'] : "KTWebDAV Move.";

            $res = KTDocumentUtil::move($oDocument, $oDestFolder, $oUser, $reason);

            if(PEAR::isError($res)){
                $this->ktwebdavLog("Move on document failed: ".$res->getMessage(), 'info', true);
                return "500 Internal Server Error - Move on document failed.";
            }

            if ($new) {
                $this->ktwebdavLog("201 Created", 'info', true);
                return "201 Created";
            } else {
                $this->ktwebdavLog("204 No Content", 'info', true);
                return "204 No Content";
            }
        }

        /**
         * MOVE method helper for Folders
         *
         * @param  array   parameter passing array
         * @param  int     Folder ID
         * @return string  HTTP status code or false

         */
        function _MOVEFolder($options, $iFolderID) {

            /* ** Ensure that the destination path exists ** */
            if ($options['dest'] == '') $options["dest"] = substr($options["dest_url"], strlen($_SERVER["SCRIPT_NAME"]));
            $options['dest'] = $this->_slashify($options['dest']);
            $this->ktwebdavLog("Entering _MOVEFolder. options are " . print_r($options, true), 'info', true);

            /* ** RFC 2518 Section 8.9.2. A folder move must have a depth of 'infinity'.
                Check the requested depth. If depth is set to '0' or '1' return a 400 error. ** */
            if ($options["depth"] != "infinity") {
                $this->ktwebdavLog("400 Bad request", 'info', true);
                return "400 Bad request - depth must be 'inifinity'.";
            }

            // Fix for Mac Goliath - and for Novell Netdrive
            // Modified - 30/10/07 - remove ktwebdav from folder path
            if($this->dav_client == 'MG' || $this->dav_client == 'MS'){
                $this->ktwebdavLog("Remove ktwebdav from destination path: ".$options['dest'], 'info', true);
                if(!(strpos($options['dest'], 'ktwebdav/ktwebdav.php/') === FALSE)){
                    $options['dest'] = substr($options['dest'], 22);
                }
                if($options['dest'][0] != '/'){
                   $options['dest'] = '/'.$options['dest'];
                }
            }

            global $default;

            /* ** Get the relevant paths.
                Check whether the destination path refers to a folder / document. ** */
            $source_path = $options["path"];
            $dest_path = urldecode($options["dest"]);
            list($iDestFolder, $iDestDoc) = $this->_folderOrDocument($dest_path);

            /* ** Get the source folder objects.
                If the destination document is null, then the destination is an existing folder. Check overwrite.
                If overwrite is true, then check permissions and delete the folder, continue.
                If the destination document returns an id, then the destination is a document, check overwrite.
                If overwrite is true, then check permissions and delete the document, continue.
                If the destination document is false, then continue. ** */
            $oSrcFolder = Folder::get($iFolderID);
            $oDestFolder = Folder::get($iDestFolder);

            $new = true;
            if (is_null($iDestDoc)) {
                // Folder exists
                $this->ktwebdavLog("Destination Folder exists.", 'info', true);
                $oReplaceFolder = $oDestFolder;
                if ($options['overwrite'] != 'T') {
                    $this->ktwebdavLog("Overwrite needs to be TRUE.", 'info', true);
                    return "412 Precondition Failed - Destination Folder exists. Overwrite needs to be TRUE.";
                }
                $this->ktwebdavLog("Overwrite is TRUE, deleting Destination Folder.", 'info', true);

                // Check if the user has permissions to delete this folder
                $oPerm =& KTPermission::getByName('ktcore.permissions.delete');
                $oUser =& User::get($this->userID);

                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oReplaceFolder)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }

                KTFolderUtil::delete($oReplaceFolder, $oUser, 'KTWebDAV move overwrites target.');

                // Destination folder has been replaced so we need to get the parent folder object
                list($iDestFolder, $iDestDoc) = $this->_folderOrDocument($dest_path);
                $oDestFolder = Folder::get($iDestFolder);

                $new = false;
            } else if ($iDestDoc !== false) {
                // Destination is a document
                $this->ktwebdavLog("Destination is a document.", 'info', true);
                $oReplaceDoc = Document::get($iDestDoc);
                if ($options['overwrite'] != 'T') {
                    $this->ktwebdavLog("Overwrite needs to be TRUE.", 'info', true);
                    return "412 Precondition Failed - Destination Folder is a document. Overwrite needs to be TRUE.";
                }
                $this->ktwebdavLog("Overwrite is TRUE, deleting Destination Document.", 'info', true);

                // Check if the user has permissions to delete this document
                $oPerm =& KTPermission::getByName('ktcore.permissions.delete');
                $oUser =& User::get($this->userID);

                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oReplaceDoc)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }
                KTDocumentUtil::delete($oReplaceDoc, 'KTWebDAV move overwrites target.');
                $new = false;
            }

            /* ** Check if the source and destination directories are the same and the destination is not an existing folder.
                Then action is probably a rename.
                Check if user has permission to write to the folder.
                Rename the document. ** */
            if (dirname($source_path) == dirname($dest_path) && !is_null($iDestDoc)) {
                // This is a rename
                $this->ktwebdavLog("Rename collection.", 'info', true);
                $this->ktwebdavLog("Got an oSrcFolder of " . print_r($oSrcFolder, true), 'info', true);
                $this->ktwebdavLog("Got an new name of " . basename($dest_path), 'info', true);

                include_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

                // Check if the user has permissions to write this folder
                $oPerm =& KTPermission::getByName('ktcore.permissions.folder_rename');
                $oUser =& User::get($this->userID);
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oSrcFolder)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }
                $res = KTFolderUtil::rename($oSrcFolder, basename($dest_path), $oUser);
                if (PEAR::isError($res) || is_null($res) || ($res === false)) {
                    return "404 Not Found - " . $res->getMessage();
                } else {
                    if($new){
                        $this->ktwebdavLog("201 Created", 'info', true);
                        return "201 Created";
                    }else{
                        $this->ktwebdavLog("204 No Content", 'info', true);
                        return "204 No Content";
                    }
                }

            }

            include_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

            /* ** Get the destination folder object and the source document object.
                Check if user has permission to write to the folder.
                Move the folder. ** */
            $oUser =& User::get($this->userID);
            $this->ktwebdavLog("Got an oSrcFolder of " . print_r($oSrcFolder, true), 'info', true);
            $this->ktwebdavLog("Got an oDestFolder of " . print_r($oDestFolder, true), 'info', true);
            $this->ktwebdavLog("Got an oUser of " . print_r($oUser, true), 'info', true);

            // Check if the user has permissions to write in this folder
            $oPerm =& KTPermission::getByName('ktcore.permissions.write');
            $oUser =& User::get($this->userID);
            if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDestFolder)) {
                return "403 Forbidden - User does not have sufficient permissions";
            }

            $res = KTFolderUtil::move($oSrcFolder, $oDestFolder, $oUser);

            if(PEAR::isError($res)){
                $this->ktwebdavLog("Move on folder failed: ".$res->getMessage(), 'info', true);
                return "500 Internal Server Error - Move on folder failed.";
            }

            if($new){
                $this->ktwebdavLog("201 Created", 'info', true);
                return "201 Created";
            }else{
                $this->ktwebdavLog("204 No Content", 'info', true);
                return "204 No Content";
            }
        }

        /**
         * COPY method handler
         * Method checks if the source path refers to a document / folder then calls the appropriate method handler.
         *
         * @param $options array   parameter passing array
         * @param $del string  delete source flag
         * @return string  HTTP status code or false
         */
        function COPY($options, $del = false)
        {
            $this->ktwebdavLog("Entering COPY. options are " . print_r($options, true), 'info', true);
            $this->ktwebdavLog("del is: " . $del, 'info', true);

            /* ** Check that writing to the server is allowed * **/
            if ($this->checkSafeMode()) {

                if (!empty($_SERVER["CONTENT_LENGTH"])) { // no body parsing yet
                    $this->ktwebdavLog("415 Unsupported media type", 'info', true);
                    return "415 Unsupported media type - No body parsing yet";
                }

                /*		// no copying to different WebDAV Servers yet
                        if (isset($options["dest_url"])) {
                        $this->ktwebdavLog("502 bad gateway", 'info', true);
                        return "502 bad gateway - No copying to different WebDAV Servers yet";
                        }
                 */

                /* ** Get the path to the document/folder to be copied.
                    Call function to check if the path refers to a document or a folder.
                    Return 404 error if the path is invalid. ** */
                $source_path = $options["path"];
                $this->ktwebdavLog("SourcePath is: " . $source_path, 'info', true);
                $source_res = $this->_folderOrDocument($source_path);
                if ($source_res === false) {
                    $this->ktwebdavLog("404 Not found - The document could not be found.", 'info', true);
                    return "404 Not found - The document could not be found.";
                }

                /* ** Get the returned parent folder id and document/folder id.
                    If the parent folder id is false, return 404 error.
                    If the document id is either false or null, then the source is a folder.
                    If the document id exists then the source is a document.
                    If the source is a folder then call _COPYFolder.
                    If the source is a document then check if its checked out and call _COPYDocument. ** */
                list($iFolderID, $iDocumentID) = $source_res;
                if ($iFolderID === false && ($iDocumentID === false || is_null($iDocumentID))) {
                    $this->ktwebdavLog("404 Not found - The folder could not be found.", 'info', true);
                    return "404 Not found - The folder could not be found.";
                }

                if (is_null($iDocumentID) || $iDocumentID === false) {
                    // Source is a folder
                    $this->ktwebdavLog("Source is a Folder.", 'info', true);
                    $copystat = $this->_COPYFolder($options, $iFolderID);

                } else {
                    // Source is a document
                    $this->ktwebdavLog("Source is a Document.", 'info', true);

					if ($this->canCopyMoveRenameDocument($iDocumentID)) {
						$copystat = $this->_COPYDocument($options, $iFolderID, $iDocumentID);
					} else {
					    // Document is locked
						return "423 Locked - Cannot COPY document because it is checked out by another user.";
					}

                }

                /* ** Deprecated. If the request is a move then delete the source **
                // Delete the source if this is a move and the copy was ok
                if ($del && ($copystat{0} == "2")) {
                    $delstat = $this->DELETE(array("path" => $options["path"]));
                    $this->ktwebdavLog("DELETE in COPY/MOVE stat is: " . $delstat, 'info', true);
                    if (($delstat{0} != "2") && (substr($delstat, 0, 3) != "404")) {
                        return $delstat;
                    }
                }
                */

                $this->ktwebdavLog("Final copystat result is: " . $copystat, 'info', true);
                return $copystat;

            }  else return "423 Locked - KTWebDAV is in SafeMode";
        }

        /**
         * COPY method helper for Documents
         *
         * @param $options array   parameter passing array
         * @param $iFolderID int     Folder ID
         * @param $iDocumentID int     Document ID
         * @return string  HTTP status code or false
         */
        function _COPYDocument($options, $iFolderID, $iDocumentID) {

            /* ** Ensure that the destination path exists ** */
            if ($options['dest'] == '') $options["dest"] = substr($options["dest_url"], strlen($_SERVER["SCRIPT_NAME"]));
            $this->ktwebdavLog("Entering _COPYDocument. options are " . print_r($options, true), 'info', true);

            /* ** Get the relevant paths. Get the basename of the destination path as the destination filename.
                Check whether the destination path refers to a folder / document. ** */
            $source_path = $options["path"];
            $dest_path = urldecode($options["dest"]);
            $sDestFileName = basename($dest_path);

            list($iDestFolder, $iDestDoc) = $this->_folderOrDocument($dest_path);

            if($iDestFolder === false){
                return "409 Conflict - Destination folder does not exist.";
            }

            /* ** Depth must be infinity to copy a document ** */
            if ($options["depth"] != "infinity") {
                // RFC 2518 Section 9.2, last paragraph
                $this->ktwebdavLog("400 Bad request", 'info', true);
                return "400 Bad request - Depth must be 'infinity'.";
            }

            global $default;

            /* ** Get the source folder object.
                If the destination document is null, then the destination is a folder, set the destination filename to empty, continue.
                If the destination document returns an id, then the document exists. Check overwrite.
                If overwrite is true, then check permissions and delete the document, continue.
                If the destination document is false, then continue. ** */
            $oSrcFolder = Folder::get($iFolderID);

            $new = true;
            if (is_null($iDestDoc)) {
                // the dest is a folder
                //			$this->ktwebdavLog("400 Bad request", 'info', true);
                $this->ktwebdavLog("Destination is a folder.", 'info', true);
                $sDestFileName = '';
                //return "400 Bad request - Destination is a Folder";
            } else if ($iDestDoc !== false) {
                // Document exists
                $this->ktwebdavLog("Destination Document exists.", 'info', true);
                $oReplaceDoc = Document::get($iDestDoc);
                if ($options['overwrite'] != 'T') {
                    $this->ktwebdavLog("Overwrite needs to be TRUE.", 'info', true);
                    return "412 Precondition Failed - Destination Document exists. Overwrite needs to be TRUE.";
                }
                $this->ktwebdavLog("Overwrite is TRUE, deleting Destination Document.", 'info', true);

                // Check if the user has permissions to delete this document
                $oPerm =& KTPermission::getByName('ktcore.permissions.delete');
                $oUser =& User::get($this->userID);
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oReplaceDoc)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }
                KTDocumentUtil::delete($oReplaceDoc, 'KTWebDAV copy with overwrite set.');
                $new = false;
            }

            /* ** Get the destination folder object and the source document object.
                Check if user has permission to write to the document and folder.
                Copy the document. ** */
            $oDestFolder = Folder::get($iDestFolder);
            $oSrcDoc = Document::get($iDocumentID);

            include_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

            $this->ktwebdavLog("Got an oSrcDoc of " .$oSrcDoc->getName() . print_r($oSrcDoc, true), 'info', true);
            $this->ktwebdavLog("Got an oDestFolder of " .$oDestFolder->getName() . print_r($oDestFolder, true), 'info', true);

            // Check if the user has permissions to write in this folder
            $oPerm =& KTPermission::getByName('ktcore.permissions.write');
            $oUser =& User::get($this->userID);
            if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDestFolder)) {
                return "403 Forbidden - User does not have sufficient permissions";
            }

            $reason = (isset($_SERVER['HTTP_REASON']) && !empty($_SERVER['HTTP_REASON'])) ? $_SERVER['HTTP_REASON'] : "KTWebDAV Copy.";

            $oDesDoc = KTDocumentUtil::copy($oSrcDoc, $oDestFolder, $reason, $sDestFileName);

            if(PEAR::isError($oDesDoc)){
                $this->ktwebdavLog("Copy on document failed: ".$oDesDoc->getMessage(), 'info', true);
                return "500 Internal Server Error - Copy on document failed.";
            }

            if ($new) {
                $this->ktwebdavLog("201 Created", 'info', true);
                return "201 Created";
            } else {
                $this->ktwebdavLog("204 No Content", 'info', true);
                return "204 No Content";
            }
        }

        /**
         * COPY method helper for Folders
         *
         * @param  array   parameter passing array
         * @param  int     Parent Folder ID
         * @return string  HTTP status code or false
         */
        function _COPYFolder($options, $iFolderID) {

            /* ** Ensure that the destination path exists ** */
            if ($options['dest'] == '') $options["dest"] = substr($options["dest_url"], strlen($_SERVER["SCRIPT_NAME"]));
            $this->ktwebdavLog("Entering _COPYFolder. options are " . print_r($options, true), 'info', true);

            /* ** RFC 2518 Section 8.8.3. DAV compliant servers must support depth headers of '0' and 'infinity'.
                Check the requested depth. If depth is set to '0', set copyall to false. A depth of 0 indicates
                that the folder is copied without any children. If depth is set to '1', return a 400 error. ** */
            $copyAll = true;
            if ($options["depth"] != "infinity") {
                if($options['depth'] == '0'){
                    $copyAll = false;
                    $this->ktwebdavLog("Depth is 0. Copy only the base folder.", 'info', true);
                }else{
                    $this->ktwebdavLog("400 Bad request. Depth must be infinity or 0.", 'info', true);
                    return "400 Bad request - Depth must be 'infinity' or '0'.";
                }
            }

            global $default;

            $new = true;

            /* ** Get the relevant paths. Get the basename of the destination path as the destination path name.
                Check whether the destination path refers to a folder / document. ** */
            $source_path = $options["path"];
            $dest_path = urldecode($options["dest"]);
            $sDestPathName = basename($dest_path);

            list($iDestFolder, $iDestDoc) = $this->_folderOrDocument($dest_path);

            /* ** Get the source and destination folder objects.
                If the destination document is null, then the destination is an existing folder. Check overwrite.
                If overwrite is true, then check permissions and delete the folder, continue.
                If the destination document returns an id, then the destination is a document, return 409 error.
                If the destination document is false, then continue. ** */
            $oSrcFolder = Folder::get($iFolderID);
            $oDestFolder = Folder::get($iDestFolder);

            include_once(KT_LIB_DIR . '/foldermanagement/folderutil.inc.php');

            if(is_null($iDestDoc)) {
                // Destination is a folder and exists
                //$sDestPathName = '';
                $this->ktwebdavLog("Destination Folder exists.", 'info', true);

                $oReplaceFolder = $oDestFolder;
                if ($options['overwrite'] != 'T') {
                    $this->ktwebdavLog("Overwrite needs to be TRUE.", 'info', true);
                    return "412 Precondition Failed - Destination Folder exists. Overwrite needs to be TRUE.";
                }
                $this->ktwebdavLog("Overwrite is TRUE, deleting Destination Folder.", 'info', true);

                // Check if the user has permissions to delete this folder
                $oPerm =& KTPermission::getByName('ktcore.permissions.delete');
                $oUser =& User::get($this->userID);
                if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oReplaceFolder)) {
                    return "403 Forbidden - User does not have sufficient permissions";
                }
                KTFolderUtil::delete($oReplaceFolder, $oUser, 'KTWebDAV move overwrites target.');

                // Destination folder has been deleted - get new object of destination parent folder
                list($iDestFolder, $iDestDoc) = $this->_folderOrDocument($dest_path);
                $oDestFolder = Folder::get($iDestFolder);

                $new = false;
            } else if ($iDestDoc !== false) {
                // Destination is a document
                return "409 Conflict - Can't write a collection to a document";
            }

            /* ** Get the destination folder object and the source document object.
                Check if user has permission to write to the folder.
                Copy the document. Pass parameters for the destination folder name and the depth of copy. ** */
            $oUser =& User::get($this->userID);
            $this->ktwebdavLog("Got an oSrcFolder of " . print_r($oSrcFolder, true), 'info', true);
            $this->ktwebdavLog("Got an oDestFolder of " . print_r($oDestFolder, true), 'info', true);
            $this->ktwebdavLog("Got an oUser of " . print_r($oUser, true), 'info', true);

            // Check if the user has permissions to write in this folder
            $oPerm =& KTPermission::getByName('ktcore.permissions.write');
            $oUser =& User::get($this->userID);
            if (!KTPermissionUtil::userHasPermissionOnItem($oUser, $oPerm, $oDestFolder)) {
                return "403 Forbidden - User does not have sufficient permissions";
            }

            $reason = (isset($_SERVER['HTTP_REASON']) && !empty($_SERVER['HTTP_REASON'])) ? $_SERVER['HTTP_REASON'] : "KTWebDAV Copy.";

            $res = KTFolderUtil::copy($oSrcFolder, $oDestFolder, $oUser, $reason, $sDestPathName, $copyAll);

            if(PEAR::isError($res)){
                $this->ktwebdavLog("Copy on folder failed: ".$res->getMessage(), 'info', true);
                return "500 Internal Server Error - Copy on folder failed.";
            }

            if ($new) {
                $this->ktwebdavLog("201 Created", 'info', true);
                return "201 Created";
            } else {
                $this->ktwebdavLog("204 No Content", 'info', true);
                return "204 No Content";
            }

        }

        /**
         * LOCK method handler
         *
         * @param  array   parameter passing array
         * @return string  HTTP status code or false
         */
        function LOCK(&$options)
        {
            return "200 OK";
        }

        /**
         * UNLOCK method handler
         *
         * @param  array   parameter passing array
         * @return string  HTTP status code or false
         */
        function UNLOCK(&$options)
        {
            return "200 OK";
        }

        /**
         * checkLock() helper
         *
         * @param  string  resource path to check for locks
         * @return string  HTTP status code or false
         */
        function checkLock($path)
        {
            $result = false;

            return $result;
        }


		/**
         * canCopyMoveRenameDocument() helper
         * checks if document is checked out; if not, returns true
         * if checked out, cheks if checked out by same user; if yes, returns true;
         * else returns false
         *
         * @return bool  true or false
         */
        function canCopyMoveRenameDocument($iDocumentID)
        {
        	$this->ktwebdavLog("Entering canCopyMoveRenameDocument ", 'info', true);

            $oDocument =& Document::get($iDocumentID);

			if (is_null($oDocument) || ($oDocument === false) || PEAR::isError($oDocument)) {
				$this->ktwebdavLog("Document invalid ". print_r($oDocument, true), 'info', true);
				return false;
			}

			if($oDocument->getIsCheckedOut()) {
				$info = array();
				$info["props"][] = $this->mkprop($sNameSpace, 'CheckedOut', $oDocument->getCheckedOutUserID());
				//$this->ktwebdavLog("getIsCheckedOut ". print_r($info,true), 'info', true);

				$oCOUser = User::get( $oDocument->getCheckedOutUserID() );

				if (PEAR::isError($oCOUser) || is_null($oCOUser) || ($oCOUser === false)) {
					$couser_id = '0';
				} else {
					$couser_id = $oCOUser->getID();
				}

				//$this->ktwebdavLog("getCheckedOutUserID " .$couser_id, 'info', true);

				$oUser =& User::get($this->userID);

				//$this->ktwebdavLog("this UserID " .$oUser->getID(), 'info', true);

				if (PEAR::isError($oUser) || is_null($oUser) || ($oUser === false)) {
						$this->ktwebdavLog("User invalid ". print_r($oUser, true), 'info', true);
						return false;
					} else {
						$ouser_id = $oUser->getID();
					}

				//$this->ktwebdavLog("that UserID " .$oCOUser->getID(), 'info', true);

				if ($couser_id != $ouser_id) {
					$this->ktwebdavLog("Document checked out by another user $couser_id != $ouser_id", 'info', true);
					return false;
				} else {
					$this->ktwebdavLog("Document checked out by this user", 'info', true);
					return true;
				}
			} else {
				//not checked out
				$this->ktwebdavLog("Document not checked out by any user", 'info', true);
				return true;
			}
        }

        /**
         * checkSafeMode() helper
         *
         * @return bool  true or false
         */
        function checkSafeMode()
        {

            // Check/Set the WebDAV Client
            $userAgentValue = $_SERVER['HTTP_USER_AGENT'];
            // KT Explorer
            if (stristr($userAgentValue,"Microsoft Data Access Internet Publishing Provider")) {
                $this->dav_client = "MS";
                $this->ktwebdavLog("WebDAV Client : " . $userAgentValue, 'info', true);
            }
            // Mac Finder
            if (stristr($userAgentValue,"Macintosh") || stristr($userAgentValue,"Darwin")) {
                $this->dav_client = "MC";
                $this->ktwebdavLog("WebDAV Client : " . $userAgentValue, 'info', true);
            }
            // Mac Goliath
            if (stristr($userAgentValue,"Goliath")) {
                $this->dav_client = "MG";
                $this->ktwebdavLog("WebDAV Client : " . $userAgentValue, 'info', true);
            }
            // Konqueror
            if (stristr($userAgentValue,"Konqueror")) {
                $this->dav_client = "KO";
                $this->ktwebdavLog("WebDAV Client : " . $userAgentValue, 'info', true);
            }
            // Neon Library ( Gnome Nautilus, cadaver, etc)
            if (stristr($userAgentValue,"neon")) {
                $this->dav_client = "NE";
                $this->ktwebdavLog("WebDAV Client : " . $userAgentValue, 'info', true);
            }
            // Windows WebDAV
            if ($this->dav_client == 'MS' && $this->safeMode == 'off') {

                $this->ktwebdavLog("This is MS type client with SafeMode Off.", 'info', true);
                return true;

            }
            if ($this->dav_client == 'MS' && $this->safeMode != 'off') {

                $this->ktwebdavLog("This is MS type client with SafeMode On.", 'info', true);
                return false;

            }
            // Mac Finder
            if ($this->dav_client == 'MC' && $this->safeMode == 'off') {

                $this->ktwebdavLog("This is Mac Finder type client with SafeMode off.", 'info', true);
                return true;

            }
            if ($this->dav_client == 'MC' && $this->safeMode != 'off') {

                $this->ktwebdavLog("This is Mac Finder type client with SafeMode on.", 'info', true);
                return false;

            }
            // Mac Goliath
            if ($this->dav_client == 'MG' && $this->safeMode == 'off') {

                $this->ktwebdavLog("This is a Mac Goliath type client with SafeMode off.", 'info', true);
                return true;

            }
            // Mac Goliath
            if ($this->dav_client == 'MG' && $this->safeMode != 'off') {

                $this->ktwebdavLog("This is a Mac Goliath type client with SafeMode on.", 'info', true);
                return false;

            }
            // Konqueror
            if ($this->dav_client == 'KO' && $this->safeMode == 'off') {

                $this->ktwebdavLog("This is Konqueror type client with SafeMode Off.", 'info', true);
                return true;

            }
            if ($this->dav_client == 'KO' && $this->safeMode != 'off') {

                $this->ktwebdavLog("This is Konqueror type client with SafeMode On.", 'info', true);
                return false;

            }
            // Neon Library (Gnome Nautilus, cadaver, etc.)
            if ($this->dav_client == 'NE' && $this->safeMode == 'off') {

                $this->ktwebdavLog("This is Neon type client with SafeMode Off.", 'info', true);
                return true;

            }
            if ($this->dav_client == 'NE' && $this->safeMode != 'off') {

                $this->ktwebdavLog("This is Neon type client with SafeMode On.", 'info', true);
                return false;

            }

            $this->ktwebdavLog("Unknown client. SafeMode needed.", 'info', true);
            return false;

        }

        }


        ?>
