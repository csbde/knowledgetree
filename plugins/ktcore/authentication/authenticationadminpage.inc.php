<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationsource.inc.php');

class KTAuthenticationAdminPage extends KTAdminDispatcher {
    function do_main() {
        $this->aBreadcrumbs[] = array('name' => _('Authentication'), 'url' => $_SERVER['PHP_SELF']);
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/manage');
        $fields = array();

        $fields[] = new KTStringWidget(_('Name'), 'FIXME', 'name', "", $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProvidersInfo();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[2]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget(_('Authentication provider'), 'FIXME', 'authentication_provider', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);

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
        $this->aBreadcrumbs[] = array('name' => _('Authentication'), 'url' => $_SERVER['PHP_SELF']);
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/viewsource');
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $this->aBreadcrumbs[] = array('name' => $oSource->getName(), 'url' => $_SERVER['PHP_SELF'] . "?source_id=" . $oSource->getId());
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
            $this->aBreadcrumbs[] = array('name' => _('Authentication'), 'url' => $_SERVER['PHP_SELF']);
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
        $this->successRedirectToMain(_("Source created"));
        exit(0);
    }

    function do_editSourceProvider() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('name' => _('Authentication'), 'url' => $_SERVER['PHP_SELF']);
        $this->aBreadcrumbs[] = array('name' => $oSource->getName(), 'url' => $_SERVER['PHP_SELF'] . "?source_id=" . $oSource->getId());
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;

        $oProvider->dispatch();
        exit(0);
    }

    function do_performEditSourceProvider() {
        $oSource =& KTAuthenticationSource::get($_REQUEST['source_id']);
        $sProvider = $oSource->getAuthenticationProvider();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $oProvider =& $oRegistry->getAuthenticationProvider($sProvider);

        $this->aBreadcrumbs[] = array('name' => _('Authentication'), 'url' => $_SERVER['PHP_SELF']);
        $this->aBreadcrumbs[] = array('name' => $oSource->getName(), 'url' => $_SERVER['PHP_SELF'] . "?source_id=" . $oSource->getId());
        $oProvider->aBreadcrumbs = $this->aBreadcrumbs;

        $oProvider->dispatch();
        exit(0);
    }
}
