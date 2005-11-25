<?php

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/authentication/authenticationproviderregistry.inc.php');

class KTAuthenticationAdminPage extends KTAdminDispatcher {
    function do_main() {
        $oTemplate =& $this->oValidator->validateTemplate('ktcore/authentication/manage');
        $fields = array();

        $fields[] = new KTStringWidget('Name', 'FIXME', 'name', "", $this->oPage, true);

        $aVocab = array();
        $oRegistry =& KTAuthenticationProviderRegistry::getSingleton();
        $aProviders = $oRegistry->getAuthenticationProviders();
        foreach ($aProviders as $aProvider) {
            $aVocab[$aProvider[1]] = $aProvider[0];
        }
        $fieldOptions = array("vocab" => $aVocab);
        $fields[] = new KTLookupWidget('Authentication provider', 'FIXME', 'authentication_provider', null, $this->oPage, true, null, $fieldErrors, $fieldOptions);

        $oTemplate->setData(array(
            'context' => &$this,
            'fields' => $fields,
            'providers' => $aProviders,
        ));
        return $oTemplate->render();

    }
}
