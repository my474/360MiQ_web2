package com.miq360.notifications

import android.content.Context
import androidx.core.content.edit
import java.util.UUID

internal object MiqNotificationContract {
    const val CHANNEL_ID = "miq_notifications"
    const val CHANNEL_NAME = "360MiQ notifications"
    const val CHANNEL_DESCRIPTION = "Price alerts and account notifications"
    const val BRIDGE_NAME = "MiqAndroidNotifications"
    const val DEFAULT_ORIGIN = "https://360miq.com"
    const val EXTRA_LINK_URL = "miq_notification_link_url"
    const val EXTRA_NOTIFICATION_ID = "miq_notification_id"
    const val FCM_LINK_URL = "link_url"
    const val FCM_NOTIFICATION_ID = "notification_id"

    private const val PREFERENCES = "miq_notification_preferences"
    private const val INSTALLATION_ID = "installation_id"
    private const val OPTED_IN = "opted_in"
    private const val LAST_REGISTRATION = "last_registration"

    private fun preferences(context: Context) =
        context.getSharedPreferences(PREFERENCES, Context.MODE_PRIVATE)

    fun installationId(context: Context): String {
        val preferences = preferences(context)
        val existing = preferences.getString(INSTALLATION_ID, null)
        if (!existing.isNullOrBlank()) return existing
        val created = "android-${UUID.randomUUID()}"
        preferences.edit { putString(INSTALLATION_ID, created) }
        return created
    }

    fun optedIn(context: Context): Boolean =
        preferences(context).getBoolean(OPTED_IN, false)

    fun setOptedIn(context: Context, enabled: Boolean) {
        preferences(context).edit { putBoolean(OPTED_IN, enabled) }
    }

    fun saveRegistration(context: Context, registration: String) {
        preferences(context).edit {
            putString(LAST_REGISTRATION, registration)
        }
    }

    fun clearRegistration(context: Context) {
        preferences(context).edit { remove(LAST_REGISTRATION) }
    }
}
