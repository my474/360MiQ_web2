# 360MiQ Android notification integration

This Android library is designed for the existing `com.miq360` WebView app. It
does not request notification permission during app startup. The website's
notification-settings button sends the request through an origin-scoped
WebMessage bridge, and the native layer then returns the FCM token to the
authenticated page for CSRF-protected registration.

## Integrate into the app

1. Include this directory as `:notification-integration` and add
   `implementation(project(":notification-integration"))` to the app module.
   The host's plugin management must provide versions for both
   `com.android.library` and `org.jetbrains.kotlin.android`, which the library
   applies without pinning versions that could conflict with the app.
2. Keep the app's existing `com.google.gms.google-services` plugin and
   `google-services.json`. The module uses Firebase BoM 34.16.0 and the main
   `firebase-messaging` artifact (not the retired KTX artifact).
3. Attach immediately after creating the trusted WebView and before loading the
   first page:

```kotlin
MiqNotificationCoordinator.attach(
    activity = this,
    webView = webView,
    appVersion = BuildConfig.VERSION_NAME
)
```

4. Pass notification launch intents to the WebView from both lifecycle paths:

```kotlin
override fun onCreate(savedInstanceState: Bundle?) {
    super.onCreate(savedInstanceState)
    // create/configure webView, attach coordinator, then:
    MiqNotificationIntentHandler.openNotification(intent, webView)
}

override fun onNewIntent(intent: Intent) {
    super.onNewIntent(intent)
    setIntent(intent)
    MiqNotificationIntentHandler.openNotification(intent, webView)
}
```

The merged manifest contributes `POST_NOTIFICATIONS` and the non-exported
`FirebaseMessagingService`, plus a monochrome default status-bar icon and the
default channel metadata used by background FCM notifications. The coordinator creates channel
`miq_notifications`, stores a stable installation ID, refreshes rotated tokens
through `onNewToken`, and deletes the local FCM token when the user disables app
notifications. Server logout independently retires devices bound to that login
session, so a failed WebView cleanup request cannot leave private push enabled.
Both directions of the bridge enforce the exact `https://360miq.com` origin;
an asynchronous token callback is dropped if the WebView has navigated elsewhere.
If the host app already declares Firebase default notification metadata, keep a
monochrome small icon and the `miq_notifications` channel ID when overriding the
library values.
