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

require_once('../../config/dmsDefaults.php');

require_once(KT_LIB_DIR . '/metadata/metadatautil.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');

$sSourceName = "ActiveDirectory";
$sFieldsetNamespace = "http://ktcvs.local/local/fieldsets/synctestfieldset";
$sFieldName = "synctest";
$sSearch = "(objectClass=organizationalPerson)";
$sAttribute = "cn";
$sRootDN = null;

$aAuthenticationSources =& KTAuthenticationSource::getList();
$oSource = null;
foreach($aAuthenticationSources as $oPotentialSource) {
    if ($oPotentialSource->getName() == $sSourceName) {
        $oSource =& $oPotentialSource;
    }
}
if (empty($oSource)) {
    printf("No authentication source named %s found\n", $sSourceName);
    exit(1);
}

$oFieldset =& KTFieldset::getByNamespace($sFieldsetNamespace);
if (PEAR::isError($oFieldset)) {
    printf("No fieldset named %s found\n", $sFieldsetNamespace);
    exit(1);
}
$oField = DocumentField::getByFieldsetAndName($oFieldset, $sFieldName);
if (PEAR::isError($oField)) {
    printf("No field named %s found in fieldset %s\n", $sFieldName, $sFieldsetNamespace);
    exit(1);
}

$oAuthenticator =& KTAuthenticationUtil::getAuthenticatorForSource($oSource);
$oLdap =& $oAuthenticator->oLdap;

$aParams = array(
    'scope' => 'sub',
    'attributes' => array($sAttribute),
);

$aResults = $oLdap->search($sRootDn, $sSearch, $aParams);

$aValues = array();
foreach ($aResults->entries() as $oEntry) {
    // print $oEntry->dn() . "\n";
    $sValue = $oEntry->get_value($sAttribute, 'single');
    // print $sValue . "\n";
    if (!empty($sValue)) {
        $aValues[] = $sValue;
    }
}

$aValues = array_unique($aValues);

KTMetadataUtil::synchroniseMetadata($oField, $aValues);
