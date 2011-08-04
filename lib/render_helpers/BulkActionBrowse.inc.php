<?php

require_once(KT_LIB_DIR . '/render_helpers/BrowseView.inc.php');

/**
 * Default user browse view class.
 * Does not differ from regular browse view, so nothing here.  Kind of silly but does allow a nicer name.
 */
class BulkActionBrowse extends UserBrowseView {
	private $action;
	public $showSelection = false;

	public function setAction($action)
	{
		$this->action = $action;
	}

    protected function getFolderActionMenu()
    {
		$share = '<li class="action_share_folder [actions.share_folder]"><a href="#" onclick="javascript:kt.app.sharewithusers.shareContentWindow(\'[id]\',\'[item_type]\',\'[user_id]\');">Share This Folder</a></li>';
		$rename = '<li class="action_rename_folder [actions.rename]"><a href="action.php?kt_path_info=ktcore.actions.folder.rename&fFolderId=[id]">Rename Folder</a></li>';
		$subscribe = '<!-- <li class="[actions.subscribe]"><a href="#" onclick=\'alert("JavaScript to be modified")\'>Subscribe to Folder</a></li> -->';
		$transactions = '<li class="action_view_transactions [actions.view_transactions]"><a href="action.php?kt_path_info=ktcore.actions.folder.transactions&fFolderId=[id]">View Folder Activity</a></li>';
		$separator = '<li class="separator[separatorA]"></li>';
    	if($this->action == 'copy') {
			$actions = $share . $rename . $subscribe . $separator. $transactions;
    	} else {
			$actions = $transactions;
    	}

        return '<td>
                    <ul class="folder actionMenu">
                        <li class="actionIcon actions">
                            <ul>
                            ' . $actions . '
                            </ul>
                        </li>
                    </ul>
				</td>';
    }

	protected function getDocumentActionMenu($share_separator = null)
    {
    	if($this->action != 'copy') {
			return '';
    	}

        return '<ul class="doc actionMenu">
                                <!-- li class="actionIcon comments"></li -->
                                <li class="actionIcon actions">
                                    <ul>
                                        <li class="action_share_document [actions.share_document]"><a href="#" onclick="javascript:kt.app.sharewithusers.shareContentWindow(\'[id]\',\'[item_type]\',\'[user_id]\', \'[isfinalize_document]\');">Share This Document</a></li>
                                        '. $share_separator .'
                                        <li class="action_download [actions.download]"><a href="action.php?kt_path_info=ktcore.actions.document.view&fDocumentId=[id]">Download</a></li>
                                        <li class="separator separatorA[separatorA]"></li>
                                        <li class="action_copy [actions.copy]"><a href="#" onclick="javascript:{kt.app.copy.doTreeAction(\'copy\', [id]);}">Copy</a></li>
                                        <li class="action_cancel_checkout [actions.cancel_checkout]"><a href="#" onclick="kt.app.document_actions.checkout_actions(\'[id]\', \'cancelcheckout\');">Cancel Check-out</a></li>
                                        <li class="separator separatorC[separatorC]"></li>
                                        <li class="action_alerts [actions.alerts]"><a href="#" onclick="javascript:{alerts.displayAction(\'\', [id], \'browse-view\');}">Alerts</a></li>
                                        <li class="action_email [actions.email]"><a href="action.php?kt_path_info=ktcore.actions.document.email&fDocumentId=[id]">Email</a></li>
                                        <li class="separator separatorD[separatorD]"></li>
                                    </ul>
                                </li>
                            </ul>';
    }

    public function renderBulkActionMenu($items, $folder)
    {
    	return '';
    }
}

?>
