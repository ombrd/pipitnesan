#!/bin/bash
LOGO="../public/images/logo.png"

# Android
sips -z 48 48 $LOGO --out android/app/src/main/res/mipmap-mdpi/ic_launcher.png
sips -z 48 48 $LOGO --out android/app/src/main/res/mipmap-mdpi/ic_launcher_round.png

sips -z 72 72 $LOGO --out android/app/src/main/res/mipmap-hdpi/ic_launcher.png
sips -z 72 72 $LOGO --out android/app/src/main/res/mipmap-hdpi/ic_launcher_round.png

sips -z 96 96 $LOGO --out android/app/src/main/res/mipmap-xhdpi/ic_launcher.png
sips -z 96 96 $LOGO --out android/app/src/main/res/mipmap-xhdpi/ic_launcher_round.png

sips -z 144 144 $LOGO --out android/app/src/main/res/mipmap-xxhdpi/ic_launcher.png
sips -z 144 144 $LOGO --out android/app/src/main/res/mipmap-xxhdpi/ic_launcher_round.png

sips -z 192 192 $LOGO --out android/app/src/main/res/mipmap-xxxhdpi/ic_launcher.png
sips -z 192 192 $LOGO --out android/app/src/main/res/mipmap-xxxhdpi/ic_launcher_round.png

echo "Android icons generated."
