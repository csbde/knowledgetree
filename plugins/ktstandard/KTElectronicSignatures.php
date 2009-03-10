<?php
/**
 * Electronic Signatures
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

require_once('../../config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/security/Esignature.inc.php');

/**
 * Class handles the electronic signatures
 *
 * @author KnowledgeTree Team
 * @package Electronic Signatures
 */
class KTElectronicSignatures
{
    /**
     * The error returned when attempting to authenticate
     *
     * @access private
     * @var $error
     */
    private $error;

    /**
     * If the system is locked for the session
     *
     * @access private
     * @var bool
     */
    private $lock;

    /**
     * If electronic signatures are enabled
     *
     * @access private
     * @var bool
     */
    private $enabled;

    /**
     * The ESignature object
     *
     * @access private
     * @var ESignature object
     */
    private $eSignature;

    /**
     * Constructor function for the class
     *
     * @author KnowledgeTree Team
     * @access public
     * @return KTElectronicSignatures
     */
    public function KTElectronicSignatures()
    {
        $this->eSignature = new ESignature();
        $this->lock = $this->eSignature->isLocked();
        $this->enabled = $this->eSignature->isEnabled();
    }

    /**
     * Returns the form requesting the signature
     *
     * @author KnowledgeTree Team
     * @access public
     * @return html
     */
    public function getSignatureForm($head)
    {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('ktstandard/signatures/signature_form');
        $aTemplateData = array(
            'head' => $head
        );

        if(!$this->enabled){
            return 'disabled';
        }

        if($this->lock){
            $this->error = $this->eSignature->getLockMsg();
            return $this->getError();
        }
        return $oTemplate->render($aTemplateData);
    }

    /**
     * Attempts authentication of the signature
     *
     * @author KnowledgeTree Team
     * @access public
     * @param string $username The users username.
     * @param string $password The users password.
     * @param string $comment A comment on the action performed.
     * @return bool True if authenticated | False if rejected
     */
    public function authenticateSignature($username, $password, $comment, $action, $type, $details)
    {
        $result = $this->eSignature->sign($username, $password, $comment, $action, $type, $details);
        if(!$result){
            $this->error = $this->eSignature->getError();
            $this->lock = $this->eSignature->isLocked();
        }
        return $result;
    }

    /**
     * Returns the error from the attempted signature
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getError()
    {
        return '<div class="error">'.$this->error.'</div>';
    }

    /**
     * Checks whether the electronic signature system is locked at which point authentication is not allowed.
     *
     * @author KnowledgeTree Team
     * @access public
     * @return bool
     */
    public function isLocked()
    {
        return $this->lock;
    }
}

$sign = new KTElectronicSignatures();

// User has signed so authenticate the signature
if($_POST['action'] == 'submit'){
    $user = $_POST['sign_username'];
    $password = $_POST['sign_password'];
    $comment = $_POST['sign_comment'];
    $action = $_POST['sign_action'];
    $type = $_POST['sign_type'];
    $details = $_POST['sign_details'];

    if($sign->authenticateSignature($user, $password, $comment, $action, $type, $details)){
        echo 'success';
        exit;
    }
    echo $sign->getError();
    if($sign->isLocked()){
        exit;
    }
}

$head = $_POST['head'];
echo $sign->getSignatureForm($head);

exit;
?>