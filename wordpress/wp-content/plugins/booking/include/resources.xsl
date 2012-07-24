<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="resources_table.xsl"/>

<xsl:template match="/view">

<div id="meta-wrapper">
    <div style="margin-top:10px;height:1px;clear:both;border-top:1px solid #bbc;"><xsl:comment/></div>
    <div id="ajax_respond"><xsl:comment/></div>
    <div class="clear"><xsl:comment/></div>
    <div style="width:100%;text-align:left;margin:15px auto;color:red"><xsl:value-of select="errorMessage"/><xsl:comment/></div>
    <div class="metabox-holder" style="margin-top:0px;">
        <div style="float:left;">
            <xsl:apply-templates select="resources" />
            <div class="clear" style="height:10px;"><xsl:comment/></div>
        </div>
        <div style="width:320px; float:right;">
            <form method="post" action="" name="post_option_add_resources">
                <table class="resource_table0 booking_table" cellspacing="0" cellpadding="0" style="width:99%;">
                    <thead>
                        <tr><th style="height:30px;">Add New Resource(s)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td class="alternative_color" style="height:40px;">
                            <table style="width:100%; padding:0px;">
                                <tbody>
                                    <tr>
                                        <td style="padding:0px; height:32px; font-weight:bold;">Name:</td>
                                        <td style="padding:0px;"><input id="resource_name_new" type="text" name="resource_name_new" value="" maxlength="50" style="float:left; width:100%;"/></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="height:1px; padding:0px; border-top: 1px solid #ccc;"></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0px; height:32px;">Parent:</td>
                                        <td style="padding:0px;">
                                            <select id="resource_parent_new" name="resource_parent_new" style="float:left; width:100%;">
                                                <option value="0"> - </option>
                                                <xsl:apply-templates select="//resource" mode="parent_resource_selection"/>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0px; height:32px;">Type:</td>
                                        <td style="padding:0px;">
                                            <select id="resource_type_new" name="resource_type_new" onchange="if(this.value == 'room' || this.value == 'private') jQuery('.capacity_row').hide(); else jQuery('.capacity_row').show(); alert('done '+jQuery('.capacity_row'));" style="float:left; width:100%;">
                                                <option value="bed">Bed</option>
                                                <option value="room">Shared Room</option>
                                                <option value="private">Private Room</option>
                                                <option value="group">Group</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0px; height:32px;">Capacity:</td>
                                        <td style="padding:0px;">
                                            <select id="resource_capacity_new" name="resource_capacity_new" style="float:left; width:50px;">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                                <option value="9">9</option>
                                                <option value="10">10</option>
                                                <option value="11">11</option>
                                                <option value="12">12</option>
                                                <option value="13">13</option>
                                                <option value="14">14</option>
                                                <option value="15">15</option>
                                                <option value="16">16</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td></tr>
                        <tr>
                            <td style="height:35px; border-top:1px solid #ccc;">
                                <input class="button-secondary" type="submit" name="submit_add_resources" value="+ Add new resource(s)" style="float:left;"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="clear" style="height:40px;"><xsl:comment/></div>
            </form>
        </div>
    </div>
</div>

</xsl:template>

<!-- builds drill-down of resource id, name -->
<xsl:template mode="parent_resource_selection" match="resource">
    <option value="{id}">
        <xsl:call-template name ="indent">
            <xsl:with-param name="i">1</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="level"/></xsl:with-param>
        </xsl:call-template>
        <xsl:value-of select ="name"/>
    </option>
</xsl:template>

<!-- adds non-breaking spaces -->
<xsl:template name="indent">
    <xsl:param name="i"/>
    <xsl:param name="value"/>
    <xsl:if test="$i &lt; $value">
        &#160;&#160;&#160;&#160;
        <xsl:call-template name ="indent">
            <xsl:with-param name="i"><xsl:value-of select ="$i+1"/></xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="level"/></xsl:with-param>
        </xsl:call-template>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>