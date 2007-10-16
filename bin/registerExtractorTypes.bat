@echo off

SET PATH=%PATH%;..\..\php;..\..\php\bin
setlocal
cd "@@BITROCK_INSTALLDIR@@\knowledgeTree\search2\indexing\bin"

php -Cq registerTypes.php