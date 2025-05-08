#!/bin/bash
nohup google-chrome-stable --new-window --app=http://192.168.1.117:8080/siresi-new/login?computername=$(hostname) >/dev/null 2>&1 &
