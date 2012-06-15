<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="//resource">
    <xsl:if test="level = 1">
        <h1><xsl:value-of select="name"/></h1>
    </xsl:if>
    <xsl:if test="level = 2">
        <h2><xsl:value-of select="name"/></h2>
    </xsl:if>
    <xsl:if test="level = 3">
        <h3><xsl:value-of select="name"/></h3>
    </xsl:if>
    
    <!-- recurse if required -->
    <xsl:apply-templates select="resource"/>
    
    <!-- table visible only if we have at least one -->
    <xsl:if test="allocation">
    
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr valign="top">
                    <td width="240"></td>
                    <td class="availability_header"><xsl:value-of select="/view/dateheaders/header"/></td>
                </tr>
                <tr valign="top">
                    <td colspan="2" width="870" valign="top">
                        <table class="availability" width="100%" cellspacing="0" cellpadding="3" border="0">
                            <thead>
                                <tr>
                                    <th class="avail_attrib">Name</th>
                                    <th class="avail_attrib">Gender</th>
                                    <th class="avail_attrib">Room Type</th>
                                    <th class="avail_calendar_chevrons"><a href="javascript:shift_availability_calendar('left');">&lt;&lt;</a></th>
                                    <xsl:apply-templates select="/view/dateheaders/datecol" mode="availability_date_header"/>
                                    <th class="avail_calendar_chevrons"><a href="javascript:shift_availability_calendar('right');">&gt;&gt;</a></th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:apply-templates select="allocation" mode="allocation_dates"/>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </xsl:if>  
</xsl:template>

<!-- adds "class" attribute to a table row depending on position -->
<xsl:template name="row_class" match="text()">
    <xsl:param name="posn" select="'0'" />
    <xsl:attribute name="class">
        <xsl:choose>
            <xsl:when test="$posn mod 2">odd</xsl:when>
            <xsl:otherwise>even</xsl:otherwise>
        </xsl:choose>
    </xsl:attribute>
</xsl:template>

<!-- adds header entries for the availability table -->
<xsl:template mode="availability_date_header" match="datecol">
    <th class="avail_date_attrib"><xsl:value-of select="date"/><span><xsl:value-of select="day"/></span></th>
</xsl:template>

<!-- adds row for each allocation in the availability table -->
<xsl:template mode="allocation_dates" match="allocation">
    <tr>
        <td class="avail_attrib">
            <xsl:if test="isAvailable != 'true'">
                <xsl:attribute name="class">highlight_cell_red</xsl:attribute>
            </xsl:if>
            <xsl:value-of select="name"/>
        </td>
        <td class="avail_attrib"><xsl:value-of select="gender"/></td>
        <td class="avail_attrib"><xsl:value-of select="resource"/></td>
        <td class="avail_calendar_chevrons"><xsl:if test="bookingsBeforeMinDate > 0">+<xsl:value-of select="bookingsBeforeMinDate"/></xsl:if></td>
        <xsl:apply-templates select="dates/date" mode="allocation_date"/>
        <td class="avail_calendar_chevrons"><xsl:if test="bookingsAfterMaxDate > 0">+<xsl:value-of select="bookingsAfterMaxDate"/></xsl:if></td>
    </tr>
</xsl:template>

<!-- adds table entries for each allocation in the availability table -->
<xsl:template mode="allocation_date" match="date">
    <td id="cell_{../../rowid}_{.}">
        <xsl:attribute name="class">avail_date_attrib date_status_<xsl:value-of select="@state"/></xsl:attribute>
        <xsl:choose>
            <xsl:when test="@state != 'inactive'">
                <a href="javascript:toggle_booking_date({../../rowid}, '{.}', 'cell_{../../rowid}_{.}');"><xsl:value-of select="@payment"/></a>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="@payment"/>
            </xsl:otherwise>
        </xsl:choose>
    </td>
</xsl:template>

</xsl:stylesheet>