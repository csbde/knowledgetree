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
 */

require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/browse/columnregistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/reorderdisplay.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');
require_once(KT_LIB_DIR . "/widgets/FieldsetDisplayRegistry.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldsetDisplay.inc.php");
require_once(KT_LIB_DIR . "/widgets/widgetfactory.inc.php");
require_once(KT_LIB_DIR . "/validation/dispatchervalidation.inc.php");
require_once(KT_LIB_DIR . "/metadata/fieldsetregistry.inc.php");
require_once(KT_LIB_DIR . "/validation/validatorfactory.inc.php");


class ManageBrandDispatcher extends KTAdminDispatcher {

    private $maxLogoWidth = 313;
    private $maxLogoHeight = 50;
    public $supportedTypes = array('gif', 'png', 'pjpeg', 'jpe', 'jpeg', 'jpg', 'jfif', 'jfif-tbnl');
    
    function check() {

        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Manage Branding'));
        return parent::check();
    }

    function do_main() {
        $uploadLogoForm = $this->getUploadLogoForm();
        return $uploadLogoForm->render();
    }


    /**
     * Returns the upload logo form
     * @return KTForm 
     *
     */

    function getUploadLogoForm() {
        $this->oPage->setBreadcrumbDetails(_kt("Upload Logo"));

        $oForm = new KTForm;
        $oForm->setOptions(array(
                    'identifier' => 'ktcore.folder.branding',
                    'label' => _kt('Upload Logo'),
                    'submit_label' => _kt('Upload'),
                    'action' => 'upload',
                    'fail_action' => 'main',
                    'encoding' => 'multipart/form-data',
                    'context' => &$this,
                    'extraargs' => $this->meldPersistQuery("","",true),
                    'description' => _kt('You can upload a logo to brand your KnowledgeTree site.')
                    ));

        $oWF =& KTWidgetFactory::getSingleton();

        $widgets = array();
        $validators = array();

        // Adding the File Upload Widget
        $widgets[] = $oWF->get('ktcore.widgets.file', array(
                    'label' => _kt('Logo File'),
                    'required' => true,
                    'name' => 'file',
                    'id' => 'file',
                    'value' => '',
                    'description' => _kt("The logo's dimensions should be 313px width by 50px height. If your logo doesn't fit these dimensions, you can choose to scale it."),
                    ));
        
        //$aVocab['crop'] = 'Crop - Cut out a selection';
        $aVocab['scale'] = 'Scale - Stretch or Shrink to fit';
        $aVocab['nothing'] = 'Don\'t do anything <span class="descriptiveText">(My image has the correct dimensions)</span>';
        
		//Adding document type lookup widget
		$widgets[] = $oWF->get('ktcore.widgets.selection',array(
                    'label' => _kt('Fitting Image'),
				    'id' => 'logo_action',
                    'description' => _kt('How would you like to resize the image?'),
                    'name' => 'resize_method',
                    'vocab' => $aVocab,
                    'selected' => 'crop',
                    'label_method' => 'getName',
                    'simple_select' => true,
		));

        $oForm->setWidgets($widgets); 
        $oForm->setValidators($validators);

        // TODO: Should electronic signature be implemented for this?
        // Implement an electronic signature for accessing the admin section, it will appear every 10 minutes
        /* //Have to instanciate the oFolder
        global $default;
        $iFolderId = $this->oFolder->getId();
        if($default->enableESignatures){
            $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
            $heading = _kt('You are attempting to perform a bulk upload');
            $submit['type'] = 'button';
            $submit['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'ktcore.transactions.bulk_upload', 'bulk', 'bulk_upload_form', 'submit', {$iFolderId});";
        }else{
            $submit['type'] = 'submit';
            $submit['onclick'] = '';
        }
        */

        return $oForm;
    }


    /**
     * Returns the scale logo form
     *
     * This form will display a preview of all the possible sclaled combinations.
     * This includes:
     *
     * Stretched, Top Left Cropped, Proportional Stretch, Proportional Top Left Cropped
     *
     * @return KTForm 
     *
     */

