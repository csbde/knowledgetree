<?php
/*
 * $Id$
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
 * (c) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once('config/dmsDefaults.php');

class ActivationDispatcher extends KTDispatcher {
    
    function do_main()
    {        
        // curl request to pardot
        $status = 'success';
        $user = User::get($_SESSION['userID']);
        $url = 'http://www2.knowledgetree.com/l/2622/2010-06-04/1V35H';
        $data = http_build_query(array('account_name' => ACCOUNT_NAME, 'name' => $user->getUserName(), 'email' => $user->getEmail()));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded')); 
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close ($ch);
        
        if (!(substr($info['http_code'], 0, 1) == '2')) {
            $status = 'error';
        }

        $templating =& KTTemplating::getSingleton();
		$template = $templating->loadTemplate('kt3/activate');
		$templateData = array(
              'context' => $this,
              'status' => $status
		);
		
		return $template->render($templateData);
    }
    
}

$d = new ActivationDispatcher();
$d->dispatch();