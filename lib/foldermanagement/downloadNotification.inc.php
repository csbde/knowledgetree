<?php

/**
 * Class handles the bulk download notification
 *
 * @author KnowledgeTree Team
 * @package Bulk Downloads
 */
class KTDownloadNotification
{
    /**
     * The error returned
     *
     * @access public
     * @var $error
     */
    public $error;
    
    /**
     * The user for which the download notification is being generated
     * 
     * @access private
     * @var $code
     */
    private $code;    

    /**
     * Constructor function for the class
     *
     * @author KnowledgeTree Team
     * @access public
     * @return KTElectronicSignatures
     */
    public function KTDownloadNotification($code)
    {
    	$this->code = $code;
    }

    /**
     * Returns the form displaying the download link (and option to cancel the download?)
     *
     * @author KnowledgeTree Team
     * @access public
     * @param string $head The heading for the form
     * @param string $request_type Determines the actions for the buttons
     * @return html
     */
    public function getNotificationForm($head)
    {
    	global $default;

        // check for the download link
        
        $link = DownloadQueue::getDownloadLink($this->code);
        $text = 'Download Now';
        
        $cancelAction = 'deleteDownload()';
        $closeAction = 'deferDownload()';
        
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/notifications/notification.BulkDownload');
        $aTemplateData = array(
            'head' => $head,
            'downloadLink' => $link,
            'downloadText' => $text,
            'exportCode' => $this->code,
            'cancelAction' => $cancelAction,
            'closeAction' => $closeAction
        );

        return $oTemplate->render($aTemplateData);
    }
    
    /**
     * Returns the error from the attempted signature
     *
     * @author KnowledgeTree Team
     * @access public
     * @return string
     */
    public function getError()
    {
        return '<div class="error">'.$this->error.'</div>';
    }

    /**
     * Displays a button for closing the panel
     *
     * @author KnowledgeTree Team
     * @access public
     * @param string $request_type Determines the action taken on close - close = redirect action | null = close panel action
     * @param string $request Optional. Used within the close action.
     * @return string
     */
    public function getCloseButton($request_type, $request = null)
    {
        switch ($request_type){
            case 'close':
                $cancelAction = "window.location.href = '{$request}'";
                break;

            default:
                $cancelAction = "panel_close()";
        }

        return '<div class="form_actions" style="margin-top: 30px;">
            <a href="#" onclick="javascript: '.$cancelAction.'">'._kt('Close').'</a>
            </div>';
    }
}

?>