@echo off
setlocal
cd /d "%~dp0"
echo TSSMT is starting at http://localhost:8000
echo Press Ctrl+C to stop the server.
php -S localhost:8000 -t public
