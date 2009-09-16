#!/bin/sh

cd ../bin
while true; do
/usr/local/zend/bin/php -Cq scheduler.php
  sleep 30
done