    function getScaleLogoForm($logoItems = array()) {
        $this->oPage->setBreadcrumbDetails(_kt("Scale Logo"));

        $oForm = new KTForm;
        $oForm->setOptions(array(
                    'identifier' => 'ktcore.folder.branding',
                    'label' => _kt('Choose Logo'),
                    'submit_label' => _kt('Select'),
                    'action' => 'selectLogo',
                    'cancel_action' => 'main',
                    'fail_action' => 'main',
                    'encoding' => 'multipart/form-data',
                    'context' => &$this,
                    'extraargs' => $this->meldPersistQuery("","",true),
                    'description' => _kt('Choose a logo by clicking on one of the images')
                    ));

        $oWF =& KTWidgetFactory::getSingleton();

        $widgets = array();
        $validators = array();

        $logoFileName = 'var'.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$logoFileName;

        // Adding the image select widget (User will select the best image)
        $widgets[] = $oWF->get('ktcore.widgets.imageselect', array(
                    'label' => _kt('Logo Preview'),
                    'name' => $logoFileName,
                    'value' => $logoItems,
                    ));

        // Adding the Hidden FileName Input String
        $widgets[] = $oWF->get('ktcore.widgets.hidden', array(
                    'name' => 'kt_imageselect',
                    'value' => $logoItems[0],
                    ));

        $oForm->setWidgets($widgets);
        $oForm->setValidators($validators);

        return $oForm;
    }


    /**
     * Returns the crop logo form
     *
     * This form will assist the user in selecting an area of the image to use as the logo
     * within predefined dimensions required for the logo to fit properly into the page header.
     *
     * @return KTForm 
     *
     */

    function getCropLogoForm($logoFileName = '') {
        $this->oPage->setBreadcrumbDetails(_kt("Crop Logo"));

        $oForm = new KTForm;
        $oForm->setOptions(array(
                    'identifier' => 'ktcore.folder.branding',
                    'name' => 'crop_form',
                    'label' => _kt('Crop Logo'),
                    'submit_label' => _kt('Crop'),
                    'action' => 'crop',
                    'fail_action' => 'main',
                    'encoding' => 'multipart/form-data',
                    'context' => &$this,
                    'extraargs' => $this->meldPersistQuery("","",true),
                    'description' => _kt('Use this facility to ensure that the logo meets the required dimensions for the header.')
                    ));

        $oWF =& KTWidgetFactory::getSingleton();

        $widgets = array();
        $validators = array();

        $logoFile = 'var'.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$logoFileName;
        $ext = end(explode('.', end(explode(DIRECTORY_SEPARATOR, $logoFile))));
        $type = $ext;
        
        $imageWidth = $this->getImageWidth($logoFile, $type);
        $imageHeight = $this->getImageHeight($logoFile, $type);

        if ($imageWidth > $this->maxLogoWidth) {
            $imageWidth = $this->maxLogoWidth;
        }

        if ($imageHeight > $this->maxLogoHeight) {
            $imageHeight = $this->maxLogoHeight;
        }

        // Adding the Image Crop Widget
        $widgets[] = $oWF->get('ktcore.widgets.imagecrop', array(
                    'label' => _kt('Crop Logo'),
                    //'name' => 'Logo',
                    'value' => $logoFile,
                    'init_width' => $imageWidth,
                    'init_height' => $imageHeight,
                    'description' => _kt('To crop an area of the logo, click and drag the resizable rectangle over the image.'),
                    ));

        // Adding the Hidden FileName Input String
        $widgets[] = $oWF->get('ktcore.widgets.hidden', array(
                    'name' => 'logo_file_name',
                    'value' => $logoFileName,
                    ));

        // Adding the Hidden Coordinates X1
        $widgets[] = $oWF->get('ktcore.widgets.hidden', array(
                    'name' => 'crop_x1',
                    'value' => 'x1test',
                    ));

        // Adding the Hidden Coordinates Y1
        $widgets[] = $oWF->get('ktcore.widgets.hidden', array(
                    'name' => 'crop_y1',
                    'value' => '',
                    ));

        // Adding the Hidden Coordinates X2
        $widgets[] = $oWF->get('ktcore.widgets.hidden', array(
                    'name' => 'crop_x2',
                    'value' => '',
                    ));

        // Adding the Hidden Coordinates Y2
        $widgets[] = $oWF->get('ktcore.widgets.hidden', array(
                    'name' => 'crop_y2',
                    'value' => '',
                    ));
                    
        $oForm->setWidgets($widgets);
        $oForm->setValidators($validators);

        return $oForm;
    }

