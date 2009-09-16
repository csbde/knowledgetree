#!/bin/sh

cd ../bin
while true; do
../scritps/php.sh -Cq scheduler.php
  sleep 30
done
