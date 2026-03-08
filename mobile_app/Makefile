.PHONY: install start android ios build-android-debug build-android-release build-android-bundle clean clean-android clean-ios

# Install dependencies for both JS and iOS
install:
	npm install
	cd ios && pod install

# Start the Metro bundler
start:
	npm start

# Run the app on Android
android:
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
