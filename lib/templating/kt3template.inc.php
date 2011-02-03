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
 *
 *
 * -------------------------------------------------------------------------
 *
 * KT3 Template Base
 *
 * Represents core UI logic, including how sub-components interact with
 * the overall page.
 *
 * For the meaning of each of the variables and functions, see inline.
 *
 */

require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/session/control.inc");
require_once(KT_LIB_DIR . "/util/ktVar.php");
require_once(KT_DIR . '/search2/search/search.inc.php');
require_once(KT_LIB_DIR . "/users/shareduserutil.inc.php");

class KTPage {

    public $hide_section = false;
	public $secondary_title = null;

    /** resources are "filename"->1 to allow subcomponents to require items. */
    public $js_resources = Array();
    public $css_resources = Array();
    public $theme_css_resources = Array();
	public $ie_only_css = Array();
	public $theme_ie_only_css = Array();
    public $js_standalone = Array();
    public $css_standalone = Array();
    public $onload = false;

	/** context-relevant information */
	public $errStack = Array();
	public $booleanLink = false;
    public $infoStack = Array();
	public $portlets = Array();
	public $show_portlets = true;

    /** miscellaneous items */
    public $title = '';
    public $systemName = APP_NAME;
    public $systemURL = 'http://www.knowledgetree.com/';
    public $breadcrumbs = false;
    public $breadcrumbDetails = false;
    public $breadcrumbSection = false;
    public $menu = null;
    public $userMenu = null;
    public $helpPage = null;

    /** the "component".  Used to set the page header (see documentation for explanation). */
    public $componentLabel = 'Browse Documents';
    public $componentClass = 'browse_collections';

    /** $contents is the center of the page.  In KT < 3, this was CentralPayload. */
    public $contents = '';

    public $template = "kt3/standard_page";

    public $contentType = 'text/html';
    public $charset = 'UTF-8';

    public $content_class;

    /* Whether or not to sanitize info */
    public $allowHTML = false;

    private $initialised = false;

    // TODO create init function which will deal with loading of css and js, rather than constructor;
    //      will allow setting of section prior to determining what additionals to load.

    // TODO deal with login page, it will hit the default same as dashboard, but must not (nothing much needs to load on login page...)

    public function __construct() { }

