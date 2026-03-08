# Mobile App

This is the React Native mobile application for the Pipitnesan project.

## Requirements

- Node.js (>= 22.11.0)
- npm or yarn
- Android Studio / Android SDK (for Android development)
- Xcode (for iOS development, macOS only)
- CocoaPods (for iOS)

## Installation

1. Install the JavaScript dependencies:
   ```bash
   npm install
   # or
   yarn install
   ```

2. Install iOS CocoaPods dependencies (macOS only):
   ```bash
   cd ios
   pod install
   cd ..
   # or using npx
   npx pod-install
   ```

## Running Locally

First, start the Metro bundler:
```bash
npm start
```

In a new terminal window, run the application on your preferred platform:

**Android:**
```bash
npm run android
# or
npx react-native run-android
```

**iOS:**
```bash
npm run ios
# or
npx react-native run-ios
```

## Build for Debug

### Android

To build a debug APK (`.apk`), run the following commands:
```bash
cd android
./gradlew assembleDebug
```
The built APK will be located at `android/app/build/outputs/apk/debug/app-debug.apk`. You can install it on a connected device using `adb install <path-to-apk>`.

### iOS

Building a debug version for iOS is typically done via Xcode:
1. Open `ios/mobile_app.xcworkspace` in Xcode.
2. Select your target simulator or connected iOS device.
3. Click the "Play" (Run) button or press `Cmd + R` to build and run the app.

## Build for Production

### Android

To build for production, you need to configure your signing keys in `android/app/build.gradle`. Once configured:

1. **Build a release APK** (for testing on devices):
   ```bash
   cd android
   ./gradlew assembleRelease
   ```
   The APK will be available at `android/app/build/outputs/apk/release/app-release.apk`.

2. **Build an Android App Bundle (AAB)** (for Google Play Store submission):
   ```bash
   cd android
   ./gradlew bundleRelease
   ```
   The AAB will be available at `android/app/build/outputs/bundle/release/app-release.aab`.

### iOS

To build for production on iOS, you must ensure your provisioning profiles and signing certificates are properly set up via your Apple Developer account.

1. Open `ios/mobile_app.xcworkspace` in Xcode.
2. Select your project in the project navigator and ensure your **Signing & Capabilities** are correctly configured using your team profile.
3. Select "Any iOS Device (arm64)" as the destination target.
4. Go to the top menu and select **Product > Archive**.
5. Once the archiving process is complete, Xcode Organizer will open.
6. From there, you can click **Distribute App** to upload it to App Store Connect (TestFlight/App Store) or export it for Ad Hoc distribution.
