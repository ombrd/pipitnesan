@echo off

if "%~1"=="install" goto install
if "%~1"=="start" goto start
if "%~1"=="android" goto android
if "%~1"=="ios" goto ios
if "%~1"=="build-android-debug" goto build_android_debug
if "%~1"=="build-android-release" goto build_android_release
if "%~1"=="build-android-bundle" goto build_android_bundle
if "%~1"=="clean" goto clean
if "%~1"=="clean-android" goto clean_android
if "%~1"=="clean-ios" goto clean_ios

echo Usage: make.bat [install^|start^|android^|ios^|build-android-debug^|build-android-release^|build-android-bundle^|clean^|clean-android^|clean-ios]
goto :eof

:install
call npm install
goto :eof

:start
call npm start
goto :eof

:android
call npm run android
goto :eof

:ios
call npm run ios
goto :eof

:build_android_debug
cd android
call gradlew assembleDebug
cd ..
echo Debug APK built at: android\app\build\outputs\apk\debug\app-debug.apk
goto :eof

:build_android_release
cd android
call gradlew assembleRelease
cd ..
echo Release APK built at: android\app\build\outputs\apk\release\app-release.apk
goto :eof

:build_android_bundle
cd android
call gradlew bundleRelease
cd ..
echo Release AAB built at: android\app\build\outputs\bundle\release\app-release.aab
goto :eof

:clean
rmdir /s /q node_modules
call npm cache clean --force
rmdir /s /q android\app\build
echo Cleaned React Native project (Note: iOS specific steps and watchman are skipped on Windows).
goto :eof

:clean_android
cd android
call gradlew clean
cd ..
echo Cleaned Android build.
goto :eof

:clean_ios
echo Clean iOS applies mostly to macOS, skipping xcodebuild clean.
goto :eof