    /**
     * Returns the apply logo form
     *
     * This form will display a preview of the correctly sized logo and prompt the user to apply it.
     *
     * @return KTForm 
     *
     */

    function getApplyLogoForm($logoFileName = '') {
        $this->oPage->setBreadcrumbDetails(_kt("Apply Logo"));

        $oForm = new KTForm;
        $oForm->setOptions(array(
                    'identifier' => 'ktcore.folder.branding',
                    'label' => _kt('Apply Logo'),
                    'submit_label' => _kt('Apply'),
                    'action' => 'apply',
                    'cancel_action' => 'main',
                    'fail_action' => 'main',
                    'encoding' => 'multipart/form-data',
                    'context' => &$this,
                    'extraargs' => $this->meldPersistQuery("","",true),
                    'description' => _kt('Applying the logo will activate it in the header making it visible to all who access this site.')
                    ));

        $oWF =& KTWidgetFactory::getSingleton();

        $widgets = array();
        $validators = array();

        $logoFileName = 'var'.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$logoFileName;

        // Adding the Image Widget
        $widgets[] = $oWF->get('ktcore.widgets.image', array(
                    'label' => _kt('Logo Preview'),
                    'name' => $logoFileName, // title and alt attributes get set to this.
                    'value' => $logoFileName,
                    'width' => $this->maxLogoWidth,
                    'height' => $this->maxLogoHeight,
                    'div_border' => '1px solid #cccccc'
                    ));

        // Adding the Hidden FileName Input String
        $widgets[] = $oWF->get('ktcore.widgets.hidden', array(
                    'name' => 'logo_file_name',
                    'value' => $logoFileName,
                    ));

        $oForm->setWidgets($widgets);
        $oForm->setValidators($validators);

        return $oForm;
    }

    /*
     *  Action responsible for uploading the logo
     *
     */

