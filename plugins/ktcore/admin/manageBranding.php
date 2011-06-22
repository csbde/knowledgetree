<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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
require_once(KT_LIB_DIR . '/widgets/FieldsetDisplayRegistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldsetDisplay.inc.php');
require_once(KT_LIB_DIR . '/widgets/widgetfactory.inc.php');
require_once(KT_LIB_DIR . '/validation/dispatchervalidation.inc.php');
require_once(KT_LIB_DIR . '/metadata/fieldsetregistry.inc.php');
require_once(KT_LIB_DIR . '/validation/validatorfactory.inc.php');

class ManageBrandDispatcher extends KTAdminDispatcher
{
    private $maxLogoWidth = 313;
    private $maxLogoHeight = 50;
    public $supportedTypes = array('gif', 'png', 'pjpeg', 'jpe', 'jpeg', 'jpg');

    public function check()
    {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _kt('Manage Branding'));
        return parent::check();
    }

    public function do_main()
    {
        $form = $this->getLogoUploadForm();
        
        $config = KTConfig::getSingleton();

        $templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('ktcore/branding/list');
        $templateData = array(
            'form' => $form,
            'has_custom_logo' => $config->get('ui/mainLogo'),
        );

        return $template->render($templateData);
    }

    public function getLogoUploadForm()
    {
        //$this->oPage->setBreadcrumbDetails(_kt('Upload Custom Logo'));

        $form = new KTForm();
        $form->setOptions(array(
                'identifier' => 'ktcore.folder.branding',
                'label' => _kt('Upload Custom Logo'),
                'submit_label' => _kt('Update'),
                'action' => 'saveLogo',
                'fail_url' => 'main',
                'encoding' => 'multipart/form-data',
                'cancel_url' => "javascript:void(cancelBranding());",
                'context' => $this
            ));

        $widgetFactory = KTWidgetFactory::getSingleton();
        $config = KTConfig::getSingleton();
        $logoTitle = $config->get('ui/mainLogoTitle');

        $widgets = array();
        $widgets[] = $widgetFactory->get('ktcore.widgets.string', array(
            'label' => _kt('Title'),
            'required' => false,
            'name' => 'logo_title',
            'value' => $logoTitle,
            'description' => _kt('This will appear when hovering over the logo.'),
        ));

        $widgets[] = $widgetFactory->get('ktcore.widgets.file', array(
            'label' => _kt('Logo Image'),
            'required' => false,
            'name' => 'file',
            'id' => 'file',
            'value' => '',
            'description' => _kt('The dimensions of the logo should be 313px width by 50px height.'), //If your logo doesn't fit these dimensions, you can choose to scale it.
        ));

        $form->setWidgets($widgets);

        return $form;
    }
    
    public function do_resetLogo()
    {
        $this->saveConfiguration('', '');
        $this->successRedirectTo('main', _kt('The custom logo has been removed'));
    }

    public function do_saveLogo()
    {
        $logoTitle = trim(strip_tags($_REQUEST['data']['logo_title']));
        $fileDetails = $_FILES['_kt_attempt_unique_file'];

        $aOptions = array('redirect_to' => 'main');

        if ($logoTitle != '') {
            $this->oValidator->validateIllegalCharacters($logoTitle, $aOptions);
        }

        $logo = '';
        if (!empty($fileDetails['name'])) {
            $logo = $this->uploadLogo($fileDetails);
        } else {
            $logo = FALSE;
        }

        if (!$this->saveConfiguration($logoTitle, $logo)) {
            $this->errorRedirectToMain(_kt('The logo could not be saved, please try again.'));
        }

        $this->successRedirectTo('main', _kt('The logo has been uploaded successfully.'));
    }

    public function saveConfiguration($logoTitle, $logoPath)
    {
        $config = KTConfig::getSingleton();
        $res = $config->set('ui/mainLogoTitle', $logoTitle);
        
        if ($logoPath !== FALSE) {
            $res = $config->set('ui/mainLogo', $logoPath);
        }
        
        return $res;
    }

    public function uploadLogo($fileDetails)
    {
        global $default;

        $config = KTConfig::getSingleton();
        $storage = KTStorageManagerUtil::getSingleton();
        $brandDir = $default->varDirectory . '/branding';

        if (stristr(PHP_OS,'WIN')) {
            $brandDir = str_replace('/', '\\', $brandDir);
        }

        if (!$storage->file_exists($brandDir)) {
             $storage->mkdir($brandDir, 0755, true);
        }

        $extension = end(explode('.', $fileDetails['name']));
        $mimeType = $fileDetails['type'];

        $extension = $this->isSupportedExtension($extension, $mimeType);
        if ($extension === false) {
            $supportedList = implode(', ', $this->supportedTypes);
            return $this->errorRedirectToMain("The file uploaded is not supported. Supported types are: {$supportedList}.");
        }

        // Use a timestamp to avoid browser cache
        $logoFileName = 'logo-' . md5(Date('ymd-hms')) . '.' . $extension;
        $logoFile = $brandDir . DIRECTORY_SEPARATOR . $logoFileName;

        if (!$storage->move_uploaded_file($fileDetails['tmp_name'], $logoFile)) {
            $default->log->error("Couldn't upload file from {$fileDetails['tmp_name']} to {$logoFile}");
            return $this->errorRedirectToMain('The logo could not be uploaded, please try again later.');
        }

        $oldLogo = $config->get('ui/mainLogo', $logoPath);
        if ($storage->file_exists($oldLogo)) {
            @$storage->unlink($oldLogo);
        }

        $storage->setAllAccess($logoFile);

        return $storage->getStoragePath($logoFile);
    }

    public function isSupportedExtension($extension = '', $mimeType = '')
    {
        if (empty($extension)) {
            $extension = $this->getExtension($mimeType);
        }

        if (in_array(strtolower($extension), $this->supportedTypes)) {
            return $extension;
        }

        return false;
    }

    public function getExtensionFromMimeType($mimeType)
    {
        switch(strtolower($mimeType)) {
            case 'image/gif':
                return 'gif';

            case 'image/png':
            case 'image/x-png':
                return 'png';

            case 'image/jpeg':
            case 'image/pjpeg':
            case 'image/jpeg':
                return 'jpeg';

            default:
                return '';
        }
    }

    public function handleOutput($output)
    {
        print $output;
    }
}

?>
