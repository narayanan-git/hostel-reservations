<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

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
                <a href="{/bookingview/editbooking_url}?bookingid={id}" data-original-title="Edit Booking" rel="tooltip" class="tooltip_bottom">
                    <img src="{/bookingview/homeurl}/wp-content/plugins/booking/img/edit_type.png" style="width:12px; height:13px;"/>
                </a>
                <xsl:if test="allowCheckout = 'true'">
                    <a href="javascript:toggle_checkout_for_booking({id})" data-original-title="Checkout" rel="tooltip" class="tooltip_bottom">
                        <img src="{/bookingview/homeurl}/wp-content/plugins/booking/img/checkout.png" style="margin-left:5px"/>
                    </a>
                </xsl:if>
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