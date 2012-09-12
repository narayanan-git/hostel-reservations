<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="allocation_view_resource.xsl"/>
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="/allocationview">

    <div id="wpdev-booking-allocations-general" class="wrap bookingpage">
        <div class="icon32" style="margin:10px 25px 10px 10px;"><img src="/wp-content/plugins/booking/img/calendar-48x48.png"/><br /></div>
        <h2>Allocations</h2>
        <div class="wpdevbk">
            <div id="ajax_working"><xsl:comment/></div>
            <div class="clear" style="height:1px;"><xsl:comment/></div>
            <div id="ajax_respond"><xsl:comment/></div>
    
            <!-- define tabs and help -->
            <div style="height:1px;clear:both;margin-top:30px;"><xsl:comment/></div>
            <div id="menu-wpdevplugin">
                <div class="nav-tabs-wrapper">
                    <div class="nav-tabs">

                        <a title=""  href="#" class="nav-tab nav-tab-active">
                            <img class="menuicons" src="/wp-content/plugins/booking/img/Season-64x64.png"/>Allocations
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
    
            <div class="booking-submenu-tab-container">
                <div class="nav-tabs booking-submenu-tab-insidecontainer">

                    <div id="filter" class="visibility_container active">

                        <xsl:call-template name="show_allocation_view"/>

                        <span id="show_link_advanced_booking_filter" class="tab-bottom tooltip_right" data-original-title="Show Legend"  rel="tooltip"><a href="javascript:void(0)" onclick="javascript:jQuery('.advanced_booking_filter').show();jQuery('#show_link_advanced_booking_filter').hide();jQuery('#hide_link_advanced_booking_filter').show();"><span class="icon-chevron-down"></span></a></span>
                        <span id="hide_link_advanced_booking_filter" style="display:none;" class="tab-bottom tooltip_right" data-original-title="Hide Legend" rel="tooltip" ><a href="javascript:void(0)"  onclick="javascript:jQuery('.advanced_booking_filter').hide(); jQuery('#hide_link_advanced_booking_filter').hide(); jQuery('#show_link_advanced_booking_filter').show();"><span class="icon-chevron-up"></span></a></span>
                    </div>

                    <div class="visibility_container" id="help" style="display:none;"><xsl:comment/></div>

                </div>
            </div>

            <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
    
            <div class="visibility_container" id="allocation_view">
                <xsl:apply-templates select="resource"/>
                <xsl:comment/>
            </div>
        </div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </div>

</xsl:template>

<!-- tabbed view for "Allocations" -->
<xsl:template name="show_allocation_view">
    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <div class="wpdevbk-filters-section ">

        <form  name="allocation_view_form" action="" method="post" id="allocation_view_form"  class="form-inline">
            <a class="btn btn-primary" style="float: left; margin-right: 15px;"
                onclick="javascript:allocation_view_form.submit();">Apply <span class="icon-refresh icon-white"></span>
            </a>

            <div class="control-group" style="float:left;">
                <label for="allocationmindate" class="control-label"><xsl:comment/></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar" 
                            value="{filter/allocationmindate}"  id="allocationmindate"  name="allocationmindate" />
                        <span class="add-on"><span class="icon-calendar"></span></span>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;">Date (from)</p>
                </div>
            </div>
    
            <div class="control-group" style="float:left;">
                <label for="allocationmaxdate" class="control-label"><xsl:comment/></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar" 
                            value="{filter/allocationmaxdate}"  id="allocationmaxdate"  name="allocationmaxdate" />
                        <span class="add-on"><span class="icon-calendar"></span></span>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;">Date (to)</p>
                </div>
            </div>
    
            <div class="btn-group" style="margin-top: 2px; margin-right: 30px; vertical-align: top; float:right;">
                <a  data-original-title="Print bookings listing"  rel="tooltip"
                    class="tooltip_top btn" onclick="javascript:print_booking_listing();">
                    Print <span class="icon-print"></span></a>
                <a data-original-title="Export only current page of bookings to CSV format"  rel="tooltip" class="tooltip_top btn" onclick="javascript:export_booking_listing('page');">
                    Export <span class="icon-list"></span></a>
            </div>
                
            <span style="display:none;" class="advanced_booking_filter">
                <div class="block_hints datepick">
                    <div class="wpdev_hint_with_text">
                        <div class="block_free legend_date_status_available">&#160;</div>
                        <div class="block_text">- Available</div>
                    </div>
                    <div class="wpdev_hint_with_text">
                        <div class="block_booked legend_date_status_reserved">&#160;</div>
                        <div class="block_text">- Reserved</div>
                    </div>
                    <div class="wpdev_hint_with_text">
                        <div class="block_booked legend_date_status_paid">&#160;</div>
                        <div class="block_text">- Paid/Checked-in</div>
                    </div>
                    <div class="wpdev_hint_with_text">
                        <div class="block_booked legend_date_status_free">&#160;</div>
                        <div class="block_text">- Free Night</div>
                    </div>
                    <div class="wpdev_hint_with_text">
                        <div class="block_booked legend_date_status_hours">&#160;</div>
                        <div class="block_text">- Paid with Hours</div>
                    </div>
                    <div class="wpdev_hint_with_text">
                        <div class="block_booked legend_checkedout">&#160;</div>
                        <div class="block_text">- Checked Out</div>
                    </div>
                </div>
            </span>

            <div class="clear"><xsl:comment/></div>
        </form>

    </div>
    <div style="clear:both;height:1px;"><xsl:comment/></div>
</xsl:template>

</xsl:stylesheet>