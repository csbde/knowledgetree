#!/bin/sh

DIR=`dirname $0`
cd $DIR
cd ..
pwd

#pull in comm stuff
cp -a ../Commercial-Plugins-DEV-trunk/alerts plugins/
cp -a ../Commercial-Plugins-DEV-trunk/conditional-metadata plugins/
cp -a ../Commercial-Plugins-DEV-trunk/custom-numbering plugins/
cp -a ../Commercial-Plugins-DEV-trunk/documentcomparison plugins/
cp -a ../Commercial-Plugins-DEV-trunk/network plugins/
cp -a ../Commercial-Plugins-DEV-trunk/professional-reporting plugins/
cp -a ../Commercial-Plugins-DEV-trunk/shortcuts plugins/
cp -a ../Commercial-Plugins-DEV-trunk/wintools plugins/

rm -f i18n/templates.c
find resources -name "*.js" | sort | python ./bin/jsi18n.py > templates/ktcore/javascript_i18n.smarty
php bin/smarty_to_gettext.php . > i18n/templates.c
find . -type f -name "*.php" -o -name "*.inc" | sort | xgettext --no-wrap -d knowledgeTree -L PHP -s -f - --keyword=_kt -o i18n/knowledgeTree.pot
echo i18n/templates.c i18n/transactions.c i18n/permissions.c | xargs -n 1 | sort | xgettext --no-wrap -d knowledgeTree -j -s -f - -o i18n/knowledgeTree.pot

#remove comm stuff again
rm -rf plugins/alerts
rm -rf plugins/conditional-metadata
rm -rf plugins/custom-numbering
rm -rf plugins/documentcomparison
rm -rf plugins/network
rm -rf plugins/professional-reporting
rm -rf plugins/shortcuts
rm -rf plugins/wintools

#alerts  conditional-metadata  custom-numbering  documentcomparison  i18n  network  professional-reporting  shortcuts  wintools
