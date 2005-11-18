<?php

$oKTActionRegistry =& KTActionRegistry::getSingleton();
$oPRegistry =& KTPortletRegistry::getSingleton();

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
