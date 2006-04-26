<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
