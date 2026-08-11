package com.miq360.notifications

import android.Manifest
import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.webkit.WebView
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.content.ContextCompat
import androidx.core.app.NotificationManagerCompat
import androidx.fragment.app.Fragment
import androidx.fragment.app.FragmentActivity
import androidx.webkit.WebMessageCompat
import androidx.webkit.WebViewCompat
import androidx.webkit.WebViewFeature
import com.google.android.gms.tasks.Task
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
    private var tokenDeletion: Task<Void>? = null
    private var tokenSyncQueued = false
    private var queuedPermissionReport = false

    init {
        require(trustedOrigin == MiqNotificationContract.DEFAULT_ORIGIN) {
            "The notification bridge must use the canonical HTTPS origin."
        }
        createNotificationChannel(activity)
        bindPermissionFragment(activity)
        installOriginScopedBridge(webView)
        active = WeakReference(this)
    }

    private fun bindPermissionFragment(activity: FragmentActivity) {
        if (::permissionFragment.isInitialized) permissionFragment.clearCallback()
        permissionFragment = PermissionFragment.obtain(activity) { granted ->
            val currentActivity = activityRef.get()
            if (currentActivity != null) {
                if (granted) {
                    MiqNotificationContract.setOptedIn(currentActivity, true)
                    syncToken(reportPermissionResult = true)
                } else {
                    MiqNotificationContract.setOptedIn(currentActivity, false)
                    deleteToken()
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
            if (WebViewFeature.isFeatureSupported(WebViewFeature.WEB_MESSAGE_LISTENER)) {
                WebViewCompat.removeWebMessageListener(webView, MiqNotificationContract.BRIDGE_NAME)
            }
        }
        activityRef.clear()
        webViewRef.clear()
    }

    private fun installOriginScopedBridge(webView: WebView) {
        check(WebViewFeature.isFeatureSupported(WebViewFeature.WEB_MESSAGE_LISTENER)) {
            "Android System WebView must support origin-scoped WebMessage listeners."
        }
        WebViewCompat.addWebMessageListener(
            webView,
            MiqNotificationContract.BRIDGE_NAME,
            setOf(trustedOrigin)
        ) { _, message: WebMessageCompat, sourceOrigin, isMainFrame, _ ->
            if (!isMainFrame || !isTrustedPage(sourceOrigin.toString())) return@addWebMessageListener
            val action = try { JSONObject(message.data ?: "{}").optString("action") } catch (_: Throwable) { "" }
            when (action) {
                "requestPermission" -> requestPermission()
                "syncToken" -> reconcileToken()
                "deleteToken" -> deleteToken()
            }
        }
    }

    private fun requireActivity(): FragmentActivity =
        activityRef.get() ?: error("The notification host activity is no longer available.")

    private fun requestPermission() {
        val activity = requireActivity()
        activity.runOnUiThread {
            val systemEnabled = NotificationManagerCompat.from(activity).areNotificationsEnabled()
            val runtimeGranted = runtimePermissionGranted(activity)
            if (systemEnabled && runtimeGranted) {
                MiqNotificationContract.setOptedIn(activity, true)
                syncToken(true)
            } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU && !runtimeGranted) {
                permissionFragment.requestPermission()
            } else {
                MiqNotificationContract.setOptedIn(activity, false)
                deleteToken()
                reportToPage(false, "", "Notifications are disabled in Android settings")
            }
        }
    }

    private fun reconcileToken() {
        val activity = requireActivity()
        if (!MiqNotificationContract.optedIn(activity)) {
            reportToPage(false, "", "App notifications are disabled")
            return
        }
        if (NotificationManagerCompat.from(activity).areNotificationsEnabled() && runtimePermissionGranted(activity)) {
            syncToken(false)
            return
        }
        MiqNotificationContract.setOptedIn(activity, false)
        deleteToken()
        reportToPage(false, "", "Notifications are disabled in Android settings")
    }

    private fun runtimePermissionGranted(activity: FragmentActivity): Boolean =
        Build.VERSION.SDK_INT < Build.VERSION_CODES.TIRAMISU ||
            ContextCompat.checkSelfPermission(activity, Manifest.permission.POST_NOTIFICATIONS) == PackageManager.PERMISSION_GRANTED

    internal fun syncToken(reportPermissionResult: Boolean) {
        val activity = requireActivity()
        val pendingDeletion = tokenDeletion
        if (pendingDeletion != null && !pendingDeletion.isComplete) {
            queuedPermissionReport = queuedPermissionReport || reportPermissionResult
            if (tokenSyncQueued) return
            tokenSyncQueued = true
            pendingDeletion.addOnCompleteListener {
                tokenSyncQueued = false
                val reportAfterDeletion = queuedPermissionReport
                queuedPermissionReport = false
                if (tokenDeletion === pendingDeletion) tokenDeletion = null
                if (MiqNotificationContract.optedIn(activity)) syncToken(reportAfterDeletion)
            }
            return
        }
        FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
            val token = if (task.isSuccessful) task.result.orEmpty() else ""
            if (!MiqNotificationContract.optedIn(activity)) {
                // A pending token lookup may finish after the user opts out.
                // Do not persist or report that stale result, and revoke it in
                // case it was minted after the earlier deletion completed.
                MiqNotificationContract.clearToken(activity)
                deleteProviderToken(activity)
                return@addOnCompleteListener
            }
            if (token.isBlank()) {
                if (reportPermissionResult) {
                    // Permission alone is not a completed opt-in. Roll back the
                    // native state when the explicit request cannot obtain a
                    // token, even if the WebView navigates before our callback.
                    MiqNotificationContract.setOptedIn(activity, false)
                    MiqNotificationContract.clearToken(activity)
                    deleteProviderToken(activity)
                    reportToPage(false, "", task.exception?.message.orEmpty())
                }
                return@addOnCompleteListener
            }
            MiqNotificationContract.saveToken(activity, token)
            reportToPage(true, token, "")
        }
    }

    private fun deleteToken() {
        val activity = requireActivity()
        MiqNotificationContract.setOptedIn(activity, false)
        deleteProviderToken(activity)
    }

    private fun deleteProviderToken(activity: FragmentActivity) {
        val deletion = FirebaseMessaging.getInstance().deleteToken()
        tokenDeletion = deletion
        deletion.addOnCompleteListener {
            if (tokenDeletion === deletion) tokenDeletion = null
            if (!MiqNotificationContract.optedIn(activity)) MiqNotificationContract.clearToken(activity)
        }
    }

    private fun reportToPage(granted: Boolean, token: String, error: String) {
        val activity = activityRef.get() ?: return
        val webView = webViewRef.get() ?: return
        val metadata = JSONObject()
            .put("installation_id", MiqNotificationContract.installationId(activity))
            .put("label", Build.MANUFACTURER + " " + Build.MODEL)
            .put("app_version", appVersion)
            .put("error", error.take(160))
        val script = "window.MIQNotifications&&window.MIQNotifications.androidPermissionResult(" +
            granted + "," + JSONObject.quote(token) + "," + JSONObject.quote(metadata.toString()) + ");"
        activity.runOnUiThread {
            if (!isTrustedPage(webView.url)) return@runOnUiThread
            webView.evaluateJavascript(script, null)
        }
    }

    private fun reportUnreadToPage(unreadCount: Int) {
        val activity = activityRef.get() ?: return
        val webView = webViewRef.get() ?: return
        val script = "window.MIQNotifications&&window.MIQNotifications.updateUnread(" +
            unreadCount.coerceAtLeast(0) + ");"
        activity.runOnUiThread {
            if (!isTrustedPage(webView.url)) return@runOnUiThread
            webView.evaluateJavascript(script, null)
        }
    }

    private fun isTrustedPage(value: String?): Boolean {
        val current = try { Uri.parse(value ?: "") } catch (_: Throwable) { return false }
        val trusted = Uri.parse(trustedOrigin)
        val currentPort = if (current.port >= 0) current.port else if (current.scheme.equals("https", true)) 443 else 80
        val trustedPort = if (trusted.port >= 0) trusted.port else if (trusted.scheme.equals("https", true)) 443 else 80
        return current.scheme.equals(trusted.scheme, ignoreCase = true) &&
            current.host.equals(trusted.host, ignoreCase = true) &&
            current.userInfo == null &&
            currentPort == trustedPort
    }

    companion object {
        private const val FRAGMENT_TAG = "miq-notification-permission"
        private var active: WeakReference<MiqNotificationCoordinator>? = null

        @JvmStatic
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
            return MiqNotificationCoordinator(activity, webView, appVersion, trustedOrigin)
        }

        internal fun tokenRotated() {
            val coordinator = active?.get() ?: return
            val activity = coordinator.activityRef.get() ?: return
            if (!MiqNotificationContract.optedIn(activity)) return
            coordinator.reconcileToken()
        }

        internal fun notificationReceived(unreadCount: Int) {
            active?.get()?.reportUnreadToPage(unreadCount)
        }
    }

    class PermissionFragment : Fragment() {
        private var callback: ((Boolean) -> Unit)? = null
        private val launcher = registerForActivityResult(ActivityResultContracts.RequestPermission()) { granted ->
            callback?.invoke(granted)
        }

        fun requestPermission() = launcher.launch(Manifest.permission.POST_NOTIFICATIONS)

        fun clearCallback() {
            callback = null
        }

        companion object {
            fun obtain(activity: FragmentActivity, callback: (Boolean) -> Unit): PermissionFragment {
                val manager = activity.supportFragmentManager
                val fragment = (manager.findFragmentByTag(MiqNotificationCoordinator.FRAGMENT_TAG) as? PermissionFragment)
                    ?: PermissionFragment().also {
                        manager.beginTransaction().add(it, MiqNotificationCoordinator.FRAGMENT_TAG).commitNow()
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
        ).apply { description = MiqNotificationContract.CHANNEL_DESCRIPTION }
        manager.createNotificationChannel(channel)
    }
}