    /**
     * Initialises css and javascript according to component class.
     * Initialises menus and portlets.
     *
     * @param string $section
     */
    public function init($section = null)
    {
        if ($this->initialised) { return ; }

        global $default;

        if (!empty($section)) {
            $this->setSection($section);
        }

        $oConfig = KTConfig::getSingleton();

        /*

        Component classes not yet covered...

            case 'search':
                $this->componentLabel = _kt('Search');
                $this->componentClass = 'search';
                break;

            case 'preferences':
                $this->componentLabel = _kt('Preferences');
                $this->componentClass = 'preferences';
                break;

        */

        // set inclusion map
        // TODO this maybe happens elsewhere and is laoded into the config object?
        // TODO consider 'all' option, which means will appear on any page.  These could be loaded in addition
        //      to ones matching the current filter.
        $cssIncludes = array('resources/css/newui/newui.upload.css' => array('browse_collections', 'dashboard'));
        $jsIncludes = array(
                        'thirdpartyjs/jquery/plugins/ajaxupload/fileuploader.js' => array('browse_collections', 'dashboard'),
                        'resources/js/newui/kt.app.upload.js' => array('browse_collections', 'dashboard'),
	                    'resources/js/newui/kt.app.inviteusers.js' => array('browse_collections', 'document_details', 'dashboard', 'administration'),
                        'resources/js/newui/kt.app.sharewithusers.js' => array('browse_collections', 'document_details'),
            	        'resources/js/jquery.blockui.js' => array('browse_collections', 'document_details', 'dashboard', 'administration')
                      );

        // set the system url
        $this->systemURL = $oConfig->get('ui/systemUrl');

        // hack for now, just to test
        /*$adminPage = preg_match('/admin\.php$/', $_SERVER['SCRIPT_FILENAME']);
        // $adminPage = false;
        if ($adminPage) {
            $this->componentClass = 'admin';
        }*/

        // testing
        /*$this->componentClass = 'browse_collections';*/

        /* default css files initialisation */
        $css = array(
            'thirdpartyjs/extjs/resources/css/ext-all.css',
            'resources/css/kt-framing.css',
            'resources/css/kt-contenttypes.css',
            'resources/css/kt-headings.css',
            'resources/css/kt-new-ui.css',
            'resources/css/newui/dropdown.css',
            /* REWORK INTO SINGLE STYLE SHEET */
            'resources/css/newui/dropdown_styles.css',
        );

        // load area specific files
        foreach ($cssIncludes as $cssFile => $includeLocations) {
            if (in_array($this->componentClass, $includeLocations)) {
                $css[] = $cssFile;
            }
        }

        $this->requireCSSResources($css);

        if ($oConfig->get('ui/morphEnabled') == '1') {
        	$morphTheme = $oConfig->get('ui/morphTo');
        	$this->requireThemeCSSResource('skins/kts_'.$oConfig->get('ui/morphTo').'/kt-morph.css');
        	$this->requireThemeCSSResource('skins/kts_'.$oConfig->get('ui/morphTo').'/kt-ie-morph.css', true);
        }

        // IE only
        $this->requireCSSResource('resources/css/kt-ie-icons.css', true);

        /* default js files initialisation */
        // TODO : Remove js based on user type.  Also based on location.  This is a major problem with running EVERYTHING through generic code...what use is uploader code on a page which does not support uploads in the UI?
        $js = Array();

		$js[] = 'thirdpartyjs/MochiKit/MochiKitPacked.js';
        $js[] = 'resources/js/kt-utility.js';
        $js[] = 'presentation/i18nJavascript.php';

        $js[] = 'thirdpartyjs/extjs/adapter/ext/ext-base.js';
        $js[] = 'thirdpartyjs/extjs/ext-all.js';
        $js[] = 'thirdpartyjs/jquery/jquery-1.4.2.min.js';
        $js[] = 'thirdpartyjs/jquery/jquery_noconflict.js';
        $js[] = 'thirdpartyjs/jquery/plugins/urlparser/jquery.url.js';
        $js[] = 'resources/js/search2widget.js';
//        $js[] = 'thirdpartyjs/plupload/js/plupload.min.js';
//        $js[] = 'thirdpartyjs/plupload/js/plupload.html5.min.js';
//        $js[] = 'thirdpartyjs/plupload/js/jquery.plupload.queue.min.js';
        $js[] = 'resources/js/newui/ktjapi.all.js';
        $js[] = 'resources/js/newui/kt.containers.js';
        $js[] = 'resources/js/newui/kt.lib.js';
        $js[] = 'resources/js/newui/kt.api.js';

        // Shared users cannot re-share or invite users to the system.
        if (SharedUserUtil::isSharedUser()) {
            unset($jsIncludes['resources/js/newui/kt.app.sharewithusers.js']);
            unset($jsIncludes['resources/js/newui/kt.app.inviteusers.js']);
            unset($jsIncludes['resources/js/jquery.blockui.js']);
        }

        $js[] = 'resources/js/newui/newUIFunctionality.js';
        $js[] = 'resources/js/newui/jquery.helper.js';
        $js[] = 'resources/js/newui/buttontabs.jquery.js';

        // load area specific files
        foreach ($jsIncludes as $jsFile => $includeLocations) {
            if (in_array($this->componentClass, $includeLocations)) {
                $js[] = $jsFile;
            }
        }

        $combinationFile = $this->combineResources($js, 'js');
        $this->requireJSResources(array($combinationFile));

        // this is horrid, but necessary.
        // hack for now, just to test
        if ($this->componentClass != 'administration') {
            $this->requireJSStandalone('addLoadEvent(partial(initDeleteProtection, "' . _kt('Are you sure you wish to delete this item?') . '"));');
        }

        /* menu initialisation*/
        // FIXME:  how do we want to handle the menu?
        $this->initMenu();

        /* portlet initialisation */
        $this->show_portlets = true;
        /* breadcrumbs */

        $this->initialised = true;
    }

