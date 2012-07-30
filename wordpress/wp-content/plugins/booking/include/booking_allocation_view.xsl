<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
// View of tabbed screen with allocations and bookings
-->
<xsl:include href="allocation_view.xsl"/>
<xsl:include href="booking_view.xsl"/>

<xsl:template match="/view">

    <!-- required for legend... TODO: why do we have admin.css and client.css? -->
    <link href="/wp-content/plugins/booking/css/client.css" rel="stylesheet" type="text/css" />

    <!-- define tabs and help -->
    <div style="height:1px;clear:both;margin-top:30px;"><xsl:comment/></div>
    <div id="menu-wpdevplugin">
        <div class="nav-tabs-wrapper">
            <div class="nav-tabs">

                <a title=""  href="#" onclick="javascript:jQuery('.visibility_container').hide(); jQuery('#filter').show();jQuery('#allocation_view').show();jQuery('.nav-tab').removeClass('nav-tab-active');jQuery(this).addClass('nav-tab-active');">
                    <xsl:attribute name="class">
                        nav-tab
                        <xsl:if test="active = 'allocation'">nav-tab-active</xsl:if>
                    </xsl:attribute>
                    <img class="menuicons" src="/wp-content/plugins/booking/img/Season-64x64.png"/>Allocations
                </a>
                <a title=""  href="#" onclick="javascript:jQuery('.visibility_container').hide(); jQuery('#bookings').show();jQuery('#booking_view').show();jQuery('.nav-tab').removeClass('nav-tab-active');jQuery(this).addClass('nav-tab-active');">
                    <xsl:attribute name="class">
                        nav-tab
                        <xsl:if test="active = 'booking'">nav-tab-active</xsl:if>
                    </xsl:attribute>
                    <img class="menuicons" src="/wp-content/plugins/booking/img/actionservices24x24.png"/>Bookings
                </a>
                                    
                <span class="dropdown pull-right">
                    <a href="#" data-toggle="dropdown" class="dropdown-toggle nav-tab ">
                        <img class="menuicons" src="/wp-content/plugins/booking/img/system-help22x22.png"/>Help <span class="caret" style="border-top-color: #333333 !important;"/>
                    </a>
                    <ul class="dropdown-menu" id="menu1" style="right:0px; left:auto;">
                        <li><a href="/help/" target="_blank">Help</a></li>
                        <li><a href="/faq/" target="_blank">FAQ</a></li>
                        <li><a href="/support/" target="_blank">Technical Support</a></li>
                    </ul>
                </span>

            </div>
        </div>
    </div>
    
    <div class="booking-submenu-tab-container" style="">
        <div class="nav-tabs booking-submenu-tab-insidecontainer">

            <div id="filter">
                <xsl:attribute name="class">
                    visibility_container
                    <xsl:if test="active = 'allocation'">active</xsl:if>
                </xsl:attribute>
                <xsl:attribute name="style">
                    <xsl:choose>
                        <xsl:when test="active = 'allocation'">display:block;</xsl:when>
                        <xsl:otherwise>display:none;</xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>

                <xsl:call-template name="show_allocation_view"/>

                <span id="show_link_advanced_booking_filter" class="tab-bottom tooltip_right" data-original-title="Show Legend"  rel="tooltip"><a href="#" onclick="javascript:jQuery('.advanced_booking_filter').show();jQuery('#show_link_advanced_booking_filter').hide();jQuery('#hide_link_advanced_booking_filter').show();"><span class="icon-chevron-down"></span></a></span>
                <span id="hide_link_advanced_booking_filter" style="display:none;" class="tab-bottom tooltip_right" data-original-title="Hide Legend" rel="tooltip" ><a href="#"  onclick="javascript:jQuery('.advanced_booking_filter').hide(); jQuery('#hide_link_advanced_booking_filter').hide(); jQuery('#show_link_advanced_booking_filter').show();"><span class="icon-chevron-up"></span></a></span>
            </div>

            <div id="bookings">
                <xsl:attribute name="class">
                    visibility_container
                    <xsl:if test="active = 'booking'">active</xsl:if>
                </xsl:attribute>
                <xsl:attribute name="style">
                    <xsl:choose>
                        <xsl:when test="active = 'booking'">display:block;</xsl:when>
                        <xsl:otherwise>display:none;</xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
                <xsl:call-template name="show_booking_view"/>
            </div>

            <div class="visibility_container" id="help" style="display:none;"><xsl:comment/></div>

        </div>
    </div>

    <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
    
    <div class="visibility_container" id="allocation_view">
        <xsl:attribute name="style">
            <xsl:choose>
                <xsl:when test="active = 'allocation'">display:block;</xsl:when>
                <xsl:otherwise>display:none;</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
    
        <xsl:apply-templates select="allocationview/resource"/>
    </div>
    <div class="visibility_container" id="booking_view">
        <xsl:attribute name="style">
            <xsl:choose>
                <xsl:when test="active = 'booking'">display:block;</xsl:when>
                <xsl:otherwise>display:none;</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:apply-templates select="bookingview"/>
    </div>

    <xsl:call-template name="write_inline_js"/>
    <xsl:call-template name="write_inline_css"/>

</xsl:template>

</xsl:stylesheet>