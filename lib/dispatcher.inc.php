<?php

class KTDispatcher {
    var $event_var = "action";

    function dispatch () {
        $method = 'do_main';
        if (array_key_exists($this->event_var, $_REQUEST)) {
            $event = $_REQUEST[$this->event_var];
            if (method_exists($this, 'do_' . $event)) {
                $method = 'do_' . $event;
            }
        }

        $ret = $this->$method();
        $this->handleOutput($ret);
    }

    function errorRedirectTo($event, $error_message, $sQuery = "") {
        /* $method = 'do_main';
        if (method_exists($this, 'do_' . $event)) {
            $method = 'do_' . $event;
        }*/
        $_SESSION['KTErrorMessage'][] = $error_message;
        //exit(redirect($_SERVER["PHP_SELF"] . '?action=' . $event));
        exit($this->redirectTo($event, $sQuery));
        //return $this->$method();
    }

    function redirectTo($event, $sQuery = "") {
        if (is_array($sQuery)) {
            $sQuery['action'] = $event;
            $aQueryStrings = array();
            foreach ($sQuery as $k => $v) {
                $aQueryStrings[] = urlencode($k) . "=" . urlencode($v);
            }
            $sQuery = "?" . join('&', $aQueryStrings);
        } else {
            if (!empty($sQuery)) {
                $sQuery = '?action=' . $event . '&' . $sQuery;
            } else {
                $sQuery = '?action=' . $event;
            }
        }
        exit(redirect($_SERVER["PHP_SELF"] . $sQuery));
    }

    function errorRedirectToMain($error_message, $sQuery = "") {
        return $this->errorRedirectTo('main', $error_message, $sQuery);
    }

    function redirectToMain($sQuery = "") {
        return $this->redirectTo('main', $sQuery);
    }

    function handleOutput($sOutput) {
        print $sOutput;
    }
}

class KTStandardDispatcher extends KTDispatcher {
    var $bLogonRequired = true;
    var $bAdminRequired = false;

    function permissionDenied () {
        print "Permission denied";
    }

    function dispatch () {
        $session = new Session();
        $sessionStatus = $session->verify($bDownload);

        if ($bLogonRequired !== false) {
            if (empty($_SESSION['userID'])) {
                $this->permissionDenied();
                exit(0);
            }
        }

        if ($bAdminRequired !== false) {
            if (!Permission::userIsSystemAdministrator($_SESSION['userID'])) {
                $this->permissionDenied();
                exit(0);
            }
        }

        if ($this->check() !== true) {
            $this->permissionDenied();
            exit(0);
        }

        return parent::dispatch();
    }

    function check() {
        return true;
    }

    function handleOutput($data) {
        global $main;
        $main->bFormDisabled = true;
        $main->setCentralPayload($data);
        $main->render();
    }
}

class KTAdminDispatcher extends KTStandardDispatcher {
    var $bAdminRequired = true;
}

?>
