<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
// View of allocations by group, room, beds...
-->
<xsl:template match="/view">

    <!-- define tabs and help -->
    <div style="height:1px;clear:both;margin-top:30px;"></div>
    <div id="menu-wpdevplugin">
        <div class="nav-tabs-wrapper">
            <div class="nav-tabs">

                <a class="nav-tab  nav-tab-active " title=""  href="#" onclick="javascript:jQuery('.visibility_container').hide(); jQuery('#filter').show();jQuery('.nav-tab').removeClass('nav-tab-active');jQuery(this).addClass('nav-tab-active');"><img class="menuicons" src="/wp-content/plugins/booking/img/Season-64x64.png"/>Filter</a>
                <a class="nav-tab " title=""  href="#" onclick="javascript:jQuery('.visibility_container').hide(); jQuery('#actions').show();jQuery('.nav-tab').removeClass('nav-tab-active');jQuery(this).addClass('nav-tab-active');"><img class="menuicons" src="/wp-content/plugins/booking/img/actionservices24x24.png"/>Actions</a>
                                    
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

            <div class="visibility_container active" id="filter" style="display:block;">
                <xsl:call-template name="show_booking_filters"/>

                <span id="show_link_advanced_booking_filter" class="tab-bottom tooltip_right" data-original-title="Expand Advanced Filter"  rel="tooltip"><a href="#" onclick="javascript:jQuery('.advanced_booking_filter').show();jQuery('#show_link_advanced_booking_filter').hide();jQuery('#hide_link_advanced_booking_filter').show();"><span class="icon-chevron-down"></span></a></span>
                <span id="hide_link_advanced_booking_filter" style="display:none;" class="tab-bottom tooltip_right" data-original-title="Collapse Advanced Filter" rel="tooltip" ><a href="#"  onclick="javascript:jQuery('.advanced_booking_filter').hide(); jQuery('#hide_link_advanced_booking_filter').hide(); jQuery('#show_link_advanced_booking_filter').show();"><span class="icon-chevron-up"></span></a></span>
            </div>

            <div class="visibility_container" id="actions"  style="display:none;">
                <xsl:call-template name="show_booking_actions"/>
            </div>

            <div class="visibility_container" id="help" style="display:none;"><xsl:comment/></div>

        </div>
    </div>

    <div class="btn-group" style="position:absolute;right:20px;">
        <input style="vertical-align:bottom;height: 27px;margin-bottom: 13px;" type="checkbox" checked="CHECKED" id="is_send_email_for_pending"
             data-original-title="Send email notification to customer after approvement, unapprovement, deletion of bookings"  rel="tooltip" class="tooltip_top"/>
        <span style="color: #777777;line-height: 36px;text-shadow: 0 1px 0 #FFFFFF;vertical-align: top;" >Emails sending</span>
    </div>

    <div style="height:1px;clear:both;margin-top:40px;"><xsl:comment/></div>
    
    <xsl:apply-templates select="resource"/>

</xsl:template>

