#!/bin/bash
PHP_CLI="/usr/local/zend/bin/php"

BIN_DIR=$(dirname $0)
cd $BIN_DIR
cd ../../
cd bin

while true; do
        $PHP_CLI -Cq scheduler.php
        sleep 30
done