    function do_upload(){
        global $default;
        
		$oForm = $this->getUploadLogoForm();
        $res = $oForm->validate();
        if (!empty($res['errors'])) {
            return $oForm->handleError();
        }
        
        //  Setting up the branding directory, logos will be stored in var/branding/
        $brandDir = $default->varDirectory.DIRECTORY_SEPARATOR.'branding';

		if (stristr(PHP_OS,'WIN')) {
            $brandDir = str_replace('/', '\\', $brandDir);
		}
        
        //if branding dir does not exist, generate one and add an index file to block access
        if (!file_exists($brandDir)) {
        	 mkdir($brandDir, 0755);
        	 touch($brandDir.DIRECTORY_SEPARATOR.'index.html');
        	 file_put_contents($brandDir.DIRECTORY_SEPARATOR.'index.html', 'You do not have permission to access this directory.');
        }

        $logoDir = $brandDir.DIRECTORY_SEPARATOR."logo";
        //if branding dir does not exist, generate one and add an index file to block access
        if (!file_exists($logoDir)) {
        	 mkdir($logoDir, 0755);
        	 touch($logoDir.DIRECTORY_SEPARATOR.'index.html');
        	 file_put_contents($logoDir.DIRECTORY_SEPARATOR.'index.html', 'You do not have permission to access this directory.');
        }

        $logoFileName = $_FILES['_kt_attempt_unique_file']['name'];

        //Changing to logo.jpg (Need to preserve extention as GD requires the exact image type to work)
        $ext = end(explode('.', $logoFileName));

        $type = $_FILES['_kt_attempt_unique_file']['type'];

        //Stage 1 filename based ext check:
        if (!$this->isSupportedExtension($ext)) {
            //If filename based extension isn't supported will try and guess based on mime type
            $default->log->error("Stage 1: Unsupported file type: '".$type."' for file: ':".$_FILES['_kt_attempt_unique_file']['name']."'");
            $ext = $this->getExtension($type);
            
            if (!$this->isSupportedExtension($ext)) {
                $default->log->error("Unsupported file type: '".$type."' for file: ':".$_FILES['_kt_attempt_unique_file']['name']."'");
                $this->errorRedirectToMain("The file you tried to upload is not supported.");
            }
        }
        
        $type = $ext; //GD Methods changed to accept extention as type
        
        $logoFileName = 'logo_tmp_'.md5(Date('ymd-hms')).'.'.$ext; //Fighting the browser cache here
        $logoFile = $logoDir.DIRECTORY_SEPARATOR.$logoFileName;

        // deleting old tmp file
		if (file_exists($logoFile)) {
			@unlink($logoFile);
		}

        //TODO: Test Upload Failure by setting the $logoFile to ''

        if(!move_uploaded_file($_FILES['_kt_attempt_unique_file']['tmp_name'], $logoFile)) {
            $default->log->error("Couldn't upload file from '".$_FILES['_kt_attempt_unique_file']['tmp_name']."' to '$logoFile'");
            $this->errorRedirectToMain("Couldn't upload file");
            exit(0);
        }

        $resizeMethod = $_REQUEST['data']['resize_method'];

        $relDir = 'var'.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR;

        switch ($resizeMethod) {
            case 'crop':
            
                if ($this->isImageCroppable($logoFile, $this->maxLogoWith, $this->maxLogoHeight, $type)) {
    
                    $retForm = $this->getCropLogoForm($logoFileName);
                } else {
                    $_SESSION['KTErrorMessage'][] = _kt("The image was too small to be cropped.");
                    $retForm = $this->getApplyLogoForm($logoFileName);
                }
                
                return $retForm->render();
                
            case 'scale':
            
                $logoFileNameStretched = 'logo_tmp_stretched_'.md5(Date('ymd-hms')).'.'.$ext; //Fighting the browser cache here
                $logoFileStretched = $logoDir.DIRECTORY_SEPARATOR.$logoFileNameStretched;

                $logoFileNameCropped = 'logo_tmp_cropped_'.md5(Date('ymd-hms')).'.'.$ext; //Fighting the browser cache here
                $logoFileCropped = $logoDir.DIRECTORY_SEPARATOR.$logoFileNameCropped;

                $default->log->info($logoFileStretched);
                $default->log->info($logoFileCropped);

                //Creating stretched image                
                $res = $this->scaleImage($logoFile, $logoFileStretched, $this->maxLogoWidth, $this->maxLogoHeight, $type, false, false);

                //Creating top-left cropped image
                $res = $this->scaleImage($logoFile, $logoFileCropped, $this->maxLogoWidth, $this->maxLogoHeight, $type, false, true);
                $res = $this->cropImage($logoFileCropped, $logoFileCropped, 0, 0, $this->maxLogoWidth, $this->maxLogoHeight, $type);

                $logoItem[] = $relDir.$logoFileNameStretched;
                $logoItem[] = $relDir.$logoFileNameCropped;
                
                $form = $this->getScaleLogoForm($logoItem);
                return $form->render();
                
            default:
                $form = $this->getApplyLogoForm($logoFileName);
                return $form->render();
        }        

    }


    /**
     * Returns the MIME of the filename, deducted from its extension
     * If the extension is unknown, returns "image/jpeg"
     */
    function getMime($filename)
    {
        $pos = strrpos($filename, '.');
        $extension = "";
        if ($pos !== false) {
            $extension = strtolower(substr($filename, $pos+1));
        }

        switch($extension) {
        case 'gif':
            return 'image/gif';
        case 'jfif':
            return 'image/jpeg';
        case 'jfif-tbnl':
            return 'image/jpeg';
        case 'png':
            return 'image/png';
        case 'jpe':
            return 'image/jpeg';
        case 'jpeg':
            return 'image/jpeg';
        case 'jpg':
            return 'image/jpeg';
        default:
            return 'image/jpeg';
        }
    }


    /**
     * Returns the MIME of the filename, deducted from its extension
     * If the extension is unknown, returns "image/jpeg"
     */
    function getExtension($type)
    {

        switch(strtolower($type)) {
        case 'image/gif':
            return 'gif';
        case 'image/jpeg':
            return 'jfif';
        case 'image/jpeg':
            return 'jfif-tbnl';
        case 'image/png':
            return 'png';
        case 'image/x-png':
            return 'png';
        case 'image/jpeg':
            return 'jpe';
        case 'image/jpeg':
            return 'jpeg';
        case 'image/jpeg':
            return 'jpg';
        case 'image/pjpeg':
            return 'jpg';
        default:
            return 'image/jpeg';
        }
    }


