<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="/view" name="daily_summary_contents">

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

</xsl:stylesheet>