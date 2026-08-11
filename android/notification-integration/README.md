# 360MiQ Android notifications

This module provides the native Firebase Cloud Messaging integration for the
`com.miq360` WebView app. The host app already includes the module, installs
an origin-scoped bridge for `https://360miq.com`, preserves notification
launch data through `SplashScreen`, and routes notification taps through both
cold-start and `onNewIntent` paths.

The module uses FCM's Firebase Installation ID (FID) registration API. Automatic
FCM registration is disabled until the user opts in; after opt-in, the current
FID is returned to the authenticated website and refreshed through
`onRegistered`. Opt-out disables auto-registration and calls
`FirebaseMessaging.unregister()`. Both registration and unregistration callbacks
are forwarded to the authenticated website so its device binding stays current.

The app does not request notification permission at startup. Android's runtime
permission dialog is opened only after the signed-in user selects **Enable app
notifications** on the website's notification settings page.

## Required Firebase configuration

1. In the same Firebase project used by the 360MiQ notification server,
   register an Android app whose package name is exactly `com.miq360`.
2. Download that app's `google-services.json`.
3. Place it at `app/google-services.json`.

Debug builds remain available without the file so unrelated Android work is not
blocked, but native push registration reports a configuration error and release
packaging deliberately fails. Never add the server FCM service-account JSON or
private key to the Android project.

## Verification

```powershell
.\gradlew.bat testDebugUnitTest
.\gradlew.bat lintDebug
.\gradlew.bat assembleDebug
```

After adding the real Firebase configuration, test on Android 13 or newer:

- no permission prompt appears during startup;
- selecting **Enable app notifications** prompts once and registers the device;
- foreground, background, and killed-app notifications arrive;
- tapping a notification opens its trusted 360MiQ URL;
- disabling notifications and signing out retire the FCM registration;
- unread totals update the website account badge.
