<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="allocationview">
    <xsl:apply-templates select="resource"/>
</xsl:template>

<!-- this template is only used for AllocationView -->
<xsl:template match="resource">
    <xsl:if test="level = 1"> <!-- add extra space at root level -->
        <br/>
    </xsl:if>
    <xsl:if test="type = 'group'">
        <div class="allocation_view_resource_title" style="padding-left: {-15+15*level}px;"><xsl:value-of select="name"/></div>
    </xsl:if>

    <xsl:choose>
        <!-- if we are one level up from a leaf (room), then we generate a single table containing all children (beds) -->
        <xsl:when test="resource/cells/allocationcell">
            <div id="table_resource_{id}">
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                        <tr valign="top">
                            <td width="180"></td>
                            <td class="availability_header"><xsl:value-of select="//dateheaders/header"/></td>
                        </tr>
                        <tr valign="top">
                            <td colspan="2" width="{60 * count(//dateheaders/datecol)}" valign="top">
                                <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                                    <thead>
                                        <tr class="even">
                                            <th class="alloc_resource_attrib"><xsl:value-of select="name"/></th>
                                            <xsl:apply-templates select="//dateheaders/datecol" mode="availability_date_header"/>
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
            </div>
        </xsl:when>
        <xsl:otherwise>
            <!-- recurse if required -->
            <xsl:apply-templates select="resource"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- adds header entries for the availability table -->
<xsl:template mode="availability_date_header" match="datecol">
    <th class="alloc_view_date"><xsl:value-of select="date"/><span><xsl:value-of select="day"/></span></th>
</xsl:template>

<!-- adds row for each resource in the availability table -->
<xsl:template match="cells">
    <tr>
        <xsl:attribute name="class">
            <xsl:choose>
                <xsl:when test="position() mod 2">odd</xsl:when>
                <xsl:otherwise>even</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <td>
            <xsl:attribute name="class">
                border_left border_right
                <xsl:if test="position() = last()">
                    border_bottom
                </xsl:if>
            </xsl:attribute>
            <xsl:value-of select="../name"/>
        </td>
        <xsl:apply-templates select="allocationcell"/>
    </tr>
</xsl:template>

<!-- adds table entries for each allocation cell in the availability table -->
<xsl:template match="allocationcell">
    <td>
        <xsl:attribute name="class">
            <xsl:if test="count(../../../resource) = count(../../preceding-sibling::resource)+1">
                border_bottom
            </xsl:if>
            <xsl:if test="position() = last()">
                border_right
            </xsl:if>
        </xsl:attribute>
        <xsl:if test="@span &gt; 1">
            <xsl:attribute name="colspan"><xsl:value-of select="@span"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="id &gt; 0">
            <a>
                <xsl:attribute name="class">
                    booking_item <xsl:value-of select="render"/> status_<xsl:value-of select="status"/><xsl:if test="checkedout = 'true'">_checkout</xsl:if>
                </xsl:attribute>
                <xsl:value-of select="name"/>&#160;
            </a>
        </xsl:if>
        <xsl:if test="render = 'rounded_both' or render = 'rounded_right'">
            <xsl:if test="status = 'free' or status = 'hours' or status = 'paid'">
                <div style="position:relative;">
                    <a href="javascript:toggle_checkout_for_allocation({../../../id}, {id}, {count(preceding-sibling::allocationcell)});" class="checkout_link" title="checkout/uncheckout">
                        <img class="toggle_checkout" alt=""/>
                    </a>
                </div>
            </xsl:if>
        </xsl:if>
    </td>
</xsl:template>

</xsl:stylesheet>