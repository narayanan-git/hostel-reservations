<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

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
                            value="{/view/allocationview/filter/allocationmindate}"  id="allocationmindate"  name="allocationmindate" />
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
                            value="{/view/allocationview/filter/allocationmaxdate}"  id="allocationmaxdate"  name="allocationmaxdate" />
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
                </div>
            </span>

            <div class="clear"><xsl:comment/></div>
        </form>

    </div>
    <div style="clear:both;height:1px;"><xsl:comment/></div>
</xsl:template>

<!-- this template is only used for AllocationView -->
<xsl:template match="resource">
    <xsl:if test="level = 1"> <!-- add extra space at root level -->
        <br/>
    </xsl:if>
    <xsl:if test="type = 'group'">
        <div class="allocation_view_resource_title" style="padding-left: {-15+15*level}px;"><xsl:value-of select="name"/></div>
    </xsl:if>

    <xsl:choose>
        <!-- if we are one level up from a leaf (room), then we generate a single table containing all children (beds) -->
        <xsl:when test="resource/cells/allocationcell">
            <br/>
            <div id="table_resource_{id}">
            <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr valign="top">
                        <td width="180"></td>
                        <td class="availability_header"><xsl:value-of select="/view/allocationview/dateheaders/header"/></td>
                    </tr>
                    <tr valign="top">
                        <td colspan="2" width="{60 * count(/view/allocationview/dateheaders/datecol)}" valign="top">
                            <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
                                <thead>
                                    <tr>
                                        <th class="alloc_resource_attrib"><xsl:value-of select="name"/></th>
                                        <xsl:apply-templates select="/view/allocationview/dateheaders/datecol" mode="availability_date_header"/>
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
            </div>
        </xsl:when>
        <xsl:otherwise>
            <!-- recurse if required -->
            <xsl:apply-templates select="resource"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- adds header entries for the availability table -->
<xsl:template mode="availability_date_header" match="datecol">
    <th class="alloc_view_date"><xsl:value-of select="date"/><span><xsl:value-of select="day"/></span></th>
</xsl:template>

<!-- adds row for each resource in the availability table -->
<xsl:template match="cells">
    <tr>
        <xsl:attribute name="class">
            <xsl:choose>
                <xsl:when test="position() mod 2">odd</xsl:when>
                <xsl:otherwise>even</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <td>
            <xsl:attribute name="class">
                border_left border_right
                <xsl:if test="position() = last()">
                    border_bottom
                </xsl:if>
            </xsl:attribute>
            <xsl:value-of select="../name"/>
        </td>
        <xsl:apply-templates select="allocationcell"/>
    </tr>
</xsl:template>

<!-- adds table entries for each allocation cell in the availability table -->
<xsl:template match="allocationcell">
    <td>
        <xsl:attribute name="class">
            <xsl:if test="count(../../../resource) = count(../../preceding-sibling::resource)+1">
                border_bottom
            </xsl:if>
            <xsl:if test="position() = last()">
                border_right
            </xsl:if>
        </xsl:attribute>
        <xsl:if test="@span &gt; 1">
            <xsl:attribute name="colspan"><xsl:value-of select="@span"/></xsl:attribute>
        </xsl:if>
        <xsl:if test="id &gt; 0">
            <a>
                <xsl:attribute name="class">
                    booking_item <xsl:value-of select="render"/> status_<xsl:value-of select="status"/><xsl:if test="checkedout = 'true'">_checkout</xsl:if>
                </xsl:attribute>
                <xsl:value-of select="name"/>&#160;
            </a>
        </xsl:if>
        <xsl:if test="render = 'rounded_both' or render = 'rounded_right'">
            <xsl:if test="status = 'free' or status = 'hours' or status = 'paid'">
                <div style="position:relative;">
                    <a href="javascript:toggle_checkout_for_allocation({../../../id}, {id}, {count(preceding-sibling::allocationcell)});" class="checkout_link" title="checkout/uncheckout">
                        <img class="toggle_checkout" alt=""/>
                    </a>
                </div>
            </xsl:if>
        </xsl:if>
    </td>
</xsl:template>

<xsl:template name="write_inline_js">

    <script type="text/javascript">
        jQuery(document).ready( function(){
            jQuery('input.wpdevbk-filters-section-calendar').datepick(
                {   showOn: 'focus',
                    multiSelect: 0,
                    numberOfMonths: 1,
                    stepMonths: 1,
                    prevText: '&lt;&lt;',
                    nextText: '&gt;&gt;',
                    dateFormat: 'yy-mm-dd',
                    changeMonth: false,
                    changeYear: false,
                    minDate: null, maxDate: '1Y',
                    showStatus: false,
                    multiSeparator: ', ',
                    closeAtTop: false,
                    firstDay: 0, // 0 = sunday
                    gotoCurrent: false,
                    hideIfNoPrevNext:true,
                    useThemeRoller :false,
                    mandatory: true
                }
            );

            jQuery('# a.popover_here').popover( {
                placement: 'bottom'
                , delay: { show: 100, hide: 100 }
                , content: ''
                , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
                });

            jQuery('.tooltip_right').tooltip( {
                animation: true
                , delay: { show: 500, hide: 100 }
                , selector: false
                , placement: 'right'
                , trigger: 'hover'
                , title: ''
                , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });

            jQuery('.tooltip_left').tooltip( {
                animation: true
                , delay: { show: 500, hide: 100 }
                , selector: false
                , placement: 'left'
                , trigger: 'hover'
                , title: ''
                , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });

            jQuery('.tooltip_top').tooltip( {
                animation: true
                , delay: { show: 500, hide: 100 }
                , selector: false
                , placement: 'top'
                , trigger: 'hover'
                , title: ''
                , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });
    
            jQuery('.tooltip_bottom').tooltip( {
                animation: true
                , delay: { show: 500, hide: 100 }
                , selector: false
                , placement: 'bottom'
                , trigger: 'hover'
                , title: ''
                , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            });
       });
    </script>
</xsl:template>

<xsl:template name="write_inline_css">

    <style type="text/css">
        #datepick-div .datepick-header {
               width: 172px !important;
        }
        #datepick-div {
            -border-radius: 3px;
            -box-shadow: 0 0 2px #888888;
            -webkit-border-radius: 3px;
            -webkit-box-shadow: 0 0 2px #888888;
            -moz-border-radius: 3px;
            -moz-box-shadow: 0 0 2px #888888;
            width: 172px !important;
        }
        #datepick-div .datepick .datepick-days-cell a{
            font-size: 12px;
        }
        #datepick-div table.datepick tr td {
            border-top: 0 none !important;
            line-height: 24px;
            padding: 0 !important;
            width: 24px;
        }
        #datepick-div .datepick-control {
            font-size: 10px;
            text-align: center;
        }
        #datepick-div .datepick-one-month {
            height: 215px;
        }
    </style>

</xsl:template>

</xsl:stylesheet>