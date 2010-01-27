#!/bin/bash

#
#  This script will upgrade all the pear components specific to knowledgetree
#

#KTDIR="/var/www/knowledgetree"
KTDIR="$1";
TMPDIR="$KTDIR/var/pear"
PEAR_BAK="$TMPDIR/pear_backup_$(date +%Y%m%d).tgz"
PEAR_LOG="$TMPDIR/upgrade_log.txt"

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
pear config-set php_dir "$TMPDIR" > "$PEAR_LOG"
pear channel-update pear.php.net >> "$PEAR_LOG"
pear config-set preferred_state stable >> "$PEAR_LOG"
pear install --alldeps PEAR >> "$PEAR_LOG"
pear install --alldeps Cache_Lite >> "$PEAR_LOG"
pear install --alldeps Config >> "$PEAR_LOG"
pear install --alldeps DB >> "$PEAR_LOG"
pear install --alldeps File >> "$PEAR_LOG"
pear install --alldeps MDB2#mysql >> "$PEAR_LOG"
pear install --alldeps Log >> "$PEAR_LOG"
pear install --alldeps PHP_Compat >> "$PEAR_LOG"
pear install --alldeps Services_JSON >> "$PEAR_LOG"
pear install --alldeps MIME_Type >> "$PEAR_LOG"
pear config-set preferred_state beta >> "$PEAR_LOG"
pear install --alldeps File_Gettext >> "$PEAR_LOG"
pear install --alldeps Net_LDAP >> "$PEAR_LOG"
pear install --alldeps SOAP >> "$PEAR_LOG"
pear config-set preferred_state stable >> "$PEAR_LOG"

if [ "$(grep -i error $PEAR_LOG)" ]; then
    echo "There where errors retrieving the pear packages from pear.php.net."
    exit;
fi

# Backing up the current pear directory
tar -czvf "$PEAR_BAK" "$KTDIR/thirdparty/pear"

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

echo "Backup of you old pear instance can be found here:"
echo "$PEAR_BAK"
