@echo off

if "%~1"=="setup" goto setup
if "%~1"=="up" goto up
if "%~1"=="down" goto down
if "%~1"=="restart" goto restart
if "%~1"=="shell" goto shell
if "%~1"=="share" goto share

echo Usage: make.bat [setup^|up^|down^|restart^|shell^|share]
goto :eof

:setup
echo Copying .env.example to .env (if not exists)...
if not exist .env copy .env.example .env
echo Installing Composer dependencies via temporary container...
docker run --rm ^
    -v "%cd%:/var/www/html" ^
    -w /var/www/html ^
    laravelsail/php84-composer:latest ^
    composer install --ignore-platform-reqs
echo Setup complete! Now run 'make up' to start the application.
goto :eof

:up
echo Starting the application...
call vendor\bin\sail up -d
echo Application is running at http://localhost
goto :eof

:down
echo Stopping the application...
call vendor\bin\sail down
goto :eof

:restart
echo Restarting the application...
call vendor\bin\sail down
call vendor\bin\sail up -d
goto :eof

:shell
call vendor\bin\sail shell
goto :eof

:share
echo Sharing application via Ngrok tunnel...
call vendor\bin\sail share
goto :eof