    /**
     * Returns TRUE of the extension is supported
     */
    function isSupportedExtension($extension)
    {
        if (in_array(strtolower($extension), $this->supportedTypes)) {
            return TRUE;
        }
        
        return FALSE;
    }

     
    /*
     *  This method uses the GD library to scale an image.
     *  - Supported images are jpeg, png  and gif
     *
     */
    public function scaleImage( $origFile, $destFile, $width, $height, $type = 'jpg', $scaleUp = false, $keepProportion = true) {
        global $default;
        
        //Requires the GD library if not exit gracefully
        if (!extension_loaded('gd')) {
            $default->log->error("The GD library isn't loaded");
            return false;
        }
        
        switch($type) {
            case 'jpg':
                $orig = imagecreatefromjpeg($origFile);
                break;
            case 'png':
                $orig = imagecreatefrompng($origFile);
                break;
            case 'gif':
                $orig = imagecreatefromgif($origFile);
                break;
            default:
                //Handle Error
                $default->log->error("Tried to crop an unsupported file type: $type");
                return false;
        }

        if($orig) {
            /*
             *  calculate the size of the new image.
             */
            $orig_x = imagesx($orig);
            $orig_y = imagesy($orig);
            
            if (($orig_x < $width) && ($orig_y < $height)) {
                //Image Qualifies for Upscaling
                //If we're not going to scale up then exit here.
                if (!$scaleUp) {
                    return true;
                }
            }
            
            $image_x = $width;
            $image_y = $height;

            //Constraining proportion
            if ($keepProportion) {
                $image_y = round(($orig_y * $image_x) / $orig_x); //Preserve proportion
            }

            /*
             * create the new image, and scale the original into it.
             */
            $image = imagecreatetruecolor($image_x, $image_y);
            imagecopyresampled($image, $orig, 0, 0, 0, 0, $image_x, $image_y, $orig_x, $orig_y);

            switch($type) {
                case 'jpg':
                    imagejpeg($image, $destFile);
                    break;
                case 'png':
                    imagepng($image, $destFile);
                    break;
                case 'gif':
                    imagegif($image, $destFile);
                    break;
                default:
                    //Handle Error
                    $default->log->error("Tried to crop an unsupported file type: $type");
                    return false;
            }

        } else {
            //Handle Error
            $default->log->error("Couldn't obtain a valid GD resource");
            $default->log->error($sourceFile);
            $default->log->error($destFile);
            return false;
        }
        
        return true;
    }


    /*
     *  Action responsible for cropping the logo
     *
     */
    function do_crop(){
        global $default;

        $logoFileName = $_REQUEST['data']['logo_file_name'];
        $logoFile = 'var'.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$logoFileName;

        $ext = end(explode('.', $logoFileName));
        $destFileName = 'logo_tmp_'.md5(Date('ymd-hms')).'.'.$ext;
        $destFile = 'var'.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$destFileName;

        $x1 = $_REQUEST['data']['crop_x1'];
        $y1 = $_REQUEST['data']['crop_y1'];
        
        $x2 = $_REQUEST['data']['crop_x2'];
        $y2 = $_REQUEST['data']['crop_y2'];
        
        $type = $this->getMime($logoFileName);
        
        //GD Crop
        $res = $this->cropImage($logoFile, $destFile, $x1, $y1, $x2, $y2, $type);
        
        //If dimensions don't conform then will scale it further
        $width = $x2 - $x1;
        $height = $y2 - $y1;
        
        if (($width > $this->maxLogoWidth) || ($height > $this->maxLogoHeight)) {
            $default->log->info('SCALING IMAGE AFTER CROP');
            $res = $this->scaleImage($destFile, $destFile, $this->maxLogoWidth, $this->maxLogoHeight, $type, false, false);
        }
        
        // ImageMagick Crop
        /*
        // do generation
        $pathConvert = (!empty($default->convertPath)) ? $default->convertPath : 'convert';
        
        // windows path may contain spaces
        if (stristr(PHP_OS,'WIN')) {
		    $cmd = "\"{$pathConvert}\" \"{$logoFileName}" . $pageNumber . "\" -crop 313x50+110+110 \"$logoFileName\"";
        }
	    else {
		    $cmd = "{$pathConvert} {$logoFileName}" . $pageNumber . " -resize 313x50 $logoFileName";
	    }

	    $result = KTUtil::pexec($cmd);
        */

        $applyLogoForm = $this->getApplyLogoForm($destFileName);
        return $applyLogoForm->render();
        
    }

