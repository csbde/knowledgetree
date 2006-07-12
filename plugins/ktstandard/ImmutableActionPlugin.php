<?php

class KTImmutableActionPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.immutableaction.plugin";

    function KTImmutableActionPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Immutable action plugin');
        return $res;
    }

    function setup() {
        $this->registerAction('documentaction', 'KTDocumentImmutableAction', 'ktcore.actions.document.immutable');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTImmutableActionPlugin', 'ktstandard.immutableaction.plugin', __FILE__);

class KTDocumentImmutableAction extends KTDocumentAction {
    var $sName = "ktcore.actions.document.immutable";
    function getDisplayName() {
        return _kt('Make immutable');
    }

    function do_main() {
        $this->oDocument->setImmutable(true);
        $this->oDocument->update();
        controllerRedirect('viewDocument', 'fDocumentId=' .  $this->oDocument->getId());
    }
}

