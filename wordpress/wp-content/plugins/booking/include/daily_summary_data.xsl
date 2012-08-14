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
    <xsl:apply-templates select="allocationview" mode="fb"/>
    <xsl:apply-templates select="allocationview" mode="pastdue"/>
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

<!-- ====================================================================== -->
<!-- this template used to display allocation view for unpaid/past due beds -->
<xsl:template match="allocationview" mode="pastdue">
    <xsl:if test=".//resource/unpaid = 'true'">
        <br/>
        <div id="pastdue_view">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr valign="top">
                        <td class="availability_header" width="360">Unpaid / Past Due</td>
                        <td class="availability_header"><xsl:value-of select="/view/allocationview/dateheaders/header"/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2" width="{60 * count(/view/allocationview/dateheaders/datecol)}" valign="top">
                            <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                                <thead>
                                    <tr>
                                        <th class="border_left border_right">Room</th>
                                        <th class="border_left border_right">Bed</th>
                                        <xsl:apply-templates select="/view/allocationview/dateheaders/datecol" mode="availability_date_header"/>
                                    </tr>
                                </thead>
                                <tbody>
                                    <xsl:apply-templates select="resource" mode="pastdue"/>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </xsl:if>
</xsl:template>

<!-- generates a row in the past due availability table -->
<xsl:template match="resource" mode="pastdue">
    <xsl:if test="unpaid = 'true'">
        <xsl:apply-templates select="cells" mode="pastdue"/>
    </xsl:if>

    <!-- recurse if required -->
    <xsl:apply-templates select="resource" mode="pastdue"/>
</xsl:template>

<!-- adds row for each resource past due in the availability table -->
<xsl:template match="cells" mode="pastdue">
    <xsl:if test="allocationcell">
        <tr>
            <xsl:attribute name="class">
                <xsl:choose>
                    <!-- the idea is you can find the position of the parent node by you counting all its preceding siblings. -->
                    <xsl:when test="count(parent::resource/preceding-sibling::resource) mod 2">even</xsl:when>
                    <xsl:otherwise>odd</xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>

            <td class="border_left border_right border_bottom">
                <span style="margin-left:15px">
                    <xsl:value-of select="../../name"/>
                </span>
            </td>
            <td class="border_left border_right border_bottom">
                <span style="margin-left:15px">
                    <xsl:value-of select="../name"/>
                </span>
            </td>
            <xsl:apply-templates select="allocationcell"/>
        </tr>
    </xsl:if>
</xsl:template>
<!-- ====================================================================== -->


<!-- this template used to display allocation view for under free beds -->
<xsl:template match="allocationview" mode="fb">
    <br/>
    <div id="allocation_view">
        <table width="100%" cellspacing="0" cellpadding="0" border="0">
            <tbody>
                <tr valign="top">
                    <td class="availability_header" width="360">Free Beds</td>
                    <td class="availability_header"><xsl:value-of select="/view/allocationview/dateheaders/header"/></td>
                </tr>
                <tr valign="top">
                    <td colspan="2" width="{60 * count(/view/allocationview/dateheaders/datecol)}" valign="top">
                        <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                            <thead>
                                <tr>
                                    <th class="border_left border_right"><xsl:comment/></th>
                                    <xsl:apply-templates select="/view/allocationview/dateheaders/datecol" mode="availability_date_header"/>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:apply-templates select="resource" mode="fb"/>
                                <tr>
                                    <td class="border_left border_right border_bottom">
                                        <span style="margin-left:25px;font-weight:bold">Totals</span>
                                    </td>
                                    <xsl:apply-templates select="totals/freebeds/freebed"/>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</xsl:template>

<!-- generates a row in the free bed availability table -->
<xsl:template match="resource" mode="fb">
    <xsl:apply-templates select="freebeds"/>
    <xsl:apply-templates select="cells" mode="fb"/>
    
    <!-- recurse if required -->
    <xsl:apply-templates select="resource" mode="fb"/>