    /*
     *  This method is used to determine if the image actually can be cropped
     *  - Supported images are jpeg, png  and gif
     *
     */
    public function isImageCroppable( $origFile, $width, $height, $type) {
        global $default;
        
        //Requires the GD library if not exit gracefully
        if (!extension_loaded('gd')) {
            $default->log->error("The GD library isn't loaded");
            return false;
        }
        
        switch($type) {
            case 'jpg':
                $orig = imagecreatefromjpeg($origFile);
                break;
            case 'png':
                $orig = imagecreatefrompng($origFile);
                break;
            case 'gif':
                $orig = imagecreatefromgif($origFile);
                break;
            default:
                //Handle Error
                $default->log->error("Tried to crop an unsupported file type: $type");
                return false;
        }


        if($orig) {
            /*
             *  calculate the size of the new image.
             */
            $orig_x = imagesx($orig);
            $orig_y = imagesy($orig);

            if (($orig_x > $width) || ($orig_y > $height)) {
                return true;
            }
            
        } else {
            //Handle Error
            $default->log->error("Couldn't obtain a valid GD resource $origFile");
            return false;
        }
        
        return true;
    }


    /*
     *  This method uses GD library to return the image width
     *  - Supported images are jpeg, png  and gif     *
     */
    public function getImageWidth( $origFile, $type) {
        global $default;
        
        //Requires the GD library if not exit gracefully
        if (!extension_loaded('gd')) {
            $default->log->error("The GD library isn't loaded");
            return false;
        }
        
        switch($type) {
            case 'jpg':
                $orig = imagecreatefromjpeg($origFile);
                break;
            case 'png':
                $orig = imagecreatefrompng($origFile);
                break;
            case 'gif':
                $orig = imagecreatefromgif($origFile);
                break;
            default:
                //Handle Error
                $default->log->error("Tried to crop an unsupported file type: $type");
                return false;
        }

        if($orig) {
            /*
             *  calculate the size of the new image.             */
            
            $orig_x = imagesx($orig);
            return $orig_x;
            
        } else {
            //Handle Error
            $default->log->error("Couldn't obtain a valid GD resource $origFile");
            return false;
        }
        
        return false;
    }


    /*
     *  This method uses GD library to return the image height
     *  - Supported images are jpeg, png  and gif     *
     */
    public function getImageHeight( $origFile, $type) {
        global $default;
        
        //Requires the GD library if not exit gracefully
        if (!extension_loaded('gd')) {
            $default->log->error("The GD library isn't loaded");
            return false;
        }
        
        switch($type) {
            case 'jpg':
                $orig = imagecreatefromjpeg($origFile);
                break;
            case 'png':
                $orig = imagecreatefrompng($origFile);
                break;
            case 'gif':
                $orig = imagecreatefromgif($origFile);
                break;
            default:
                //Handle Error
                $default->log->error("Tried to crop an unsupported file type: $type");
                return false;
        }

        if($orig) {
            /*
             *  calculate the size of the new image.             */
            
            $orig_y = imagesy($orig);
            return $orig_y;
            
        } else {
            //Handle Error
            $default->log->error("Couldn't obtain a valid GD resource $origFile");
            return false;
        }
        
        return false;
    }


