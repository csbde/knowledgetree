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

    function errorRedirectTo($event, $error_message) {
        /* $method = 'do_main';
        if (method_exists($this, 'do_' . $event)) {
            $method = 'do_' . $event;
        }*/
        $_SESSION['KTErrorMessage'][] = $error_message;
        exit(redirect($_SERVER["PHP_SELF"] . '?action=' . $event));
        //return $this->$method();
    }

    function redirectTo($event, $sQuery) {
        exit(redirect($_SERVER["PHP_SELF"] . '?action=' . $event . "&" . $sQuery));
    }

    function errorRedirectToMain($error_message) {
        return $this->errorRedirectTo('main', $error_message);
    }

    function handleOutput($sOutput) {
        print $sOutput;
    }
}

class KTAdminDispatcher extends KTDispatcher {
    function permissionDenied () {
        print "Permission denied";
    }

    function dispatch () {
        if (!checkSession()) {
            exit($this->permissionDenied());
        }
        return parent::dispatch();
    }
}

?>
