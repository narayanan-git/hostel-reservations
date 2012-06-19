<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
// View of allocations by group, room, beds...
-->
<xsl:template match="/view">
    <xsl:apply-templates select="resource"/>
</xsl:template>

<xsl:template match="resource">
    <div class="allocation_view_resource_lvl{level}"><xsl:value-of select="name"/></div>

    <xsl:choose>
        <!-- if this is the immediate parent (one level up from leaf node), then we generate a single table containing all children -->
        <xsl:when test="type = 'room' and resource/cells/allocationcell">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr valign="top">
                        <td width="180"></td>
                        <td class="availability_header"><xsl:value-of select="/view/dateheaders/header"/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2" width="{60 * count(/view/dateheaders/datecol)}" valign="top">
                            <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                                <thead>
                                    <tr>
                                        <th class="alloc_resource_attrib">Bed</th>
                                        <xsl:apply-templates select="/view/dateheaders/datecol" mode="availability_date_header"/>
                                    </tr>
                                </thead>
                                <tbody>
                                    <xsl:apply-templates select="resource/cells"/>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </xsl:when>
        <!-- if this is a room (as a single unit), then we generate a single table with only the beds in the room -->
        <xsl:when test="type = 'room' and numberChildren = 0">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr valign="top">
                        <td width="180"></td>
                        <td class="availability_header"><xsl:value-of select="/view/dateheaders/header"/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2" width="{60 * count(/view/dateheaders/datecol)}" valign="top">
                            <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                                <thead>
                                    <tr>
                                        <th class="alloc_resource_attrib"></th>
                                        <xsl:apply-templates select="/view/dateheaders/datecol" mode="availability_date_header"/>
                                    </tr>
                                </thead>
                                <tbody>
                                    <xsl:apply-templates select="cells"/>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </xsl:when>
        <xsl:otherwise>
            <!-- recurse if required -->
            <xsl:apply-templates select="resource"/>
        </xsl:otherwise>
    </xsl:choose>
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
    <th class="alloc_view_date"><xsl:value-of select="date"/><span><xsl:value-of select="day"/></span></th>
</xsl:template>

<!-- adds row for each resource in the availability table -->
<xsl:template match="cells">
    <tr>
        <xsl:if test="../type = 'bed'">
            <td><xsl:value-of select="../name"/></td>
        </xsl:if>
        <xsl:if test="../type = 'room'">
            <td>Room Bed</td>
        </xsl:if>
        <xsl:apply-templates select="allocationcell"/>
    </tr>
</xsl:template>

<!-- adds table entries for each allocation cell in the availability table -->
<xsl:template match="allocationcell">
    <td>
        <xsl:if test="@span &gt; 1">
            <xsl:attribute name="colspan"><xsl:value-of select="@span"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="id &gt; 0">
            <a class="booking_item status_{status}"><xsl:value-of select="name"/></a>
        </xsl:if>
    </td>
</xsl:template>

</xsl:stylesheet>