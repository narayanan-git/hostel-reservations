﻿<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:output method="html" indent="yes" version="4.0"/>
<xsl:include href="allocation_table.xsl"/>
<xsl:include href="booking_comment_log.xsl"/>

<xsl:template match="/editbooking">

    <script type="text/javascript">
        // update the total field to 2 decimal places (readonly)
        function updateTotal() {
            var total = parseFloat(jQuery('#deposit_paid').val()) + parseFloat(jQuery('#amount_to_pay').val());
            jQuery('#total_amount').val(total.toFixed(2));
        }

        // update selected attributes when room size has changed
        function roomSizeChanged() {
            if(jQuery('#req_room_size').val() == '4') {
                toggleCheckedProperties(1);
            }
            else if(jQuery('#req_room_size').val() == '6') {
                toggleCheckedProperties(1);
            }
            else if(jQuery('#req_room_size').val() == '8') {
                toggleCheckedProperties(2);
            }
            else if(jQuery('#req_room_size').val() == '10') {
                toggleCheckedProperties(3);
            }
            else if(jQuery('#req_room_size').val() == '10+') {
                toggleCheckedProperties(3, 4, 5, 6);
            }
            else if(jQuery('#req_room_size').val() == '12') {
                toggleCheckedProperties(4);
            }
            else if(jQuery('#req_room_size').val() == '14') {
                toggleCheckedProperties(5);
            }
            else if(jQuery('#req_room_size').val() == '16') {
                toggleCheckedProperties(6);
            }
        }

        // toggles the checked attributes related to room size
        // each argument corresponds with the id of the property to be checked
        // any id not checked and related to room size with be unchecked
        function toggleCheckedProperties() {

            // only applicable for properties 1-9
            for (var i = 0; i &lt; 10; i++) {
                var i_is_checked = false;

                // if id is specified in argument, tick property
                for (var j = 0; j &lt; arguments.length; j++) {
                    if (i == arguments[j]) {
                        i_is_checked = true; 
                        break;
                    }
                }
                jQuery('#resource_property_' + i).attr('checked', i_is_checked);
            }
        }

        // executes on initial page load
        function initOnPageLoad() {
            updateTotal();
            jQuery('#req_room_size').val('10+');  // default selection
            roomSizeChanged();
        }

        window.onload=initOnPageLoad;  // initialise form values

        // script to run when Add Allocations button is pressed...
        jQuery(document).ready(function() { 
 
            //select all the a tag with name equal to modal
            jQuery('input[id=modal]').click(function(e) {
                //Cancel the link behavior
                e.preventDefault();
                //Get the tag identifier
                var id = jQuery(this).attr('name');

                // check first that first name is specified
                if(jQuery('input[name=firstname]').val() === '') {
                    showErrorMessage( jQuery('input[name=firstname]')[0], 'This field is required' );
                    return;
                }
     
                //Get the screen height and width
                var maskHeight = jQuery(document).height();
                var maskWidth = jQuery(window).width();

                //Set height and width to mask to fill up the whole screen
                jQuery('#mask').css({'width':maskWidth,'height':maskHeight});
         
                //transition effect    
                jQuery('#mask').fadeIn(1000);   
                jQuery('#mask').fadeTo("slow",0.8); 
     
                //Get the window height and width
                var winH = jQuery(window).height();
                var winW = jQuery(window).width();
               
                //Set the popup window to center
                jQuery(id).css('top',  winH/2-jQuery(id).height()/2);
                jQuery(id).css('left', winW/2-jQuery(id).width()/2);
     
                //transition effect
                jQuery(id).fadeIn(2000);
     
            });
     
            //if close button is clicked
            jQuery('.window .close').click(function (e) {
                //Cancel the link behavior
                e.preventDefault();
                jQuery('#mask, .window').hide();
            });    
     
            //if mask is clicked
            jQuery('#mask').click(function () {
                jQuery(this).hide();
                jQuery('.window').hide();
            });        
     
        });
    </script>

    <!-- div#mask used to blank out screen from this point when bringing up modal window --> 
    <div id="mask"><xsl:comment/></div>

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

                <!-- ################ BEGIN MODAL DIALOG ############################# -->
                <div id="boxes">
                    <div id="dialog" class="window">
                        <div class="metabox-holder" style="text-align:left;">

                            <div style="float:right; text-align:right; vertical-align:top;">
                                <!-- close button is defined as close class -->
                                <a href="#" class="close">Close</a>
                            </div>

                            <h3>Allocations</h3>

                            <div style="float:left; width:450px;">
                                <p>Add <input type="text" name="num_guests_m" value="" size="3" /> Male Guest(s)<br/>
                                    Add <input type="text" name="num_guests_f" value="" size="3" /> Female Guest(s)<br/>
                                    Add <input type="text" name="num_guests_x" value="" size="3" /> Unspecified Guest(s)<br/>
                                    <input type="hidden" name="num_guests"/> <xsl:comment>placeholder for error message</xsl:comment>
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
                                        <select id="req_room_size" name="req_room_size" onchange="roomSizeChanged()">
                                            <option value="4">4 Bed</option>
                                            <option value="6">6 Bed</option>
                                            <option value="8">8 Bed</option>
                                            <option value="10">10 Bed</option>
                                            <option value="10+">10+ Bed</option>
                                            <option value="12">12 Bed</option>
                                            <option value="14">14 Bed</option>
                                            <option value="16">16 Bed</option>
                                            <option value="P">Private</option>
                                        </select>
                                        <select id="req_room_type" name="req_room_type">
                                            <option value="X">Mixed</option>
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
                                <div id="allocation_modal_anchor"><xsl:comment/><!-- error response here --></div>
                                <p><input type="button" value="ADD" onclick="add_booking_allocation(this.form);" /></p>
                            </div>
                    
                            <div style="float:left;">
                                <div id="calendar_booking1">&#160;</div>
                                <textarea rows="3" cols="50" id="calendar_booking1" name="calendar_booking1" style="display:none;"><xsl:comment/></textarea>
                                <div id="calendar_anchor"><xsl:comment/><!-- error response here --></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ################ END MODAL DIALOG ############################# -->

                <div style="text-align:left;">
                    <!-- dorm allocations get inserted here via ajax-->
                    <div id="booking_allocations"><xsl:apply-templates select="allocations" /><xsl:comment/></div>
                    <div class="clear" style="clear:both;height:10px;"><xsl:comment/></div>
                    <div id="ajax_respond"><xsl:comment/><!-- ajax response here--></div>
                
                    <p> <!-- #dialog is the identifier of the DIV defined in the code above -->
                        <input id="modal" type="button" value="Add Allocations..." name="#dialog" onclick="javascript:;" />
                        <input style="margin-left:20px;" type="button" value="Save" onclick="save_booking(this.form);" />
                    </p>
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
        <input type="checkbox" name="resource_property" id="resource_property_{id}" value="{id}"/>
        &#160;<xsl:value-of select="value"/>
    </div>
</xsl:template>

</xsl:stylesheet>