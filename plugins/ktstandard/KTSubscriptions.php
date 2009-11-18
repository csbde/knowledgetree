<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 * 
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
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

function wrapString($str, $length = 20){
    // Wrap string to given character length (content rendered from ajax doesn't render correctly in ie)
    $len = mb_strlen($str);
    $out = '';
    $pos = 0;
    while($len > $length && $pos !== false){
        $pos = mb_strpos($str, ' ', $length);
        $line = mb_strcut($str, 0, $pos+1);
        $str = mb_strcut($str, $pos+1);
        $len = mb_strlen($str);
        $out .= $line.'<br>';
    }
    $out .= $str;
    return $out;
}

// {{{ KTSubscriptionPortlet
class KTSubscriptionPortlet extends KTPortlet {
    function KTSubscriptionPortlet() {
        parent::KTPortlet(_kt("Subscriptions"));
    }

    function render() {
        if ($this->oDispatcher->oUser->isAnonymous()) {
            return null;
        }

        if($this->oDispatcher->oDocument){
            $oObject = $this->oDispatcher->oDocument;
            $type = 'documentsubscriptionaction';
        }else if($this->oDispatcher->oFolder){
            $oObject = $this->oDispatcher->oFolder;
            $type = 'foldersubscriptionaction';
        }else{
            // not in a folder or document
            return null;
        }

        global $default;
		$serverName = $default->serverName;
		$base_url = ($default->sslEnabled ? 'https' : 'http') .'://'.$serverName;
        $oUser = $this->oDispatcher->oUser;
        $this->actions = array();

        // Get the actions
        $oKTActionRegistry =& KTActionRegistry::getSingleton();
        $actions = $oKTActionRegistry->getActions($type);

        foreach ($actions as $aAction){
            list($sClassName, $sPath) = $aAction;
            $oSubscription = new $sClassName($oObject, $oUser);
            $actionInfo = $oSubscription->getInfo();
            if(!empty($actionInfo)){
                if(isset($actionInfo['active']) && $actionInfo['active'] == 'no'){
                    $nonActiveUrl = $base_url.$actionInfo['url'];
                    $nonActiveName = $actionInfo['name'];
                }else {
                    $aInfo = $actionInfo;
                }
            }
        }

        // Create js script
        $url = $base_url.$aInfo['url'];
        $script = '<script type="text/javascript">
            function doSubscribe(action){
                var respDiv = document.getElementById("response");
                var link = document.getElementById("subscribeLink");

                Ext.Ajax.request({
                    url: "'.$url.'",
                    success: function(response) {
                        respDiv.innerHTML = response.responseText;
                        respDiv.style.display = "block";
                        link.style.display = "none";
                        if(document.getElementById("subLink")){
                            document.getElementById("subLink").style.display = "none";
                        }
                    },
                    failure: function() {
                        respDiv.innerHTML = "'._kt('There was a problem with the subscription, please refresh the page and try again.').'";
                        respDiv.style.display = "block";
                    },
                    params: {
                        action: action
                    }
                });
            }
        </script>';

        $script .= "<a id='subscribeLink' style='cursor:pointer' onclick='javascript: doSubscribe(\"ajax\")'>{$aInfo['name']}</a>";

        $aInfo['js'] = $script;
        $this->actions[] = $aInfo;

        if(isset($aInfo['subaction'])){
            $subInfo = array();
            $subInfo['js'] = "<a id='subLink' style='cursor:pointer' onclick='javascript: doSubscribe(\"add_subfolders\")'>{$aInfo['subaction']}</a>";
            $this->actions[] = $subInfo;
        }

        $this->actions[] = array("name" => _kt("Manage subscriptions"), "url" => $this->oPlugin->getPagePath('manage'));
        $btn = '<div id="response" style="padding: 2px; margin-right: 10px; margin-left: 10px; background: #CCC; display:none;"></div>';

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("kt3/portlets/actions_portlet");
        $aTemplateData = array(
            'context' => $this,
            'btn' => $btn
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
        $aInfo = parent::getInfo();
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            $aInfo['active'] = 'no';
        }
        return $aInfo;
    }

    function do_ajax() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $str = _kt('You are already subscribed to that document');
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $str = _kt('You have been subscribed to this document');
            } else {
                $str = _kt('There was a problem subscribing you to this document');
            }
        }
        $str = wrapString($str);
        echo $str;
        exit(0);
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
        $aInfo = parent::getInfo();
        if (!Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            $aInfo['active'] = 'no';
        }
        return $aInfo;
    }

    function do_ajax() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (!Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $str = _kt('You are not subscribed to this document');
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $str = _kt('You have been unsubscribed from this document');
            } else {
                $str = _kt('There was a problem unsubscribing you from this document');
            }
        }
        $str = wrapString($str);
        echo $str;
        exit(0);
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

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo["document"];
        // fire subscription alerts for the checked out document

        if(!$bulk_action) {
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->CheckoutDocument($oDocument, $oFolder);
        }
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

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo["document"];

        // fire subscription alerts for the deleted document
        if(!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            //$oSubscriptionEvent->RemoveDocument($oDocument, $oFolder);
        }
    }
}
// }}}

// {{{ KTDocumentMoveSubscriptionTrigger
class KTDocumentMoveSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo["document"];
        $oOldFolder =& $this->aInfo["old_folder"];
        $oNewFolder =& $this->aInfo["new_folder"];

        if(!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oSubscriptionEvent->MoveDocument($oDocument, $oNewFolder, $oNewFolder);
        }
    }
}
// }}}

// {{{ KTArchiveSubscriptionTrigger
class KTArchiveSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo["document"];
        if(!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->ArchivedDocument($oDocument, $oFolder);
        }
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
        $indexer = Indexer::get();
        $indexer->index($oDocument, 'D');
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
        $aInfo = parent::getInfo();
        if (Subscription::exists($this->oUser->getID(), $this->oFolder->getID(), SubscriptionEvent::subTypes('Folder'))) {
            // KTFolderUnsubscriptionAction will display instead.
            $aInfo['active'] = 'no';
        }
        // return the url to the action for subfolders - display the Subscribe link, on clicking, display the include subfolders link.
        $aInfo['subaction'] = _kt('Subscribe to folder and <br>subfolders');
        return $aInfo;
    }

    function do_ajax() {
        $this->subscribe();
    }

    function do_add_subfolders() {
        $this->subscribe(true);
    }

    function subscribe($incSubFolders = false) {
        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $str = _kt('You are already subscribed to this folder');
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);

            if($incSubFolders){
                $oSubscription->setWithSubFolders(true);
            }

            $res = $oSubscription->create();
            if ($res) {
                if($incSubFolders){
                    $str = _kt('You have been subscribed to this folder and its subfolders');
                }else{
                    $str = _kt('You have been subscribed to this folder');
                }
            } else {
                $str = _kt('There was a problem subscribing you to this folder');
            }
        }
        echo wrapString($str);
        exit(0);
    }

    function do_main() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $_SESSION['KTErrorMessage'][] = _kt("You are already subscribed to this folder");
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
        $aInfo = parent::getInfo();
        if (!Subscription::exists($this->oUser->getID(), $this->oFolder->getID(), SubscriptionEvent::subTypes('Folder'))) {
            $aInfo['active'] = 'no';
        }
        return $aInfo;
    }

    function do_ajax() {
        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (!Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $str = _kt('You were not subscribed to that folder');
        } else {
            $oSubscription = & Subscription::getByIDs($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $str = _kt('You have been unsubscribed from this folder');
            } else {
                $str = _kt('There was a problem unsubscribing you from this folder');
            }
        }
        echo wrapString($str);
        exit(0);
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
