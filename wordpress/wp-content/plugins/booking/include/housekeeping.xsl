<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="inline_scripts.xsl"/>

<xsl:template match="view">

    <div class="wpdevbk center">
        <h3 id="selected_date_label"><xsl:comment/></h3>
    </div>

    <div class="wpdevbk">
        <div class="booking-submenu-tab-container">
            <div class="nav-tabs booking-submenu-tab-insidecontainer">
                <form id="housekeeping_form" class="form-inline" method="post" action="" name="housekeeping_form">
                    <a class="btn btn-primary" style="float: left; margin-right: 15px;" onclick="javascript:housekeeping_form.submit();">Apply <span class="icon-refresh icon-white"></span></a>

                    <div class="control-group" style="float: left;">
                        <div class="inline controls">
                            <div class="btn-group">
                                <input style="width:100px;" class="span2span2 wpdevbk-filters-section-calendar" value="{selectiondate}" id="calendar_selected_date" name="housekeeping_date" type="text"/>
                                <span class="add-on"><span class="icon-calendar"><xsl:comment/></span></span>
                            </div>
                            <p class="help-block" style="float:left;padding-left:5px;">Date</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script type="text/javascript">

jQuery(document).ready(function() {

    // pre-populate label on first time through
    var parsedDate = jQuery.datepicker.parseDate('yy-mm-dd', '<xsl:value-of select="selectiondate"/>');
    var formattedDate = jQuery.datepicker.formatDate('D, MM d, yy', parsedDate);
    jQuery('#selected_date_label').html(formattedDate);
});

</script>
    


    Home URL: <xsl:value-of select="home_url"/><br/>

    <!-- show the last /completed/ job -->
    <!-- also, if one is currently pending/submitted - disable the refresh button -->
    <xsl:choose>
        <xsl:when test="job">
            ID: <xsl:value-of select="job/id"/><br/>
            Name: <xsl:value-of select="job/name"/><br/>
            Status: <xsl:value-of select="job/status"/><br/>
            Created Date: <xsl:value-of select="job/created_date"/><br/>
            Last Updated Date: <xsl:value-of select="job/last_updated_date"/><br/>
        </xsl:when>
        <xsl:otherwise>
            No job defined.
        </xsl:otherwise>
    </xsl:choose>

    <table class="allocation_view" width="100%" cellspacing="0" cellpadding="3" border="0">
        <thead>
            <th width="50">Room</th>
            <th width="100">Bed</th>
            <th>Bedsheets</th>
        </thead>
        <tbody>
            <xsl:apply-templates select="bed" mode="bedsheet_row"/>
        </tbody>
    </table>

    <xsl:call-template name="write_inline_js"/>

</xsl:template>

<xsl:template match="bed" mode="bedsheet_row">

    <tr>
        <xsl:attribute name="class">
            alloc_resource_attrib
            <xsl:choose>
                <xsl:when test="position() mod 2">odd</xsl:when>
                <xsl:otherwise>even</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>

        <td class="border_left border_right" valign="top">
            <xsl:value-of select="room"/>
        </td>
        <td class="border_right" valign="top">
            <xsl:value-of select="bed_name"/>
        </td>
        <td>
    guest name: <xsl:value-of select="guest_name"/><br/>
    checkin_date: <xsl:value-of select="checkin_date"/><br/>
    checkout_date: <xsl:value-of select="checkout_date"/><br/>
    data href:  <xsl:value-of select="data_href"/><br/>
    Created: <xsl:value-of select="created_date"/><br/>
    Bed Sheet: <xsl:value-of select="bedsheet"/><br/>
    <xsl:if test="/view/ignore_rooms/room = room">IGNORE THIS ROOM!</xsl:if>
        </td>
    </tr>

</xsl:template>

</xsl:stylesheet>