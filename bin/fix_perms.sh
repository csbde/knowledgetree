#!/bin/sh

wwwroot=/usr/local/www/owl/dms

chown -R www:wheel $wwwroot
touch $wwwroot/log.txt
#chown -R www:www $wwwroot/log.txt $wwwroot/Documents
chmod -R 750 $wwwroot/Documents/*
