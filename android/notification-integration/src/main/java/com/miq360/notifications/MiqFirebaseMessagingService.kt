package com.miq360.notifications

import android.Manifest
import android.app.PendingIntent
import android.content.Intent
import android.content.pm.PackageManager
import android.net.Uri
import android.os.Build
import androidx.core.content.ContextCompat
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class MiqFirebaseMessagingService : FirebaseMessagingService() {
    override fun onNewToken(token: String) {
        if (MiqNotificationContract.optedIn(this)) {
            MiqNotificationContract.saveToken(this, token)
            MiqNotificationCoordinator.tokenRotated()
        } else {
            MiqNotificationContract.clearToken(this)
        }
    }

    override fun onMessageReceived(message: RemoteMessage) {
        // Android displays notification payloads itself while the app is in the
        // background. This path covers foreground and data-only delivery.
        if (!MiqNotificationContract.optedIn(this)) return
        message.data["unread_count"]?.toIntOrNull()?.let { unreadCount ->
            MiqNotificationCoordinator.notificationReceived(unreadCount)
        }
        if (!NotificationManagerCompat.from(this).areNotificationsEnabled()) return
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
            ContextCompat.checkSelfPermission(this, Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED
        ) return
        val title = message.notification?.title ?: "360MiQ notification"
        val body = message.notification?.body ?: "You have a new notification."
        val link = safeLink(message.data["link_url"])
        val launchIntent = packageManager.getLaunchIntentForPackage(packageName) ?: return
        launchIntent.flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
        launchIntent.data = Uri.parse(link)
        launchIntent.putExtra(MiqNotificationContract.EXTRA_LINK_URL, link)
        launchIntent.putExtra(MiqNotificationContract.EXTRA_NOTIFICATION_ID, message.data["notification_id"].orEmpty())
        val pendingIntent = PendingIntent.getActivity(
            this,
            message.data["notification_id"]?.hashCode() ?: 0,
            launchIntent,
            PendingIntent.FLAG_UPDATE_CURRENT or PendingIntent.FLAG_IMMUTABLE
        )
        val notification = NotificationCompat.Builder(this, MiqNotificationContract.CHANNEL_ID)
            .setSmallIcon(R.drawable.ic_stat_miq_notification)
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(NotificationCompat.BigTextStyle().bigText(body))
            .setAutoCancel(true)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setContentIntent(pendingIntent)
            .build()
        NotificationManagerCompat.from(this).notify(message.messageId?.hashCode() ?: link.hashCode(), notification)
    }

    private fun safeLink(value: String?): String {
        val fallback = "${MiqNotificationContract.DEFAULT_ORIGIN}/workspace?tab=notifications"
        return try {
            val uri = Uri.parse(value ?: fallback)
            if (uri.scheme.equals("https", true) &&
                uri.host.equals("360miq.com", true) &&
                uri.userInfo == null &&
                (uri.port == -1 || uri.port == 443)
            ) uri.toString() else fallback
        } catch (_: Throwable) { fallback }
    }
}
