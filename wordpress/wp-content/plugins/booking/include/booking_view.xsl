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
        
                <div class="btn-group" style="margin-top: 2px; vertical-align: top;">
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
    <div id="listing_visible_bookings">
        <div class="row-fluid booking-listing-header">
            <div class="booking-listing-collumn span1">ID</div>
            <div class="booking-listing-collumn span2">Labels</div>
            <div class="booking-listing-collumn span4">Booking Details</div>
            <div class="booking-listing-collumn span3">Booking Dates</div>
            <div class="booking-listing-collumn span2">Actions</div>
            <div id="booking_row_1" class="row-fluid booking-listing-row clearfix-height">
    
                <div class="booking-listing-collumn span1 bktextcenter">
                    <span class="field-id">1</span>
                    <div class="field-date"> 07 / 10 / 2012, Tue</div>
                    <span class="field-time">03:03 am</span>
                </div>
    
                <div class="booking-listing-collumn span2 bktextleft booking-labels">
                    <span class="label label-resource label-info">Default</span>
                    <span class="label label-pending  ">Pending</span>
                    <span class="label label-approved  hidden_items ">Approved</span>
                </div>
    
                <div class="booking-listing-collumn span4 bktextjustify">
                    <div style="text-align:left">
                        <strong>First Name</strong>:<span class="fieldvalue">Victoria</span>
                        <strong>Last Name</strong>:<span class="fieldvalue">Smith</span>
                        <strong>Email</strong>:<span class="fieldvalue">victoria@wpdevelop.com</span>
                        <strong>Phone</strong>:<span class="fieldvalue">(044)458-77-88</span>
                        <strong>Number of visitors</strong>:<span class="fieldvalue"> 2</span>
                        <strong>Children</strong>:<span class="fieldvalue"> no</span>
                        <strong>Details</strong>:<span class="fieldvalue"> Please, reserve an appartment with fresh flowers.</span>
                    </div>
                </div>
    
                <div class="booking-listing-collumn span3 bktextleft booking-dates">
                    <div class="booking_dates_small "><span class="field-booking-date ">July 16, 2012</span><span class="date_tire"> - </span><span class="field-booking-date ">July 18, 2012</span></div>
                </div>
                
                <div class="booking-listing-collumn span2 bktextcenter  booking-actions">
                    <div class="actions-fields-group">
                        <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking-reservation&amp;booking_type=1&amp;booking_hash=7ca1fcf39e7e50620d7c397eee5cc9a3&amp;parent_res=1" onclick="" data-original-title="Edit Booking" rel="tooltip" class="tooltip_bottom">
                            <img src="http://personal.wpbookingcalendar.com/wp-content/plugins/booking/img/edit_type.png" style="width:12px; height:13px;"/>
                        </a>
                        <a href="javascript:;" data-original-title="Here can be some note about this booking..." rel="tooltip" class="remark_bk_link tooltip_top" onclick='javascript: if (document.getElementById("remark_row1").style.display=="block") document.getElementById("remark_row1").style.display="none"; else document.getElementById("remark_row1").style.display="block"; '>
                            <img src="http://personal.wpbookingcalendar.com/wp-content/plugins/booking/img/notes_rd.png" style="width:16px; height:16px;"/>
                        </a>
                        <a href="javascript:;" data-original-title="Change Resource" rel="tooltip" class="tooltip_bottom" onclick='javascript:
                            document.getElementById("new_booking_resource_booking_id").value = "1";
                            setSelectBoxByValue("new_booking_resource", 1 );
                            var cbr;
                            cbr = jQuery("#change_booking_resource_controll_elements").detach();
                            cbr.appendTo(jQuery("#changing_bk_res_in_booking1"));
                            cbr = null;
                            jQuery(".booking_row_modification_element_changing_resource").hide();
                            jQuery("#changing_bk_res_in_booking1").show();
                    '>
                            <img src="http://personal.wpbookingcalendar.com/wp-content/plugins/booking/img/exchange.png" style="width:16px; height:16px;"/>
                        </a>
                
                        <a href="javascript:;" class="tooltip_bottom approve_bk_link   " onclick="javascript:approve_unapprove_booking(1,1, 2, 'en_US' , 1  );" data-original-title="Approve" rel="tooltip">
                            <img src="http://personal.wpbookingcalendar.com/wp-content/plugins/booking/img/accept-24x24.gif" style="width:14px; height:14px;"/>
                        </a>
                            
                        <a href="javascript:;" class="tooltip_bottom pending_bk_link   hidden_items  " onclick="javascript:if ( bk_are_you_sure('Are you really want to set booking as pending ?') ) approve_unapprove_booking(1,0, 2, 'en_US' , 1  );" data-original-title="Unapprove" rel="tooltip">
                            <img src="http://personal.wpbookingcalendar.com/wp-content/plugins/booking/img/remove-16x16.png" style="width:15px; height:15px;"/>
                        </a>
                            
                        <a href="javascript:;" onclick="javascript:if ( bk_are_you_sure('Are you really want to delete this booking ?') ) delete_booking(1, 2, 'en_US' , 1   );" data-original-title="Delete" rel="tooltip" class="tooltip_bottom">
                            <img src="http://personal.wpbookingcalendar.com/wp-content/plugins/booking/img/delete_type.png" style="width:13px; height:13px;"/>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</xsl:template>

</xsl:stylesheet>