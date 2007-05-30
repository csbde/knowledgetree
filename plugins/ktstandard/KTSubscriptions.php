<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

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
    var $autoRegister = true;
    
    function KTSubscriptionPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Subscription Plugin');
        return $res;
    }        

    function setup() {
        $this->registerPortlet('browse', 'KTSubscriptionPortlet',
            'ktcore.portlets.subscription', __FILE__);
        $this->registerAction('documentsubscriptionaction', 'KTDocumentSubscriptionAction',
            'ktstandard.subscription.documentsubscription');
        $this->registerAction('documentsubscriptionaction', 'KTDocumentUnsubscriptionAction',
            'ktstandard.subscription.documentunsubscription');
        $this->registerTrigger('checkout', 'postValidate', 'KTCheckoutSubscriptionTrigger',
            'ktstandard.triggers.subscription.checkout');
        $this->registerTrigger('edit', 'postValidate', 'KTEditSubscriptionTrigger',
            'ktstandard.triggers.subscription.checkout');            
        $this->registerTrigger('delete', 'postValidate', 'KTDeleteSubscriptionTrigger',
            'ktstandard.triggers.subscription.delete');
        $this->registerTrigger('moveDocument', 'postValidate', 'KTDocumentMoveSubscriptionTrigger',
            'ktstandard.triggers.subscription.moveDocument');
        $this->registerTrigger('archive', 'postValidate', 'KTArchiveSubscriptionTrigger',
            'ktstandard.triggers.subscription.archive');
        $this->registerTrigger('discussion', 'postValidate', 'KTDiscussionSubscriptionTrigger',
            'ktstandard.triggers.subscription.archive');          
        $this->registerAction('foldersubscriptionaction', 'KTFolderSubscriptionAction',
            'ktstandard.subscription.foldersubscription');
        $this->registerAction('foldersubscriptionaction', 'KTFolderUnsubscriptionAction',
            'ktstandard.subscription.folderunsubscription');
        $this->registerPage('manage', 'KTSubscriptionManagePage');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTSubscriptionPlugin', 'ktstandard.subscriptions.plugin', __FILE__);

// {{{ KTSubscriptionPortlet
class KTSubscriptionPortlet extends KTPortlet {
    function KTSubscriptionPortlet() {
        parent::KTPortlet(_kt("Subscriptions"));
    }

    function render() {
        if (!$this->oDispatcher->oDocument && !$this->oDispatcher->oFolder) {
            return null;
        }
        if ($this->oDispatcher->oUser->isAnonymous()) {
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

        $this->actions[] = array("name" => _kt("Manage subscriptions"), "url" => $this->oPlugin->getPagePath('manage'));

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/actions_portlet");
        $aTemplateData = array(
            "context" => $this,
        );
        return $oTemplate->render($aTemplateData);
    }
}
// }}}

// {{{ KTDocumentSubscriptionAction
class KTDocumentSubscriptionAction extends KTDocumentAction {
    var $sName = 'ktstandard.subscription.documentsubscription';

    function getDisplayName() {
        return _kt('Subscribe to document');
    }

    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            return null;
        }
        return parent::getInfo();
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _kt("You are already subscribed to that document");
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _kt("You have been subscribed to this document");
            } else {
                $_SESSION['KTErrorMessage'][] = _kt("There was a problem subscribing you to this document");
            }
        }
        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }
}
// }}}

// {{{ KTDocumentUnsubscriptionAction
class KTDocumentUnsubscriptionAction extends KTDocumentAction {
    var $sName = 'ktstandard.subscription.documentunsubscription';

    function getDisplayName() {
        return _kt('Unsubscribe from document');
    }

    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            return parent::getInfo();
        }
        return null;
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (!Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _kt("You were not subscribed to that document");
        } else {
            $oSubscription = & Subscription::getByIDs($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _kt("You have been unsubscribed from this document");
            } else {
                $_SESSION['KTErrorMessage'][] = _kt("There was a problem unsubscribing you from this document");
            }
        }
        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }
}
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
// }}}


// {{{ KTCheckoutSubscriptionTrigger
class KTEditSubscriptionTrigger {
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
        $oSubscriptionEvent->ModifyDocument($oDocument, $oFolder);

    }
}
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
        $oSubscriptionEvent->ArchivedDocument($oDocument, $oFolder);
    }
}
// }}}



class KTDiscussionSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate() {
        $oDocument =& $this->aInfo["document"];
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        
        $oSubscriptionEvent->DiscussDocument($oDocument, $oFolder);
    }
}
// }}}

// {{{ KTFolderSubscriptionAction
class KTFolderSubscriptionAction extends KTFolderAction {
    var $sName = 'ktstandard.subscription.foldersubscription';

    function getDisplayName() {
        return _kt('Subscribe to folder');
    }

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
            $_SESSION['KTErrorMessage'][] = _kt("You are already subscribed to that folder");
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _kt("You have been subscribed to this folder");
            } else {
                $_SESSION['KTErrorMessage'][] = _kt("There was a problem subscribing you to this folder");
            }
        }
        controllerRedirect('browse', 'fFolderId=' . $this->oFolder->getId());
        exit(0);
    }
}
// }}}

// {{{ KTFolderUnsubscriptionAction
class KTFolderUnsubscriptionAction extends KTFolderAction {
    var $sName = 'ktstandard.subscription.folderunsubscription';

    function getDisplayName() {
        return _kt('Unsubscribe from folder');
    }

    function getInfo() {
        if (Subscription::exists($this->oUser->getID(), $this->oFolder->getID(), SubscriptionEvent::subTypes('Folder'))) {
            return parent::getInfo();
        }
        return null;
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (!Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _kt("You were not subscribed to that folder");
        } else {
            $oSubscription = & Subscription::getByIDs($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $_SESSION['KTInfoMessage'][] = _kt("You have been unsubscribed from this folder");
            } else {
                $_SESSION['KTErrorMessage'][] = _kt("There was a problem unsubscribing you from this folder");
            }
        }
        controllerRedirect('browse', 'fFolderId=' . $this->oFolder->getId());
        exit(0);
    }
}
// }}}

// {{{ KTSubscriptionManagePage
class KTSubscriptionManagePage extends KTStandardDispatcher {
    function do_main() {
        $this->aBreadcrumbs[] = array("name" => _kt("Subscription Management"));
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
            $this->errorRedirectToMain(_kt('No subscriptions were chosen'));
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

        $sMessage = _kt("Subscriptions removed") . ": ";
        if ($iFailures) {
            $sMessage .= sprintf(_kt('%d successful, %d failures'), $iSuccesses, $iFailures);
        } else {
            $sMessage .= sprintf('%d', $iSuccesses);
        }
        $this->successRedirectToMain($sMessage);
        exit(0);
    }
}
// }}}
