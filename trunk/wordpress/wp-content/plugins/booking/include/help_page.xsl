﻿<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">
    <xsl:choose>
        <xsl:when test="name = 'help'">
            <ul>
        </xsl:when>

        <xsl:when test="name = 'pages'">
            <ul>
        </xsl:when>

        <xsl:when test="name = 'add-edit-booking'">
            <h1>Introduction</h1>
        </xsl:when>

        <xsl:when test="name = 'allocations'">
            <h1>Introduction</h1>
        </xsl:when>

        <xsl:when test="name = 'bookings'">
            <h1>Introduction</h1>
        </xsl:when>

        <xsl:when test="name = 'daily-summary'">
            <h1>Introduction</h1>
        </xsl:when>

        <xsl:when test="name = 'resources'">
            <h1>Introduction</h1>
        </xsl:when>

        <xsl:when test="name = 'housekeeping'">
            <h1>Introduction</h1>
        </xsl:when>

        <xsl:when test="name = 'faq'">
            <ul>
        </xsl:when>

        <xsl:when test="name = 'how-do-i-add-a-new-booking'">
            <h2>Summary</h2>
        </xsl:when>

        <xsl:when test="name = 'how-do-i-check-in-a-guest'">
            <h2>Summary</h2>

        <xsl:when test="name = 'how-do-i-checkout-a-single-guest'">
            <h2>Process</h2>
        </xsl:when>

        <xsl:when test="name = 'how-do-i-checkout-all-guests-for-a-booking'">
            <h2>Process</h2>
        </xsl:when>

        <xsl:when test="name = 'how-do-i-add-additional-nights-to-an-existing-booking'">
            <h2>Process</h2>
        </xsl:when>

        <xsl:when test="name = 'how-do-i-cancel-nights-from-an-existing-booking'">
            <h2>Summary</h2>
        </xsl:when>

        <xsl:when test="name = 'how-do-i-change-the-room-allocation-for-a-booking'">
            <h2>Process</h2>
        </xsl:when>

        <xsl:when test="name = 'how-do-i-deactivate-a-room-for-a-particular-set-of-dates'">
            <h2>Summary</h2>
        </xsl:when>

        <xsl:otherwise>
            Help page slug '<xsl:value-of select="name"/>' not recognised.
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>