    /*
     *  This method uses the GD library to crop an image.
     *  - Supported images are jpeg, png  and gif
     *
     */
    public function cropImage( $origFile, $destFile, $x1, $y1, $x2, $y2, $type = 'jpg', $scaleUp = true) {
        global $default;

        $width = $x2 - $x1;
        $height = $y2 - $y1;
        
        //Requires the GD library if not exit gracefully
        if (!extension_loaded('gd')) {
            $default->log->error("The GD library isn't loaded");
            return false;
        }
        
        switch($type) {
            case 'jpg':
                $orig = imagecreatefromjpeg($origFile);
                break;
            case 'png':
                $orig = imagecreatefrompng($origFile);
                break;
            case 'gif':
                $orig = imagecreatefromgif($origFile);
                break;
            default:
                //Handle Error
                $default->log->error("Tried to crop an unsupported file type: $type");
                return false;
        }

        if($orig) {
            /*
             * create the new image, and crop it.
             */
            $image = imagecreatetruecolor($width, $height);
            //imagecopyresampled($image, $orig, 0, 0, 0, 0, $image_x, $image_y, $orig_x, $orig_y);

            // Generate the cropped image
            imagecopyresampled($image, $orig, 0, 0, $x1, $y1, $width, $height, $width, $height);
            //imagecopyresized($canvas, $piece, 0,0, $cropLeft, $cropHeight,$newwidth, $newheight, $width, $height);

            switch($type) {
                case 'jpg':
                    imagejpeg($image, $destFile);
                    break;
                case 'png':
                    imagepng($image, $destFile);
                    break;
                case 'gif':
                    imagegif($image, $destFile);
                    break;
                default:
                    //Handle Error
                    $default->log->error("Tried to crop an unsupported file type: $type");
                    return false;
            }


        } else {
            //Handle Error
            $default->log->error("Couldn't obtain a valid GD resource $sourceFile $destFile");
            return false;
        }
        
        return true;
    }


    /*
     *  Action responsible for selecting the logo after it has been scaled.
     *
     */
    function do_selectLogo(){
        global $default;

        $tmpLogoFileName = end(explode(DIRECTORY_SEPARATOR, $_REQUEST['kt_imageselect']));
        
        $form = $this->getApplyLogoForm($tmpLogoFileName);
        return $form->render();

    }



    /*
     *  Action responsible for applying the logo
     *
     */
    function do_apply(){
        global $default;

        $rootPath = $default->varDirectory . '/';
        
        $tmpLogoFileName = $_REQUEST['data']['logo_file_name'];
        $tmpLogoFileName = end(explode(DIRECTORY_SEPARATOR, $tmpLogoFileName));
        $tmpLogoFile = $default->varDirectory.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$tmpLogoFileName;

        $ext = end(explode('.', $tmpLogoFileName));
        $logoFileName = 'logo_'.md5(Date('ymd-hms')).'.'.$ext;
        $logoFile = $default->varDirectory.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$logoFileName;

        $logoFileRel = end(explode(DIRECTORY_SEPARATOR, $default->varDirectory)).DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR.$logoFileName;

        // Applying the new logo
        if(!@copy($tmpLogoFile, $logoFile)){
            $default->log->info("Couldn't copy logo ".$tmpLogoFile." to ".$logoFile);
        } else {
            //Cleaning stale files
            $brandDir = $default->varDirectory.DIRECTORY_SEPARATOR.'branding'.DIRECTORY_SEPARATOR.'logo'.DIRECTORY_SEPARATOR;
            $handle = opendir($brandDir);
            while (false !== ($file = readdir($handle))) {
                if (!is_dir($file) && $file != $tmpLogoFileName && $file != $logoFileName) {
                    if (!@unlink($brandDir.$file)) {
                        $default->log->error("Couldn't delete '".$brandDir.$file."'");
                    } else {
                        $default->log->error("Cleaning Brand Logo Dir: Deleted '".$brandDir.$file."'");
                    }                    
                }
            }
        }

        //
        // Updating Config Settings with the new Logo Location
        //
        
        $sql = "SELECT id from config_settings WHERE item = 'companyLogo'";
        $companyLogoId = DBUtil::getOneResultKey($sql,'id');
        if (PEAR::isError($companyLogoId))
        {
            if (PEAR::isError($res)) {
                $default->log->error(sprintf(_kt("Failed to apply logo: %s"), $res->getMessage()));
                $this->errorRedirectToMain(sprintf(_kt("Failed to apply logo: %s"), $res->getMessage()));
                exit();
            }
        }
        
        $res = DBUtil::autoUpdate('config_settings', array('value' => $logoFileRel), $companyLogoId);
        if (PEAR::isError($res)) {
            $default->log->error(sprintf(_kt("Failed to apply logo: %s"), $res->getMessage()));
            $this->errorRedirectToMain(sprintf(_kt("Failed to apply logo: %s"), $res->getMessage()));
            exit();
        }
        
        // Clear the cached settings
        $oKTConfig = new KTConfig();
        $oKTConfig->clearCache();
        
        $this->successRedirectTo('', _kt("Logo succesfully applied."));
    }

}

?>
