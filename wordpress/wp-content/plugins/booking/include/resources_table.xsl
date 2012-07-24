<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="resources">
    <table id="resources_table" class="resource_table0 booking_table" cellspacing="0" cellpadding="0" style="width:99%;">
        <thead>
            <tr>
                <th style="width:10px; height:35px;">ID</th>
                <th style="height:35px;">Resource Name</th>
                <th style="width:50px;">Type</th>
                <th class="tipcy" title="Max number of occupants" style="width:50px;">Beds</th>
                <th style="width:10px;" title="Actions">Actions</th>
            </tr>
        </thead>
        <tbody>
            <xsl:apply-templates select="resource" mode="resource_table_contents" />
        </tbody>
    </table>
</xsl:template>

<xsl:template mode="resource_table_contents" match="resource">
    <tr>
        <td style="font-size:10px; font-weight: bold; border-right: 0px solid #ddd; border-left: 1px solid #aaa; text-align: center;"><xsl:value-of select="id"/></td>
        
        <td>
            <xsl:attribute name="style">
                <xsl:choose>
                    <!-- if this is a parent resource, make it bold -->
                    <xsl:when test="level = 1">
                        font-size: 11px; font-weight:bold; width:210px;
                    </xsl:when>
                    <!-- if this *belongs* to another resource, left pad it and not bold -->
                    <xsl:otherwise>
                        font-size: 11px; padding-left: <xsl:value-of select="15*level"/>px;
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>

            <xsl:choose>
                <xsl:when test="../editResource = id and level = 1">
                     <input id="resource_name{id}" type="text" name="resource_name{id}" value="{name}" style="width:210px; font-weight:bold;" maxlength="50"/>
                </xsl:when>
                <xsl:when test="../editResource = id and level != 1">
                    <input id="resource_name{id}" type="text" name="resource_name{id}" value="{name}" style="width:170px; font-size:11px;" maxlength="50"/>
                </xsl:when>
                <xsl:otherwise>
                    <div id="resource_name{id}"><xsl:value-of select="name"/></div>
                </xsl:otherwise>
            </xsl:choose>
        </td>
        
        <td style="font-size:10px; font-weight: bold; text-align: left; padding-left: 5px;"><xsl:value-of select="type"/></td>

        <xsl:choose>
            <!-- if this is a bed, don't show capacity -->
            <xsl:when test="type = 'bed'">
                <td><!-- blank --></td>
            </xsl:when>
            <!-- type is room or group, show the total number of beds -->
            <xsl:otherwise>
                <td style="text-align:center;"><xsl:value-of select="numberChildren"/></td>
            </xsl:otherwise>
        </xsl:choose>
        
        <td>
            <xsl:choose>
                <xsl:when test="../editResource = id">
                    <div style="text-align:center;">
                        <a class="tooltip_bottom" rel="tooltip" data-original-title="Save" onclick="javascript:save_resource({id});" href="javascript:;">
                            <img style="width:13px; height:13px;" src="/wp-content/plugins/booking/img/accept-24x24.gif" title="Save" alt="Save"/>
                        </a>
                    </div>
                </xsl:when>
                <xsl:otherwise>
                    <a class="tooltip_bottom" rel="tooltip" data-original-title="Edit" onclick="javascript:edit_resource({id});" href="javascript:;">
                        <img style="width:13px; height:13px;" src="/wp-content/plugins/booking/img/edit_type.png" title="Edit" alt="Edit"/>
                    </a>
                    <span style="padding-left: 10px;"></span>
                    <a class="tooltip_bottom" rel="tooltip" data-original-title="Delete" onclick="javascript:delete_resource({id});" href="javascript:;">
                        <img style="width:13px; height:13px;" src="/wp-content/plugins/booking/img/delete_type.png" title="Delete" alt="Delete"/>
                    </a>
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </tr>
</xsl:template>

</xsl:stylesheet>