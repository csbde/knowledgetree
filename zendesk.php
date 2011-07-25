<?php
/**
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009, 2010 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */

require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/users/User.inc");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . '/config/config.inc.php');

class ZendDeskDispatcher extends KTStandardDispatcher {
	private $fullname;
	private $email;
	private $externalId;
	private $token;
	private $urlPrefix;
	private $user;

	public function __construct()
	{
		parent::KTStandardDispatcher();

		if(!isset($_SESSION['userID'])) {
			return ;
		}
		$this->user = new User();
		$this->user = $this->user->get($_SESSION['userID']);
		$this->fullname = $this->user->getUserName();
		$this->email = $this->user->getEmail();
		$this->email = ($this->email != '')? $this->email : $this->name . '@knowledgetree.com';
		$this->externalId = ACCOUNT_NAME . '_' . $_SESSION['userID'];
		$this->urlPrefix = 'knowledgetree';

		$oConfig = KTConfig::getSingleton();
		$this->token = $oConfig->get('tokens/zendesk', false);
	}

    public function do_main() {
    	return $this->renderZendDeskRedirect();
    }

	/**
     * This method will manually redirect the user to the zend desk page.
     */
    private function renderZendDeskRedirect() {
    	header('Location: ' . $this->getAuthenticationUrl());
    	exit;
    }

    /**
     * Build url 
     */
    private function getAuthenticationUrl() {
    	global $default;
		$timestamp = time();
    	$message = $this->fullname . $this->email . $this->externalId . $this->token . $timestamp;
		$accessPoint = "https://$this->urlPrefix.zendesk.com/access/remote/?";
		$accessPoint .= 'name=' . $this->fullname . '&';
		$accessPoint .= 'email=' . $this->email . '&';
		$accessPoint .= 'external_id=' . $this->externalId . '&';
		$accessPoint .= 'timestamp=' . $timestamp . '&';
		$accessPoint .= 'hash=' . md5($message);
    	$default->log->info(__CLASS__ . " : " . __FUNCTION__ . " : $accessPoint");

    	return $accessPoint;
    }
}

$oDispatcher = new ZendDeskDispatcher();
$oDispatcher->dispatch();
?>