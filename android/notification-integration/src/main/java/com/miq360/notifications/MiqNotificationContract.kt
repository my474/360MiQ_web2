package com.miq360.notifications

import android.content.Context
import java.util.UUID

internal object MiqNotificationContract {
    const val CHANNEL_ID = "miq_notifications"
    const val CHANNEL_NAME = "360MiQ notifications"
    const val CHANNEL_DESCRIPTION = "Price alerts and account notifications"
    const val BRIDGE_NAME = "MiqAndroidNotifications"
    const val DEFAULT_ORIGIN = "https://360miq.com"
    const val EXTRA_LINK_URL = "miq_notification_link_url"
    const val EXTRA_NOTIFICATION_ID = "miq_notification_id"

    private const val PREFERENCES = "miq_notification_preferences"
    private const val INSTALLATION_ID = "installation_id"
    private const val OPTED_IN = "opted_in"
    private const val LAST_TOKEN = "last_token"

    private fun preferences(context: Context) =
        context.getSharedPreferences(PREFERENCES, Context.MODE_PRIVATE)

    fun installationId(context: Context): String {
        val preferences = preferences(context)
        val existing = preferences.getString(INSTALLATION_ID, null)
        if (!existing.isNullOrBlank()) return existing
        val created = "android-${UUID.randomUUID()}"
        preferences.edit().putString(INSTALLATION_ID, created).apply()
        return created
    }

    fun optedIn(context: Context): Boolean = preferences(context).getBoolean(OPTED_IN, false)

    fun setOptedIn(context: Context, enabled: Boolean) {
        preferences(context).edit().putBoolean(OPTED_IN, enabled).apply()
    }

    fun saveToken(context: Context, token: String) {
        preferences(context).edit().putString(LAST_TOKEN, token).apply()
    }

    fun clearToken(context: Context) {
        preferences(context).edit().remove(LAST_TOKEN).apply()
    }
}
