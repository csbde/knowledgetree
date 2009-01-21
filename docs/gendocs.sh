#!/bin/bash

rm -rf phpdoc/*

DATETIME=`date`
VERSION=`cat VERSION-NAME.txt`

cp kt-phpdoc.ini kt-phpdoc.ini.orig
sed -i -e "s/##VERSION##/$VERSION($DATETIME)/" kt-phpdoc.ini
phpdoc -c kt-phpdoc.ini
mv kt-phpdoc.ini.orig kt-phpdoc.ini

