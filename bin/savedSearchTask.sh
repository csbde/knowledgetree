#!/bin/sh

# SETUP PATH TO FIND PHP
PATH=$PATH:../../php/bin:../../php:

# WORK OUT DIRECTORIES
USER_DIR=`pwd`
SCRIPT_DIR=$USER_DIR/`dirname $0`
PHP_SCRIPT_DIR=$SCRIPT_DIR/../search2/search/bin

# EXECUTE SCRIPT IN THE SCRIPT DIRECTORY
cd $PHP_SCRIPT_DIR
php -Cq cronSavedSearch.php