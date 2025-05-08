@echo off
set computername=%hostname%

start "Chrome" "C:\Users\10092177\AppData\Local\Google\Chrome\Application\chrome.exe" --new-window http://localhost/siresi-v1.0.0/login?computername=%computername%