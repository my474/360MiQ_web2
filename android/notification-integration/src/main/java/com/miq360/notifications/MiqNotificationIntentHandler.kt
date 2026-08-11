package com.miq360.notifications

import android.content.Intent
import android.net.Uri
import android.webkit.WebView

object MiqNotificationIntentHandler {
    /** Call from both Activity.onCreate and Activity.onNewIntent. */
    @JvmStatic
    fun openNotification(intent: Intent?, webView: WebView): Boolean {
        val candidate = intent?.getStringExtra(MiqNotificationContract.EXTRA_LINK_URL)
            ?: intent?.getStringExtra("link_url")
            ?: intent?.dataString
            ?: return false
        val uri = try { Uri.parse(candidate) } catch (_: Throwable) { return false }
        if (!uri.scheme.equals("https", true) ||
            !uri.host.equals("360miq.com", true) ||
            uri.userInfo != null ||
            (uri.port != -1 && uri.port != 443)
        ) return false
        webView.loadUrl(uri.toString())
        intent.removeExtra(MiqNotificationContract.EXTRA_LINK_URL)
        intent.removeExtra(MiqNotificationContract.EXTRA_NOTIFICATION_ID)
        intent.removeExtra("link_url")
        if (intent.dataString == candidate) intent.data = null
        return true
    }
}
