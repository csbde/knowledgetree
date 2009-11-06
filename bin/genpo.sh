#!/bin/sh

DIR=`dirname $0`
cd $DIR
cd ..
pwd

#pull in comm stuff
#cp -R ../commercial-plugins/alerts plugins/commercial/
#cp -R ../commercial-plugins/conditional-metadata plugins/commercial/
#cp -R ../commercial-plugins/custom-numbering plugins/commercial/
#cp -R ../commercial-plugins/documentcomparison plugins/commercial/
#cp -R ../commercial-plugins/network plugins/commercial/
#cp -R ../commercial-plugins/professional-reporting plugins/commercial/
#cp -R ../commercial-plugins/shortcuts plugins/commercial/
#cp -R ../commercial-plugins/wintools plugins/commercial/
#cp -R ../commercial-plugins/guidInserter plugins/commercial/
#cp -R ../commercial-plugins/clienttools plugins/commercial/
#cp -R ../commercial-plugins/electronic-signatures plugins/commercial/
#cp -R ../commercial-plugins/officeaddin plugins/commercial/
#cp -R ../ktofficeAddIn/ktoffice ktoffice

cp -R ../commercial-plugins plugins/commercial
cp -R ../ktofficeaddin/ktoffice ktoffice

rm -f i18n/templates.c
find resources -name "*.js" | sort | python ./bin/jsi18n.py > templates/ktcore/javascript_i18n.smarty
php bin/smarty_to_gettext.php . > i18n/templates.c
find . -type f -name "*.php" -o -name "*.inc" | sort | xgettext --no-wrap -d knowledgeTree -L PHP -s -f - --keyword=_kt -o i18n/knowledgeTree.pot
echo i18n/templates.c i18n/transactions.c i18n/permissions.c | xargs -n 1 | sort | xgettext --no-wrap -d knowledgeTree -j -s -f - -o i18n/knowledgeTree.pot

# ktoffice js tamplate file generation
cd ktoffice/pogenerator
php generatetemplate.php > ../../templates/ktcore/ktoffice_i18n.smarty
cd ../../

#remove comm stuff again
rm -rf plugins/commercial
rm -rf ktoffice

#alerts  conditional-metadata  custom-numbering  documentcomparison  i18n  network  professional-reporting  shortcuts  wintools guidInserter clienttools electronic-signatures officeaddin

# Manually append some strings with #appname# issues
echo ' ' >> i18n/knowledgeTree.pot
echo 'msgid "By default, KnowledgeTree controls its own users and groups and stores all information about them inside the database. In many situations, an organisation will already have a list of users and groups, and needs to use that existing information to allow access to the DMS. These <strong>Authentication Sources</strong> allow the system administrator to specify additional sources of authentication data."' >> i18n/knowledgeTree.pot
echo 'msgstr ""' >> i18n/knowledgeTree.pot

echo 'msgid "This report lists all mime types and extensions that can be identified by KnowledgeTree."' >> i18n/knowledgeTree.pot
echo 'msgstr ""' >> i18n/knowledgeTree.pot
