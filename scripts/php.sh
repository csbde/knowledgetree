#!/bin/sh
PHPRC=/usr/local/zend/etc
export PHPRC
PHP_PEAR_SYSCONF_DIR=/usr/local/zend/etc
export PHP_PEAR_SYSCONF_DIR
LD_LIBRARY_PATH="/usr/local/zend/lib/php_extensions:$LD_LIBRARY_PATH"
export LD_LIBRARY_PATH
exec /usr/local/zend/bin/php "$@"
