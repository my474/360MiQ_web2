package com.miq360.notifications

import android.Manifest
import android.annotation.SuppressLint
import android.app.PendingIntent
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import androidx.core.app.NotificationCompat
import androidx.core.app.NotificationManagerCompat
import androidx.core.content.ContextCompat
import androidx.core.net.toUri
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

@SuppressLint("MissingFirebaseInstanceTokenRefresh")
class MiqFirebaseMessagingService : FirebaseMessagingService() {
    override fun onRegistered(installationId: String) {
        if (MiqNotificationContract.optedIn(this)) {
            MiqNotificationContract.saveRegistration(this, installationId)
            MiqNotificationCoordinator.registrationReceived(installationId)
        } else {
            MiqNotificationContract.clearRegistration(this)
        }
    }

    override fun onUnregistered(installationId: String) {
        MiqNotificationContract.clearRegistration(this)
        MiqNotificationCoordinator.registrationRemoved(installationId)
    }

    override fun onMessageReceived(message: RemoteMessage) {
        // Android displays notification payloads itself while the app is in the
        // background. This path covers foreground and data-only delivery.
        if (!MiqNotificationContract.optedIn(this)) return

        val unreadCount = message.data["unread_count"]?.toIntOrNull()?.coerceAtLeast(0)
        unreadCount?.let(MiqNotificationCoordinator::notificationReceived)

        if (!NotificationManagerCompat.from(this).areNotificationsEnabled()) return
        if (
            Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU &&
            ContextCompat.checkSelfPermission(
                this,
                Manifest.permission.POST_NOTIFICATIONS
            ) != PackageManager.PERMISSION_GRANTED
        ) {
            return
        }

        val title = message.notification?.title ?: "360MiQ notification"
        val body = message.notification?.body ?: "You have a new notification."
        val link = safeLink(message.data[MiqNotificationContract.FCM_LINK_URL])
        val notificationId = message.data[MiqNotificationContract.FCM_NOTIFICATION_ID].orEmpty()
        val launchIntent = packageManager.getLaunchIntentForPackage(packageName) ?: return
        launchIntent.flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
        launchIntent.data = link.toUri()
        launchIntent.putExtra(MiqNotificationContract.EXTRA_LINK_URL, link)
        launchIntent.putExtra(MiqNotificationContract.EXTRA_NOTIFICATION_ID, notificationId)

        val requestKey = notificationId.ifBlank { message.messageId ?: link }
        val pendingIntent = PendingIntent.getActivity(
            this,
            requestKey.hashCode(),
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
            .setVisibility(NotificationCompat.VISIBILITY_PRIVATE)
            .setContentIntent(pendingIntent)
            .apply {
                if (unreadCount != null && unreadCount > 0) setNumber(unreadCount)
            }
            .build()

        val systemNotificationId = message.messageId?.hashCode() ?: requestKey.hashCode()
        NotificationManagerCompat.from(this).notify(systemNotificationId, notification)
    }

    private fun safeLink(value: String?): String =
        MiqNotificationIntentHandler.validatedUrl(value)
            ?: "${MiqNotificationContract.DEFAULT_ORIGIN}/workspace?tab=notifications"
}
