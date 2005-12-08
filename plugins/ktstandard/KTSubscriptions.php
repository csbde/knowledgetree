<?php

require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');


require_once(KT_LIB_DIR . '/subscriptions/SubscriptionManager.inc');
require_once(KT_LIB_DIR . "/subscriptions/subscriptions.inc.php");

require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

class KTSubscriptionPlugin extends KTPlugin {
    var $sNamespace = "ktstandard.subscriptions.plugin";
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTSubscriptionPlugin', 'ktstandard.subscriptions.plugin', __FILE__);
$oPlugin =& $oRegistry->getPlugin('ktstandard.subscriptions.plugin');

// {{{ KTSubscriptionPortlet
class KTSubscriptionPortlet extends KTPortlet {
    function KTSubscriptionPortlet() {
        parent::KTPortlet(_("Subscriptions"));
    }

    function render() {
        if (!$this->oDispatcher->oDocument && !$this->oDispatcher->oFolder) {
            return null;
        }
        if ($this->oDispatcher->oDocument) {
            $oKTActionRegistry =& KTActionRegistry::getSingleton();
            $actions = $oKTActionRegistry->getActions('documentsubscriptionaction');
            foreach ($actions as $aAction) {
                list($sClassName, $sPath) = $aAction;
                if (!empty($sPath)) {
                    // require_once(KT_DIR .
                    // Or something...
                }
                $oObject =& new $sClassName($this->oDispatcher->oDocument, $this->oDispatcher->oUser);
                $this->actions[] = $oObject->getInfo();
            }
        }

        if ($this->oDispatcher->oFolder) {
            $oKTActionRegistry =& KTActionRegistry::getSingleton();
            $actions = $oKTActionRegistry->getActions('foldersubscriptionaction');
            foreach ($actions as $aAction) {
                list($sClassName, $sPath) = $aAction;
                if (!empty($sPath)) {
                    // require_once(KT_DIR .
                    // Or something...
                }
                $oObject =& new $sClassName($this->oDispatcher->oFolder, $this->oDispatcher->oUser);
                $this->actions[] = $oObject->getInfo();
            }
        }

        $this->actions[] = array("name" => _("Manage subscriptions"), "url" => $this->oPlugin->getPagePath('manage'));

        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/actions_portlet");
        $aTemplateData = array(
            "context" => $this,
        );
        return $oTemplate->render($aTemplateData);
    }
}
$oPlugin->registerPortlet('browse', 'KTSubscriptionPortlet', 'ktcore.portlets.subscription', '/plugins/ktcore/KTPortlets.php');
// }}}

// {{{ KTDocumentSubscriptionAction
class KTDocumentSubscriptionAction extends KTDocumentAction {
    var $sName = 'ktstandard.subscription.documentsubscription';
    var $sDisplayName = 'Subscribe to document';
    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            return null;
        }
        return parent::getInfo();
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _("You are already subscribed to that document");
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _("You have been subscribed to this document");
            } else {
                $_SESSION['KTErrorMessage'][] = _("There was a problem subscribing you to this document");
            }
        }
        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }
}
$oPlugin->registerAction('documentsubscriptionaction', 'KTDocumentSubscriptionAction', 'ktstandard.subscription.documentsubscription');
// }}}

// {{{ KTDocumentUnsubscriptionAction
class KTDocumentUnsubscriptionAction extends KTDocumentAction {
    var $sName = 'ktstandard.subscription.documentunsubscription';
    var $sDisplayName = 'Unsubscribe from document';
    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            return parent::getInfo();
        }
        return null;
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (!Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _("You were not subscribed to that document");
        } else {
            $oSubscription = & Subscription::getByIDs($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _("You have been unsubscribed from this document");
            } else {
                $_SESSION['KTErrorMessage'][] = _("There was a problem unsubscribing you from this document");
            }
        }
        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }
}
$oPlugin->registerAction('documentsubscriptionaction', 'KTDocumentUnsubscriptionAction', 'ktstandard.subscription.documentunsubscription');
// }}}

// {{{ KTCheckoutSubscriptionTrigger
class KTCheckoutSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate() {
        global $default;
        $oDocument =& $this->aInfo["document"];
        // fire subscription alerts for the checked out document

        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->CheckoutDocument($oDocument, $oFolder);

    }
}
$oPlugin->registerTrigger('checkout', 'postValidate', 'KTCheckoutSubscriptionTrigger', 'ktstandard.triggers.subscription.checkout');
// }}}

// {{{ KTDeleteSubscriptionTrigger
class KTDeleteSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate() {
        global $default;
        $oDocument =& $this->aInfo["document"];

        // fire subscription alerts for the deleted document
        
        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->RemoveDocument($oDocument, $oFolder);
    }
}
$oPlugin->registerTrigger('delete', 'postValidate', 'KTDeleteSubscriptionTrigger', 'ktstandard.triggers.subscription.delete');
// }}}

// {{{ KTDocumentMoveSubscriptionTrigger
class KTDocumentMoveSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate() {
        global $default;
        $oDocument =& $this->aInfo["document"];
        $oOldFolder =& $this->aInfo["old_folder"];
        $oNewFolder =& $this->aInfo["new_folder"];

        
        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oSubscriptionEvent->MoveDocument($oDocument, $oNewFolder, $oNewFolder);
    }
}
$oPlugin->registerTrigger('moveDocument', 'postValidate', 'KTDocumentMoveSubscriptionTrigger', 'ktstandard.triggers.subscription.moveDocument');
// }}}

