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
echo Setup complete! Starting the application...
goto up

:up
echo Starting the application...
docker compose up -d
echo Application is running at http://localhost
goto :eof

:down
echo Stopping the application...
docker compose down
goto :eof

:restart
echo Restarting the application...
docker compose down
docker compose up -d
goto :eof

:shell
docker compose exec laravel.test bash
goto :eof

:share
echo Sharing application via Ngrok tunnel requires manual execution of Sail on Windows.
echo Try running: wsl ./vendor/bin/sail share
goto :eof
