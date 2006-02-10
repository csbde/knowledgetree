<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');

require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

class KTAuthenticationAdminPage extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $res = parent::check();
        $this->aBreadcrumbs[] = array('name' => _('Authentication'), 'url' => $_SERVER['PHP_SELF']);
        return $res;
    }

    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/manage');
        $fields = array();

        $fields[] = new KTStringWidget(_('Name'), _('A short name which helps identify this source of authentication data.'), 'name', "", $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProvidersInfo();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[2]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget(_('Authentication provider'), _('The type of source (e.g. <strong>LDAP</strong>)'), 'authentication_provider', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);

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

        $fields[] = new KTStringWidget(_('Name'), _('A short name which helps identify this source of authentication data.'), 'name', "", $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProvidersInfo();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[2]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget(_('Authentication provider'), _('The type of source (e.g. <strong>LDAP</strong>)'), 'authentication_provider', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);

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
        $this->oPage->setTitle(sprintf(_("Authentication source: %s"), $oSource->getName()));
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
        $this->oPage->setTitle(sprintf(_("Editing authentication source: %s"), $oSource->getName()));
        $this->oPage->setBreadcrumbDetails('editing');

        $fields = array();

        $fields[] = new KTStringWidget(_('Name'), _('A short name which helps identify this source of authentication data.'), 'authentication_name', $oSource->getName(), $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProvidersInfo();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[2]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget(_('Authentication provider'), _('The type of source (e.g. <strong>LDAP</strong>)'), 'authentication_provider', $oSource->getAuthenticationProvider(), $this->oPage, true, null, $fieldErrors, $fieldOptions);

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
        $aErrorOptions['message'] = _("No name provided");
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
        $sName = $this->oValidator->validateString($sName, $aErrorOptions);

        $aErrorOptions['message'] = _("No authentication provider chosen");
        $sProvider = KTUtil::arrayGet($_REQUEST, 'authentication_provider');
        $sProvider = $this->oValidator->validateString($sProvider, $aErrorOptions);

        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        if (method_exists($oProvider, 'do_newsource')) {
            $oProvider->aBreadcrumbs = $this->aBreadcrumbs;

            return $oProvider->dispatch();
        }

        return $this->do_newsource_final();
    }

    function do_newsource_final() {
        $aErrorOptions = array(
            'redirect_to' => array('main'),
        );
        $aErrorOptions['message'] = _("No name provided");
        $sName = KTUtil::arrayGet($_REQUEST, 'name');
        $sName = $this->oValidator->validateString($sName, $aErrorOptions);

        $aErrorOptions['message'] = _("No authentication provider chosen");
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
        $this->successRedirectTo('editSourceProvider', _("Source created"), sprintf('source_id=%d', $oSource->getId()));
    }

    function do_deleteSource() {
        $oSource =& $this->oValidator->validateAuthenticationSource($_REQUEST['source_id']);

        $aGroups = Group::getByAuthenticationSource($oSource);
        $aUsers = User::getByAuthenticationSource($oSource);

        if (empty($aUsers) && empty($aGroups)) {
            $oSource->delete();
            $this->successRedirectToMain(_("Authentication source deleted"));
        }

        $this->errorRedirectToMain(_("Authentication source is still in use, not deleted"));
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
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;

        $oProvider->dispatch();
        exit(0);
    }

    function do_performEditSourceProvider() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('name' => $oSource->getName(), 'url' => KTUtil::addQueryStringSelf("source_id=" . $oSource->getId()));
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;

        $oProvider->dispatch();
        exit(0);
    }
}
