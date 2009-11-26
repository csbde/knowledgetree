#!/bin/bash
PHP_CLI="/usr/local/zend/bin/php"

BIN_DIR=$(dirname $0)
INSTALL_DIR="../..${BIN_DIR}"

cd $INSTALL_DIR/bin

while true; do
        $PHP_CLI -Cq scheduler.php
        sleep 30
done
