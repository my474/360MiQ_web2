package com.miq360.notifications

import org.junit.Assert.assertEquals
import org.junit.Assert.assertNull
import org.junit.Test

class MiqNotificationIntentHandlerTest {
    @Test
    fun acceptsOnlyCanonicalHttpsLinks() {
        assertEquals(
            "https://360miq.com/workspace?tab=notifications",
            MiqNotificationIntentHandler.validatedUrl(
                "https://360miq.com/workspace?tab=notifications"
            )
        )
        assertEquals(
            "https://360MIQ.com:443/account/",
            MiqNotificationIntentHandler.validatedUrl(
                "https://360MIQ.com:443/account/"
            )
        )

        assertNull(
            MiqNotificationIntentHandler.validatedUrl(
                "http://360miq.com/workspace"
            )
        )
        assertNull(
            MiqNotificationIntentHandler.validatedUrl(
                "https://360miq.com.evil.example/workspace"
            )
        )
        assertNull(
            MiqNotificationIntentHandler.validatedUrl(
                "https://user@360miq.com/workspace"
            )
        )
        assertNull(
            MiqNotificationIntentHandler.validatedUrl(
                "https://360miq.com:444/workspace"
            )
        )
        assertNull(MiqNotificationIntentHandler.validatedUrl("/workspace"))
        assertNull(MiqNotificationIntentHandler.validatedUrl(null))
    }
}
