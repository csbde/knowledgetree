#!/bin/bash

#
#  This script will upgrade all the pear components specific to knowledgetree
#

#KTDIR="/var/www/knowledgetree"
KTDIR="$1";
TMPDIR="$KTDIR/var/pear"

if [ "$KTDIR" == "" ]; then
    echo "Usage: $0 path/to/knowledgetree/directory";
    echo "e.g. $0 /var/www/knowledgetree";
    exit;
fi

if [ ! -x "$TMPDIR" ]; then
    mkdir -p "$TMPDIR"
fi

if [ ! -x "$(which pear)" ]; then
    echo "pear is not installed. please install pear before using this script.";
    exit;
fi

# Installing latest pear packages into tmp directory
pear config-set php_dir "$TMPDIR"
pear channel-update pear.php.net
pear config-set preferred_state stable
pear install --alldeps PEAR
pear install --alldeps Cache_Lite
pear install --alldeps Config
pear install --alldeps DB
pear install --alldeps File
pear install --alldeps MDB2#mysql
pear install --alldeps Log
pear install --alldeps PHP_Compat
pear install --alldeps Services_JSON
pear install --alldeps MIME_Type
pear config-set preferred_state beta
pear install --alldeps File_Gettext
pear install --alldeps Net_LDAP
pear install --alldeps SOAP
pear config-set preferred_state stable

# Backing up the current pear directory
tar -czvf "$TMPDIR/pear_backup_$(date +%Y%m%d).tgz" "$KTDIR/thirdparty/pear"

#
# The following section was created via an ls in the current knowledgetree 
# pear directory for version 3.7.0.3
#
# It contains the accurate/compressed list of pear packages known to be currently 
# needed by knowledgetree. If any new package is implemented it should be added 
# to this list to stay in the upgrade path.
#

cp -frv "$TMPDIR/Config.php" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/Console" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/DB" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/DB.php" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/File" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/GraphViz.php" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/HTTP" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/HTTP.php" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/JSON.php" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/Log" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/Log.php" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/MIME" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/Net" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/PEAR.php" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/PHP" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/SOAP" "$KTDIR/thirdparty/pear/"
cp -frv "$TMPDIR/System.php" "$KTDIR/thirdparty/pear/"