<!-- tabbed view for "Filter" -->
<xsl:template name="show_booking_filters">
    <div style="clear:both;height:1px;"><xsl:comment/></div>
    <div class="wpdevbk-filters-section ">

        <div style="  float: right; margin-top: -90px;">
            <form  name="booking_filters_formID" action="" method="post" id="booking_filters_formID" class=" form-search">
                <input class="input-small" type="text" placeholder="Booking ID" name="wh_booking_id" id="wh_booking_id" value=""/>
                <button class="btn small" type="submit">Go</button>
            </form>
        </div>

        <form  name="booking_filters_form" action="" method="post" id="booking_filters_form"  class="form-inline">
            <input type="hidden" name="page_num" id ="page_num" value="1" />
            <a class="btn btn-primary" style="float: left; margin-right: 15px; margin-top: 5px;"
                onclick="javascript:booking_filters_form.submit();">Apply <span class="icon-refresh icon-white"></span>
            </a>

            <div class="control-group" style="float:left;">
                <label for="wh_approved" class="control-label"></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <a href="#" data-toggle="dropdown" id="wh_approved_selector" class="btn dropdown-toggle">All <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#" onclick="javascript:jQuery('#wh_approved_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span>');jQuery('#wh_approved').val('0');" >Pending</a></li>
                            <li><a href="#" onclick="javascript:jQuery('#wh_approved_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span>');jQuery('#wh_approved').val('1');" >Approved</a></li>
                            <li class="divider"></li>
                            <li><a href="#" onclick="javascript:jQuery('#wh_approved_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span>');jQuery('#wh_approved').val('');" >All</a></li>
                        </ul>
                        <input type="hidden" value="" id="wh_approved" name="wh_approved" />
                    </div>
                    <p class="help-block">Booking Status</p>
                </div>
            </div>

            <script type="text/javascript">
    
                function wpdevbk_days_selection_in_filter( primary_field, secondary_field, primary_value, secondary_value ) {
    
                    if (primary_value == '0') {         // Actual  = '', ''
                        jQuery('#' + primary_field   ).val('0');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector').html( 'Actual dates' + ' <span class="caret"></span>');
                    } else if (primary_value == '1') {  // Today
                        jQuery('#' + primary_field   ).val('1');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector').html( 'Today' + ' <span class="caret"></span>');
                    } else if (primary_value == '2') {  // Previous
                        jQuery('#' + primary_field   ).val('2');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector').html( 'Previous dates' + ' <span class="caret"></span>');
                    } else if (primary_value == '3') { // All
                        jQuery('#' + primary_field   ).val('3');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector').html( 'All dates' + ' <span class="caret"></span>');
                    } else if (primary_value == '4') { // Next
                        jQuery('#' + primary_field   ).val('4');
                        jQuery('#' + secondary_field ).val(secondary_value);
                        jQuery('#'+primary_field+'_selector').html( 'Some Next days' + ' <span class="caret"></span>');
                    } else if (primary_value == '5') { // Prior
                        jQuery('#' + primary_field   ).val('5');
                        jQuery('#' + secondary_field ).val(secondary_value);
                        jQuery('#'+primary_field+'_selector').html( 'Some Prior days' + ' <span class="caret"></span>');
                    } else if (primary_value == '6') { // Fixed
                        jQuery('#' + primary_field   ).val(secondary_value[0]);
                        jQuery('#' + secondary_field ).val(secondary_value[1]);
                        jQuery('#'+primary_field+'_selector').html( 'Fixed dates interval' + ' <span class="caret"></span>');
                    }
                    jQuery('#' + primary_field+ '_container').hide();
                }
    
            </script>
    
            <div class="control-group" style="float:left;">
                <label for="wh_booking_date" class="control-label"></label>
                <div class="inline controls">
                    <input type="hidden" value="0"  id="wh_booking_date"  name="wh_booking_date" />
                    <input type="hidden" value="" id="wh_booking_date2" name="wh_booking_date2" />
                    <div class="btn-group">
                        <a onclick="javascript:jQuery('#wh_booking_date_container').show();" id="wh_booking_date_selector" data-toggle="dropdown"  class="btn dropdown-toggle" href="#">Actual dates <span class="caret"></span></a>
                        <ul class="dropdown-menu" style="display:none;" id="wh_booking_date_container" >
                            <li><a onclick="javascript:wpdevbk_days_selection_in_filter( 'wh_booking_date', 'wh_booking_date2', '0' , '' );" href="#">Actual dates</a></li>
                            <li><a onclick="javascript:wpdevbk_days_selection_in_filter( 'wh_booking_date', 'wh_booking_date2', '1' , '' );" href="#">Today</a></li>
                            <li><a onclick="javascript:wpdevbk_days_selection_in_filter( 'wh_booking_date', 'wh_booking_date2', '2' , '' );" href="#">Previous dates</a></li>
                            <li><a onclick="javascript:wpdevbk_days_selection_in_filter( 'wh_booking_date', 'wh_booking_date2', '3' , '' );" href="#">All dates</a></li>
                            <li class="divider"></li>
                            <li><div style="margin-left:15px;"> 
                                    <input type="radio" value="next" id="wh_booking_datedays_interval1" name="wh_booking_datedays_interval_Radios" style="margin:-2px 5px 0px -5px;"/>
                                    <span>Next: </span>
                                    <select class="span1" style="width:85px;" id="wh_booking_datenext" name="wh_booking_datenext">
                                        <option value="1">1 day</option>
                                        <option value="2">2 days</option>
                                        <option value="3">3 days</option>
                                        <option value="4">4 days</option>
                                        <option value="5">5 days</option>
                                        <option value="6">6 days</option>
                                        <option value="7">1 week</option>
                                        <option value="14">2 weeks</option>
                                        <option value="30">1 month</option>
                                        <option value="60">2 months</option>
                                        <option value="90">3 months</option>
                                        <option value="183">6 months</option>
                                        <option value="365">1 Year</option>
                                    </select>
                                </div>
                            </li>
                            <li><div style="margin-left:15px;">
                                    <input type="radio" value="prior" id="wh_booking_datedays_interval2" name="wh_booking_datedays_interval_Radios" style="margin:-2px 5px 0px -5px;"/>
                                    <span>Prior: </span>
                                    <select class="span1" style="width:85px;" id="wh_booking_dateprior" name="wh_booking_dateprior" >
                                        <option value="-1">1 day</option>
                                        <option value="-2">2 days</option>
                                        <option value="-3">3 days</option>
                                        <option value="-4">4 days</option>
                                        <option value="-5">5 days</option>
                                        <option value="-6">6 days</option>
                                        <option value="-7">1 week</option>
                                        <option value="-14">2 weeks</option>
                                        <option value="-30">1 month</option>
                                        <option value="-60">2 months</option>
                                        <option value="-90">3 months</option>
                                        <option value="-183">6 months</option>
                                        <option value="-365">1 Year</option>
                                    </select>
                                </div>
                            </li>
                            <li><input type="radio"  value="fixed" id="wh_booking_datedays_interval3" name="wh_booking_datedays_interval_Radios" style="margin:0 0 0 10px;"/>
                                <div style="margin-left:30px;margin-top:-17px;">
                                    <div>Check in: : </div>
                                    <div class="input-append">
                                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar wpdevbk-filters-section-calendar"  placeholder="2012-02-25"
                                            value=""  id="wh_booking_datefixeddates"  name="wh_booking_datefixeddates" />
                                        <span class="add-on"><span class="icon-calendar"></span></span>
                                    </div>
                                    <div style="margin-top: 10px;">Check out: : </div>
                                    <div class="input-append">
                                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar wpdevbk-filters-section-calendar"  placeholder="2012-02-25"
                                            value=""  id="wh_booking_date2fixeddates"  name="wh_booking_date2fixeddates" />
                                        <span class="add-on"><span class="icon-calendar"></span></span>
                                    </div>
                                </div>
                            </li>
                            <li class="divider"></li>
                            <li style="margin: 0;padding: 0 5px;text-align: right;">
                                <div class="btn-toolbar" style="margin:0px;">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary"
                                            onclick="javascript: var rad_val = jQuery('input:radio[name=wh_booking_datedays_interval_Radios]:checked').val();
                                                if (rad_val == 'next') wpdevbk_days_selection_in_filter( 'wh_booking_date', 'wh_booking_date2', '4' , jQuery('#wh_booking_datenext').val() );
                                                if (rad_val == 'prior') wpdevbk_days_selection_in_filter( 'wh_booking_date', 'wh_booking_date2', '5' , jQuery('#wh_booking_dateprior').val() );
                                                if (rad_val == 'fixed') wpdevbk_days_selection_in_filter( 'wh_booking_date', 'wh_booking_date2', '6' , [ jQuery('#wh_booking_datefixeddates').val(), jQuery('#wh_booking_date2fixeddates').val()  ]  );">Apply</button>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn" onclick="javascript: jQuery('#wh_booking_date_container').hide();">Close</button>
                                    </div>
                                </div>
                            </li>
                        </ul>    
                    </div>
                <p class="help-block">Booking dates</p>
                </div>
            </div>
    
            <div class="control-group" style="float:left;">
                <label for="or_sort" class="control-label"></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <a href="#" data-toggle="dropdown" id="or_sort_selector" class="btn dropdown-toggle">ID <span class="icon-arrow-up "></span> <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="#" onclick="javascript:jQuery('#or_sort_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#or_sort').val('');" >ID <span class="icon-arrow-up "></span></a></li>
                            <li class="divider"></li>
                            <li><a href="#" onclick="javascript:jQuery('#or_sort_selector').html(jQuery(this).html() + ' &lt;span class=&quot;caret&quot;&gt;&lt;/span&gt;');jQuery('#or_sort').val('booking_id_asc');" >ID <span class="icon-arrow-down "></span></a></li>
                        </ul>
                        <input type="hidden" value="" id="or_sort" name="or_sort" />
                    </div>
                <p class="help-block" style="float:left;padding-left:5px">Sort</p>
                </div>
            </div>
    
            <div class="control-group" style="float:left;">
                <label for="wh_modification_datefixeddates" class="control-label"></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar wpdevbk-filters-section-calendar"  placeholder="2012-02-25"
                            value=""  id="wh_modification_datefixeddates"  name="wh_modification_datefixeddates" />
                        <span class="add-on"><span class="icon-calendar"></span></span>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;">Check in (from)</p>
                </div>
            </div>
    
            <div class="control-group" style="float:left;">
                <label for="wh_modification_date2fixeddates" class="control-label"></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <input style="width:100px;" type="text" class="span2span2 wpdevbk-filters-section-calendar wpdevbk-filters-section-calendar"  placeholder="2012-02-25"
                            value=""  id="wh_modification_date2fixeddates"  name="wh_modification_date2fixeddates" />
                        <span class="add-on"><span class="icon-calendar"></span></span>
                    </div>
                <p class="help-block" style="float:left;padding-left:5px;">Check in (to)</p>
                </div>
            </div>
    
            <div class="control-group" style="float:left;">
                <label for="search_name" class="control-label"></label>
                <div class="inline controls">
                    <div class="btn-group">
                        <input style="width:100px;" type="text" class="span2span2"  placeholder=""
                            value=""  id="search_name"  name="search_name" />
                    </div>
                    <p class="help-block">Search by Name</p>
                </div>
            </div>
    
            <div class="clear"><xsl:comment/></div>
        </form>

    </div>
    <div style="clear:both;height:1px;"><xsl:comment/></div>
