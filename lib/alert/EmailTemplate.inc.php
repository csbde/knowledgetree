<?php

require_once(KT_LIB_DIR . "/templating/templating.inc.php");

/**
 * Represents an email template
 *
 */
class EmailTemplate{
	/** template */
	var $sTemplate;
	/** template data */
	var $aTemplateData;
	
	function EmailTemplate($sTemplate, $aTemplateData = array()){
		$this->sTemplate = $sTemplate;
		$this->aTemplateData = $aTemplateData;
	}
	
	function getTemplate(){
		return $this->sTemplate;
	}
	
	function setTemplate($sTemplate){
		$this->sTemplate = $sTemplate;
	}
	
	function getTemplateData(){
		return $this->aTemplateData;
	}
	
	function setTemplateData($aTemplateData){
		$this->aTemplateData = $aTemplateData;
	}
	
	/**
	 * Renders template to a valid email body.
	 *
	 * @return HTML email body
	 */
	function getBody(){
		$oTemplating =& KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate($this->sTemplate);
		return "<html><body>".$oTemplate->render($this->aTemplateData)."</body></html>";
	}	
	
}










?>