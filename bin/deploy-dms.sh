#!/usr/local/bin/bash
#
# deploy-dms.sh - cvs update the current dev build and update the version/builddate

wwwroot=/usr/local/www/owl/owl
version=/usr/local/www/owl/owl/config/owl.php
tmp=/tmp/owl.php

cd $wwwroot
cvs update
# sed the version with today's date
cat $version | sed 's#@build-date@#`date +%Y%m%d`g' > $tmp
mv $tmp $version
rm $tmp
