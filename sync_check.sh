#!/bin/bash
UUID=$(cat /proc/sys/kernel/random/uuid)
echo "$UUID" > /var/www/sgu/file_sync
