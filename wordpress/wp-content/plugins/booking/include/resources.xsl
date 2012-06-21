<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="/">

<script language="javascript">
function deleteResource(resourceId) {
    if (bk_are_you_sure('Are you sure you want to delete ' + document.getElementById('resource_name'+resourceId).innerHTML + '?')) {
        document.post_option_resources_action.resource_id_delete.value = resourceId;
        document.post_option_resources_action.submit();
    }
}
</script>

<div style="margin-top:10px;height:1px;clear:both;border-top:1px solid #bbc;"><xsl:comment/></div>
    <div id="ajax_respond"><xsl:comment/></div>
    <div class="clear"><xsl:comment/></div>
    <div id="ajax_working"><xsl:comment/></div>
    <div id="poststuff" class="metabox-holder" style="margin-top:0px;">
        <div style="float:left;">
            <form id="post_option_resources_action" method="post" action="" name="post_option_resources_action">
                <input type="hidden" id="resource_id_delete" name="resource_id_delete"/>
                <table class="resource_table0 booking_table" cellspacing="0" cellpadding="0" style="width:99%;">
                    <thead>
                        <tr>
                            <th style="width:15px;"><input id="resources_items_all" class="resources_items" type="checkbox" name="resources_items_all" onclick="javascript:jQuery('.resources_items').attr('checked', this.checked);"/></th>
                            <th style="width:10px; height:35px; border-left: 1px solid #BBBBBB;">ID</th>
                            <th style="height:35px;">Resource Name</th>
                            <th style="width:50px;">Type</th>
                            <th class="tipcy" title="Max number of occupants" style="width:50px;">Beds</th>
                            <th style="width:10px;" title="Actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="/resources/resource" />
                    </tbody>
                </table>
            </form>
            <div class="clear" style="height:10px;"><xsl:comment/></div>
            <input class="button-primary" type="submit" name="submit_resources" value="Save" style="float:left;"/>
            <div class="clear" style="height:10px;"><xsl:comment/></div>
        </div>
        <div style="width:320px; float:right;">
            <form id="post_option_add_resources" method="post" action="" name="post_option_add_resources">
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
                                            <select id="resource_type_new" name="resource_type_new" style="float:left; width:100%;">
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
</xsl:template>

<xsl:template match="resource">
    <tr>
        <td><input id="resources_items_{id}" class="resources_items" type="checkbox" name="resources_items_{id}"/></td>
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
                <xsl:when test="/resources/editResource = id and level = 1">
                     <input id="resource_name{id}" type="text" name="resource_name{id}" value="{name}" style="width:210px; font-weight:bold;" maxlength="50"/>
                </xsl:when>
                <xsl:when test="/resources/editResource = id and level != 1">
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
                <xsl:when test="/resources/editResource = id">
                    <div style="text-align:center;">
                        <a class="tooltip_bottom" rel="tooltip" data-original-title="Save" onclick="javascript:document.post_option_resources_action.submit();" href="javascript:;">
                            <img style="width:13px; height:13px;" src="/wp-content/plugins/booking/img/accept-24x24.gif" title="Save" alt="Save"/>
                        </a>
                    </div>
                </xsl:when>
                <xsl:otherwise>
                    <a class="tooltip_bottom" rel="tooltip" data-original-title="Edit" onclick="javascript:window.location.href='/wp-admin/admin.php?page=booking/wpdev-booking.phpwpdev-booking-resources&amp;editResourceId={id}';" href="javascript:;">
                        <img style="width:13px; height:13px;" src="/wp-content/plugins/booking/img/edit_type.png" title="Edit" alt="Edit"/>
                    </a>
                    <span style="padding-left: 10px;"></span>
                    <a class="tooltip_bottom" rel="tooltip" data-original-title="Delete" onclick="javascript:deleteResource({id});" href="javascript:;">
                        <img style="width:13px; height:13px;" src="/wp-content/plugins/booking/img/delete_type.png" title="Delete" alt="Delete"/>
                    </a>
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </tr>
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