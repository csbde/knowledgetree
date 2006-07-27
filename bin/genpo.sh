#!/bin/sh

DIR=`dirname $0`
cd $DIR
cd ..
pwd

rm -f i18n/templates.c
find resources -name "*.js" | sort | python ./bin/jsi18n.py > templates/ktcore/javascript_i18n.smarty
php bin/smarty_to_gettext.php . > i18n/templates.c
find . -type f -name "*.php" -o -name "*.inc" | sort | xgettext --no-wrap -d knowledgeTree -L PHP -s -f - --keyword=_kt -o i18n/knowledgeTree.pot
echo i18n/templates.c i18n/permissions.c | xargs -n 1 | sort | xgettext --no-wrap -d knowledgeTree -j -s -f - -o i18n/knowledgeTree.pot

