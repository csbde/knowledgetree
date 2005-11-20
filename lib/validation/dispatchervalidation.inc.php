<?php

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
            $this->oDispatcher->errorPage(sprintf("Invalid identifier provided for: %s", $entity_name));
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
        if (is_null($sMessage)) {
            if ($oException) {
                $sMessage = $oException->toString();
            } else {
                $sMessage = "An error occurred, and no error message was given";
            }
        }
        if ($aRedirectTo) {
            $aRealRedirectTo = array($aRedirectTo[0], $sMessage, KTUtil::arrayGet($aRedirectTo, 1));
            call_user_func_array(array($this->oDispatcher, 'errorRedirectTo'), $aRealRedirectTo);
        }
        $this->oDispatcher->errorPage($sMessage);
    }

    function &validateTemplate($sTemplateName, $aOptions = null) {
        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate =& $oTemplating->loadTemplate($sTemplateName);
        $aOptions['message'] = KTUtil::arrayGet($aOptions, 'message', 'Failed to locate template');
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

    function &validateCondition($iId, $aOptions = null) {
        require_once(KT_LIB_DIR .  '/search/savedsearch.inc.php');
        $oSearch = $this->validateEntity('KTSavedSearch', $iId, $aOptions);
        if ($oSearch->getIsCondition()) {
            return $oSearch;
        }
        $aOptions = KTUTil::meldOptions($aOptions, array(
            'message' => "Condition is a saved search, but not a condition",
        ));
        $this->handleError($aOptions);
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
                $aErrors[$k] = PEAR::raiseError("Required value $k not set");
                continue;
            }
            $sValidationFunction = $this->_generateValidationFunction($aValidatorInfo['type']);
            if (!method_exists($this, $sValidationFunction)) {
                $aErrors[$k] = PEAR::raiseError("Unknown validation function for required value $k");
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
            return PEAR::raiseError(sprintf("Provided variable %s is not a valid %s", $aKeyInfo['var'], $entity_name));
        }
        return $oEntity;
    }
}

?>
