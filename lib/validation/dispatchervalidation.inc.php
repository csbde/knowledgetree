<?php


class KTDispatcherValidation {
    function &validateFolder (&$this, $iId, $aOptions = null) {
        return KTDispatcherValidation::validateEntity($this, 'Folder', $iId, $aOptions);
    }

    function &validatePermissionByName (&$this, $iId, $aOptions = null) {
        KTUtil::meldOptions($aOptions, array(
            'method' => 'getByName',
        ));
        return KTDispatcherValidation::validateEntity($this, 'KTPermission', $iId, $aOptions);
    }

    function userHasPermissionOnItem(&$this, $oUser, $oPermission, $oItem, $aOptions) {
        require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
        if (KTPermissionUtil::userHasPermissionOnItem($oUser, $oPermission, $oItem)) {
            return;
        }
        $this->errorPage(_("Insufficient permissions to perform action"));
    }

    function &validateEntity(&$this, $entity_name, $iId, $aOptions = null) {
        $aOptions = (array)$aOptions;

        $aFunc = array($entity_name, KTUtil::arrayGet($aOptions, 'method', 'get'));
        $oEntity =& call_user_func($aFunc, $iId);
        if (PEAR::isError($oEntity) || ($oEntity === false)) {
            $this->errorPage(sprintf("Invalid identifier provided for: %s", $entity_name));
        }
        return $oEntity;
    }

    function notError(&$this, &$res, $aOptions = null) {
        $aOptions = (array)$aOptions;
        if (PEAR::isError($res)) {
            $aOptions = KTUTil::meldOptions($aOptions, array(
                'exception' => $res,
            ));
            KTDispatcherValidation::handleError($this, $aOptions);
        }
    }

    function notErrorFalse(&$this, &$res, $aOptions = null) {
        $aOptions = (array)$aOptions;
        if (PEAR::isError($res) || ($res === false)) {
            $aOptions = KTUTil::meldOptions($aOptions, array(
                'exception' => $res,
            ));
            KTDispatcherValidation::handleError($this, $aOptions);
        }
    }

    function handleError($this, $aOptions = null) {
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
            call_user_func_array(array($this, 'errorRedirectTo'), $aRealRedirectTo);
        }
        $this->errorPage($sMessage);
    }
}

?>
