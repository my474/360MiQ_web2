package com.miq360.notifications

import android.Manifest
import android.annotation.SuppressLint
import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.pm.PackageManager
import android.os.Build
import android.util.Log
import android.webkit.WebView
import androidx.activity.result.contract.ActivityResultContracts
import androidx.annotation.RequiresApi
import androidx.core.app.NotificationManagerCompat
import androidx.core.content.ContextCompat
import androidx.core.net.toUri
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.webkit.WebMessageCompat
import androidx.webkit.WebViewCompat
import androidx.webkit.WebViewFeature
import com.google.android.gms.tasks.Task
import com.google.firebase.FirebaseApp
import com.google.firebase.messaging.FirebaseMessaging
import org.json.JSONObject
import java.lang.ref.WeakReference

/**
 * Origin-scoped bridge between the authenticated 360MiQ WebView and FCM.
 * Attach before loading the first 360miq.com page. Permission is requested only
 * after JavaScript posts the explicit requestPermission action.
 */
class MiqNotificationCoordinator private constructor(
    activity: FragmentActivity,
    webView: WebView,
    appVersion: String,
    trustedOrigin: String
) {
    private var activityRef = WeakReference(activity)
    private val webViewRef = WeakReference(webView)
    private val appVersion = appVersion.take(40)
    private val trustedOrigin = trustedOrigin.trimEnd('/')
    private lateinit var permissionFragment: PermissionFragment
    private var bridgeInstalled = false
    private var registrationTask: Task<Void>? = null
    private var unregistrationTask: Task<Void>? = null
    private var registrationSyncQueued = false
    private var queuedPermissionReport = false
    private var explicitRegistrationPending = false

    init {
        require(trustedOrigin == MiqNotificationContract.DEFAULT_ORIGIN) {
            "The notification bridge must use the canonical HTTPS origin."
        }
        createNotificationChannel(activity)
        bindPermissionFragment(activity)
        bridgeInstalled = installOriginScopedBridge(webView)
        active = WeakReference(this)
    }

    private fun bindPermissionFragment(activity: FragmentActivity) {
        if (::permissionFragment.isInitialized) permissionFragment.clearCallback()
        permissionFragment = PermissionFragment.obtain(activity) { granted ->
            val currentActivity = activityRef.get()
            if (currentActivity != null) {
                if (granted) {
                    MiqNotificationContract.setOptedIn(currentActivity, true)
                    syncRegistration(reportPermissionResult = true)
                } else {
                    MiqNotificationContract.setOptedIn(currentActivity, false)
                    unregisterProvider(currentActivity)
                    reportToPage(false, "", "Permission was denied")
                }
            }
        }
    }

    private fun reattach(activity: FragmentActivity) {
        if (activityRef.get() === activity) return
        activityRef = WeakReference(activity)
        createNotificationChannel(activity)
        bindPermissionFragment(activity)
    }

    private fun detach() {
        permissionFragment.clearCallback()
        webViewRef.get()?.let { webView ->
            if (
                bridgeInstalled &&
                WebViewFeature.isFeatureSupported(WebViewFeature.WEB_MESSAGE_LISTENER)
            ) {
                WebViewCompat.removeWebMessageListener(
                    webView,
                    MiqNotificationContract.BRIDGE_NAME
                )
            }
        }
        bridgeInstalled = false
        activityRef.clear()
        webViewRef.clear()
    }

    @SuppressLint("RequiresFeature")
    private fun installOriginScopedBridge(webView: WebView): Boolean {
        if (!WebViewFeature.isFeatureSupported(WebViewFeature.WEB_MESSAGE_LISTENER)) {
            Log.w(TAG, "Android System WebView does not support the secure notification bridge")
            return false
        }
        WebViewCompat.addWebMessageListener(
            webView,
            MiqNotificationContract.BRIDGE_NAME,
            setOf(trustedOrigin)
        ) { _, message: WebMessageCompat, sourceOrigin, isMainFrame, _ ->
            if (!isMainFrame || !isTrustedPage(sourceOrigin.toString())) {
                return@addWebMessageListener
            }
            val action = try {
                JSONObject(message.data ?: "{}").optString("action")
            } catch (_: Throwable) {
                ""
            }
            when (action) {
                "requestPermission" -> requestPermission()
                "syncToken" -> reconcileRegistration()
                "deleteToken" -> deleteRegistration()
            }
        }
        return true
    }

    private fun requireActivity(): FragmentActivity =
        activityRef.get() ?: error("The notification host activity is no longer available.")

    private fun requestPermission() {
        val activity = requireActivity()
        activity.runOnUiThread {
            val systemEnabled =
                NotificationManagerCompat.from(activity).areNotificationsEnabled()
            val runtimeGranted = runtimePermissionGranted(activity)
            if (systemEnabled && runtimeGranted) {
                MiqNotificationContract.setOptedIn(activity, true)
                syncRegistration(reportPermissionResult = true)
            } else if (
                Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
                !runtimeGranted
            ) {
                permissionFragment.requestPermission()
            } else {
                MiqNotificationContract.setOptedIn(activity, false)
                unregisterProvider(activity)
                reportToPage(
                    false,
                    "",
                    "Notifications are disabled in Android settings"
                )
            }
        }
    }

    private fun reconcileRegistration() {
        val activity = requireActivity()
        if (!MiqNotificationContract.optedIn(activity)) {
            reportToPage(false, "", "App notifications are disabled")
            return
        }
        if (
            NotificationManagerCompat.from(activity).areNotificationsEnabled() &&
            runtimePermissionGranted(activity)
        ) {
            syncRegistration(reportPermissionResult = false)
            return
        }
        MiqNotificationContract.setOptedIn(activity, false)
        unregisterProvider(activity)
        reportToPage(false, "", "Notifications are disabled in Android settings")
    }

    private fun runtimePermissionGranted(activity: FragmentActivity): Boolean =
        Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU ||
            ContextCompat.checkSelfPermission(
                activity,
                Manifest.permission.POST_NOTIFICATIONS
            ) == PackageManager.PERMISSION_GRANTED

    internal fun syncRegistration(reportPermissionResult: Boolean) {
        val activity = requireActivity()
        val messaging = firebaseMessaging(activity)
        if (messaging == null) {
            MiqNotificationContract.setOptedIn(activity, false)
            MiqNotificationContract.clearRegistration(activity)
            reportToPage(
                false,
                "",
                "Firebase is not configured in this Android app build"
            )
            return
        }

        val pendingUnregistration = unregistrationTask
        if (pendingUnregistration != null && !pendingUnregistration.isComplete) {
            queuedPermissionReport = queuedPermissionReport || reportPermissionResult
            if (registrationSyncQueued) return
            registrationSyncQueued = true
            pendingUnregistration.addOnCompleteListener {
                registrationSyncQueued = false
                val reportAfterUnregistration = queuedPermissionReport
                queuedPermissionReport = false
                if (unregistrationTask === pendingUnregistration) {
                    unregistrationTask = null
                }
                if (MiqNotificationContract.optedIn(activity)) {
                    syncRegistration(reportAfterUnregistration)
                }
            }
            return
        }

        explicitRegistrationPending =
            explicitRegistrationPending || reportPermissionResult
        val pendingRegistration = registrationTask
        if (pendingRegistration != null && !pendingRegistration.isComplete) return

        messaging.isAutoInitEnabled = true
        val registration = messaging.register()
        registrationTask = registration
        registration.addOnCompleteListener { task ->
            if (registrationTask === registration) registrationTask = null
            if (!MiqNotificationContract.optedIn(activity)) {
                // Registration may finish after opt-out. Revoke it instead of
                // allowing a late callback to restore push delivery.
                MiqNotificationContract.clearRegistration(activity)
                unregisterProvider(activity)
                return@addOnCompleteListener
            }
            if (!task.isSuccessful) {
                if (explicitRegistrationPending) {
                    explicitRegistrationPending = false
                    MiqNotificationContract.setOptedIn(activity, false)
                    MiqNotificationContract.clearRegistration(activity)
                    unregisterProvider(activity)
                    reportToPage(
                        false,
                        "",
                        task.exception?.message.orEmpty().ifBlank {
                            "Android could not register for notifications"
                        }
                    )
                }
            }
        }
    }

    private fun deleteRegistration() {
        val activity = requireActivity()
        MiqNotificationContract.setOptedIn(activity, false)
        unregisterProvider(activity)
    }

    private fun unregisterProvider(activity: FragmentActivity) {
        val messaging = firebaseMessaging(activity)
        if (messaging == null) {
            unregistrationTask = null
            MiqNotificationContract.clearRegistration(activity)
            return
        }
        messaging.isAutoInitEnabled = false
        val unregistration = messaging.unregister()
        unregistrationTask = unregistration
        unregistration.addOnCompleteListener {
            if (unregistrationTask === unregistration) {
                unregistrationTask = null
            }
            if (!MiqNotificationContract.optedIn(activity)) {
                MiqNotificationContract.clearRegistration(activity)
            }
        }
    }

    private fun registrationReceived(installationId: String) {
        val activity = activityRef.get() ?: return
        if (!MiqNotificationContract.optedIn(activity)) {
            unregisterProvider(activity)
            return
        }
        explicitRegistrationPending = false
        MiqNotificationContract.saveRegistration(activity, installationId)
        reportToPage(true, installationId, "")
    }

    private fun firebaseMessaging(activity: FragmentActivity): FirebaseMessaging? {
        return try {
            if (FirebaseApp.initializeApp(activity) == null) {
                null
            } else {
                FirebaseMessaging.getInstance()
            }
        } catch (error: RuntimeException) {
            Log.e(TAG, "Firebase Messaging is unavailable", error)
            null
        }
    }

    private fun reportToPage(granted: Boolean, token: String, error: String) {
        val activity = activityRef.get() ?: return
        val webView = webViewRef.get() ?: return
        val metadata = JSONObject()
            .put(
                "installation_id",
                MiqNotificationContract.installationId(activity)
            )
            .put("label", Build.MANUFACTURER + " " + Build.MODEL)
            .put("app_version", appVersion)
            .put("target_type", "fid")
            .put("error", error.take(160))
        val script =
            "window.MIQNotifications&&window.MIQNotifications.androidPermissionResult(" +
                granted + "," + JSONObject.quote(token) + "," +
                JSONObject.quote(metadata.toString()) + ");"
        activity.runOnUiThread {
            if (!isTrustedPage(webView.url)) return@runOnUiThread
            webView.evaluateJavascript(script, null)
        }
    }

    private fun reportRegistrationRemovedToPage(installationId: String) {
        val activity = activityRef.get() ?: return
        val webView = webViewRef.get() ?: return
        val script =
            "window.MIQNotifications&&window.MIQNotifications.androidRegistrationRemoved(" +
                JSONObject.quote(installationId) + ");"
        activity.runOnUiThread {
            if (!isTrustedPage(webView.url)) return@runOnUiThread
            webView.evaluateJavascript(script, null)
        }
    }

    private fun reportUnreadToPage(unreadCount: Int) {
        val activity = activityRef.get() ?: return
        val webView = webViewRef.get() ?: return
        val script =
            "window.MIQNotifications&&window.MIQNotifications.updateUnread(" +
                unreadCount.coerceAtLeast(0) + ");"
        activity.runOnUiThread {
            if (!isTrustedPage(webView.url)) return@runOnUiThread
            webView.evaluateJavascript(script, null)
        }
    }

    private fun isTrustedPage(value: String?): Boolean {
        val current = try {
            (value ?: "").toUri()
        } catch (_: Throwable) {
            return false
        }
        val trusted = trustedOrigin.toUri()
        val currentPort =
            if (current.port >= 0) current.port
            else if (current.scheme.equals("https", true)) 443
            else 80
        val trustedPort =
            if (trusted.port >= 0) trusted.port
            else if (trusted.scheme.equals("https", true)) 443
            else 80
        return current.scheme.equals(trusted.scheme, ignoreCase = true) &&
            current.host.equals(trusted.host, ignoreCase = true) &&
            current.userInfo == null &&
            currentPort == trustedPort
    }

    companion object {
        private const val TAG = "MiqNotifications"
        private const val FRAGMENT_TAG = "miq-notification-permission"
        private var active: WeakReference<MiqNotificationCoordinator>? = null

        @JvmStatic
        @JvmOverloads
        fun attach(
            activity: FragmentActivity,
            webView: WebView,
            appVersion: String,
            trustedOrigin: String = MiqNotificationContract.DEFAULT_ORIGIN
        ): MiqNotificationCoordinator {
            val existing = active?.get()
            if (existing?.webViewRef?.get() === webView) {
                existing.reattach(activity)
                return existing
            }
            existing?.detach()
            return MiqNotificationCoordinator(
                activity,
                webView,
                appVersion,
                trustedOrigin
            )
        }

        internal fun registrationReceived(installationId: String) {
            active?.get()?.registrationReceived(installationId)
        }

        internal fun registrationRemoved(installationId: String) {
            active?.get()?.reportRegistrationRemovedToPage(installationId)
        }

        internal fun notificationReceived(unreadCount: Int) {
            active?.get()?.reportUnreadToPage(unreadCount)
        }
    }

    class PermissionFragment : Fragment() {
        private var callback: ((Boolean) -> Unit)? = null
        private val launcher = registerForActivityResult(
            ActivityResultContracts.RequestPermission()
        ) { granted ->
            callback?.invoke(granted)
        }

        @RequiresApi(Build.VERSION_CODES.TIRAMISU)
        fun requestPermission() {
            launcher.launch(Manifest.permission.POST_NOTIFICATIONS)
        }

        fun clearCallback() {
            callback = null
        }

        companion object {
            fun obtain(
                activity: FragmentActivity,
                callback: (Boolean) -> Unit
            ): PermissionFragment {
                val manager = activity.supportFragmentManager
                val fragment = (
                    manager.findFragmentByTag(FRAGMENT_TAG) as? PermissionFragment
                ) ?: PermissionFragment().also {
                    manager.beginTransaction().add(it, FRAGMENT_TAG).commitNow()
                }
                fragment.callback = callback
                return fragment
            }
        }
    }

    private fun createNotificationChannel(activity: FragmentActivity) {
        if (Build.VERSION.SDK_INT < Build.VERSION_CODES.O) return
        val manager = activity.getSystemService(NotificationManager::class.java)
        val channel = NotificationChannel(
            MiqNotificationContract.CHANNEL_ID,
            MiqNotificationContract.CHANNEL_NAME,
            NotificationManager.IMPORTANCE_HIGH
        ).apply {
            description = MiqNotificationContract.CHANNEL_DESCRIPTION
        }
        manager.createNotificationChannel(channel)
    }
}
