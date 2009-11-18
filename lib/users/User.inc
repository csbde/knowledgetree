<?php
/**
 * $Id$
 *
 * Represents a user as per the users table in the database.
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

require_once(KT_LIB_DIR . '/database/dbutil.inc');
require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . "/util/sanitize.inc");

define('ADMIN_USER_ID', 1);

class User extends KTEntity {
    /** user's login name */
    var $sUserName;
    /** user's name (first and last) */
    var $sName;
    /** user's password */
    var $sPassword;
    /** user's maximum allowed file storage quota in bytes */
    var $iQuotaMax = 1234567890;
    /** user's current file storage quota in bytes */
    var $iQuotaCurrent = 0;
    /** user's email address */
    var $sEmail = "";
    /** user's mobile phone number */
    var $sMobile = "";
    /** notify user by mail status */
    var $bEmailNotification = false;
    /** notify user via sms (mobile phone) status */
    var $bSmsNotification = false;
    /** maxiumum concurrent sessions user may have */
    var $iMaxSessions = 5;
    /** primary key of language preferred by user */
    var $iLanguageID;
    /** internal variable used to determine if the password has changed or not */
    var $bPasswordChanged = false;
    /** authentication source for this user (built-in if null) */
    var $iAuthenticationSourceId = null;
    /** authentication details so that the source knows who this user is */
    var $sAuthenticationDetails = null;
    var $sAuthenticationDetails2 = null;
    var $iAuthenticationDetailsInt1 = null;
    var $iAuthenticationDetailsInt2 = null;
    var $dAuthenticationDetailsDate1 = null;
    var $dAuthenticationDetailsDate2 = null;
    var $bAuthenticationDetailsBool1 = null;
    var $bAuthenticationDetailsBool2 = null;
    var $dLastLogin = null;
    var $bDisabled = false;

    /**
     * The dashboard state for the current user
     * @var string
     */
    var $sDashboardState = '';

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sUserName' => 'username',
        'sName' => 'name',
        'sPassword' => 'password',
        'iQuotaMax' => 'quota_max',
        'iQuotaCurrent' => 'quota_current',
        'sEmail' => 'email',
        'sMobile' => 'mobile',
        'bEmailNotification' => 'email_notification',
        'bSmsNotification' => 'sms_notification',
        'iMaxSessions' => 'max_sessions',
        'iLanguageID' => 'language_id',
        'iAuthenticationSourceId' => 'authentication_source_id',
        'sAuthenticationDetails' => 'authentication_details_s1',
        'sAuthenticationDetails2' => 'authentication_details_s2',
        'iAuthenticationDetailsInt1' => 'authentication_details_i1',
        'iAuthenticationDetailsInt2' => 'authentication_details_i2',
        'dAuthenticationDetailsDate1' => 'authentication_details_d1',
        'dAuthenticationDetailsDate2' => 'authentication_details_d2',
        'bAuthenticationDetailsBool1' => 'authentication_details_b1',
        'bAuthenticationDetailsBool2' => 'authentication_details_b2',
        'dLastLogin' => 'last_login',
        'bDisabled' => 'disabled',
    );

    var $_bUsePearError = true;

    function _table() {
        return KTUtil::getTableName("users");
    }

    // STATIC
    function _ktentityOptions() {
        return array(
            'orderby' => 'name',
        );
    }


    function getUserName() { return sanitizeForSQLtoHTML($this->sUserName); }
    function setUserName($sNewValue) { $this->sUserName = sanitizeForSQL($sNewValue); }
    function getPassword() { return sanitizeForSQLtoHTML($this->sPassword); }
    function setPassword($sNewValue) { $this->sPassword = sanitizeForSQL($sNewValue); $this->bPasswordChanged = true; }
    function getQuotaMax() { return $this->iQuotaMax; }
    function setQuotaMax($iNewValue) { $this->iQuotaMax = $iNewValue; }
    function setName($sNewValue) { $this->sName = sanitizeForSQL($sNewValue); }
    function getName() { return sanitizeForSQLtoHTML($this->sName); }
    function getQuotaCurrent() { return $this->iQuotaCurrent; }
    function getEmail() { return sanitizeForSQLtoHTML($this->sEmail); }
    function setEmail($sNewValue) { $this->sEmail = sanitizeForSQL($sNewValue); }
    function getMobile() { return sanitizeForSQLtoHTML($this->sMobile); }
    function setMobile($sNewValue) { $this->sMobile = sanitizeForSQL($sNewValue); }
    function getEmailNotification() { return $this->bEmailNotification; }
    function setEmailNotification($bNewValue) { $this->bEmailNotification = KTUtil::anyToBool($bNewValue); }
    function getSmsNotification() { return $this->bSmsNotification; }
    function setSmsNotification($bNewValue) { $this->bSmsNotification = $bNewValue; }
    function getMaxSessions() { return $this->iMaxSessions; }
    function setMaxSessions($iNewValue) { $this->iMaxSessions = $iNewValue; }
    function getLanguageID() { return $this->iLanguageIDID; }
    function setLanguageID($iNewValue) { $this->iLanguageIDID = $iNewValue; }
    function getAuthenticationSourceId() { return $this->iAuthenticationSourceId; }
    function setAuthenticationSourceId($iNewValue) { $this->iAuthenticationSourceId = $iNewValue; }
    function getAuthenticationDetails() { return $this->sAuthenticationDetails; }
    function setAuthenticationDetails($sNewValue) { $this->sAuthenticationDetails = $sNewValue; }
    function getAuthenticationDetails2() { return $this->sAuthenticationDetails2; }
    function setAuthenticationDetails2($sNewValue) { $this->sAuthenticationDetails2 = $sNewValue; }

    function getAuthenticationDetailsInt1() { return $this->iAuthenticationDetailsInt1; }
    function setAuthenticationDetailsInt1($mValue) { $this->iAuthenticationDetailsInt1 = $mValue; }
    function getAuthenticationDetailsInt2() { return $this->iAuthenticationDetailsInt2; }
    function setAuthenticationDetailsInt2($mValue) { $this->iAuthenticationDetailsInt2 = $mValue; }

    function getAuthenticationDetailsDate1() { return $this->dAuthenticationDetailsDate1; }
    function setAuthenticationDetailsDate1($mValue) { $this->dAuthenticationDetailsDate1 = $mValue; }
    function getAuthenticationDetailsDate2() { return $this->dAuthenticationDetailsDate2; }
    function setAuthenticationDetailsDate2($mValue) { $this->dAuthenticationDetailsDate2 = $mValue; }

    function getAuthenticationDetailsBool1() { return $this->bAuthenticationDetailsBool1; }
    function setAuthenticationDetailsBool1($mValue) { $this->bAuthenticationDetailsBool1 = $mValue; }
    function getAuthenticationDetailsBool2() { return $this->bAuthenticationDetailsBool2; }
    function setAuthenticationDetailsBool2($mValue) { $this->bAuthenticationDetailsBool2 = $mValue; }

    function getLastLogin() { return $this->dLastLogin; }
    function setLastLogin($mValue) { $this->dLastLogin = $mValue; }

    function getDisabled() { return $this->bDisabled; }
    function setDisabled($mValue) { $this->bDisabled = $mValue; }

    /* return the key for storing dashboard in system settings */
    function _getDashboardStateKey() {
        return 'dashboard-state-' . $this->getId();
    }

    function getDashboardState() {
        if(empty($this->sDashboardState)){
            $this->sDashboardState = KTUtil::getSystemSetting($this->_getDashboardStateKey());
        }
        return $this->sDashboardState;
    }

    function setDashboardState($mValue) {
        $this->sDashboardState = $mValue;
        KTUtil::setSystemSetting($this->_getDashboardStateKey(), $mValue);
    }

    function refreshDashboadState()
    {
    	require_once(KT_DIR . "/thirdparty/pear/JSON.php");
    	$dashletRegistry = & KTDashletRegistry::getSingleton();

    	$update = false;
    	$knownlist = array();

    	$oJSON = new Services_JSON();
    	$state = $this->getDashboardState();
    	$dashlets = $oJSON->decode($state);

    	if (!isset($dashlets->left)) $dashlets->left = array();
    	if (!isset($dashlets->right)) $dashlets->right = array();
    	$mergedlist = kt_array_merge($dashlets->left,$dashlets->right);

    	foreach($mergedlist as $dashlet)
    	{
    		array_push($knownlist,"'".$dashlet->id."'");
    	}

    	$sKnownlist = implode(',', $knownlist);
    	$aDashlets = $dashletRegistry->getNewDashlets($this, $sKnownlist);

    	if(!empty($aDashlets)){
        	$column=1;
        	foreach($aDashlets as $class)
        	{
        		$column = ($column + 1) %2;
        	    $obj = new stdClass();
        		$obj->id=$class;
        		$obj->state=0;

        		if ($column == 0)
        			array_push($dashlets->left,$obj);
    			else
        			array_push($dashlets->right,$obj);
        		$update=true;
        	}
    	}

    	// Check if the KT Info dashlet is being displayed
    	// If it's not in the top left corner, move it there.
    	if((!(strpos($sKnownlist, 'KTInfoDashlet') === false) && $knownlist[0] != "'KTInfoDashlet'") || empty($knownlist)){
    	    $left = $dashlets->left;
    	    $right = $dashlets->right;

    	    $found = false;
    	    $update = true;
    	    foreach($left as $key => $item){
    	        if($item->id == 'KTInfoDashlet'){
    	            // found the dashlet
    	            $found = 'true';
    	            array_splice($dashlets->left, $key, 1);
    	            array_unshift($dashlets->left, $item);
    	            continue;
    	        }
    	    }
    	    if(!$found){
    	        foreach($right as $key => $item){
        	        if($item->id == 'KTInfoDashlet'){
        	            // found the dashlet
        	            $found = 'true';
    	                array_splice($dashlets->right, $key, 1);
        	            array_unshift($dashlets->left, $item);
        	            continue;
        	        }
        	    }
    	    }
    	}

    	if ($update)
    	{
    		$state = $oJSON->encode($dashlets);
	    	$this->setDashboardState($state);
    	}
    }


    function &get($iId) {
        return KTEntityUtil::get('User', $iId);
    }

	/**
	* update the datastore, without overwriting the password.
	*
	* only works for a subset of the db values.
	*/
	function doLimitedUpdate() {
		$sQuery = 'UPDATE ' . $this->_table() . ' SET ';
		$aParams = array();

		$blacklist = array(
			"sPassword" => 1,
		);

		$aParts = array(); // quick workaround to make the join less hurtful.

		foreach ($this->_aFieldToSelect as $attr => $column) {
			if (!array_key_exists($attr, $blacklist)) {
				$val = $this->$attr;
				$aParts[] = $column . ' = ?';
				$aParams[] = $val;
			}
		}
		$sQuery .= join(', ', $aParts);

		$sQuery .= ' WHERE id = ? ';
		$aParams[] = $this->getId();

		$res = DBUtil::runQuery(array($sQuery, $aParams));

        $group = sprintf("%s/%s", get_class($this), 'id');
        $oCache =& KTCache::getSingleton();
        $oCache->remove($group, $this->iId);
        $this->clearCachedGroups();

		return $res;
	}


    /**
    * Static function
    * Get a list of users
    *
    * @param  String  Where clause (not required)
    *
    * @return Array array of User objects, false otherwise and set $_SESSION["errorMessage"]
    */
    function getList($sWhereClause = null, $aOptions = null) {
        if(!is_array($aOptions)) $aOptions = array($aOptions);
        $aOptions['orderby'] = KTUtil::arrayGet($aOptions, 'orderby', 'name');

        // don't include deleted users: disabled = 2
        if(!empty($sWhereClause)){
            if(strpos(strtolower($sWhereClause), 'where ') === false){
                $sWhereClause = '('.$sWhereClause.')';
            }
            $sWhereClause .= ' AND disabled != 2';
        }else{
            $sWhereClause = 'disabled != 2';
        }

        return KTEntityUtil::getList2('User', $sWhereClause, $aOptions);
    }

    function getEmailUsers($sWhereClause = null) {
        $aUsers = array();
        foreach (User::getList($sWhereClause) as $oUser) {
            if ($oUser->getEmail()) {
                $aUsers[] = $oUser;
            }
        }
        return $aUsers;
    }

    /**
     * Return the useID for the user
     *
     * @return int the unitID, false otherwise and $_SESSION["errorMessage"] set
     */
    function getUnitId() {
        $ugl = KTUtil::getTableName("users_groups");
        $g = KTUtil::getTableName("groups");
        $aQuery = array(
            "SELECT DISTINCT g.unit_id AS unit_id FROM $ugl AS ugl INNER JOIN $g AS g ON ugl.group_id = g.id WHERE ugl.user_id = ?",
            array($this->iId),
        );
        return DBUtil::getOneResultKey($aQuery, 'unit_id');
    }

    /**
     * static function
     *
     * gets the id of a user using their username
     *
     * @param   string  The username for which we want its ID
     */
    function getUserID($sUsername) {
        global $default;

        $id = lookupID($default->users_table, "username",  $sUsername);

        $this->iId = $id;
    }

    /** Static function
    * Gets the user's default top level folder for the current user
    */
    function getHomeFolderID() {
    	$iUnitId = $this->getUnitId();

        if (empty($iUnitId)) {
            return false;
        }

        $oUnit =& Unit::get($iUnitId);
        return $oUnit->getFolderId();
    }

	function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('User', $aOptions); }
	function &getByUserName($sUserName, $aOptions = null) {
        return KTEntityUtil::getBy('User', 'username', $sUserName, $aOptions);
    }

    /**
     * Check whether a user has been deleted
     *
     * @param string $sUsername
     * @return boolean
     */
    function checkDeletedUser($sUsername) {
        $deletedUsername = "kt_deleted_{$sUsername}_";
        $query = "SELECT * FROM users WHERE username LIKE '{$deletedUsername}%'";
        $result = DBUtil::getOneResult($query);

        if(PEAR::isError($result) || empty($result)){
            return false;
        }

        // Check that the deleted username is correct
        if($deletedUsername.$result['id'] != $result['username']){
            return false;
        }

        return true;
    }

    function getByAuthenticationSource($oSource, $aOptions = null) {
        $iSourceId = KTUtil::getId($oSource);
        $aOptions = KTUtil::meldOptions($aOptions, array(
            'multi' => true,
        ));
        return KTEntityUtil::getByDict('User', array(
            'authentication_source_id' => $iSourceId,
        ), $aOptions);
    }

    function &getByAuthenticationSourceAndDetails($oSource, $sDetails, $aOptions = null) {
        $iSourceId = KTUtil::getId($oSource);

        return KTEntityUtil::getByDict('User', array(
            'authentication_source_id' => $iSourceId,
            'authentication_details_s1' => $sDetails,
        ), $aOptions);
    }

    function getByLastLoginBefore($dDateTime) {
        return KTEntityUtil::getByDict('User', array(
            'last_login' => array('type' => 'before', 'value' => $dDateTime),
        ), array('multi' => true));
    }

    /**
     * Get the number of users logged in between two dates
     * @params   date $startDate
     *           date $endDate
     *
     * @return Users Object array
     */
    function getByHaveNotLoggedIn($startDate, $endDate) {
        if($startDate > $endDate) return false;
        $sWhereClause = " last_login not between '$startDate' AND '$endDate'";

        return KTEntityUtil::getList2('User', $sWhereClause);
    }

    /*
     * Modified : Jarrett Jordaan
     * Added : Get new users
     * Date : 24/04/09
     *
     */
    function getByLastLoginNever() {
        $aOptions['orderby'] = 'name';
        $sWhereClause = 'last_login is null';
        return KTEntityUtil::getList2('User', $sWhereClause, $aOptions);
    }

    /*
     * Modified : Jarrett Jordaan
     * Added : Filter out disabled users
     * Date : 24/04/09
     * Added : Removed disabled users Filter
     * Date : 24/04/09
     */
    function getByLastLoginAfter($dDateTime) {
        return KTEntityUtil::getByDict('User', array(
            'last_login' => array('type' => 'after', 'value' => $dDateTime),
//            'disabled' => 0,
        ), array('multi' => true));
    }

    function getNumberEnabledUsers() {
        $sQuery = sprintf('SELECT COUNT(id) AS number FROM %s WHERE disabled = ? AND id > 1', KTUtil::getTableName('users'));
        $aParams = array(false);
        return DBUtil::getOneResultKey(array($sQuery, $aParams), 'number');
    }

    function isAnonymous() { return $this->iId == -2; }

    function disable() {
        $this->setDisabled(1);
        $this->update();
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            BaobabKeyUtil::deallocateUser($this);
        }
        return;
    }

    function enable() {
        $this->setDisabled(0);
        $this->update();
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            BaobabKeyUtil::allocateUser($this);
        }
        return;
    }

    function create() {
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            $res = BaobabKeyUtil::canAddUser();
            if (PEAR::isError($res)) {
                return $res;
            }
        }
        return parent::create();
    }

    function delete()
    {
    	$this->setDisabled(2);
    	$this->setEmailNotification(false);
    	//change username
    	$tempUsername = $this->getUsername();
    	$this->getUserID($tempUsername);
    	$tempUserID = $this->iId;
    	$DeletedUsername = 'kt_deleted_'.$tempUsername.'_'.$tempUserID;
    	$this->setUsername($DeletedUsername);

    	//nullify all authentication_xxx fields
    	$this->setAuthenticationSourceId(null);
	    $this->setAuthenticationDetails(null);
	    $this->setAuthenticationDetails2(null);
		$this->setAuthenticationDetailsInt1(null);
	    $this->setAuthenticationDetailsInt2(null);
		$this->setAuthenticationDetailsDate1(null);
	    $this->setAuthenticationDetailsDate2(null);
		$this->setAuthenticationDetailsBool1(null);
	    $this->setAuthenticationDetailsBool2(null);

        $this->update();
        if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
            $path = KTPluginUtil::getPluginPath('ktdms.wintools');
            require_once($path . 'baobabkeyutil.inc.php');
            BaobabKeyUtil::deallocateUser($this);
        }
        return;

    }

    function hasPermission($oUser, $oPermission, $oFolderOrDocument) {
    	return KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $oFolderOrDocument);

	}

}
?>
