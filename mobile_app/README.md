# Pipitnesan Mobile App

This is the React Native mobile application for the Pipitnesan project.

## Requirements

- **Node.js**: >= 22.11.0
- **npm**: Version compatible with Node 22
- **Android Studio**: Android SDK & Build Tools for Android development.
- **Xcode**: For iOS development (macOS only).
- **CocoaPods**: Required for iOS native dependencies.

## Installation

1. **JavaScript Dependencies**:
   ```powershell
   npm install --force
   ```
   *(Note: `--force` may be required to resolve legacy dependency conflicts during initial setup.)*

2. **iOS Dependencies** (macOS only):
   ```bash
   npx pod-install
   ```

## Local Development (Quick Start)

The project includes a `make.bat` script (for Windows) to simplify common tasks:

### 1. Start the Metro Bundler
```powershell
.\make.bat start
```

### 2. Run on Emulator/Device
Open a new terminal for these:
- **Android**: `.\make.bat android`
- **iOS**: `.\make.bat ios`

---

## 🚀 Building for Release (Android)

Follow these steps to generate a release APK for testing.

### Step 1: Prepare Environment
Ensure your dependencies are clean and up to date:
```powershell
.\make.bat clean
npm install
```

### Step 2: Generate Release APK
Run the following command to bundle JavaScript and compile the native app:
```powershell
.\make.bat build-android-release
```

### Step 3: Locate the APK
Once finished, the build will be located at:
`android/app/build/outputs/apk/release/app-release.apk`

---

## 🔧 Build Fixes & Troubleshooting

### Hermes Compiler Issue (Windows)
If you encounter `Process 'command 'cmd'' finished with non-zero exit value 1` during the `:app:createBundleReleaseJsAndAssets` task, it is likely due to a missing Windows compiler in `@react-native/hermes-compiler`.
- **Fix**: We use **`hermes-compiler@0.16.0`** which includes the required `hermesc.exe` for Win64.
- This is already configured in `package.json` and `android/app/build.gradle`.

### JCenter Repository Errors
Gradle 9+ has removed support for JCenter. If third-party libraries (like `react-native-sqlite-storage`) fail with "Could not find method jcenter()":
- **Fix**: Patch the library's `build.gradle` (within `node_modules`) to replace `jcenter()` with `mavenCentral()`.

### Signing Configuration
By default, the release build is configured to use the **Debug Signing Key** for convenience during testing.
- To use a production key, update the `signingConfigs.release` block in `android/app/build.gradle` with your `.keystore` details.

---

## Utility Commands (make.bat)
| Command | Description |
| :--- | :--- |
| `install` | Runs `npm install` |
| `start` | Starts the Metro bundler |
| `android` | Runs the app on a connected Android device/emulator |
| `ios` | Runs the app on iOS (macOS only) |
| `clean` | Cleans the build directories and caches |
| `build-android-release` | Generates a release APK |
| `build-android-bundle` | Generates an Android App Bundle (AAB) |
