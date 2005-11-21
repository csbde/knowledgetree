<?php

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/actions/portletregistry.inc.php');
require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');
require_once(KT_LIB_DIR . '/subscriptions/SubscriptionEngine.inc');
require_once(KT_LIB_DIR . '/subscriptions/SubscriptionConstants.inc');
require_once(KT_LIB_DIR . '/subscriptions/SubscriptionManager.inc');

$oKTActionRegistry =& KTActionRegistry::getSingleton();
$oPRegistry =& KTPortletRegistry::getSingleton();
$oTRegistry =& KTTriggerRegistry::getSingleton();

// {{{ KTDocumentSubscriptionAction
class KTDocumentSubscriptionAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.subscription';
    var $sDisplayName = 'Subscribe to document';
    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
            return null;
        }
        return parent::getInfo();
    }

    function do_main() {
        $iSubscriptionType = SubscriptionConstants::subscriptionType("DocumentSubscription");
        if (Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = "You are already subscribed to that document";
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = "You have been subscribed to this document";
            } else {
                $_SESSION['KTErrorMessage'][] = "There was a problem subscribing you to this document";
            }
        }
        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }
}
$oKTActionRegistry->registerAction('subscriptionaction', 'KTDocumentSubscriptionAction', 'ktcore.actions.document.subscription');
// }}}

// {{{ KTDocumentUnsubscriptionAction
class KTDocumentUnsubscriptionAction extends KTDocumentAction {
    var $sName = 'ktcore.actions.document.unsubscription';
    var $sDisplayName = 'Unsubscribe from document';
    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
            return parent::getInfo();
        }
        return null;
    }

    function do_main() {
        $iSubscriptionType = SubscriptionConstants::subscriptionType("DocumentSubscription");
        if (!Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = "You were not subscribed to that document";
        } else {
            $oSubscription = & Subscription::getByIDs($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = "You have been unsubscribed from this document";
            } else {
                $_SESSION['KTErrorMessage'][] = "There was a problem unsubscribing you from this document";
            }
        }
        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }
}
$oKTActionRegistry->registerAction('subscriptionaction', 'KTDocumentUnsubscriptionAction', 'ktcore.actions.document.unsubscription');
// }}}

// {{{ KTSubscriptionPortlet
class KTSubscriptionPortlet extends KTPortlet {
    function KTSubscriptionPortlet() {
        parent::KTPortlet("Subscriptions");
    }

    function render() {
        if (!$this->oDispatcher->oDocument && !$this->oDispatcher->oFolder) {
            return null;
        }
        if ($this->oDispatcher->oDocument) {
            $oKTActionRegistry =& KTActionRegistry::getSingleton();
            $actions = $oKTActionRegistry->getActions('subscriptionaction');
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

        $oTemplating = new KTTemplating;
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/actions_portlet");
        $aTemplateData = array(
            "context" => $this,
        );
        return $oTemplate->render($aTemplateData);
    }
}
$oPRegistry->registerPortlet('browse', 'KTSubscriptionPortlet', 'ktcore.portlets.subscription', '/plugins/ktcore/KTPortlets.php');
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
        $count = SubscriptionEngine::fireSubscription($oDocument->getId(), SubscriptionConstants::subscriptionAlertType("CheckOutDocument"),
                 SubscriptionConstants::subscriptionType("DocumentSubscription"),
                 array( "folderID" => $oDocument->getFolderID(),
                        "modifiedDocumentName" => $oDocument->getName() ));
        $default->log->info("checkOutDocumentBL.php fired $count subscription alerts for checked out document " . $oDocument->getName());
    }
}
$oTRegistry->registerTrigger('checkout', 'postValidate', 'KTCheckoutSubscriptionTrigger', 'ktstandard.triggers.subscription.checkout');
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
        $count = SubscriptionEngine::fireSubscription($oDocument->getId(),
            SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument"),
            SubscriptionConstants::subscriptionType("DocumentSubscription"),
            array(
                "folderID" => $oDocument->getFolderID(),
                "removedDocumentName" => $oDocument->getName(),
                "folderName" => Folder::getFolderDisplayPath($oDocument->getFolderID()),
            ));
        $default->log->info("deleteDocumentBL.php fired $count subscription alerts for removed document " . $oDocument->getName());

        // remove all document subscriptions for this document
        if (SubscriptionManager::removeSubscriptions($oDocument->getId(), SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
            $default->log->info("deleteDocumentBL.php removed all subscriptions for this document");
        } else {
            $default->log->error("deleteDocumentBL.php couldn't remove document subscriptions");
        }
    }
}
$oTRegistry->registerTrigger('delete', 'postValidate', 'KTDeleteSubscriptionTrigger', 'ktstandard.triggers.subscription.delete');
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

        // fire subscription alerts for the moved document (and the folder its in)
        $count = SubscriptionEngine::fireSubscription($oDocument->getId(), SubscriptionConstants::subscriptionAlertType("MovedDocument"),
            SubscriptionConstants::subscriptionType("DocumentSubscription"),
            array(
                "folderID" => $oOldFolder->getId(),
                "modifiedDocumentName" => $oDocument->getName(),
                "oldFolderName" => Folder::getFolderName($oOldFolder->getId()),
                "newFolderName" => Folder::getFolderName($oNewFolder->getID()),
            )
        );
        $default->log->info("moveDocumentBL.php fired $count (folderID=$fFolderID) folder subscription alerts for moved document " . $oDocument->getName());

        // fire folder subscriptions for the destination folder
        $count = SubscriptionEngine::fireSubscription($oNewFolder->getId(), SubscriptionConstants::subscriptionAlertType("MovedDocument"),
            SubscriptionConstants::subscriptionType("FolderSubscription"),
            array(
                "folderID" => $oOldFolder->getId(),
                "modifiedDocumentName" => $oDocument->getName(),
                "oldFolderName" => Folder::getFolderName($oOldFolder->getId()),
                "newFolderName" => Folder::getFolderName($oNewFolder->getId()),
            )
        );
        $default->log->info("moveDocumentBL.php fired $count (folderID=$fFolderID) folder subscription alerts for moved document " . $oDocument->getName());
    }
}
$oTRegistry->registerTrigger('moveDocument', 'postValidate', 'KTDocumentMoveSubscriptionTrigger', 'ktstandard.triggers.subscription.moveDocument');
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

        $count = SubscriptionEngine::fireSubscription($fDocumentID, SubscriptionConstants::subscriptionAlertType("ArchivedDocument"),
            SubscriptionConstants::subscriptionType("DocumentSubscription"),
            array(
                "folderID" => $oDocument->getFolderID(),
                "modifiedDocumentName" => $oDocument->getName()
            ));
        $default->log->info("archiveDocumentBL.php fired $count subscription alerts for archived document " . $oDocument->getName());
    }
}
$oTRegistry->registerTrigger('archive', 'postValidate', 'KTArchiveSubscriptionTrigger', 'ktstandard.triggers.subscription.archive');
// }}}
