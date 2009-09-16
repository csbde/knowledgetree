#!/bin/sh

cd ../bin
while true; do
../scripts/php.sh -Cq scheduler.php
  sleep 30
done
