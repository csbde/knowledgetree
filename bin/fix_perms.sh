#!/bin/sh

wwwroot=/usr/local/www/owl/dms

touch $wwwroot/log.txt
chown -R www:wheel $wwwroot
chmod -R 750 $wwwroot/Documents/*
