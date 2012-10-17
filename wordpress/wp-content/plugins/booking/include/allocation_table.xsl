<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="allocations">

<!-- table visible only if we have at least one -->
<xsl:if test="allocation"> 
    <table width="100%" cellspacing="0" cellpadding="0" border="0">
    
        <tbody>
            <tr valign="top">
                <td width="240"></td>
                <td class="availability_header"><xsl:value-of select="dateheaders/header"/></td>
            </tr>
            <tr valign="top">
                <td colspan="2" width="870" valign="top">
                    <table class="availability" width="100%" cellspacing="0" cellpadding="3" border="0">
                        <thead>
                            <tr class="even">
                                <th class="avail_attrib">Name</th>
                                <th class="avail_attrib">Room</th>
                                <th class="avail_attrib">Bed</th>
                                <th class="avail_calendar_chevrons"><a href="javascript:shift_availability_calendar('left');">&lt;&lt;</a></th>
                                <xsl:apply-templates select="dateheaders/datecol" mode="availability_date_header"/>
                                <th class="avail_calendar_chevrons"><a href="javascript:shift_availability_calendar('right');">&gt;&gt;</a></th>
                                <th class="avail_action_icons"><xsl:comment/></th>
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

    <!-- legend -->
    <div class="block_hints datepick">
        <div class="wpdev_hint_with_text">
            <div class="block_free legend_date_status_available">#</div>
            <div class="block_text">- Available</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_booked legend_date_status_reserved">R</div>
            <div class="block_text">- Reserved</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_booked legend_date_status_paid">P</div>
            <div class="block_text">- Paid/Checked-in</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_booked legend_date_status_free">F</div>
            <div class="block_text">- Free Night</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_booked legend_date_status_hours">H</div>
            <div class="block_text">- Paid with Hours</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_booked legend_date_status_cancelled">NS</div>
            <div class="block_text">- No Show/Cancelled</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_booked legend_checkedout"><xsl:comment/></div>
            <div class="block_text">- Checked Out</div>
        </div>
    </div>
    <div class="wpdev_clear_hint"><xsl:comment/></div>
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
            <xsl:choose>
                <xsl:when test="../editingRowId = rowid">
                    <input type="text" id="allocation_name{rowid}" name="allocation_name{rowid}" value="{name}" size="5"/>
                </xsl:when>
                <xsl:otherwise>
                    <span id="allocation_name{rowid}"><xsl:value-of select="name"/></span>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:if test="gender = 'M' or gender = 'F'">
                (<xsl:value-of select="gender"/>)
            </xsl:if>
        </td>
            <xsl:choose>
                <xsl:when test="../editingRowId = rowid">
                    <td colspan="2">
                        <select id="booking_resource{rowid}" name="booking_resource{rowid}" style="width:160px">
                            <xsl:apply-templates select="../resources/resource" mode="resource_selection">
                                <xsl:with-param name="resource_id"><xsl:value-of select="resourceid"/></xsl:with-param>
                            </xsl:apply-templates>
                        </select>
                    </td>
                </xsl:when>
                <xsl:otherwise>
                    <td class="avail_attrib"><xsl:value-of select="parentresource"/></td>
                    <td class="avail_attrib"><xsl:value-of select="resource"/></td>
                </xsl:otherwise>
            </xsl:choose>
        <td class="avail_calendar_chevrons"><xsl:if test="bookingsBeforeMinDate &gt; 0">+<xsl:value-of select="bookingsBeforeMinDate"/></xsl:if></td>
        <xsl:apply-templates select="dates/date" mode="allocation_date"/>
        <td class="avail_calendar_chevrons"><xsl:if test="bookingsAfterMaxDate &gt; 0">+<xsl:value-of select="bookingsAfterMaxDate"/></xsl:if></td>
        <td class="avail_action_icons">
            <xsl:choose>
                <xsl:when test="../editingRowId = rowid">
                    <div style="text-align:center;">
                        <a class="tooltip_bottom" rel="tooltip" data-original-title="Save" onclick="javascript:save_allocation({rowid});" href="javascript:;">
                            <img style="width:13px; height:13px;" src="{../homeurl}/wp-content/plugins/booking/img/accept-24x24.gif" title="Save" alt="Save"/>
                        </a>
                    </div>
                </xsl:when>
                <xsl:otherwise>
                    <!-- only show edit/delete buttons on remainder of rows if we aren't editing a row already -->
                    <xsl:if test="not(../editingRowId)">
                        <a class="tooltip_bottom" rel="tooltip" data-original-title="Edit" onclick="javascript:edit_allocation({rowid});" href="javascript:;">
                            <img style="width:13px; height:13px;" src="{../homeurl}/wp-content/plugins/booking/img/edit_type.png" title="Edit" alt="Edit"/>
                        </a>
                        <span style="padding-left: 10px;"></span>
                        <a class="tooltip_bottom" rel="tooltip" data-original-title="Delete" onclick="javascript:delete_allocation({rowid});" href="javascript:;">
                            <img style="width:13px; height:13px;" src="{../homeurl}/wp-content/plugins/booking/img/delete_type.png" title="Delete" alt="Delete"/>
                        </a>
                    </xsl:if>
                </xsl:otherwise>
            </xsl:choose>
        </td>
    </tr>
</xsl:template>

<!-- adds table entries for each allocation in the availability table -->
<xsl:template mode="allocation_date" match="date">
    <td>
        <xsl:attribute name="class">
            avail_date_attrib 
                <xsl:choose>
                    <!-- only these states can be checkedout -->
                    <xsl:when test="@state = 'hours' or @state = 'free' or @state = 'paid'">
                        date_status_<xsl:if test="@checkedout = 'true'">checkout_</xsl:if><xsl:value-of select="@state"/>
                    </xsl:when>
                    <xsl:otherwise>
                        date_status_<xsl:value-of select="@state"/>
                    </xsl:otherwise>
                </xsl:choose>
        </xsl:attribute>
        <div style="position:relative;">
            <xsl:if test="@checkedoutset">
                <a href="javascript:toggle_checkout_on_booking_date({../../rowid}, '{.}');" class="checkout_link">
                    <xsl:attribute name="title">
                        <xsl:if test="@checkedoutset = 'false'">Checkout</xsl:if>
                        <xsl:if test="@checkedoutset = 'true'">Undo Checkout</xsl:if>
                    </xsl:attribute>
                    <xsl:if test="@checkedoutset = 'false'">
                        <img class="checkout" alt=""/>
                    </xsl:if>
                    <xsl:if test="@checkedoutset = 'true'">
                        <img class="uncheckout" alt=""/>
                    </xsl:if>
                </a>
            </xsl:if>
            <a href="javascript:toggle_booking_date({../../rowid}, '{.}');">
                <xsl:if test="@state = 'available'">
                    <xsl:value-of select="substring-before(., '.')"/>
                </xsl:if>
                <xsl:if test="@state = 'reserved'">R</xsl:if>
                <xsl:if test="@state = 'paid'">P</xsl:if>
                <xsl:if test="@state = 'free'">F</xsl:if>
                <xsl:if test="@state = 'hours'">H</xsl:if>
                <xsl:if test="@state = 'cancelled'">NS</xsl:if>
            </a>
        </div>
    </td>
</xsl:template>

<!-- builds drill-down of resource id, name -->
<xsl:template mode="resource_selection" match="resource">
    <xsl:param name="resource_id"/>
    <option value="{id}">
        <xsl:if test="$resource_id = id">
            <xsl:attribute name="selected">selected</xsl:attribute>
        </xsl:if>
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