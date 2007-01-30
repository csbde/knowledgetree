<?php
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

class PDFGeneratorAction extends KTDocumentAction {
    var $sName = 'ktstandard.pdf.action';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = 'Generate PDF';
    var $aAcceptedMimeTypes = array('doc', 'ods', 'odt');
    
    function getDisplayName() {
        // check if open office and plugin available
        $this->HAVE_GRAPHVIZ = false;
        $dotCommand = KTUtil::findCommand("ui/dot", 'dot');
 		if (!empty($dotCommand)) {
     		$this->HAVE_GRAPHVIZ = true;
     		$this->dotCommand = $dotCommand;
	        $sDocType = $this->getMimeExtension();
	        // make sure that the selected document id of an acceptable extension
	        foreach($this->aAcceptedMimeTypes as $acceptType){
	        	if($acceptType == $sDocType){
	        		return _kt('Generate PDF');
	        	}
	        }
 		}
        return '';
    }

    function do_main() {     
        $oTemplating =& KTTemplating::getSingleton();
       	$oTemplate = $oTemplating->loadTemplate('ktstandard/PDFPlugin/PDFPlugin');
       	
       	$aTemplateData = array(
			'context' => $this,
		);
      
        return $oTemplate->render($aTemplateData);
    }
    
    /**
    * Method for getting the MIME type extension for the current document.
    *
    * @return string mime time extension
    */
    function getMimeExtension() {
        $oDocument = $this->oDocument;
    	$iMimeTypeId = $oDocument->getMimeTypeID();
    	$mimetypename = KTMime::getMimeTypeName($iMimeTypeId); // mime type name
    	
        $sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT filetypes FROM " . $sTable . " WHERE mimetypes = ?";
        $aQuery = array($sQuery, array($mimetypename));
        $res = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($res)) { 
            return $res;
        } else if (count($res) != 0){
            return $res[0]['filetypes'];             
        }
        
        return _kt('Unknown Type');
    }
}
?>