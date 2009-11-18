<?php
/**
 * $Id: $
 *
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
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

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/authentication/interceptor.inc.php');
require_once(KT_LIB_DIR . '/authentication/interceptorinstances.inc.php');

class PasswordResetInterceptor extends KTInterceptor {
	var $sNamespace  = 'password.reset.login.interceptor';

	function authenticated() {
	}

	function takeover() {
	    $oRegistry =& KTPluginRegistry::getSingleton();
	    $oPlugin =& $oRegistry->getPlugin('password.reset.plugin');
        $dispatcherURL = $oPlugin->getURLPath('loginResetDispatcher.php');
        $queryString = $_SERVER['QUERY_STRING'];
        $redirect = KTUtil::arrayGet($_REQUEST, 'redirect');
        $redirect = urlencode($redirect);

        $url = KTUtil::kt_url() . $dispatcherURL;
        $url .= (!empty($queryString)) ? '?'.$queryString : '';
        redirect($url);
        exit(0);
	}
}

class PasswordResetPlugin extends KTPlugin {
	var $sNamespace = 'password.reset.plugin';
	var $autoRegister = false;

	function PasswordResetPlugin($sFilename = null) {
		$res = parent::KTPlugin($sFilename);
		$this->sFriendlyName = _kt('Password Reset Plugin');
		return $res;
	}

	function setup() {
	    // Register the interceptor
		$this->registerInterceptor('PasswordResetInterceptor', 'password.reset.login.interceptor', __FILE__);

		// Interceptor has to be added to the DB to be found
		$aOptions = array(
            'sName' => 'Password Reset Interceptor',
            'sInterceptorNamespace' => 'password.reset.login.interceptor',
            'sConfig' => ''
		);
		KTInterceptorInstance::createFromArray($aOptions);

		// Add templates directory to list
		$dir = dirname(__FILE__);
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplating->addLocation('passwordResetPlugin', $dir . '/templates');
	}
}
$oPluginRegistry =& KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('PasswordResetPlugin', 'password.reset.plugin', __FILE__);
?>
