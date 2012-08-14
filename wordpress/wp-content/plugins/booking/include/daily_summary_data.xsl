<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="/view" name="daily_summary_contents">
    <xsl:apply-templates select="dataview"/>
    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <xsl:apply-templates select="allocationview"/>
</xsl:template>

<xsl:template match="dataview">

    <div style="float:left;">
        <table width="420" cellspacing="0" cellpadding="3" border="1">
            <thead>
                <tr>
                    <th width="200"><xsl:comment/></th>
                    <th width="110">
                        Checked-Out
                    </th>
                    <th>
                        Remaining
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a id="collapse_checkouts" href="#" style="display:none" onclick="jQuery('#checkout_table').hide(); jQuery(this).hide(); jQuery('#expand_checkouts').show();"><img src="/wp-content/plugins/booking/img/collapse.gif"/></a>
                        <a id="expand_checkouts" href="#" onclick="jQuery('#checkout_table').show(); jQuery(this).hide(); jQuery('#collapse_checkouts').show();"><img src="/wp-content/plugins/booking/img/expand.gif"/></a>
                        &#160; Number of Checkouts
                    </td>
                    <td>19</td>
                    <td>36</td>
                </tr>
            </tbody>
        </table>
        <table id="checkout_table" style="display:none" width="420" cellspacing="0" cellpadding="3" border="1">
            <tbody>
                <tr>
                    <td width="200">&#160;&#160;&#160;&#160;Dorms</td>
                    <td width="110">21</td>
                    <td>18</td>
                </tr>
                <tr>
                    <td>&#160;&#160;&#160;&#160;Double</td>
                    <td>8</td>
                    <td>4</td>
                </tr>
                <tr>
                    <td>&#160;&#160;&#160;&#160;Twin</td>
                    <td>4</td>
                    <td>2</td>
                </tr>
                <tr>
                    <td>&#160;&#160;&#160;&#160;Triple</td>
                    <td>3</td>
                    <td>3</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div style="float:right;margin-right:50px;">
        <xsl:apply-templates select="checkins"/>
    </div>
        
</xsl:template>

<xsl:template match="checkins">
    <table width="420" cellspacing="0" cellpadding="3" border="1">
        <thead>
            <tr>
                <th width="200"><xsl:comment/></th>
                <th width="110">Checked-In</th>
                <th>Remaining</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="200">
                    <a id="collapse_checkins" href="#" style="display:none" onclick="jQuery('#checkin_table').hide(); jQuery(this).hide(); jQuery('#expand_checkins').show();"><img src="/wp-content/plugins/booking/img/collapse.gif"/></a>
                    <a id="expand_checkins" href="#" onclick="jQuery('#checkin_table').show(); jQuery(this).hide(); jQuery('#collapse_checkins').show();"><img src="/wp-content/plugins/booking/img/expand.gif"/></a>
                    &#160; Number of Checkins
                </td>
                <td width="110"><xsl:value-of select="@arrived"/></td>
                <td><xsl:value-of select="@remaining"/></td>
            </tr>
        </tbody>
    </table>
    <table id="checkin_table" style="display:none" width="420" cellspacing="0" cellpadding="3" border="1">
        <tbody>
            <xsl:apply-templates select="checkin"/>
        </tbody>
    </table>
</xsl:template>

<xsl:template match="checkin">
    <tr>
        <td width="200">
            <div style="margin-left:{20 * count(ancestor::*) - 20}px">
                <xsl:value-of select="caption"/>
            </div>
        </td>
        <td width="110"><xsl:value-of select="@arrived"/></td>
        <td><xsl:value-of select="@remaining"/></td>
    </tr>
    <!-- recursive -->
    <xsl:apply-templates select="checkin"/>
</xsl:template>

<!-- this template used to display allocation view for under free beds -->
<xsl:template match="allocationview">
    <div id="allocation_view">
        <xsl:apply-templates select="resource"/>
    </div>
</xsl:template>

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
            <br/>
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr valign="top">
                        <td width="180"></td>
                        <td class="availability_header"><xsl:value-of select="/view/allocationview/dateheaders/header"/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2" width="{60 * count(/view/allocationview/dateheaders/datecol)}" valign="top">
                            <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                                <thead>
                                    <tr>
                                        <th class="alloc_resource_attrib"><xsl:value-of select="name"/></th>
                                        <xsl:apply-templates select="/view/allocationview/dateheaders/datecol" mode="availability_date_header"/>
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
            <a class="booking_item {render} status_{status}"><xsl:value-of select="name"/>&#160;</a>
        </xsl:if>
    </td>
</xsl:template>

</xsl:stylesheet>