	// initiliase the menu.
    public function initMenu()
    {
    	// FIXME:  we lost the getDefaultAction stuff - do we care?
    	// note that key == action. this is _important_, since we crossmatch the breadcrumbs against this for "active"
    	$sBaseUrl = KTUtil::kt_url();

    	$this->menu = array();

    	if (!SharedUserUtil::isSharedUser()) {
    		$this->menu['dashboard'] = array('label' => _kt('Dashboard'), 'url' => $sBaseUrl . '/dashboard.php');
    	}

    	$this->menu['browse'] = array('label' => _kt('Browse All Documents'), 'url' => $sBaseUrl . '/browse.php');

    	if (ACCOUNT_ROUTING_ENABLED) {
    		$sLiveUrl = KTLiveUtil::ktlive_url();
			$this->menu['applications'] = array('label' => _kt('Applications'), 'url' => $sLiveUrl . '/applications.php');
		}

		$this->menu['administration'] = array('label' => _kt('Settings'));

		// Implement an electronic signature for accessing the admin section, it will appear every 10 minutes
    	global $default;
    	if ($default->enableAdminSignatures && ($_SESSION['electronic_signature_time'] < time())) {
    	    $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    	    $heading = _kt('You are attempting to access Settings');
    	    $this->menu['administration']['url'] = '#';
    	    $this->menu['administration']['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'dms.administration.administration_section_access', 'admin', '{$sBaseUrl}/admin.php', 'redirect');";
    	} else {
    	    $this->menu['administration']['url'] = $sBaseUrl.'/admin.php';
    	}
    }

    public function setTitle($sTitle)
    {
		$this->title = $sTitle;
    }

    private function combineResources($resources, $ext)
    {
        $combined = '';
        foreach ($resources as $resource) {
        	$combined .= "/* $resource */\n" . file_get_contents(KT_DIR . "/$resource") . "\n";
        }

        $hash = sha1($combined);
        $combinationFile = "resources/tmp/{$this->componentClass}.$ext";
        $compare = @sha1_file(KT_DIR . "/$combinationFile");
        if ($hash != $compare) {
            file_put_contents(KT_DIR . "/$combinationFile", $combined);
        }

        return $combinationFile;
    }

    /* javascript handling */

    // require that the specified JS file is referenced.
    public function requireJSResource($sResourceURL)
    {
        $this->js_resources[$sResourceURL] = 1; // use the keys to prevent multiple copies.
    }

    // require that the specified JS files are referenced.
    public function requireJSResources($aResourceURLs)
    {
        foreach ($aResourceURLs as $sResourceURL) {
            $this->js_resources[$sResourceURL] = 1;
        }
    }

    // list the distinct js resources.
    public function getJSResources()
    {
    	// get js resources specified within the plugins
    	// these need to be added to the session because KTPage is initialised after the plugins are loaded.
    	if (isset($GLOBALS['page_js_resources']) && !empty($GLOBALS['page_js_resources'])) {
    		foreach($GLOBALS['page_js_resources'] as $js) {
    			$this->js_resources[$js] = 1;
    		}
    	}

        return array_keys($this->js_resources);
    }

    public function requireJSStandalone($sJavascript)
    {
        $this->js_standalone[$sJavascript] = 1; // use the keys to prevent multiple copies.
    }

    // list the distinct js resources.
    public function getJSStandalone()
    {
        return array_keys($this->js_standalone);
    }

    /* css handling */
    // require that the specified CSS file is referenced.
    public function requireCSSResource($sResourceURL, $ieOnly = false)
    {
        if ($ieOnly !== true) {
            $this->css_resources[$sResourceURL] = 1; // use the keys to prevent multiple copies.
		} else {
		    $this->ie_only_css[$sResourceURL] = 1;
		}
    }

    // require that the specified CSS file is referenced.
    public function requireThemeCSSResource($sResourceURL, $ieOnly = false)
    {
        if ($ieOnly !== true) {
            $this->theme_css_resources[$sResourceURL] = 1; // use the keys to prevent multiple copies.
		} else {
		    $this->theme_ie_only_css[$sResourceURL] = 1;
		}
    }

    // require that the specified CSS files are referenced.
    public function requireCSSResources($aResourceURLs)
    {
        foreach ($aResourceURLs as $sResourceURL) {
            $this->css_resources[$sResourceURL] = 1;
        }
    }

    // Adds an onload function - only one can be set
    public function setBodyOnload($onload)
    {
        $this->onload = $onload;
    }

    public function getBodyOnload()
    {
        return $this->onload;
    }

    // list the distinct CSS resources.
    public function getCSSResources()
    {
        return array_keys($this->css_resources);
    }

    // list the distinct CSS resources.
    public function getCSSExternal()
    {
        return array_keys($this->css_external);
    }

    // list the distinct CSS resources.
    public function getThemeCSSResources()
    {
        return array_keys($this->theme_css_resources);
    }

	public function getCSSResourcesForIE()
	{
        return array_keys($this->ie_only_css);
    }

    public function getThemeCSSResourcesForIE()
    {
        return array_keys($this->theme_ie_only_css);
    }

    public function requireCSSStandalone($sCSS)
    {
        $this->css_standalone[$sCSS] = 1;
    }

    public function requireCSSExternal($sCSS)
    {
        $this->css_external[$sCSS] = 1;
    }

    public function getCSSStandalone()
    {
        return array_keys($this->css_standalone);
    }

    public function setPageContents($contents) { $this->contents = $contents; }
    public function setShowPortlets($bShow) { $this->show_portlets = $bShow; }

    /* set the breadcrumbs.  the first item is the area name.
       the rest are breadcrumbs. */
    public function setBreadcrumbs($aBreadcrumbs)
    {
        $breadLength = count($aBreadcrumbs);

        if ($breadLength != 0) {
            $this->breadcrumbSection = $this->_actionhelper($aBreadcrumbs[0]);
            // handle the menu
            if (($aBreadcrumbs[0]['action']) && ($this->menu[$aBreadcrumbs[0]['action']])) {
                $this->menu[$aBreadcrumbs[0]['action']]['active'] = 1;
            }
        }

        if ($breadLength > 1) {
            $this->breadcrumbs = array_map(array(&$this, '_actionhelper'), array_slice($aBreadcrumbs, 1));
        }
    }

    public function setBreadcrumbDetails($sBreadcrumbDetails) { $this->breadcrumbDetails = $sBreadcrumbDetails; }
	public function setUser($oUser) { $this->user = $oUser; }
    public function setContentClass($sClass) { $this->content_class = $sClass; }

    // FIXME refactor setSection to be generic, not a conditional.
    // assume this is admin for now.
    public function setSection($sSection)
    {
        switch ($sSection) {
            case 'administration':
                $this->componentLabel = _kt('Settings');
                $this->componentClass = 'administration';
                $this->menu['administration']['active'] = 1;
                break;

            case 'dashboard':
                $this->componentLabel = _kt('Dashboard');
                $this->componentClass = 'dashboard';
                break;

            case 'browse':
                $this->componentLabel = _kt('Browse Documents');
                $this->componentClass = 'browse_collections';
                break;

            case 'view_details':
                $this->componentLabel = _kt('Document Details');
                $this->componentClass = 'document_details';
                break;

            case 'search':
                $this->componentLabel = _kt('Search');
                $this->componentClass = 'search';
                break;

            case 'preferences':
                $this->componentLabel = _kt('Preferences');
                $this->componentClass = 'preferences';
                break;

            default:
                $this->componentLabel = _kt('Dashboard');
                $this->componentClass = 'dashboard';
        }
	}

	public function addError($sError) { array_push($this->errStack, $sError); }
	public function addInfo($sInfo) { array_push($this->infoStack, $sInfo); }

	/** no-one cares what a portlet is, but it should be renderable, and have its ->title member set. */
	public function addPortlet($oPortlet)
	{
	    array_push($this->portlets, $oPortlet);
	}

	/* LEGACY */
	public $deprecationWarning = 'Legacy UI API: ';

	public function setCentralPayload($sCentral) {
	    $this->contents = $sCentral;
		$this->addError($this->deprecationWarning . 'called <strong>setCentralPayload</strong>');
	}

	public function setOnloadJavascript($appendix) { $this->addError($this->deprecationWarning . 'called <strong>setOnloadJavascript (no-act)</strong>'); }
	public function setDHtmlScrolling($appendix) { $this->addError($this->deprecationWarning . 'called <strong>setDHTMLScrolling (no-act)</strong>'); }
	public function setFormAction($appendix) { $this->addError($this->deprecationWarning . 'called <strong>setFormAction (no-act)</strong>'); }
	public function setSubmitMethod($appendix) { $this->addError($this->deprecationWarning . 'called <strong>setSubmitMethod (no-act)</strong>'); }
	public function setHasRequiredFields($appendix) { $this->addError($this->deprecationWarning . 'called <strong>setHasRequiredFields (no-act)</strong>'); }
	public function setAdditionalJavascript($appendix) { $this->addError($this->deprecationWarning . 'called <strong>setAdditionalJavascript (no-act)</strong>'); }

	public function hideSection() { $this->hide_section = true; }
	public function setSecondaryTitle($sSecondary) { $this->secondary_title = $sSecondary; }

    /* final render call. */
    public function render()
    {
		global $default;
        $oConfig = KTConfig::getSingleton();

        if (empty($this->contents)) {
            $this->contents = '';
        }

        if (is_string($this->contents) && (trim($this->contents) === '')) {
            $this->addError(_kt('This page did not produce any content'));
            $this->contents = '';
        }

        if (!is_string($this->contents)) {
        	$this->contents = $this->contents->render();
        }

        // if we have no portlets, make the ui a tad nicer.
        if (empty($this->portlets)) {
        	$this->show_portlets = false;
        }

        if (empty($this->title)) {
        	if (!empty($this->breadcrumbDetails)) {
        		$this->title = $this->breadcrumbDetails;
        	} else if (!empty($this->breadcrumbs)) {
        		$this->title = array_slice($this->breadcrumbs, -1);
        		$this->title = $this->title[0]['label'];
        	} else if (!empty($this->breadcrumbSection)) {
        		$this->title = $this->breadcrumbSection['label'];
        	} else {
        		$this->title = $this->componentLabel;
        	}
        }

        $this->userMenu = array();
        $sBaseUrl = KTUtil::kt_url();

        if (!(PEAR::isError($this->user) || is_null($this->user) || $this->user->isAnonymous())) {
            $isAdmin = Permission::userIsSystemAdministrator($this->user->getId());

            if ($isAdmin) {
                $bCanAdd = true;
                if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
                    $path = KTPluginUtil::getPluginPath('ktdms.wintools');
                    require_once($path . 'baobabkeyutil.inc.php');
                    $bCanAdd = BaobabKeyUtil::canAddUser();
                }

                if ($bCanAdd === true) {
                    $this->userMenu['inviteuser'] = array('label' => _kt('Invite Users'), 'url' => '#');
                    $this->userMenu['inviteuser']['onclick'] = 'javascript:kt.app.inviteusers.showInviteWindow();';
                }
            }

        	if ($oConfig->get('user_prefs/restrictPreferences', false) && !$isAdmin) {
        		$this->userMenu['logout'] = array('label' => _kt('Logout'), 'url' => $sBaseUrl.'/presentation/logout.php');
        	} else {
        		if ($default->enableESignatures) {
        			$sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
        			$heading = _kt('You are attempting to modify Preferences');
        			$this->userMenu['preferences']['url'] = '#';
        			$this->userMenu['preferences']['onclick'] = "javascript: showSignatureForm('{$sUrl}', '{$heading}', 'dms.administration.accessing_preferences', 'system', '{$sBaseUrl}/preferences.php', 'redirect');";
        		} else {
        			$this->userMenu['preferences']['url'] = $sBaseUrl.'/preferences.php';
        		}

        		if (KTPluginUtil::pluginIsActive('gettingstarted.plugin')) {
        		    $heading = _kt('Getting Started');
        		    $this->userMenu['gettingstarted']['url'] = KTUtil::kt_url() . str_replace(KT_DIR, '', KTPluginUtil::getPluginPath('gettingstarted.plugin') . 'GettingStarted.php');
        		    $this->userMenu['gettingstarted']['extra'] = 'name="gettingStartedModal"';
        		    //$this->userMenu['gettingstarted']['onclick'] = "javascript: doMask();";
        		    $this->userMenu['gettingstarted']['label'] = '<span>Getting Started</span>';
        		}

				$this->userMenu['supportpage'] = array('label' => _kt('Get Help'), 'url' => $sBaseUrl.'/support.php', 'extra'=>'target="_blank"');
        		//	        $this->userMenu['preferences'] = array('label' => _kt('Preferences'), 'url' => $sBaseUrl.'/preferences.php');
        		$this->userMenu['preferences']['label'] = '<span class="normalTransformText">'.$this->user->getName().'</span>';
				// About Moved to Footer
				//$this->userMenu['aboutkt'] = array('label' => _kt('About'), 'url' => $sBaseUrl.'/about.php');
				$this->userMenu['logout'] = array('label' => _kt('Logout'), 'url' => $sBaseUrl.'/presentation/logout.php');
        	}
        } else {
        	$this->userMenu['login'] = array('label' => _kt('Login'), 'url' => $sBaseUrl.'/login.php');
        }

		// For new Layout, we need to reverse Menu,
		// so that right most items appear first
		$this->userMenu = array_reverse($this->userMenu);

        // FIXME we need a more complete solution to navigation restriction
        if (!is_null($this->menu['administration']) && !is_null($this->user)) {
        	if (!Permission::userIsSystemAdministrator($this->user->getId())) {
        		unset($this->menu['administration']);
        	}
        }

        $sContentType = 'Content-type: ' . $this->contentType;
        if (!empty($this->charset)) {
        	$sContentType .= '; charset=' . $this->charset;
        };

        header($sContentType);

        $savedSearches = SearchHelper::getSavedSearches($_SESSION['userID']);

        require_once(KT_LIB_DIR . '/browse/feedback.inc.php');
        $userFeedback = new Feedback();

        // TODO: need to refactor - is this the correct way to add this?
        $loadDND = true;
		if (ACCOUNT_ROUTING_ENABLED) {
			$fFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
			// Disable drag and drop for shared user landing browse folder view and for any non-(folder)browse section
			if (($this->componentClass != 'browse_collections') || (($this->user->getDisabled() == 4) && ($fFolderId == 1))) {
				$loadDND = false;
			}

			if (($this->user->getDisabled() == 4) && $loadDND) {
				require_once(KT_LIB_DIR . '/render_helpers/sharedContent.inc');
				$loadDND = (SharedContent::getPermissions($this->user->getId(), null, $fFolderId, 'folder') == 0) ? false : true;
			}

			if ($loadDND) {
				$uploadProgress = new DragDrop();
				$uploadProgressRendered = $uploadProgress->render();
			}
		}

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate($this->template);
        $aTemplateData = array(
        			'page' => $this,
			       	'systemversion' => $default->systemVersion,
			       	'versionname' => $default->versionName,
					'smallVersion' => $default->versionTier,
			       	'savedSearches'=> $savedSearches,
			       	'feedback' => $userFeedback->getDisplay(),
        			'uploadProgress' => $uploadProgressRendered
				);

        if ($oConfig->get('ui/automaticRefresh', false)) {
            $aTemplateData['refreshTimeout'] = (int)$oConfig->get('session/sessionTimeout') + 3;
        }

        //TODO: need to refactor - is this the correct way to add this?
        if (KTPluginUtil::pluginIsActive ( 'gettingstarted.plugin' )) {
        	$aTemplateData['gettingStarted'] = $gettingStartedRendered;
        }

		// Trigger for pending downloads
		$aTemplateData['downloadNotification'] = null;
		require_once(KT_LIB_DIR . '/triggers/triggerregistry.inc.php');
		$oKTTriggerRegistry = KTTriggerRegistry::getSingleton();
		$aTriggers = $oKTTriggerRegistry->getTriggers('ktcore', 'pageLoad');
		foreach ($aTriggers as $aTrigger) {
			$sTrigger = $aTrigger[0];
			$oTrigger = new $sTrigger;
			$aTemplateData['downloadNotification'] = $oTrigger->invoke();
		}

        // unlike the rest of KT, we use echo here.
        echo $oTemplate->render($aTemplateData);
    }