</xsl:template>


<!-- tabbed view for "Actions" -->
<xsl:template name="show_booking_actions">

    <div class="btn-toolbar" style="margin:0px;">
        <div class="btn-group" style="margin-top: 2px; vertical-align: top;">
            <a data-original-title="Approve selected bookings"  rel="tooltip" class="tooltip_top btn btn-primary"
                onclick="javascript: approve_unapprove_booking( get_selected_bookings_id_in_booking_listing() , 1, 2, 'en_US' , 1);">
                Approve <span class="icon-ok icon-white"></span></a>
            <a data-original-title="Set selected bookings as pening"  rel="tooltip" class="tooltip_top btn"
                onclick="javascript: if ( bk_are_you_sure('Are you really want to set booking as pending ?') ) approve_unapprove_booking( get_selected_bookings_id_in_booking_listing() , 0, 2, 'en_US' , 1);">
                Unapprove <span class="icon-ban-circle"></span></a>
        </div>
            
        <div class="btn-group" style="margin-top: 2px; vertical-align: top;">
            <a data-original-title="Delete selected bookings"  rel="tooltip" class="tooltip_top btn btn-danger"
                onclick="javascript: if ( bk_are_you_sure('Are you really want to delete selected booking(s) ?') ) delete_booking( get_selected_bookings_id_in_booking_listing() , 2, 'en_US' , 1  );">
                Delete <span class="icon-trash icon-white"></span></a>
            <input style="border-bottom-left-radius: 0; border-top-left-radius: 0; height: 28px; "
                   type="text" placeholder="Reason of cancellation here"
                   class="span3" value="" id="denyreason" name="denyreason" />
        </div>

        <div class="btn-group" style="margin-top: 2px; vertical-align: top;">
            <a data-original-title="Mark as read selected bookings"  rel="tooltip" class="tooltip_top btn btn"
                onclick="javascript: mark_read_booking( get_selected_bookings_id_in_booking_listing() , 0, 2, 'en_US' );">
                Read <span class="icon-eye-close"></span></a>
            <a data-original-title="Mark as Unread selected bookings"  rel="tooltip" class="tooltip_top btn"
                onclick="javascript: mark_read_booking( get_selected_bookings_id_in_booking_listing() , 1, 2, 'en_US' );">
                Unread <span class="icon-eye-open"></span></a>
        </div>

        <div class="btn-group" style="margin-top: 2px; vertical-align: top;">
            <a  data-original-title="Print bookings listing"  rel="tooltip"
                class="tooltip_top btn" onclick="javascript:print_booking_listing();">
                Print <span class="icon-print"></span></a>
            <a data-original-title="Export only current page of bookings to CSV format"  rel="tooltip" class="tooltip_top btn" onclick="javascript:export_booking_listing('page');">
                Export <span class="icon-list"></span></a>
            <a data-original-title="Export All bookings to CSV format"  rel="tooltip"
               class="tooltip_top btn" onclick="javascript:export_booking_listing('all');">
                Export All <span class="icon-list"></span></a>
        </div>
        <xsl:comment/>
    </div>
    <div class="clear" style="height:1px;"><xsl:comment/></div>
    <div id="admin_bk_messages" style="margin:0px;"><xsl:comment/></div>
    <div class="clear" style="height:1px;"><xsl:comment/></div>
