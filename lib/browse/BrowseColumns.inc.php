<?php

/** BrowserColumns
 * 
 *  Presentation and render logic for the different columns.  Each has two
 *  major methods:
 *
 *     function renderHeader($sReturnURL)
 *     function renderData($aDataRow)
 *  
 *  renderHeader returns the _content_ of the header row.
 *  renderData returns the _content_ of the body row.
 */
 
require_once(KT_LIB_DIR . "/database/dbutil.inc");
require_once(KT_LIB_DIR . '/users/User.inc');

require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');


class BrowseColumn {
    var $label = null;
    var $sort_on = false;
    var $sort_direction = "asc";
    var $name = "-";
    
    function BrowseColumn($sLabel, $sName) { 
       $this->label = $sLabel; 
       $this->name = $sName; 
    }
    // FIXME is it _really_ worth using a template here?
    function renderHeader($sReturnURL) { 
        $text = _("Abstract") . ": " . $this->label; 
        $href = $sReturnURL . "&sort_on=" . $this->name . "&sort_order=";
        $href .= $this->sort_direction == "asc" ? "desc" : "asc" ;
        
        return '<a href="' . $href . '">'.$text.'</a>';
        
    }
    function renderData($aDataRow) { 
       if ($aDataRow["type"] == "folder") {
           return $this->name . ": ". print_r($aDataRow["folder"]->getName(), true);            
        } else {
           return $this->name . ": ". print_r($aDataRow["document"]->getName(), true); 
        }
    }
    function setSortedOn($bIsSortedOn) { $this->sort_on = $bIsSortedOn; }
    function getSortedOn() { return $this->sort_on; }
    function setSortDirection($sSortDirection) { $this->sort_direction = $sSortDirection; }
    function getSortDirection() { return $this->sort_direction; }
    
    function addToFolderQuery() { return array(null, null, null); }
    function addToDocumentQuery() { return array(null, null, null); }
}

class TitleColumn extends BrowseColumn {
    var $aOptions = array();
    function setOptions($aOptions) {
        $this->aOptions = $aOptions;
    }
    // unlike others, this DOESN'T give its name.
    function renderHeader($sReturnURL) { 
        $text = _("Title");
        $href = $sReturnURL . "&sort_on=" . $this->name . "&sort_order=";
        $href .= $this->sort_direction == "asc" ? "desc" : "asc" ;
        
        return '<a href="' . $href . '">'.$text.'</a>';
        
    }

    function renderFolderLink($aDataRow) {
        $outStr = '<a href="' . $this->buildFolderLink($aDataRow) . '">';
        $outStr .= $aDataRow["folder"]->getName();
        $outStr .= '</a>';
        return $outStr;
    }

    function renderDocumentLink($aDataRow) {
        $outStr = '<a href="' . $this->buildDocumentLink($aDataRow) . '" title="' . $aDataRow["document"]->getFilename().'">';
        $outStr .= $aDataRow["document"]->getName();
        $outStr .= '</a>';
        return $outStr;
    }

    function buildDocumentLink($aDataRow) {
        $baseurl = KTUtil::arrayGet($this->aOptions, "documenturl", $GLOBALS['KTRootUrl'] . '/view.php');
        return $baseurl . '?fDocumentId=' .  $aDataRow["document"]->getId();
    }

    function buildFolderLink($aDataRow) {
        $baseurl = KTUtil::arrayGet($this->aOptions, "folderurl", "");
        return $baseurl . '?fFolderId='.$aDataRow["folder"]->getId();
    }
    
    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) { 
       $outStr = '';
       if ($aDataRow["type"] == "folder") {
           $outStr .= '<span class="contenttype folder">';
           $outStr .= $this->renderFolderLink($aDataRow);
           $outStr .= '</span>';           
        } else {
           $outStr .= '<span class="contenttype '.$this->_mimeHelper($aDataRow["document"]->getMimeTypeId()).'">';
           $outStr .= $this->renderDocumentLink($aDataRow);
           $outStr .= ' (' . $this->prettySize($aDataRow["document"]->getSize()) . ')';
           $outStr .= '</span>';
        }
        return $outStr;
    }
    
    function prettySize($size) {
        $finalSize = $size;
        $label = 'b';
        
        if ($finalSize > 1000) { $label='Kb'; $finalSize = floor($finalSize/1000); }
        if ($finalSize > 1000) { $label='Mb'; $finalSize = floor($finalSize/1000); }
        return $finalSize . $label;
    }
    
    function _mimeHelper($iMimeTypeId) {
        // FIXME lazy cache this.
        $sQuery = 'SELECT icon_path FROM mime_types WHERE id = ?';
        $res = DBUtil::getOneResult(array($sQuery, array($iMimeTypeId)));
        
        if ($res[0] !== null) {
           return $res[0];
        } else {
           return 'unspecified_type';
        }
    }
}

class DateColumn extends BrowseColumn {
    var $field_function;
    
