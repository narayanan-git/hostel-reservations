<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
// View of bookings by room, name...
-->
<xsl:include href="inline_scripts.xsl"/>
<xsl:include href="booking_summary.xsl"/>

<xsl:template match="/bookingview">

    <div id="wpdev-booking-general" class="wrap bookingpage">
        <div class="icon32" style="margin:10px 25px 10px 10px;"><img src="{homeurl}/wp-content/plugins/booking/img/calendar-48x48.png"/><br /></div>
        <h2>Bookings</h2>
        <div class="wpdevbk">
            <div id="ajax_working"><xsl:comment/></div>
            <div class="clear" style="height:1px;"><xsl:comment/></div>
            <div id="ajax_respond"><xsl:comment/></div>

            <!-- define tabs -->
            <div style="height:1px;clear:both;margin-top:30px;"><xsl:comment/></div>
            <div id="menu-wpdevplugin">
                <div class="nav-tabs-wrapper">
                    <div class="nav-tabs">

                        <a title=""  href="#" class="nav-tab nav-tab-active">
                            <img class="menuicons" src="{homeurl}/wp-content/plugins/booking/img/actionservices24x24.png"/>Bookings
                        </a>
                                    
                        <span class="dropdown pull-right">
                            <a href="#" data-toggle="dropdown" class="dropdown-toggle nav-tab ">
                                <img class="menuicons" src="{homeurl}/wp-content/plugins/booking/img/system-help22x22.png"/>Help <span class="caret" style="border-top-color: #333333 !important;"/>
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

                    <div id="bookings" class="visibility_container active">
                        <xsl:call-template name="show_booking_menu_options"/>
                    </div>

                    <div class="visibility_container" id="help" style="display:none;"><xsl:comment/></div>

                </div>
            </div>

            <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
    
            <div class="visibility_container" id="booking_view">
                <xsl:call-template name="bookingview_contents"/>
                <xsl:comment/>
            </div>
        </div>

        <xsl:call-template name="write_inline_js"/>
        <xsl:call-template name="write_inline_css"/>
    </div>

</xsl:template>