</xsl:template>

<xsl:template match="resource">
    <xsl:if test="level = 1">
        <br/>
    </xsl:if>
    <xsl:if test="type = 'group'">
        <div class="allocation_view_resource_title" style="padding-left: {15*level}px;"><xsl:value-of select="name"/></div>
    </xsl:if>

    <xsl:choose>
        <!-- if we are one level up from a leaf (room), then we generate a single table containing all children (beds) -->
        <xsl:when test="resource/cells/allocationcell">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr valign="top">
                        <td width="180"></td>
                        <td class="availability_header"><xsl:value-of select="/view/dateheaders/header"/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2" width="{60 * count(/view/dateheaders/datecol)}" valign="top">
                            <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                                <thead>
                                    <tr>
                                        <th class="alloc_resource_attrib"><xsl:value-of select="name"/></th>
                                        <xsl:apply-templates select="/view/dateheaders/datecol" mode="availability_date_header"/>
                                    </tr>
                                </thead>
                                <tbody>
                                    <xsl:apply-templates select="resource/cells"/>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </xsl:when>
        <xsl:otherwise>
            <!-- recurse if required -->
            <xsl:apply-templates select="resource"/>
        </xsl:otherwise>
    </xsl:choose>
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
    <th class="alloc_view_date"><xsl:value-of select="date"/><span><xsl:value-of select="day"/></span></th>
</xsl:template>

<!-- adds row for each resource in the availability table -->
<xsl:template match="cells">
    <tr>
        <td><xsl:value-of select="../name"/></td>
        <xsl:apply-templates select="allocationcell"/>
    </tr>
</xsl:template>

<!-- adds table entries for each allocation cell in the availability table -->
<xsl:template match="allocationcell">
    <td>
        <xsl:if test="@span &gt; 1">
            <xsl:attribute name="colspan"><xsl:value-of select="@span"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="id &gt; 0">
            <a class="booking_item status_{status}"><xsl:value-of select="name"/></a>
        </xsl:if>
    </td>
</xsl:template>

</xsl:stylesheet>