    // $sDocumentFieldFunction is _called_ on the document.
    function DateColumn($sLabel, $sName, $sDocumentFieldFunction) {
        $this->field_function = $sDocumentFieldFunction;
        parent::BrowseColumn($sLabel, $sName);
        
    }
    
    function renderHeader($sReturnURL) { 
        $text = $this->label;
        $href = $sReturnURL . "&sort_on=" . $this->name . "&sort_order=";
        $href .= $this->sort_direction == "asc" ? "desc" : "asc" ;
        
        return '<a href="' . $href . '">'.$text.'</a>';
        
    }
    
    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) { 
       $outStr = '';
       if ($aDataRow["type"] == "folder") {
           $outStr = '&nbsp;';       // no-op on folders.
        } else {
           $fn = $this->field_function;
           $dColumnDate = strtotime($aDataRow["document"]->$fn());
           
           // now reformat this into something "pretty"
           $outStr = date("d M, Y  H\\hi", $dColumnDate);
        }
        return $outStr;
    }
    
    function _mimeHelper($iMimeTypeId) {
        // FIXME lazy cache this.
        $sQuery = 'SELECT icon_path FROM mime_types WHERE id = ?';
        $res = DBUtil::getOneResult(array($sQuery, array($iMimeTypeId)));
        
        if ($res[0] !== null) {
           return $res[0];
        } else {
           return 'unspecified_type';
        }
    }
}


class UserColumn extends BrowseColumn {
    var $field_function;
    
    // $sDocumentFieldFunction is _called_ on the document.
    function UserColumn($sLabel, $sName, $sDocumentFieldFunction) {
        $this->field_function = $sDocumentFieldFunction;
        parent::BrowseColumn($sLabel, $sName);
        
    }
    
    function renderHeader($sReturnURL) { 
        $text = $this->label;
        $href = $sReturnURL . "&sort_on=" . $this->name . "&sort_order=";
        $href .= $this->sort_direction == "asc" ? "desc" : "asc" ;
        
        return '<a href="' . $href . '">'.$text.'</a>';
        
    }
    
    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) { 
        $outStr = '';
        $fn = $this->field_function;
        $iUserId = null;
        if ($aDataRow["type"] == "folder") {
            if (method_exists($aDataRow['folder'], $fn)) {
                $iUserId = $aDataRow['folder']->$fn(); // FIXME this should check if the function exists first.
            }
        } else {
            if (true) {//(method_exists($aDataRow['document'], $fn)) {
                $iUserId = $aDataRow['document']->$fn(); // FIXME this should check if the function exists first.
            }
        }
        $oUser = User::get($iUserId);
        if (PEAR::isError($oUser) || $oUser == false) {
            $outStr = '&nbsp;';
        } else {
            $outStr = $oUser->getName();
        }
        return $outStr;
    }
}

// use the _name_ parameter + _f_ + id to create a non-clashing checkbox.

class SelectionColumn extends BrowseColumn {

    function renderHeader($sReturnURL) { 
        // FIXME clean up access to oPage.
        global $main;
        $main->requireJSResource("resources/js/toggleselect.js");
        
        return '<input type="checkbox" title="toggle all" onclick="toggleSelectFor(this, \''.$this->name.'\')">';
        
    }
    
    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) { 
        $localname = $this->name;
        if ($aDataRow["type"] === "folder") { $localname .= "_f[]"; $v = $aDataRow["folderid"]; }
        else { $localname .= "_d[]"; $v = $aDataRow["docid"]; }
        
        return '<input type="checkbox" name="' . $localname . '" onactivate="activateRow(this)" value="' . $v . '"/>';
    }
    
    function _mimeHelper($iMimeTypeId) {
        // FIXME lazy cache this.
        $sQuery = 'SELECT icon_path FROM mime_types WHERE id = ?';
        $res = DBUtil::getOneResult(array($sQuery, array($iMimeTypeId)));
        
        if ($res[0] !== null) {
           return $res[0];
        } else {
           return 'unspecified_type';
        }
    }
}


class WorkflowColumn extends BrowseColumn {

    function renderHeader($sReturnURL) {         
        $text = $this->label; 
        $href = $sReturnURL . "&sort_on=" . $this->name . "&sort_order=";
        $href .= $this->sort_direction == "asc" ? "desc" : "asc" ;
        
        return '<a href="' . $href . '">'.$text.'</a>';
    }
    
    // use inline, since its just too heavy to even _think_ about using smarty.
    function renderData($aDataRow) { 
        $localname = $this->name;

        
        // only _ever_ show this folder documents.
        if ($aDataRow["type"] === "folder") { 
            return '&nbsp;';
        }
        
        $oWorkflow = KTWorkflowUtil::getWorkflowForDocument($aDataRow['document']);
        $oState = KTWorkflowUtil::getWorkflowStateForDocument($aDataRow['document']);
        if (($oState == null) || ($oWorkflow == null)) {
            return '&mdash;';
        } else {
            return $oState->getName() . ' <span class="descriptiveText">(' . $oWorkflow->getName() . ')</span>';
        }
    }
}

?>
