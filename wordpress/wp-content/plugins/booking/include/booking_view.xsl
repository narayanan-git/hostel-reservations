<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
// View of bookings by room, name...
-->

<!-- tabbed view for "Bookings" -->
<xsl:template name="show_booking_view">

    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <div class="wpdevbk-filters-section ">
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
                                    <xsl:when test="/view/bookingview/filter/status = 'reserved'">Reserved</xsl:when>
                                    <xsl:when test="/view/bookingview/filter/status = 'checkedin'">Checked-In</xsl:when>
                                    <xsl:when test="/view/bookingview/filter/status = 'checkedout'">Checked-Out</xsl:when>
                                    <xsl:when test="/view/bookingview/filter/status = 'cancelled'">Cancelled</xsl:when>
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
                            <input type="hidden" value="{/view/bookingview/filter/status}" id="filter_status" name="filter_status" />
                        </div>
                        <p class="help-block" style="margin-top:0px">Booking Status</p>
                    </div>
                </div>
    
                <div class="control-group" style="float:left;">
                    <label for="bookingmindate" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar" 
                                value="{/view/bookingview/filter/bookingmindate}"  id="bookingmindate"  name="bookingmindate" />
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
                                value="{/view/bookingview/filter/bookingmaxdate}"  id="bookingmaxdate"  name="bookingmaxdate" />
                            <span class="add-on"><span class="icon-calendar"></span></span>
                        </div>
                    <p class="help-block" style="text-align:left;padding-left:15px;">Date (to)</p>
                    </div>
                </div>
    
                <div class="control-group" style="float:left;">
                    <label for="filter_datetype" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <a href="#" data-original-title="Match from/to dates by:&lt;br&gt; Check-In Date: earliest date of any booking &lt;br&gt; Reservation Date (Any): any booking that overlaps the dates given &lt;br&gt; Date Added: date booking was created" rel="tooltip" data-toggle="dropdown" id="datetype_selector" class="btn dropdown-toggle tooltip_top">
                                <xsl:choose>
                                    <xsl:when test="/view/bookingview/filter/datetype = 'reserved'">Reservation Date (Any)</xsl:when>
                                    <xsl:when test="/view/bookingview/filter/datetype = 'creation'">Date Added</xsl:when>
                                    <xsl:otherwise>Check-In Date<xml:text> </xml:text></xsl:otherwise>
                                </xsl:choose>
                                <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#" onclick="javascript:jQuery('#datetype_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_datetype').val('checkin');" >Check-In Date</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#datetype_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_datetype').val('reserved');" >Reservation Date (Any)</a></li>
                                <li><a href="#" onclick="javascript:jQuery('#datetype_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#filter_datetype').val('creation');" >Date Added</a></li>
                            </ul>
                            <input type="hidden" value="{/view/bookingview/filter/datetype}" id="filter_datetype" name="filter_datetype" />
                        </div>
                        <p class="help-block" style="margin-top:0px; text-align:center">Match Dates By</p>
                    </div>
                </div>
                
                <div class="control-group" style="float:left;">
                    <label for="filter_name" class="control-label"><xsl:comment/></label>
                    <div class="inline controls">
                        <div class="btn-group">
                            <input style="width:140px;" type="text" class="span2span2"  placeholder=""
                                value="{/view/bookingview/filter/matchname}"  id="filter_name"  name="filter_name" />
                        </div>
                        <p class="help-block" style="text-align:center">Search by Name</p>
                    </div>
                </div>
        
                <div class="btn-group" style="float:right; margin-top: 2px; margin-right: 30px; vertical-align: top;">
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

<!-- this is the main template for BookingView -->
<xsl:template match="bookingview">
    <xsl:if test="booking">
        <div id="listing_visible_bookings">
            <div class="row-fluid booking-listing-header">
                <div class="booking-listing-collumn span1">ID</div>
                <div class="booking-listing-collumn span2">Labels</div>
                <div class="booking-listing-collumn span4">Booking Details</div>
                <div class="booking-listing-collumn span3">Booking Dates</div>
                <div class="booking-listing-collumn span2">Actions</div>
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
            <div class="field-time"><xsl:value-of select="createdBy"/></div>
        </div>

        <div class="booking-listing-collumn span2 bktextleft booking-labels">
            <xsl:apply-templates select="resources/resource" mode="label_room"/>
            <xsl:apply-templates select="statuses/status" mode="label_status"/>
            <span class="label label-approved">Blank</span>
        </div>

        <div class="booking-listing-collumn span4 bktextjustify">
            <div style="text-align:left">
                <strong>First Name</strong>:<span class="fieldvalue"><xsl:value-of select="firstname"/></span><br/>
                <strong>Last Name</strong>:<span class="fieldvalue"><xsl:value-of select="lastname"/><xsl:comment/></span><br/>
                <strong>Name of Guests</strong>:<xsl:apply-templates select="guests/guest"/><br/>
                <strong>Number of Guests</strong>:<span class="fieldvalue"><xsl:value-of select="count(guests/guest)"/></span><br/>
                <strong>Details</strong>:<span class="fieldvalue"> Please, reserve an appartment with fresh flowers.</span>
            </div>
        </div>

        <div class="booking-listing-collumn span3 bktextleft booking-dates">
            <xsl:for-each select="dates/*">
                <xsl:if test="name() = 'date'">
                    <div class="booking_dates_small"><span class="field-booking-date"><xsl:value-of select="."/></span></div>
                </xsl:if>
                <xsl:if test="name() = 'daterange'">
                    <div class="booking_dates_small "><span class="field-booking-date "><xsl:value-of select="from"/></span><span class="date_tire"> - </span><span class="field-booking-date "><xsl:value-of select="to"/></span></div>
                </xsl:if>
            </xsl:for-each>
        </div>
        
        <div class="booking-listing-collumn span2 bktextcenter booking-actions">
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

<xsl:template match="resource" mode="label_room">
    <span class="label label-resource label-info"><xsl:value-of select="."/></span>
</xsl:template>

<xsl:template match="status" mode="label_status">
    <span class="label label-pending"><xsl:value-of select="."/></span>
</xsl:template>

</xsl:stylesheet>