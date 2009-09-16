#!/bin/sh

cd ../search2/indexing/bin
/usr/local/zend/bin/php -c /use/local/zend/etc -Cq cronMigration.php
