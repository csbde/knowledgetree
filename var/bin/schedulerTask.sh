#!/bin/bash
PHP_CLI="/usr/local/zend/bin/php"

cd /var/www/bin

while true; do
        $PHP_CLI -Cq scheduler.php
        sleep 30
done

