<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
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

/**
 * This class defines the electronic signatures
 *
 * @author KnowledgeTree Team
 * @package Electronic Signatures
 * @version Version 0.1
 */
class ESignature
{
    /**
     * Check whether the electronic signature is enabled
     *
     * @access private
     * @var bool
     */
    private $enabled;

    /**
     * The number of failed logins on the current action
     *
     * @access private
     * @var integer
     */
    private $attempts;

    /**
     * Determines whether the user has been locked out of performing write actions.
     * This lock will be reset upon logging out of the system.
     *
     * @access private
     * @var bool
     */
    private $lock;

    /**
     * Contains the error message if the authentication fails
     *
     * @access private
     * @var string
     */
    private $error;

    /**
     * The object associated with the action - folder_id | Document
     *
     * @access private
     * @var folder_id | Document The Document object or the folder id
     */
    private $object = null;

    /**
     * Creates the ESignature object
     *
	* @author KnowledgeTree Team
 	* @access public
     */
    public function __construct()
    {
        $config = KTConfig::getSingleton();
        $this->enabled = $config->get('e_signatures/enableESignatures', false);

        $this->attempts = isset($_SESSION['esignature_attempts']) ? $_SESSION['esignature_attempts'] : 0;
        $this->lock = (isset($_SESSION['esignature_lock']) && $_SESSION['esignature_lock'] == 'true') ? true : false;
    }

    public function isEnabled()
    {
        if($this->enabled){
            return true;
        }
        return false;
    }

    public function isLocked()
    {
        return $this->lock;
    }

    public function getLockMsg()
    {
        return _kt('System locked. You have exceeded the number of allowed authentication attempts and will not be allowed to perform any write actions during this session.');
    }

    public function getError(){
        return $this->error;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function sign($username, $password, $comment, $action, $type = 'system', $details = null)
    {
        if(!$this->enabled){
            return true;
        }

        if($this->lock){
            $this->error = $this->getLockMsg();
            return false;
        }

        switch ($type){
            case 'document':
                $comment = _kt('Document').': '.$details.' | '.$comment;
                break;

            case 'folder':
                $comment = _kt('Folder').': '.$details.' | '.$comment;
                break;

            case 'system':
                break;
        }

        $this->error = _kt('Authentication failed. Please check your username and password and try again.');

        if(!$this->authenticate($username, $password)){
            // failed attempt - increase count, if count = 3, log and lock
            $this->attempts++;

            if($this->attempts >= 3){
                $this->lock = true;
                $_SESSION['esignature_lock'] = 'true';

                $comment = _kt('Electronic Signature - Failed Authentication: ') . $comment;
                $this->logTransaction($action, $comment, $type, $details);

                $this->error = $this->getLockMsg();
            }
            $_SESSION['esignature_attempts'] = $this->attempts;

            return false;
        }

        // set the number of attempts to 0
        $this->attempts = 0;
        $_SESSION['esignature_attempts'] = 0;
        $this->error = '';

        // log successful transaction
        $comment = _kt('Electronic Signature: ') . $comment;
        $this->logTransaction($action, $comment, $type, $details);
        return true;
    }

    private function logTransaction($action, $comment)
    {
        $date = date('Y-m-d H:i:s');

        require_once(KT_LIB_DIR . '/users/userhistory.inc.php');
        $params = array(
            'userid' => $_SESSION['userID'],
            'datetime' => $date,
            'actionnamespace' => $action,
            'comments' => $comment,
            'sessionid' => $_SESSION['sessionID'],
        );
        KTUserHistory::createFromArray($params);
    }

    private function authenticate($username, $password)
    {
        // Get the user object
        $oUser = User::getByUsername($username);
        if(PEAR::isError($oUser) || $oUser == false){
            return false;
        }

        // check user is the same as the currently logged in user
        if($oUser->iId != $_SESSION['userID']){
            $this->error = _kt('Authentication failed. The username does not match the currently logged in user.');
            return false;
        }

        // authenticate
        return KTAuthenticationUtil::checkPassword($oUser, $password);
    }

}

?>