</xsl:template>

<xsl:template match="freebeds">
    <xsl:if test="freebed">
        <tr id="resource{translate(../path, '/', '_')}">
            <xsl:attribute name="style">
                <!-- hide table row by default unless we're at the top level -->
                <xsl:if test="../level &gt; 1">
                    display:none
                </xsl:if>
            </xsl:attribute>
            <td class="border_left border_right border_bottom">
                <span style="margin-left:{15 * ../level - 10}px">
                    <a id="expand{translate(../path, '/', '_')}" href="javascript:void()" style="text-decoration:none">
                        <!-- when expanding, only expand the next level down -->
                        <xsl:attribute name="onclick">
                            jQuery('tr').filter(function() { 
                                return this.id.match(/resource<xsl:value-of select="translate(../path, '/', '_')"/>_[0-9]+$/); 
                            }).show();
                            jQuery(this).hide();
                            jQuery(this).next().show();
                        </xsl:attribute>
                        <img src="/wp-content/plugins/booking/img/expand.gif"/>
                    </a>
                    <a id="collapse{translate(../path, '/', '_')}" href="javascript:void()" style="display:none; text-decoration:none">
                        <!-- when collapsing, collapse all children -->
                        <xsl:attribute name="onclick">
                            var res_path = "<xsl:value-of select="translate(../path, '/', '_')"/>";
                            jQuery('tr').filter(function() { 
                                return this.id.match(new RegExp('resource' + res_path + '_.*', 'g')); 
                            }).hide();
                            /* reset to expand icons for all children */
                            jQuery('a').filter(function() {
                                return this.id == 'collapse' + res_path || this.id.match(new RegExp('collapse' + res_path + '_.*', 'g')); 
                            }).hide();
                            jQuery('a').filter(function() {
                                return this.id == 'expand' + res_path || this.id.match(new RegExp('expand' + res_path + '_.*', 'g')); 
                            }).show();
                        </xsl:attribute>
                        <img src="/wp-content/plugins/booking/img/collapse.gif"/>
                    </a>
                    &#160;<strong><xsl:value-of select="../name"/></strong>
                </span>
            </td>
            <xsl:apply-templates select="freebed"/>
        </tr>
    </xsl:if>
</xsl:template>

<xsl:template match="freebed">
    <td class="border_left border_right border_bottom" style="text-align:center;font-weight:bold"><xsl:value-of select="."/></td>
</xsl:template>

<!-- adds header entries for the availability table -->
<xsl:template mode="availability_date_header" match="datecol">
    <th class="alloc_view_date"><xsl:value-of select="date"/><span><xsl:value-of select="day"/></span></th>
</xsl:template>

<!-- adds row for each resource in the availability table -->
<xsl:template match="cells" mode="fb">
    <xsl:if test="allocationcell">
        <tr id="resource{translate(../path, '/', '_')}">
            <xsl:attribute name="style">
                <!-- hide table row by default unless we're at the top level -->
                <xsl:if test="../level &gt; 1">
                    display:none
                </xsl:if>
            </xsl:attribute>
            <xsl:attribute name="class">
                <xsl:choose>
                    <!-- the idea is you can find the position of the parent node by you counting all its preceding siblings. -->
                    <xsl:when test="count(parent::resource/preceding-sibling::resource) mod 2">even</xsl:when>
                    <xsl:otherwise>odd</xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>

            <td>
                <span style="margin-left:{15 * ../level}px">
                    <xsl:attribute name="class">
                        border_left border_right
                        <!-- check if this is the last resource in the group by counting the number of resource siblings after this one -->
                        <xsl:if test="count(parent::resource/following-sibling::resource) = 0">
                            border_bottom
                        </xsl:if>
                    </xsl:attribute>
                    <xsl:value-of select="../name"/>
                </span>
            </td>
            <xsl:apply-templates select="allocationcell"/>
        </tr>
    </xsl:if>
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