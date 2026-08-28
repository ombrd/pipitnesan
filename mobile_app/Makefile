.PHONY: install start stop-metro android ios build-android-debug build-android-release build-android-bundle clean clean-android clean-ios

# Ensure the React Native CLI can locate adb (it checks ANDROID_HOME, then PATH).
# Guarded so it only applies when the default macOS SDK location exists.
ANDROID_HOME ?= $(HOME)/Library/Android/sdk
ifneq ($(wildcard $(ANDROID_HOME)/platform-tools/adb),)
export ANDROID_HOME
export PATH := $(ANDROID_HOME)/platform-tools:$(PATH)
endif

# Install dependencies for both JS and iOS
install:
	npm install
	cd ios && pod install

# Start the Metro bundler
start:
	npm start

# Stop the background Metro dev server
stop-metro:
	@lsof -tiTCP:8081 -sTCP:LISTEN | xargs kill 2>/dev/null || true
	@echo "Metro dev server stopped."

# Run the app on Android
# Note: built ABIs are controlled by reactNativeArchitectures in android/gradle.properties.
# The RN CLI no longer auto-starts Metro, so start it in the background when it is
# not already running (logs: metro.log; stop with: make stop-metro; foreground: make start).
android:
	@if ! lsof -iTCP:8081 -sTCP:LISTEN >/dev/null 2>&1; then \
		echo "Metro dev server not running - starting it in the background (logs: metro.log)"; \
		nohup npx react-native start > metro.log 2>&1 & \
		sleep 5; \
	fi
	npm run android

# Run the app on iOS
ios:
	npm run ios

# Build Android Debug APK
build-android-debug:
	cd android && ./gradlew assembleDebug
	@echo "Debug APK built at: android/app/build/outputs/apk/debug/app-debug.apk"

# Build Android Release APK
build-android-release:
	cd android && ./gradlew assembleRelease
	@echo "Release APK built at: android/app/build/outputs/apk/release/app-release.apk"

# Build Android Release App Bundle (AAB)
build-android-bundle:
	cd android && ./gradlew bundleRelease
	@echo "Release AAB built at: android/app/build/outputs/bundle/release/app-release.aab"

# Clean project caches and node_modules
clean:
	watchman watch-del-all || true
	rm -rf node_modules
	npm cache clean --force
	rm -rf ~/.rncache
	rm -rf android/app/build
	rm -rf ios/Pods ios/Podfile.lock ios/build
	@echo "Cleaned React Native project."

# Clean Android build
clean-android:
	cd android && ./gradlew clean
	@echo "Cleaned Android build."

# Clean iOS build
clean-ios:
	cd ios && xcodebuild clean
	rm -rf ios/build
	@echo "Cleaned iOS build."