	/**   helper functions */
	// returns an array ('url', 'label')
    public function _actionhelper($aActionTuple)
    {
        $aTuple = Array('label' => $aActionTuple['name']);
        if ($aActionTuple['action']) {
           $aTuple['url'] = generateControllerLink($aActionTuple['action'], $aActionTuple['query']);
        } else if ($aActionTuple['url']) {
           $sUrl = $aActionTuple['url'];
           $sQuery = KTUtil::arrayGet($aActionTuple, 'query');
           if ($sQuery) {
               $sUrl = KTUtil::addQueryString($sUrl, $sQuery);
           }
		   $aTuple['url'] = $sUrl;
        } else if ($aActionTuple['query']) {
           $aTuple['url'] = KTUtil::addQueryStringSelf($aActionTuple['query']);
		} else {
		   $aTuple['url'] = false;
		}

		return $aTuple;
    }

    public function setHelp($sHelpPage)
    {
	   $this->helpPage = $sHelpPage;
    }

    public function getHelpURL()
    {
        if (empty($this->helpPage)) {
            return null;
        }

        return KTUtil::ktLink('help.php',$this->helpPage);
    }

    public function getReqTime()
    {
        $microtime_simple = explode(' ', microtime());
        $finaltime = (float) $microtime_simple[1] + (float) $microtime_simple[0];
        return sprintf('%.3f', ($finaltime - $GLOBALS['_KT_starttime']));
    }

    public function getDisclaimer()
    {
        $oRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oRegistry->getPlugin('ktstandard.disclaimers.plugin');
        if (!PEAR::isError($oPlugin) && !is_null($oPlugin)) {
            return $oPlugin->getPageDisclaimer();
        } else {
            return;
        }
    }

}

?>
