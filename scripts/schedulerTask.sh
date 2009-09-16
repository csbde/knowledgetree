#!/bin/sh

cd ../bin
while true; do
/usr/local/zend/bin/php -c /use/local/zend/etc -Cq scheduler.php
  sleep 30
done