<!-- filters for "Bookings" page -->
<xsl:template name="show_booking_menu_options">

    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <div class="wpdevbk-filters-section ">

        <div style="float: right; margin-top: -90px;">
            <form  name="booking_filters_formID" action="" method="post" id="booking_filters_formID" class=" form-search">
                <input class="input-small" type="text" placeholder="Booking ID" name="wh_booking_id" id="wh_booking_id" value=""/>
                <button class="btn small" type="submit">Go</button>
            </form>
        </div>

        <form name="booking_view_form" action="" method="post" id="booking_view_form"  class="form-inline">
            <div class="btn-toolbar" style="margin:0px;">
                <div class="btn-group" style="float:left; margin-right: 15px; vertical-align: top;">
                    <a data-original-title="Search for selected bookings"  rel="tooltip" class="tooltip_top btn btn-primary"
                        onclick="javascript:booking_view_form.submit();">
                        Search <span class="icon-refresh icon-white"></span></a>
                </div>
                    
                <div class="control-group" style="float:left;">
                    <label for="filter_status" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <a href="#" data-toggle="dropdown" id="status_selector" class="btn dropdown-toggle">
                                <xsl:choose>
                                    <xsl:when test="filter/status = 'reserved'">Reserved</xsl:when>
                                    <xsl:when test="filter/status = 'checkedin'">Checked-In</xsl:when>
                                    <xsl:when test="filter/status = 'checkedout'">Checked-Out</xsl:when>
                                    <xsl:when test="filter/status = 'cancelled'">Cancelled</xsl:when>
                                    <xsl:otherwise>All <xml:text> </xml:text></xsl:otherwise>
                                </xsl:choose>
                                <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#" onclick="javascript:jQuery('#status_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_status').val('reserved');" >Reserved</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#status_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_status').val('checkedin');" >Checked-In</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#status_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_status').val('checkedout');" >Checked-Out</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#status_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_status').val('cancelled');" >Cancelled</a></li>
                                <li class="divider"></li>
                                <li><a href="#" onclick="javascript:jQuery('#status_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_status').val('all');" >All</a></li>
                            </ul>
                            <input type="hidden" value="{filter/status}" id="filter_status" name="filter_status" />
                        </div>
                        <p class="help-block" style="margin-top:0px">Booking Status</p>
                    </div>
                </div>
    
                <div class="control-group" style="float:left;">
                    <label for="bookingmindate" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar" 
                                value="{filter/bookingmindate}"  id="bookingmindate"  name="bookingmindate" />
                            <span class="add-on"><span class="icon-calendar"></span></span>
                        </div>
                    <p class="help-block" style="text-align:left;padding-left:15px;">Date (from)</p>
                    </div>
                </div>
        
                <div class="control-group" style="float:left;">
                    <label for="bookingmaxdate" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar" 
                                value="{filter/bookingmaxdate}"  id="bookingmaxdate"  name="bookingmaxdate" />
                            <span class="add-on"><span class="icon-calendar"></span></span>
                        </div>
                    <p class="help-block" style="text-align:left;padding-left:15px;">Date (to)</p>
                    </div>
                </div>
    
                <div class="control-group" style="float:left;">
                    <label for="filter_datetype" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <a href="#" data-original-title="Match from/to dates by:&lt;br&gt; Check-In Date: earliest date of any allocation &lt;br&gt; Check-Out Date: latest date of any allocation &lt;br&gt; Reservation Date (Any): any booking that overlaps the dates given &lt;br&gt; Date Added: date booking was created" rel="tooltip" data-toggle="dropdown" id="datetype_selector" class="btn dropdown-toggle tooltip_top">
                                <xsl:choose>
                                    <xsl:when test="filter/datetype = 'checkout'">Check-Out Date</xsl:when>
                                    <xsl:when test="filter/datetype = 'reserved'">Reservation Date (Any)</xsl:when>
                                    <xsl:when test="filter/datetype = 'creation'">Date Added</xsl:when>
                                    <xsl:otherwise>Check-In Date<xml:text> </xml:text></xsl:otherwise>
                                </xsl:choose>
                                <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#" onclick="javascript:jQuery('#datetype_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_datetype').val('checkin');" >Check-In Date</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#datetype_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_datetype').val('checkout');" >Check-Out Date</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#datetype_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_datetype').val('reserved');" >Reservation Date (Any)</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#datetype_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_datetype').val('creation');" >Date Added</a></li>
                            </ul>
                            <input type="hidden" value="{filter/datetype}" id="filter_datetype" name="filter_datetype" />
                        </div>
                        <p class="help-block" style="margin-top:0px; text-align:center">Match Dates By</p>
                    </div>
                </div>
                
                <div class="control-group" style="float:left;">
                    <label for="filter_name" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <input style="width:140px;" type="text" class="span2span2"  placeholder=""
                                value="{filter/matchname}"  id="filter_name"  name="filter_name" />
                        </div>
                        <p class="help-block" style="text-align:center">Search by Name</p>
                    </div>
                </div>
        
                <div class="btn-group" style="margin-top: 2px; margin-right: 30px; vertical-align: top; float:right;">
                    <a  data-original-title="Print bookings listing"  rel="tooltip"
                        class="tooltip_top btn" onclick="javascript:print_booking_listing();">
                        Print <span class="icon-print"></span></a>
                    <a data-original-title="Export only current page of bookings to CSV format"  rel="tooltip" class="tooltip_top btn" onclick="javascript:export_booking_listing('page');">
                        Export <span class="icon-list"></span></a>
                </div>
                <xsl:comment/>
            </div>
        </form>
    </div>
    <div class="clear" style="height:1px;"><xsl:comment/></div>
    <div id="admin_bk_messages" style="margin:0px;"><xsl:comment/></div>
    <div class="clear" style="height:1px;"><xsl:comment/></div>
</xsl:template>

<!-- this shows the table contents for the BookingView -->
<xsl:template name="bookingview_contents">
    <xsl:if test="booking">
        <div id="listing_visible_bookings">
            <div class="row-fluid booking-listing-header">
                <div class="booking-listing-collumn span1">ID</div>
                <div class="booking-listing-collumn span2">Tags</div>
                <div class="booking-listing-collumn span3">Booking Details</div>
                <div class="booking-listing-collumn span4">Booking Dates</div>
                <div class="booking-listing-collumn span5">Actions</div>
            </div>
            <xsl:apply-templates select="booking"/>
        </div>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>