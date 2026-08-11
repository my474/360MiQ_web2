package com.miq360.notifications

import android.content.Intent
import android.webkit.WebView
import androidx.core.net.toUri
import java.net.URI

object MiqNotificationIntentHandler {
    /**
     * Copies only the notification fields needed by MainActivity. This avoids
     * forwarding arbitrary parcelables from the exported launcher activity.
     */
    @JvmStatic
    fun forwardNotificationPayload(source: Intent?, target: Intent): Intent {
        target.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
        if (source == null) return target

        copyStringExtra(source, target, MiqNotificationContract.EXTRA_LINK_URL)
        copyStringExtra(source, target, MiqNotificationContract.EXTRA_NOTIFICATION_ID)
        copyStringExtra(source, target, MiqNotificationContract.FCM_LINK_URL)
        copyStringExtra(source, target, MiqNotificationContract.FCM_NOTIFICATION_ID)

        source.dataString
            ?.let(::validatedUrl)
            ?.let { target.data = it.toUri() }
        return target
    }

    /** Call from both Activity.onCreate and Activity.onNewIntent. */
    @JvmStatic
    fun openNotification(intent: Intent?, webView: WebView): Boolean {
        val candidate = listOfNotNull(
            safeStringExtra(intent, MiqNotificationContract.EXTRA_LINK_URL),
            safeStringExtra(intent, MiqNotificationContract.FCM_LINK_URL),
            intent?.dataString
        ).firstNotNullOfOrNull(::validatedUrl) ?: return false

        webView.loadUrl(candidate)
        intent?.removeExtra(MiqNotificationContract.EXTRA_LINK_URL)
        intent?.removeExtra(MiqNotificationContract.EXTRA_NOTIFICATION_ID)
        intent?.removeExtra(MiqNotificationContract.FCM_LINK_URL)
        intent?.removeExtra(MiqNotificationContract.FCM_NOTIFICATION_ID)
        intent?.data = null
        return true
    }

    internal fun validatedUrl(value: String?): String? {
        val candidate = value?.trim().orEmpty()
        if (candidate.isEmpty()) return null
        val uri = try {
            URI(candidate)
        } catch (_: Exception) {
            return null
        }
        return if (
            uri.scheme.equals("https", ignoreCase = true) &&
            uri.host.equals("360miq.com", ignoreCase = true) &&
            uri.rawUserInfo == null &&
            (uri.port == -1 || uri.port == 443)
        ) {
            candidate
        } else {
            null
        }
    }

    private fun copyStringExtra(source: Intent, target: Intent, key: String) {
        safeStringExtra(source, key)?.let { target.putExtra(key, it) }
    }

    private fun safeStringExtra(intent: Intent?, key: String): String? = try {
        intent?.getStringExtra(key)
    } catch (_: RuntimeException) {
        null
    }
}
