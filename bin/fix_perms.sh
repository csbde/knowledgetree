#!/bin/sh

wwwroot=../`dirname $0`

touch $wwwroot/log.txt
chown -R www:wheel $wwwroot
chmod -R 750 $wwwroot/Documents/*
