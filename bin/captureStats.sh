#!/bin/sh

out=stats-`date +%Y-%m-%d`.out
# clear the output file
> $out

echo "break to stop stats collection"
while true; do
  date +%Y-%m-%d:%H:%M:%S | tee -a $out
  vmstat -c 5 | tee -a $out 
done
