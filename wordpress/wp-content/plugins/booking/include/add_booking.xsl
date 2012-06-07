<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="/addbooking">

    <xsl:if test="selectedDates = ''">
        <div id="calendar_booking1">&#160;</div>
    </xsl:if>
    <textarea rows="3" cols="50" id="date_booking1" name="date_booking1" style="display:none;"><xsl:comment/><xsl:value-of select="selectedDates"/></textarea>
    
    <!-- booking calendar legend -->
    <div class="block_hints datepick">
        <div class="wpdev_hint_with_text">
            <div class="block_free datepick-days-cell"><a>#</a></div>
            <div class="block_text">- Available</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_booked date_approved">#</div>
            <div class="block_text">- Booked</div>
        </div>
        <div class="wpdev_hint_with_text">
            <div class="block_pending date2approve">#</div>
            <div class="block_text">- Pending</div>
        </div>
    </div>
    <div class="wpdev_clear_hint"><xsl:comment/></div>
    
    <div style="clear:both;height:10px;"><xsl:comment/></div>
    
    <div id="booking_form_div1" class="booking_form_div">
        <form id="booking_form" class="booking_form" method="post" action="">
            <div id="ajax_respond_insert"><xsl:comment/><!-- record inserted success message --></div>
            <div style="text-align:left;">
                <p>First Name (required):<br />  <span class="wpdev-form-control-wrap firstname"><input type="text" name="firstname" value="" class="wpdev-validates-as-required" size="40" /></span> </p>
                <p>Last Name:<br />  <span class="wpdev-form-control-wrap lastname"><input type="text" name="lastname" value="" size="40" /></span> </p>
                <p>Add <input type="text" name="num_visitors" value="" size="5" /> 
                    <input type="radio" name="gender" value="Male" checked="checked" />Male
                    <input type="radio" name="gender" value="Female" />Female visitors to Resource 
                    <select id="booking_resource" name="booking_resource">
                        <option value="0"> - </option>
                        <xsl:apply-templates select="//resource" mode="resource_selection"/>
                    </select>
                    <input type="button" value="GO" onclick="add_booking_allocation(this.form,'en_US');" />
                </p>
                <p><div id="booking_allocations"><xsl:comment/><!-- dorm allocations get inserted here via ajax--></div></p>
                <p><div id="ajax_respond"><xsl:comment/><!-- ajax response here--></div></p>
                <p>Details:<br /><span class="wpdev-form-control-wrap details1"><textarea name="details" cols="40" rows="10"><xsl:comment/></textarea></span> </p>
                <p><input type="button" value="Send" onclick="mybooking_submit_v2(this.form);" /></p>
            </div>
            
        </form>
    </div>
    
    <div id="submitting"><xsl:comment/></div>
    <div class="form_bk_messages" id="form_bk_messages1"><xsl:comment/></div>

    <script type='text/javascript'>
        jWPDev(document).ready( function(){
          init_datepick_cal('1', // booking type
                             [], 
                             1 , // calendar count
                             0,  // get_bk_option( 'booking_start_day_weeek' ) 
                             false ); // $start_js_month
          });
    </script>
</xsl:template>

<!-- builds drill-down of resource id, name -->
<xsl:template mode="resource_selection" match="resource">
    <option value="{id}">
        <xsl:call-template name ="indent">
            <xsl:with-param name="i">1</xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="level"/></xsl:with-param>
        </xsl:call-template>
        <xsl:value-of select ="name"/>
    </option>
</xsl:template>

<!-- adds non-breaking spaces -->
<xsl:template name="indent">
    <xsl:param name="i"/>
    <xsl:param name="value"/>
    <xsl:if test="$i &lt; $value">
        &#160;&#160;&#160;&#160;
        <xsl:call-template name ="indent">
            <xsl:with-param name="i"><xsl:value-of select ="$i+1"/></xsl:with-param>
            <xsl:with-param name="value"><xsl:value-of select="level"/></xsl:with-param>
        </xsl:call-template>
    </xsl:if>
</xsl:template>

</xsl:stylesheet>