<?php

require_once(KT_LIB_DIR . '/validation/errorviewer.inc.php');

class KTDispatcherValidation {
    function KTDispatcherValidation(&$oDispatcher) {
        $this->oDispatcher =& $oDispatcher;
    }

    function &validateFolder ($iId, $aOptions = null) {
        return $this->validateEntity('Folder', $iId, $aOptions);
    }

    function &validateDocument ($iId, $aOptions = null) {
        return $this->validateEntity('Document', $iId, $aOptions);
    }

    function &validateDocumentType ($iId, $aOptions = null) {
        return $this->validateEntity('DocumentType', $iId, $aOptions);
    }

    function &validatePermissionByName ($iId, $aOptions = null) {
        $aOptions = KTUtil::meldOptions($aOptions, array(
            'method' => 'getByName',
        ));
        return $this->validateEntity('KTPermission', $iId, $aOptions);
    }

    function userHasPermissionOnItem($oUser, $oPermission, $oItem, $aOptions = null) {
        require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
        if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $oItem)) {
            return;
        }
        $this->oDispatcher->errorPage(_("Insufficient permissions to perform action"));
    }

    function &validateEntity($entity_name, $iId, $aOptions = null) {
        $aOptions = (array)$aOptions;

        $aFunc = array($entity_name, KTUtil::arrayGet($aOptions, 'method', 'get'));
        $oEntity =& call_user_func($aFunc, $iId);
        if (PEAR::isError($oEntity) || ($oEntity === false)) {
            $aOptions['message'] = KTUtil::arrayGet($aOptions, 'message', sprintf(_("Invalid identifier provided for: %s"), $entity_name));
            $this->handleError($aOptions);
        }
        return $oEntity;
    }

    function notError(&$res, $aOptions = null) {
        $aOptions = (array)$aOptions;
        if (PEAR::isError($res)) {
            $aOptions = KTUTil::meldOptions($aOptions, array(
                'exception' => $res,
            ));
            $this->handleError($aOptions);
        }
    }

    function notErrorFalse(&$res, $aOptions = null) {
        $aOptions = (array)$aOptions;
        if (PEAR::isError($res) || ($res === false)) {
            $aOptions = KTUTil::meldOptions($aOptions, array(
                'exception' => $res,
            ));
            $this->handleError($aOptions);
        }
    }

    function &notEmpty(&$res, $aOptions = null) {
        $aOptions = (array)$aOptions;
        if (empty($res) || PEAR::isError($res)) {
            $this->handleError($aOptions);
        }
        return $res;
    }

    function handleError($aOptions = null) {
        $aOptions = (array)$aOptions;
        $aRedirectTo = KTUtil::arrayGet($aOptions, 'redirect_to');
        $oException = KTUtil::arrayGet($aOptions, 'exception');
        $sMessage = KTUtil::arrayGet($aOptions, 'message');
        $sDefaultMessage = KTUtil::arrayGet($aOptions, 'defaultmessage');
        if (empty($sMessage)) {
            if ($oException) {
                $oEVRegistry = KTErrorViewerRegistry::getSingleton();
                $oViewer = $oEVRegistry->getViewer($oException);
                $sMessage = $oViewer->view();
            } elseif ($sDefaultMessage) {
                $sMessage = $sDefaultMessage;
            } else {
                $sMessage = _("An error occurred, and no error message was given");
            }
        } else {
            if ($oException) {
                $sMessage .= ': ' . $oException->getMessage();
            }
        }
        if ($aRedirectTo) {
            $aRedirectParams = KTUtil::arrayGet($aRedirectTo, 1);
            $aRealRedirectTo = array($aRedirectTo[0], $sMessage, $aRedirectParams);
            $aRealRedirectTo[] = $oException;
            call_user_func_array(array($this->oDispatcher, 'errorRedirectTo'), $aRealRedirectTo);
        }
        $this->oDispatcher->errorPage($sMessage, $oException);
    }

    function &validateTemplate($sTemplateName, $aOptions = null) {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate($sTemplateName);
        $aOptions['message'] = KTUtil::arrayGet($aOptions, 'message', _('Failed to locate template'));
        $this->notError($oTemplate, $aOptions);
        return $oTemplate;
    }

    function &validateWorkflow($iId, $aOptions = null) {
        return $this->validateEntity('KTWorkflow', $iId, $aOptions);
    }

    function &validateWorkflowState($iId, $aOptions = null) {
        return $this->validateEntity('KTWorkflowState', $iId, $aOptions);
    }

    function &validateWorkflowTransition($iId, $aOptions = null) {
        return $this->validateEntity('KTWorkflowTransition', $iId, $aOptions);
    }

    function &validatePermission($iId, $aOptions = null) {
        return $this->validateEntity('KTPermission', $iId, $aOptions);
    }

    function &validateLookup($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/documentmanagement/MetaData.inc');
        return $this->validateEntity('MetaData', $iId, $aOptions);
    }

    function &validateFieldset($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/metadata/fieldset.inc.php');
        return $this->validateEntity('KTFieldset', $iId, $aOptions);
    }

    function &validateField($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/documentmanagement/DocumentField.inc');
        return $this->validateEntity('DocumentField', $iId, $aOptions);
    }

    function &validateBehaviour($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/metadata/fieldbehaviour.inc.php');
        return $this->validateEntity('KTFieldBehaviour', $iId, $aOptions);
    }

    function &validateRole($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/roles/Role.inc');
        return $this->validateEntity('Role', $iId, $aOptions);
    }

    function &validateGroup($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/groups/Group.inc');
        return $this->validateEntity('Group', $iId, $aOptions);
    }

    function &validateUser($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/users/User.inc');
        return $this->validateEntity('User', $iId, $aOptions);
    }

    function &validateCondition($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/search/savedsearch.inc.php');
        $oSearch = $this->validateEntity('KTSavedSearch', $iId, $aOptions);
        if ($oSearch->getIsCondition()) {
            return $oSearch;
        }
        $aOptions = KTUTil::meldOptions($aOptions, array(
            'message' => _("Condition is a saved search, but not a condition"),
        ));
        $this->handleError($aOptions);
    }

    function validateString($sString, $aOptions = null) {
        $sString = trim($sString);
        if (empty($sString)) {
            $aOptions['message'] = KTUtil::arrayGet($aOptions,
                    'message', _("An empty string was given"));
            $this->handleError($aOptions);
        }
        return $sString;
    }

    function validateFile($aFile, $aOptions = null) {
        $bError = false;
        
        if (strlen(trim($aFile['name'])) == 0) {
            $bError = true;
        } else {
            $bError = KTUtil::arrayGet($aFile, 'error');
        }
        
        if ($bError) {
            $message = _("You did not select a valid document to upload");

            $errors = array(
               1 => _("The uploaded file is larger than the PHP upload_max_filesize setting"),
               2 => _("The uploaded file is larger than the MAX_FILE_SIZE directive that was specified in the HTML form"),
               3 => _("The uploaded file was not fully uploaded to the document management system"),
               4 => _("No file was selected to be uploaded to the document management system"),
               6 => _("An internal error occurred receiving the uploaded document"),
            );
            $message = KTUtil::arrayGet($errors, $aFile['error'], $message);

            if (@ini_get("file_uploads") == false) {
                $message = _("File uploads are disabled in your PHP configuration");
            }
            $aOptions['message'] = $message;
            $this->handleError($aOptions);
        }
        return $aFile;
    }

    function validateDict($aDict, $aValidation, $aOptions = null) {
        foreach ($aValidation as $k => $aValidatorInfo) {
            $sDictValue = KTUtil::arrayGet($aDict, $k, null);
            if (empty($sDictValue)) {
                /*
                if (strstr($v, '_or_null')) {
                    $aValidatedDict[$k] = null;
                }
                if (strstr($v, '_or_empty')) {
                    $aValidatedDict[$k] = '';
                }
                */
                $aErrors[$k] = PEAR::raiseError(sprintf(_("Required value %s not set"), $k));
                continue;
            }
            $sValidationFunction = $this->_generateValidationFunction($aValidatorInfo['type']);
            if (!method_exists($this, $sValidationFunction)) {
                $aErrors[$k] = PEAR::raiseError(sprintf(_("Unknown validation function for required value %s"), $k));
                continue;
            }
            $aKeyInfo = array('var' => $k);
            $this->$sValidationFunction($aKeyInfo, $sDictValue);
            $aValidatedDict[$k] = $sDictValue;
        }
        if ($aErrors) {
            $aErrorsString = '';
            foreach ($aErrors as $k => $v) {
                $aErrorsString .= $v->getMessage();
            }
            $this->oDispatcher->errorPage($aErrorsString);
        }
        return $aValidatedDict;
    }

    function _generateValidationFunction($v) {
        $iEnd = strstr($v, '_or_');
        if ($iEnd) {
            $v = substr($v, 0, $iEnd);
        }
        return '_validate' . $v;
    }

    function _validateworkflow($aKeyInfo, $id) {
        return $this->_validateentity($aKeyInfo, 'KTWorkflow', $id);
    }

    function _validateworkflowtransition($aKeyInfo, $id) {
        return $this->_validateentity($aKeyInfo, 'KTWorkflowTransition', $id);
    }
    function _validateworkflowstate($aKeyInfo, $id) {
        return $this->_validateentity($aKeyInfo, 'KTWorkflowState', $id);
    }

    function _validateentity($aKeyInfo, $entity_name, $iId, $aOptions = null) {
        $aFunc = array($entity_name, KTUtil::arrayGet($aOptions, 'method', 'get'));
        $oEntity =& call_user_func($aFunc, $iId);
        if (PEAR::isError($oEntity) || ($oEntity === false)) {
            return PEAR::raiseError(sprintf(_("Provided variable %s is not a valid %s"), $aKeyInfo['var'], $entity_name));
        }
        return $oEntity;
    }
    
    
    
    
    
    /* unlike the KTEmail version, this only handles ONE email address */
    function validateEmailAddress($sEmailAddress, $aOptions = null) {
        $sEmailAddress = trim($sEmailAddress);
        
        if (!ereg ("^[^@ ]+@[^@ ]+\.[^@ \.]+$", $sEmailAddress )) {
            $aOptions['message'] = KTUtil::arrayGet($aOptions,
                                                    'message', 
                                                    _("An invalid email address was given"));
            $this->handleError($aOptions);
        }
        return $sEmailAddress;
    }
    
    
    /* assuming something has a 'getList' static method, this may work */
    function validateDuplicateName($sEntityTypeName, $sHumanEntityTypeName, $sName, $aOptions) {
        $aMethod = array($sEntityTypeName, 'getList');

        $aList =& call_user_func($aMethod, "name = '$sName'");
        if(count($aList)) {
            $aOptions['message'] = KTUtil::arrayGet($aOptions, 'message', _("A $sHumanEntityTypeName with that name already exists"));
            $this->handleError($aOptions);
        }
        return $sName;
    }
    
    /* just does an empty string validation with an appropriate message, and then a duplicate name validation */
    function validateEntityName($sEntityTypeName, $sHumanEntityTypeName, $sName, $aOptions) {
        $aNewOptions = $aOptions;
        $aNewOptions['message'] = KTUtil::arrayGet($aOptions, 'message', _("No name was given for the $sHumanEntityTypeName"));
        
        // FIXME BD:  don't you mean $sName = $this->validateString ...
        $this->validateString($sName, $aNewOptions);
        return $this->validateDuplicateName($sEntityTypeName, $sHumanEntityTypeName, $sName, $aOptions);
    }
            

}

?>
