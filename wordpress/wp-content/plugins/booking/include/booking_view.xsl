<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
// View of bookings by room, name...
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="/bookingview">

    <!-- required for legend... TODO: why do we have admin.css and client.css? -->
    <link href="/wp-content/plugins/booking/css/client.css" rel="stylesheet" type="text/css" />

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

<xsl:template match="booking">
          
    <div id="booking_row_{id}">
        <xsl:attribute name="class">
            row-fluid booking-listing-row clearfix-height
            <xsl:if test="position() mod 2 = 0">row_alternative_color</xsl:if>
        </xsl:attribute>

        <div class="booking-listing-collumn span1 bktextcenter">
            <span class="field-id"><xsl:value-of select="id"/></span><br/><br/>
            <div class="field-date"><xsl:value-of select="createdDate"/></div>
            <div class="field-user"><xsl:value-of select="createdBy"/></div>
        </div>

        <div class="booking-listing-collumn span2 bktextleft booking-labels">
            <xsl:apply-templates select="resources/resource" mode="label_room"/>
            <xsl:apply-templates select="statuses/status" mode="label_status"/>
            <xsl:if test="referrer != ''">
                <br/><span class="label label-referrer"><xsl:value-of select="referrer"/></span>
            </xsl:if>
        </div>

        <div class="booking-listing-collumn span3 bktextjustify">
            <div style="text-align:left">
                <strong>Booking Name</strong>:<span class="fieldvalue"><xsl:value-of select="firstname"/> <xsl:value-of select="lastname"/></span><br/>
                <strong>Name of Guests</strong>:<xsl:apply-templates select="guests/guest"/><br/>
                <strong>Number of Guests</strong>:<span class="fieldvalue"><xsl:value-of select="count(guests/guest)"/></span><br/>
                <xsl:if test="comments/comment">
                    <strong>Comments</strong>:<span class="fieldvalue"><xsl:apply-templates select="comments/comment"/></span>
                </xsl:if>
            </div>
        </div>

        <div class="booking-listing-collumn span4 bktextleft booking-dates">
            <xsl:for-each select="dates/*">
                <xsl:if test="name() = 'date'">
                    <div class="booking_dates_small"><span class="field-booking-date"><xsl:value-of select="."/></span></div>
                </xsl:if>
                <xsl:if test="name() = 'daterange'">
                    <div class="booking_dates_small "><span class="field-booking-date "><xsl:value-of select="from"/></span><span class="date_tire"> - </span><span class="field-booking-date "><xsl:value-of select="to"/></span></div>
                </xsl:if>
            </xsl:for-each>
        </div>
        
        <div class="booking-listing-collumn span5 bktextcenter booking-actions">
            <div class="actions-fields-group">
                <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking-reservation&amp;bookingid={id}" data-original-title="Edit Booking" rel="tooltip" class="tooltip_bottom">
                    <img src="http://personal.wpbookingcalendar.com/wp-content/plugins/booking/img/edit_type.png" style="width:12px; height:13px;"/>
                </a>
            </div>
        </div>
    </div>
</xsl:template>

<xsl:template match="guest">
    <span class="fieldvalue"><xsl:value-of select="."/></span>
</xsl:template>

<xsl:template match="comment">
    <span class="fieldvalue"><xsl:value-of select="value"/></span><br/>
</xsl:template>

<xsl:template match="resource" mode="label_room">
    <span class="label label-info"><xsl:value-of select="."/></span><br/>
</xsl:template>

<xsl:template match="status" mode="label_status">
    <span class="label label-{.}"><xsl:value-of select="."/></span>
</xsl:template>

</xsl:stylesheet>