// {{{ KTArchiveSubscriptionTrigger
class KTArchiveSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate() {
        global $default;
        $oDocument =& $this->aInfo["document"];

        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->ArchiveDocument($oDocument, $oFolder);
    }
}
$oPlugin->registerTrigger('archive', 'postValidate', 'KTArchiveSubscriptionTrigger', 'ktstandard.triggers.subscription.archive');
// }}}

// {{{ KTFolderSubscriptionAction
class KTFolderSubscriptionAction extends KTFolderAction {
    var $sName = 'ktstandard.subscription.foldersubscription';
    var $sDisplayName = 'Subscribe to folder';
    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oFolder->getID(), SubscriptionEvent::subTypes('Folder'))) {
            // KTFolderUnsubscriptionAction will display instead.
            return null;
        }
        return parent::getInfo();
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _("You are already subscribed to that document");
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _("You have been subscribed to this document");
            } else {
                $_SESSION['KTErrorMessage'][] = _("There was a problem subscribing you to this document");
            }
        }
        controllerRedirect('browse', 'fFolderId=' . $this->oFolder->getId());
        exit(0);
    }
}
$oPlugin->registerAction('foldersubscriptionaction', 'KTFolderSubscriptionAction', 'ktstandard.subscription.foldersubscription');
// }}}

// {{{ KTFolderUnsubscriptionAction
class KTFolderUnsubscriptionAction extends KTFolderAction {
    var $sName = 'ktstandard.subscription.folderunsubscription';
    var $sDisplayName = 'Unsubscribe from folder';

    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oFolder->getID(), SubscriptionEvent::subTypes('Folder'))) {
            return parent::getInfo();
        }
        return null;
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (!Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _("You were not subscribed to that folder");
        } else {
            $oSubscription = & Subscription::getByIDs($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _("You have been unsubscribed from this folder");
            } else {
                $_SESSION['KTErrorMessage'][] = _("There was a problem unsubscribing you from this folder");
            }
        }
        controllerRedirect('browse', 'fFolderId=' . $this->oFolder->getId());
        exit(0);
    }
}
$oPlugin->registerAction('foldersubscriptionaction', 'KTFolderUnsubscriptionAction', 'ktstandard.subscription.folderunsubscription');
// }}}

// {{{ KTSubscriptionManagePage
class KTSubscriptionManagePage extends KTStandardDispatcher {
    function do_main() {
        $this->aBreadcrumbs[] = array("name" => _("Subscription Management"));
        $aFolderSubscriptions = SubscriptionManager::retrieveUserSubscriptions(
            $this->oUser->getId(), SubscriptionEvent::subTypes('Folder'));
        $aDocumentSubscriptions = SubscriptionManager::retrieveUserSubscriptions(
            $this->oUser->getId(), SubscriptionEvent::subTypes('Document'));
        $bNoSubscriptions  = ((count($aFolderSubscriptions) == 0) && (count($aDocumentSubscriptions) == 0)) ? true : false;

        $oTemplate = $this->oValidator->validateTemplate('ktstandard/subscriptions/manage');

        $aTemplateData = array(
            'aFolderSubscriptions' => $aFolderSubscriptions,
            'aDocumentSubscriptions' => $aDocumentSubscriptions,
        );

        return $oTemplate->render($aTemplateData);
    }

    function do_removeSubscriptions() {
        $foldersubscriptions = KTUtil::arrayGet($_REQUEST, 'foldersubscriptions');
        $documentsubscriptions = KTUtil::arrayGet($_REQUEST, 'documentsubscriptions');
        if (empty($foldersubscriptions) && empty($documentsubscriptions)) {
            $this->errorRedirectToMain(_('No subscriptions were chosen'));
        }

        $iSuccesses = 0;
        $iFailures = 0;

        if (!empty($foldersubscriptions)) {
            foreach ($foldersubscriptions as $iSubscriptionId) {
                $oSubscription = Subscription::get($iSubscriptionId, SubscriptionEvent::subTypes('Folder'));
                if ($oSubscription) {
                    $oSubscription->delete();
                    $iSuccesses++;
                } else {
                    $iFailures++;
                }
            }
        }

        if (!empty($documentsubscriptions)) {
            foreach ($documentsubscriptions as $iSubscriptionId) {
                $oSubscription = Subscription::get($iSubscriptionId, SubscriptionEvent::subTypes('Document'));
                if ($oSubscription) {
                    $oSubscription->delete();
                    $iSuccesses++;
                } else {
                    $iFailures++;
                }
            }
        }

        $sMessage = _("Subscriptions removed") . ": ";
        if ($iFailures) {
            $sMessage .= sprintf(_('%d successful, %d failures'), $iSuccesses, $iFailures);
        } else {
            $sMessage .= sprintf('%d', $iSuccesses);
        }
        $this->successRedirectToMain($sMessage);
        exit(0);
    }
}
$oPlugin->registerPage('manage', 'KTSubscriptionManagePage', __FILE__);
// }}}
