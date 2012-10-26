<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="/view">

    <div id="ajax_respond"><xsl:comment/></div>
    <div class="clear"><xsl:comment/></div>
    <div id="ajax_working"><xsl:comment/></div>
    <div id="poststuff" class="metabox-holder" style="margin-top:0px;">
        <div class="clear" style="height:10px;"><xsl:comment/></div>
        <form name="post_option" action="" method="post" id="post_option" >

            <div style="width:64%; float:left;margin-right:1%;">
                <div class='meta-box'>
                    <div id="bk_general_settings_main" class="postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_main');"><br/></div>
                        <h3 class='hndle'><span>Main</span></h3> 
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><label for="admin_cal_count" >Admin email:</label></th>
                                        <td><input id="email_reservation_adress"  name="email_reservation_adress" class="regular-text code" type="text" style="width:350px;" size="145" value="&quot;Booking system&quot; &lt;nowhere@anonymous.net&gt;" /><br/>
                                            <span class="description">The <b>return address</b> when sending automated emails</span>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th colspan="2" scope="row"><label for="backoffice_urls">URLs of pages to override when displaying on public site:</label><br/>
                                            <span class="description">Create menus/pages for these URLs and they will be replaced with their associated content</span>
                                        </th>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="editbooking_url">New Booking Page</label></th>
                                        <td><input id="editbooking_url"  name="hbo_editbooking_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{editbookingurl}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="bookings_url">Bookings Page</label></th>
                                        <td><input id="bookings_url"  name="hbo_bookings_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{bookingsurl}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="allocations_url">Allocations Page</label></th>
                                        <td><input id="allocations_url"  name="hbo_allocations_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{allocationsurl}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="summary_url">Daily Summary Page</label></th>
                                        <td><input id="summary_url"  name="hbo_summary_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{summaryurl}" /></td>
                                    </tr>  
                                    <tr valign="top">
                                        <th scope="row"><label for="resources_url">Resources Page</label></th>
                                        <td><input id="resources_url"  name="hbo_resources_url" class="regular-text code" type="text" style="width:350px;" size="145" value="{resourcesurl}" /></td>
                                    </tr>  
                                    <tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"><xsl:comment/></div></td></tr>
                                    <tr valign="top"> 
                                        <td colspan="2">
                                            <div style="width:100%;">
                                                <span style="color:#21759B;cursor: pointer;font-weight: bold;text-decoration: none;font-size: 11px;"
                                                    onclick="javascript: jQuery('#togle_settings_javascriptloading').slideToggle('normal');">
                                                    + <span style="border-bottom:1px dashed #21759B;">Show advanced settings of JavaScript loading</span>
                                                </span>
                                            </div>
                                            <table id="togle_settings_javascriptloading" style="display:none;" class="hided_settings_table">
                                                <tr valign="top">
                                                    <th scope="row"><label for="is_not_load_bs_script_in_client" >Disable Bootstrap loading:</label><br/>Client side</th>
                                                    <td><input id="is_not_load_bs_script_in_client" type="checkbox"   value="" name="is_not_load_bs_script_in_client"
                                                            onclick="javascript: if (this.checked) {{ var answer = confirm('Warning! You are need to be sure what you are doing. You are dissbale of loading some JavaScripts Do you really want to do this?'); if ( answer){{ this.checked = true; }} else {{this.checked = false;}} }}"/>
                                                        <span class="description"> If your theme or some other plugin is load the BootStrap JavaScripts, you can dissable  loading of this script by this plugin.</span>
                                                    </td>
                                                </tr>
                                                <tr valign="top">
                                                    <th scope="row"><label for="is_not_load_bs_script_in_admin" >Disable Bootstrap loading:</label><br/>Admin  side</th>
                                                    <td><input id="is_not_load_bs_script_in_admin" type="checkbox"  value="" name="is_not_load_bs_script_in_admin"
                                                            onclick="javascript: if (this.checked) {{ var answer = confirm('Warning! You are need to be sure what you are doing. You are dissbale of loading some JavaScripts Do you really want to do this?'); if ( answer){{ this.checked = true; }} else {{this.checked = false;}} }}"/>
                                                        <span class="description"> If your theme or some other plugin is load the BootStrap JavaScripts, you can dissable  loading of this script by this plugin.</span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr valign="top"> 
                                        <td colspan="2">
                                            <div style="width:100%;">
                                                <span style="color:#21759B;cursor: pointer;font-weight: bold;text-decoration: none;font-size: 11px;"
                                                    onclick="javascript: jQuery('#togle_settings_powered').slideToggle('normal');">
                                                    + <span style="border-bottom:1px dashed #21759B;">Show settings of powered by notice</span>
                                                </span>
                                            </div>

                                            <table id="togle_settings_powered" style="display:none;" class="hided_settings_table">
                                                    <tr valign="top">
                                                        <th scope="row"><label for="booking_is_show_powered_by_notice" >Powered by notice:</label></th>
                                                        <td><input id="booking_is_show_powered_by_notice" type="checkbox" checked="checked" value="On" name="booking_is_show_powered_by_notice"/>
                                                            <span class="description"> Turn On/Off powered by "Booking Calendar" notice under the calendar.</span>
                                                        </td>
                                                    </tr>
                                                    <tr valign="top">
                                                        <th scope="row"><label for="wpdev_copyright" >Copyright notice:</label></th>
                                                        <td><input id="wpdev_copyright" type="checkbox"   value="Off" name="wpdev_copyright"/>
                                                            <span class="description"> Turn On/Off copyright wpdevelop.com notice at footer of site view.</span>
                                                        </td>
                                                    </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class='meta-box'>
                    <div id="bk_general_settings_calendar" class="postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_calendar');"><br/></div>
                        <h3 class='hndle'><span>Calendar</span></h3> 
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><label for="booking_skin" >Calendar skin:</label></th>
                                        <td>
                                            <select id="booking_skin" name="booking_skin" style="text-transform:capitalize;">
                                                <option value="http://localhost:18560/wp-content/plugins/booking/css/skins/black.css" >Black</option><option  value="http://localhost:18560/wp-content/plugins/booking/css/skins/standard.css" >Standard</option><option  value="http://localhost:18560/wp-content/plugins/booking/css/skins/traditional-light.css" >Traditional-light</option><option selected="SELECTED" value="http://localhost:18560/wp-content/plugins/booking/css/skins/traditional.css">Traditional</option>
                                            </select>
                                            <span class="description">Select the skin of booking calendar</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="start_day_weeek" >Number of months:</label></th>
                                        <td>
                                            <select id="max_monthes_in_calendar" name="max_monthes_in_calendar">
                                                            <option  value="1m">1 month(s)</option>
                                                                <option  value="2m">2 month(s)</option>
                                                                <option  value="3m">3 month(s)</option>
                                                                <option  value="4m">4 month(s)</option>
                                                                <option  value="5m">5 month(s)</option>
                                                                <option  value="6m">6 month(s)</option>
                                                                <option  value="7m">7 month(s)</option>
                                                                <option  value="8m">8 month(s)</option>
                                                                <option  value="9m">9 month(s)</option>
                                                                <option  value="10m">10 month(s)</option>
                                                                <option  value="11m">11 month(s)</option>
                                                                <option  value="12m">12 month(s)</option>
                
                                                            <option selected="selected" value="1y">1 year(s)</option>
                                                                <option  value="2y">2 year(s)</option>
                                                                <option  value="3y">3 year(s)</option>
                                                                <option  value="4y">4 year(s)</option>
                                                                <option  value="5y">5 year(s)</option>
                                                                <option  value="6y">6 year(s)</option>
                                                                <option  value="7y">7 year(s)</option>
                                                                <option  value="8y">8 year(s)</option>
                                                                <option  value="9y">9 year(s)</option>
                                                                <option  value="10y">10 year(s)</option>
                
                                            </select>
                                            <span class="description">Select your maximum number of scroll months at booking calendar</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="start_day_weeek" >Start Day of week:</label></th>
                                        <td>
                                            <select id="start_day_weeek" name="start_day_weeek">
                                                <option selected="selected" value="0">Sunday</option>
                                                <option  value="1">Monday</option>
                                                <option  value="2">Tuesday</option>
                                                <option  value="3">Wednesday</option>
                                                <option  value="4">Thursday</option>
                                                <option  value="5">Friday</option>
                                                <option  value="6">Saturday</option>
                                            </select>
                                            <span class="description">Select your start day of the week</span>
                                        </td>
                                    </tr>

                                    <tr valign="top"><td colspan="2" style="padding:10px 0px; "><div style="border-bottom:1px solid #cccccc;"><xsl:comment/></div></td></tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="multiple_day_selections" >Multiple days selection:</label><br/>in calendar</th>
                                        <td><input id="multiple_day_selections" type="checkbox" checked="checked" value="On" name="multiple_day_selections"/>
                                            <span class="description"> Check, if you want have multiple days selection at calendar.</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="unavailable_days_num_from_today" >Unavailable days from today:</label></th>
                                        <td>
                                            <select id="unavailable_days_num_from_today" name="unavailable_days_num_from_today">
                                                                                            <option selected="selected" value="0">0</option>
                                                                                            <option  value="1">1</option>
                                                                                            <option  value="2">2</option>
                                                                                            <option  value="3">3</option>
                                                                                            <option  value="4">4</option>
                                                                                            <option  value="5">5</option>
                                                                                            <option  value="6">6</option>
                                                                                            <option  value="7">7</option>
                                                                                            <option  value="8">8</option>
                                                                                            <option  value="9">9</option>
                                                                                            <option  value="10">10</option>
                                                                                            <option  value="11">11</option>
                                                                                            <option  value="12">12</option>
                                                                                            <option  value="13">13</option>
                                                                                            <option  value="14">14</option>
                                                                                            <option  value="15">15</option>
                                                                                            <option  value="16">16</option>
                                                                                            <option  value="17">17</option>
                                                                                            <option  value="18">18</option>
                                                                                            <option  value="19">19</option>
                                                                                            <option  value="20">20</option>
                                                                                            <option  value="21">21</option>
                                                                                            <option  value="22">22</option>
                                                                                            <option  value="23">23</option>
                                                                                            <option  value="24">24</option>
                                                                                            <option  value="25">25</option>
                                                                                            <option  value="26">26</option>
                                                                                            <option  value="27">27</option>
                                                                                            <option  value="28">28</option>
                                                                                            <option  value="29">29</option>
                                                                                            <option  value="30">30</option>
                                                                                            <option  value="31">31</option>
                                                                                        </select>
                                            <span class="description">Select number of unavailable days in calendar start from today.</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="is_dif_colors_approval_pending" >Unavailable days:</label></th>
                                        <td>    <div style="float:left;width:500px;border:0px solid red;">
                                                <input id="unavailable_day0" name="unavailable_day0"   value="Off"  type="checkbox" />
                                                <span class="description">Sunday</span>&#160;
                                                <input id="unavailable_day1" name="unavailable_day1"   value="Off"  type="checkbox" />
                                                <span class="description">Monday</span>&#160;
                                                <input id="unavailable_day2" name="unavailable_day2"   value="Off"  type="checkbox" />
                                                <span class="description">Tuesday</span>&#160;
                                                <input id="unavailable_day3" name="unavailable_day3"   value="Off"  type="checkbox" />
                                                <span class="description">Wednesday</span>&#160;
                                                <input id="unavailable_day4" name="unavailable_day4"   value="Off"  type="checkbox" />
                                                <span class="description">Thursday</span>&#160;
                                                <input id="unavailable_day5" name="unavailable_day5"   value="Off"  type="checkbox" />
                                                <span class="description">Friday</span>&#160;
                                                <input id="unavailable_day6" name="unavailable_day6"   value="Off"  type="checkbox" />
                                                <span class="description">Saturday</span>
                                            </div>
                                            <div style="width:auto;margin-top:25px;">
                                                <span class="description">Check unavailable days in calendars. This option is overwrite all other settings.</span></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class='meta-box'>
                    <div id="bk_general_settings_form" class="postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_form');"><br/></div>
                        <h3 class='hndle'><span>Form</span></h3> 
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><label for="is_use_captcha" >CAPTCHA:</label><br/>at booking form</th>
                                        <td><input id="is_use_captcha" type="checkbox"   value="Off" name="is_use_captcha"/>
                                            <span class="description"> Check, if you want to activate CAPTCHA inside of booking form.</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="is_use_autofill_4_logged_user" >Auto fill fields:</label><br/>for logged in users</th>
                                        <td><input id="is_use_autofill_4_logged_user" type="checkbox" checked="checked" value="On" name="is_use_autofill_4_logged_user"/>
                                            <span class="description"> Check, if you want activate auto fill fields of booking form for logged in users.</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="is_show_legend" >Show legend:</label><br/>at booking calendar</th>
                                        <td><input id="is_show_legend" type="checkbox"   value="Off" name="is_show_legend"/>
                                            <span class="description"> Check, if you want to show legend of dates under booking calendar.</span>
                                        </td>
                                    </tr>

                                    <tr valign="top" style="padding: 0px;">
                                        <td style="width:50%;font-weight: bold;"><input  checked="checked" value="message" type="radio" id="type_of_thank_you_message"  name="type_of_thank_you_message"  onclick="javascript: jQuery('#togle_settings_thank-you_page').slideUp('normal');jQuery('#togle_settings_thank-you_message').slideDown('normal');"  /> <label for="type_of_thank_you_message" >Show "thank you" message after booking is done</label></td>
                                        <td style="width:50%;font-weight: bold;"><input   value="page" type="radio" id="type_of_thank_you_message"  name="type_of_thank_you_message"  onclick="javascript: jQuery('#togle_settings_thank-you_page').slideDown('normal');jQuery('#togle_settings_thank-you_message').slideUp('normal');"  /> <label for="type_of_thank_you_message" >Redirect visitor to a new "thank you" page </label></td>
                                    </tr>

                                    <tr valign="top" style="padding: 0px;">
                                        <td colspan="2">
                                            <table id="togle_settings_thank-you_message" style="width:100%;" class="hided_settings_table">
                                                <tr valign="top">
                                                    <th>
                                                        <label for="new_booking_title" style="font-size:12px;" >New booking title:</label><br/>
                                                        <span style="color:#888;font-weight:bold;">showing after</span> booking
                                                    </th>
                                                    <td>
                                                        <input id="new_booking_title" class="regular-text code" type="text" size="45" value="Thank you for your online reservation.  We will send confirmation of your booking as soon as possible." name="new_booking_title" style="width:99%;"/>
                                                        <span class="description">Type title of new booking <b>after booking has done by user</b></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>
                                                        <label for="new_booking_title" style=" font-weight: bold;font-size: 12px;" >Showing title time:</label><br/>
                                                        <span style="color:#888;font-weight:bold;">new booking</span>
                                                    </th>
                                                    <td>
                                                        <input id="new_booking_title_time" class="regular-text code" type="text" size="45" value="7000" name="new_booking_title_time" />
                                                        <span class="description">Type in miliseconds count of time for showing new booking title</span>
                                                    </td>
                                                </tr>
                                            </table>

                                            <table id="togle_settings_thank-you_page" style="width:100%;display:none;" class="hided_settings_table">
                                                <tr valign="top">
                                                    <th scope="row" style="width:170px;">
                                                        <label for="thank_you_page_URL" >URL of "thank you" page:</label>
                                                    </th>
                                                    <td><input value="http://localhost:18560" name="thank_you_page_URL" id="thank_you_page_URL" class="regular-text code" type="text" size="45"  style="width:99%;" />
                                                        <span class="description">Type URL of <b>"thank you" page</b></span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class='meta-box'>
                    <div id="bk_general_settings_bktable" class="postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_bktable');"><br/></div>
                        <h3 class='hndle'><span>Listing of bookings</span></h3> 
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><label for="bookings_num_per_page" >Bookings number per page:</label></th>
                                        <td>
                                            <select id="bookings_num_per_page" name="bookings_num_per_page">
                                                <option  value="5">5</option>
                                                <option selected="selected" value="10">10</option>
                                                <option  value="20">20</option>
                                                <option  value="25">25</option>
                                                <option  value="50">50</option>
                                                <option  value="75">75</option>
                                                <option  value="100">100</option>
                                            </select>
                                            <span class="description">Select number of bookings per page in booking listing</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="booking_sort_order" >Bookings default order:</label></th>
                                        <td>
                                            <select id="booking_sort_order" name="booking_sort_order">
                                                <option  value="">ID&#160;ASC</option>
                                                <option  value="booking_id_asc">ID&#160;DESC</option>
                                            </select>

                                            <span class="description">Select your default order of bookings in the booking listing</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="booking_default_toolbar_tab" >Default toolbar tab:</label></th>
                                        <td>
                                            <select id="booking_default_toolbar_tab" name="booking_default_toolbar_tab">
                                                <option  value="filter">Filter tab</option>
                                                <option  value="actions">Actions tab</option>
                                            </select>

                                            <span class="description">Select your default opened tab in toolbar at booking listing page</span>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="booking_date_format" >Date Format:</label></th>
                                        <td>
                                            <fieldset>
            	                                <label title='F j, Y'><input type='radio' name='booking_date_format' value='F j, Y' checked='checked' /> September 8, 2012</label> &#160;&#160;&#160;
	                                            <label title='Y/m/d'><input type='radio' name='booking_date_format' value='Y/m/d' /> 2012/09/08</label> &#160;&#160;&#160;
	                                            <label title='m/d/Y'><input type='radio' name='booking_date_format' value='m/d/Y' /> 09/08/2012</label> &#160;&#160;&#160;
	                                            <label title='d/m/Y'><input type='radio' name='booking_date_format' value='d/m/Y' /> 08/09/2012</label> &#160;&#160;&#160;
                                                <div style="height:7px;">&#160;</div>
                                                <label><input type="radio" name="booking_date_format" id="date_format_custom_radio" value="F j, Y"/> Custom: </label>
                                                <input id="booking_date_format_custom" class="regular-text code" type="text" size="45" value="F j, Y" name="booking_date_format_custom" style="line-height:35px;"
                                                       onchange="javascript:document.getElementById('date_format_custom_radio').value = this.value;document.getElementById('date_format_custom_radio').checked=true;"/>
                                                    September 8, 2012&#160;&#160; Type your date format for showing in emails and booking table. <br/><a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">Documentation on date formatting.</a>
                                            </fieldset>
                                        </td>
                                    </tr>
            
                                    <tr valign="top">
                                        <th scope="row"><label for="booking_date_view_type">Dates view:</label></th>
                                        <td>
                                            <select id="booking_date_view_type" name="booking_date_view_type">
                                                <option selected="selected" value="short">Short days view</option>
                                                <option value="wide">Wide days view</option>
                                            </select>
                                            <span class="description">Select default type of dates view at the booking tables</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div style="width:35%; float:left;">
                <div class='meta-box'>
                    <div id="bk_general_settings_info" class="gdrgrid postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_info');"><br/></div>
                        <h3 class='hndle'><span>Information</span></h3>
                        <div class="inside">
                            <style type="text/css">
                                #dashboard_bk {
                                    width:100%;
                                }
                                #dashboard_bk .bk_dashboard_section {
                                    float:left;
                                    margin:0px;
                                    padding:0px;
                                    width:100%;
                                }
                                #dashboard-widgets-wrap #dashboard_bk .bk_dashboard_section {
                                    width:49%;
                                }
                                #dashboard-widgets-wrap #dashboard_bk .bk_right {
                                    float:right
                                }

                                #dashboard_bk .bk_header {
                                    color:#777777;
                                    font-family:Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                    font-size:16px;
                                    font-style:italic;
                                    line-height:24px;
                                    margin:5px;
                                    padding:0 10px;
                                }

                                #dashboard_bk .bk_table {
                                    background:none repeat scroll 0 0 #FFFBFB;
                                    border-bottom:1px solid #ECECEC;
                                    border-top:1px solid #ECECEC;
                                    margin:6px 0 0 6px;
                                    padding:2px 10px;
                                    width:95%;
                                    -border-radius:4px;
                                    -moz-border-radius:4px;
                                    -webkit-border-radius:4px;
                                    -moz-box-shadow:0 0 2px #C5C3C3;
                                    -webkit-box-shadow:0 0 2px #C5C3C3;
                                    -box-shadow:0 0 2px #C5C3C3;
                                }

                                #dashboard_bk table.bk_table td{
                                    border-top:1px solid #DDDDDD;
                                    line-height:19px;
                                    padding:4px 0px 4px 10px;
                                    font-size:12px;
                                }
                                #dashboard_bk table.bk_table tr.first td{
                                    border:none;

                                }
                                #dashboard_bk table.bk_table tr td.first{
                                    text-align:center;
                                    padding:4px 0px;
                                }
                                #dashboard_bk table.bk_table tr td a {
                                    text-decoration: none;
                                }
                                #dashboard_bk table.bk_table tr td a span{
                                    font-size:18px;
                                    font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                }
                                #dashboard_bk table.bk_table td.bk_spec_font a{
                                    font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                    font-size:14px;
                                }

                                #dashboard_bk table.bk_table td.bk_spec_font {
                                    font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                                    font-size:13px;
                                }


                                #dashboard_bk table.bk_table td.pending a{
                                    color:#E66F00;
                                }

                                #dashboard_bk table.bk_table td.new-bookings a{
                                    color:red;
                                }

                                #dashboard_bk table.bk_table td.actual-bookings a{
                                    color:green;
                                }

                                #dashboard-widgets-wrap #dashboard_bk .border_orrange, #dashboard_bk .border_orrange {
                                    border:1px solid #EEAB26;
                                    background: #FFFBCC;
                                    padding:0px;
                                    width:98%;  clear:both;
                                    margin:5px 5px 20px;
                                    border-radius:10px;
                                    -webkit-border-radius:10px;
                                    -moz-border-radius:10px;
                                }
                                #dashboard_bk .bk_dashboard_section h4 {
                                    font-size:13px;
                                    margin:10px 4px;
                                }
                                #bk_errror_loading {
                                        text-align: center;
                                        font-style: italic;
                                        font-size:11px;
                                }
                            </style>
            
                            <div id="dashboard_bk">
                                <div class="bk_dashboard_section bk_right">
                                    <span class="bk_header">Statistic:</span>
                                    <table class="bk_table">
                                        <tr class="first">
                                            <td class="first"> <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_is_new=1&amp;wh_booking_date=3"><span class="">0</span></a> </td>
                                            <td class=""> <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_is_new=1&amp;wh_booking_date=3">New (unread) booking(s)</a></td>
                                        </tr>
                                        <tr>
                                            <td class="first"> <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_approved=0&amp;wh_booking_date=3"><span class="">0</span></a></td>
                                            <td class="pending"><a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_approved=0&amp;wh_booking_date=3" class="">Pending booking(s)</a></td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="bk_dashboard_section" >
                                    <span class="bk_header">Agenda:</span>
                                    <table class="bk_table">
                                        <tr class="first">
                                            <td class="first"> <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_modification_date=1&amp;wh_booking_date=3"><span>0</span></a> </td>
                                            <td class="new-bookings"><a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_modification_date=1&amp;wh_booking_date=3" class="">Bookings, what done today</a> </td>
                                        </tr>
                                        <tr>
                                            <td class="first"> <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_booking_date=1"><span>0</span></a> </td>
                                            <td class="actual-bookings"> <a href="admin.php?page=booking/wpdev-booking.phpwpdev-booking&amp;wh_approved=&amp;wh_booking_date=1" class="">Bookings for today</a> </td>
                                        </tr>
                                    </table>
                                </div>
                                <div style="clear:both;margin-bottom:20px;"><xsl:comment/></div>

                                <div class="bk_dashboard_section" >
                                    <span class="bk_header">Current version:</span>
                                    <table class="bk_table">
                                        <tr class="first">
                                            <td style="width:35%;text-align: right;;" class="">Version:</td>
                                            <td style="text-align: left; font-weight: bold;" class="bk_spec_font">0.1</td>
                                        </tr>
                                        <tr>
                                            <td style="width:35%;text-align: right;" class="first b">Release date:</td>
                                            <td style="text-align: left;  font-weight: bold;" class="bk_spec_font">15.10.2012</td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="bk_dashboard_section bk_right">
                                    <span class="bk_header">Support:</span>
                                    <table class="bk_table">
                                        <tr class="first">
                                            <td style="text-align:center;" class="bk_spec_font"><a href="mailto:support@www.hostelbackoffice.net">Contact email</a></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://www.hostelbackoffice.net/faq/">FAQ</a></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://www.hostelbackoffice.net/help/">Have a questions?</a></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://wordpress.org/extend/plugins/hostel_backoffice">Rate plugin</a></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://www.hostelbackoffice.net/features/">Check other versions</a></td>
                                        </tr>
                                    </table>
                                </div>

                                <div style="clear:both;"><xsl:comment/></div>

                                <div style="width:95%;border:none; clear:both;margin:10px 0px;" id="bk_news_section"> <!-- Section 4 -->

                                    <div style="width: 96%; margin-right: 0px;; ">
                                        <span class="bk_header">Hostel Backoffice News:</span>
                                        <br/><br/>
                                        <div id="bk_news"> <span style="font-size:11px;text-align:center;">Loading...</span></div>
                                        <div id="ajax_bk_respond"><xsl:comment/></div>
                                        <script type="text/javascript">

                                            jQuery.ajax({                                           // Start Ajax Sending
                                                url: 'http://localhost:18560/wp-content/plugins/booking/wpdev-booking.php' ,
                                                type:'POST',
                                                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_bk_respond').html( data );},
                                                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;
                                                    //alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
                                                    //if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#faq-13');}
                                                },
                                                // beforeSend: someFunction,
                                                data:{
                                                    ajax_action : 'CHECK_BK_NEWS' 
                                                }
                                            });

                                        </script>                           
                                    </div>
                                </div>
                                <div style="clear:both;"><xsl:comment/></div>
                            </div>
                            <div style="clear:both;"><xsl:comment/></div>
                        </div>
                    </div>
                </div>

                <div class='meta-box'>
                    <div id="bk_general_settings_users_permissions" class="postbox"> 
                        <div title="Click to toggle" class="handlediv" onclick="javascript:verify_window_opening(1, 'bk_general_settings_users_permissions');"><br/></div>
                        <h3 class='hndle'><span>User permissions for plugin menu pages</span></h3> 
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <td colspan="2">
                                            <span class="description">Select user access level for the menu pages of plugin</span>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row"><label for="start_day_weeek" >Bookings:</label><br/>menu page</th>
                                        <td>
                                            <select id="user_role_booking" name="user_role_booking">
                                                <option  value="subscriber" >Subscriber</option>
                                                <option  value="administrator" >Administrator</option>
                                                <option selected="selected" value="editor" >Editor</option>
                                                <option  value="author" >Author</option>
                                                <option  value="contributor" >Contributor</option>
                                            </select>                                                
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><label for="start_day_weeek" >Add booking:</label><br/>access level</th>
                                        <td>
                                            <select id="user_role_addbooking" name="user_role_addbooking">
                                                <option  value="subscriber" >Subscriber</option>
                                                <option  value="administrator" >Administrator</option>
                                                <option selected="selected" value="editor" >Editor</option>
                                                <option  value="author" >Author</option>
                                                <option  value="contributor" >Contributor</option>
                                            </select>
                                        </td>
                                    </tr>
                                        
                                    <tr valign="top">
                                        <th scope="row"><label for="start_day_weeek" >Settings:</label><br/>access level</th>
                                        <td>
                                            <select id="user_role_settings" name="user_role_settings">
                                                <option  value="subscriber" >Subscriber</option>
                                                <option selected="selected" value="administrator" >Administrator</option>
                                                <option  value="editor" >Editor</option>
                                                <option  value="author" >Author</option>
                                                <option  value="contributor" >Contributor</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class='meta-box'>
                    <div id="bk_general_settings_uninstall" class="postbox"> 
                        <h3 class='hndle'><span>Uninstall / Deactivate</span></h3> 
                        <div class="inside">
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><label for="is_delete_if_deactive" >Delete all data:</label><br/>when plugin is deactivated</th>
                                        <td><input id="hbo_delete_db_on_deactivate" type="checkbox" value="{delete_on_deactivate}" name="hbo_delete_db_on_deactivate"
                                                onclick="javascript: if (this.checked) {{ var answer = confirm('Warning! If you check this option, all booking data will be deleted when plugin is deactivated. Do you really want to do this?'); if ( answer){{ this.checked = true;}} else {{this.checked = false;}} }}">
                                                <xsl:if test="delete_on_deactivate = 'On'">
                                                    <xsl:attribute name="checked">checked</xsl:attribute>
                                                </xsl:if>
                                            </input>
                                            <span class="description"> Check, if you want delete booking data when plugin is deactivated.</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="clear" style="height:10px;"><xsl:comment/></div>
            <input class="button-primary" style="float:right;" type="submit" value="Save Changes" name="Submit"/>
            <div class="clear" style="height:10px;"><xsl:comment/></div>
        </form>
    </div>   

</xsl:template>

</xsl:stylesheet>