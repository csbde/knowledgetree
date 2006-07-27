<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
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
