<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="allocation_table.xsl"/>
<xsl:include href="booking_comment_log.xsl"/>

<xsl:template match="/editbooking">

    <script type="text/javascript">
        // update the total field to 2 decimal places (readonly)
        function updateTotal() {
            var total = parseFloat(jQuery('#deposit_paid').val()) + parseFloat(jQuery('#amount_to_pay').val());
            jQuery('#total_amount').val(total.toFixed(2));
        }
        window.onload=updateTotal;  // update on page load
    </script>

    <div id="wpdev-booking-reservation-general" class="wrap bookingpage" style="margin-left:100px; margin-right:100px;">
        <div class="icon32" style="margin:10px 25px 10px 10px;"><img src="{homeurl}/wp-content/plugins/booking/img/add-1-48x48.png"/><br /></div>
        <h2>
            <xsl:if test="id = 0">Add Booking</xsl:if>
            <xsl:if test="id > 0">Edit Booking</xsl:if>
        </h2>
        <div id="ajax_working"><xsl:comment/></div>
        <div class="clear" style="margin:10px;"><xsl:comment/></div>
        <div style="clear:both;"><xsl:comment/></div>
    
        <div id="booking_form_div1" class="booking_form_div">
            <form id="booking_form" class="booking_form" method="post" action="">
                <div id="ajax_respond_insert"><xsl:comment/><!-- record inserted success message --></div>
                <div style="float:left; text-align:left; width:450px;">
                    <input type="hidden" name="bookingid" value="{id}"/>
                    <p>First Name (required):<br />  <input type="text" name="firstname" value="{firstname}" class="wpdev-validates-as-required" size="40" /> </p>
                    <p>Last Name:<br />  <input type="text" name="lastname" value="{lastname}" size="40"/></p>
                    <p><span class="edit_booking_header">Booked by: </span>
                        <select id="referrer" name="referrer">
                            <option value="">--</option>
                            <option value="hostelworld">
                                <xsl:if test="referrer = 'hostelworld'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                                HostelWorld
                            </option>
                            <option value="hostelbookers">
                                <xsl:if test="referrer = 'hostelbookers'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                                HostelBookers
                            </option>
                            <option value="crnet">
                                <xsl:if test="referrer = 'crnet'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                                CR Net
                            </option>
                            <option value="telephone">
                                <xsl:if test="referrer = 'telephone'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                                Telephone
                            </option>
                            <option value="walkin">
                                <xsl:if test="referrer = 'walkin'"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
                                Walk-in
                            </option>
                        </select>
                    </p>
                    <p><span class="edit_booking_header">Deposit Paid: </span><input type="text" class="wpdev-validates-as-currency" id="deposit_paid" name="deposit_paid" value="{depositpaid}" onchange="updateTotal()" size="5"/> </p>
                    <p><span class="edit_booking_header">Amount to Pay: </span><input type="text"  class="wpdev-validates-as-currency" id="amount_to_pay" name="amount_to_pay" value="{amounttopay}" onchange="updateTotal()" size="5"/> </p>
                    <p><span class="edit_booking_header">Total for Booking: </span><input type="text" id="total_amount" name="total_amount" value="" size="5" readonly="readonly"/> </p>
                </div>
                <div class="metabox-holder" style="float:left;">
                    <h3>Audit Log/Comments</h3>
                    <div id="comment_log" class="scroll_history">
                        <xsl:apply-templates select="comments/comment"/>
                        <xsl:comment/>
                    </div>
                    <p>Comment:<br />
                        <span class="wpdev-form-control-wrap"><textarea id="booking_comment" name="booking_comment" cols="38" rows="1"><xsl:comment/></textarea></span>
                    </p>
                    <div style="float:right;"><input type="button" value="Add Comment" onclick="add_booking_comment(this.form);" /></div>
                </div>
                <div class="clear" style="margin:20px;"><xsl:comment/></div>
                <div style="clear:both;height:10px;"><xsl:comment/></div>
                <hr/>
                <div style="text-align:left;">

                    <div class="metabox-holder">
                        <h3>Allocations</h3>
                        <div style="float:left; width:450px;">
                            <p>Add <input type="text" name="num_visitors" value="" size="5" /> Guest(s)</p>
                            <p>Gender:<br />  <span class="wpdev-form-control-wrap">
                                <select id="gender" name="gender" style="width:150px;">
                                    <option value="X">Unspecified</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                                </span>
                            </p>
                            <p>Room/Bed:<br />  
                                <span class="wpdev-form-control-wrap"> 
                                    <select id="booking_resource" name="booking_resource" onchange="resource_changed(this);">
                                        <option value="0" resource_type="group">Any Free Bed...</option>
                                        <xsl:apply-templates select="allocations/resources/resource" mode="resource_selection"/>
                                    </select>
                                </span>
                            </p>
                            <p>Requested Room Type:<br />  
                                <span class="wpdev-form-control-wrap"> 
                                    <select id="room_type" name="room_type" style="width:200px">
                                        <option value="X">Mixed/Don't give a Toss</option>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                    </select>
                                </span>
                            </p>
                            <div id="resource_property_selection">
                                <p>Assign To:<br />  
                                    <div style="margin-left:30px;">
                                        <xsl:apply-templates select="properties/property"/>
                                    </div>
                                </p>
                            </div>
                            <p><input type="button" value="ADD" onclick="add_booking_allocation(this.form);" /></p>
                        </div>
                    
                        <div style="float:left;">
                            <div id="calendar_booking1">&#160;</div>
                            <textarea rows="3" cols="50" id="calendar_booking1" name="calendar_booking1" style="display:none;"><xsl:comment/></textarea>
                        </div>
                    </div>
                
                    <div class="clear" style="margin:20px;"><xsl:comment/></div>
                    <div style="clear:both;height:10px;"><xsl:comment/></div>

                    <!-- dorm allocations get inserted here via ajax-->
                    <p><div id="booking_allocations"><xsl:apply-templates select="allocations" /><xsl:comment/></div></p>
                    <p><div id="ajax_respond"><xsl:comment/><!-- ajax response here--></div></p>
                
                    <p><input type="button" value="Save" onclick="save_booking(this.form);" /></p>
                </div>
            
            </form>
        </div>
    
        <div id="submitting"><xsl:comment/></div>
        <div class="form_bk_messages" id="form_bk_messages1"><xsl:comment/></div>

        <script type='text/javascript'>
            jQuery(document).ready( function(){

                // Configure and show calendar
                jQuery('#calendar_booking1').datepick(
                        {   showOn: 'both',
                            multiSelect: 50, // max number of selections
                            numberOfMonths: 1,
                            stepMonths: 1,
                            prevText: '&lt;&lt;',
                            nextText: '&gt;&gt;',
                            dateFormat: 'dd.mm.yy',
                            changeMonth: false, 
                            changeYear: false,
                            minDate: 0, maxDate: '1Y',
                            showStatus: false,
                            multiSeparator: ', ',
                            closeAtTop: false,
                            firstDay: 0,  // 0 == sunday
                            gotoCurrent: false,
                            hideIfNoPrevNext:true,
                            useThemeRoller :false // ui-cupertino.datepick.css
                        }
                );
            });

            // hide the resource property selection if a 'bed' is selected
            function resource_changed(form_component) {
                var selectedType = form_component.options[form_component.selectedIndex].getAttribute("resource_type");
                if (selectedType == 'bed') {
                    jQuery('#resource_property_selection').fadeOut(500);
                } else {
                    jQuery('#resource_property_selection').fadeIn(500);
                }
            }
        </script>
    </div>    
</xsl:template>

<xsl:template match="property">
    <div style="text-align:left;">
        <input type="checkbox" name="resource_property" value="{id}">
            &#160;<xsl:value-of select="value"/>
        </input>
    </div>
</xsl:template>

</xsl:stylesheet>