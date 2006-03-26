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

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

class KTAuthenticationAdminPage extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $res = parent::check();
        $this->aBreadcrumbs[] = array('name' => _kt('Authentication'), 'url' => $_SERVER['PHP_SELF']);
        return $res;
    }

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/manage');
        $fields = array();

        $fields[] = new KTStringWidget(_kt('Name'), _('A short name which helps identify this source of authentication data.'), 'name', "", $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProvidersInfo();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[2]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget(_kt('Authentication provider'), _('The type of source (e.g. <strong>LDAP</strong>)'), 'authentication_provider', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);

        $aSources = KTAuthenticationSource::getList();

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
            'providers' => $aProviders,
            'sources' => $aSources,
        ));
        return $oTemplate->render();
    }

    function do_addsource() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/addsource');
        $fields = array();

        $fields[] = new KTStringWidget(_kt('Name'), _('A short name which helps identify this source of authentication data.'), 'name', "", $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProvidersInfo();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[2]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget(_kt('Authentication provider'), _('The type of source (e.g. <strong>LDAP</strong>)'), 'authentication_provider', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);

        $aSources = KTAuthenticationSource::getList();

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
            'providers' => $aProviders,
            'sources' => $aSources,
        ));
        return $oTemplate->render();
    }

    function do_viewsource() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/viewsource');
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $this->aBreadcrumbs[] = array('name' => $oSource->getName());
        $this->oPage->setTitle(sprintf(_kt("Authentication source: %s"), $oSource->getName()));
        $this->oPage->setBreadcrumbDetails('viewing');
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $oTemplate->setData(array(
            'context' => &$this,
            'source' => $oSource,
            'provider' => $oProvider,
        ));
        return $oTemplate->render();
    }

    function do_editsource() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/editsource');

        $this->aBreadcrumbs[] = array(
                'name' => $oSource->getName(),
                'query' => sprintf('action=viewsource&source_id=%d', $oSource->getId()),
        );
        $this->oPage->setTitle(sprintf(_kt("Editing authentication source: %s"), $oSource->getName()));
        $this->oPage->setBreadcrumbDetails('editing');

        $fields = array();

        $fields[] = new KTStringWidget(_kt('Name'), _('A short name which helps identify this source of authentication data.'), 'authentication_name', $oSource->getName(), $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProvidersInfo();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[2]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget(_kt('Authentication provider'), _('The type of source (e.g. <strong>LDAP</strong>)'), 'authentication_provider', $oSource->getAuthenticationProvider(), $this->oPage, true, null, $fieldErrors, $fieldOptions);

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
        ));
        return $oTemplate->render();
    }

    function do_savesource() {
        $name = $this->oValidator->validateString($_REQUEST['authentication_name']);
        $authentication_provider = $this->oValidator->validateAuthenticationProvider($_REQUEST['authentication_provider']);
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $oSource->setName($name);
        $oSource->setAuthenticationProvider($authentication_provider);
        $res = $oSource->update();
        $aOptions = array(
            'message' => 'Update failed',
            'redirect_to' => array('editsource', sprintf('source_id=%d', $oSource->getId())),
        );
        $this->oValidator->notErrorFalse($res, $aOptions);
        $this->successRedirectTo('viewsource', 'Details updated', sprintf('source_id=%d', $oSource->getId()));
    }

    function do_newsource() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );
        $aErrorOptions['message'] = _kt("No name provided");
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
        $sName = $this->oValidator->validateString($sName, $aErrorOptions);
        $aErrorOptions['message'] = _kt("An authentication source with that name already exists");
        $this->oValidator->validateDuplicateName('KTAuthenticationSource', $sName, $aErrorOptions);

        $aErrorOptions['message'] = _kt("No authentication provider chosen");
        $sProvider = KTUtil::arrayGet($_REQUEST, 'authentication_provider');
        $sProvider = $this->oValidator->validateString($sProvider, $aErrorOptions);

        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        if (method_exists($oProvider, 'do_newsource')) {
            return $oProvider->subDispatch($this);
        }

        return $this->do_newsource_final();
    }

    function do_newsource_final() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );
        $aErrorOptions['message'] = _kt("No name provided");
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
        $sName = $this->oValidator->validateString($sName, $aErrorOptions);

        $aErrorOptions['message'] = _kt("No authentication provider chosen");
        $sProvider = KTUtil::arrayGet($_REQUEST, 'authentication_provider');
        $sProvider = $this->oValidator->validateString($sProvider, $aErrorOptions);

        $sNamespace = KTUtil::nameToLocalNamespace($sName, 'authentication/sources');
        $sConfig = "";

        $oSource =& KTAuthenticationSource::createFromArray(array(
            'name' => $sName,
            'namespace' => $sNamespace,
            'authenticationprovider' => $sProvider,
        ));
        $this->oValidator->notError($oSource);
        $this->successRedirectTo('editSourceProvider', _kt("Source created"), sprintf('source_id=%d', $oSource->getId()));
    }

    function do_deleteSource() {
        $oSource =& $this->oValidator->validateAuthenticationSource($_REQUEST['source_id']);

        $aGroups = Group::getByAuthenticationSource($oSource);
        $aUsers = User::getByAuthenticationSource($oSource);

        if (empty($aUsers) && empty($aGroups)) {
            $oSource->delete();
            $this->successRedirectToMain(_kt("Authentication source deleted"));
        }

        $this->errorRedirectToMain(_kt("Authentication source is still in use, not deleted"));
    }

    function do_editSourceProvider() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array(
            'name' => $oSource->getName(),
            'query' => sprintf('action=viewsource&source_id=%d', $oSource->getId()),
        );

        $oProvider->subDispatch($this);
        exit(0);
    }

    function do_performEditSourceProvider() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('name' => $oSource->getName(), 'url' => KTUtil::addQueryStringSelf("source_id=" . $oSource->getId()));

        $oProvider->subDispatch($this);
        exit(0);
    }
}
