<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:include href="daily_summary_data.xsl"/>

<xsl:template match="/view">

    <div id="wpdev-booking-general" class="wrap bookingpage">
        <div class="icon32" style="margin:10px 25px 10px 10px;"><img src="/wp-content/plugins/booking/img/notebook-48x48.jpg"/><br /></div>
        <h2>Daily Summary</h2>

        <div class="wpdevbk center">
            <h3 id="selected_date_label"><xsl:comment/></h3>
        </div>
    
        <div class="center">
            <div id="calendar_selected_date">&#160;</div>
            <textarea rows="3" cols="50" id="calendar_selected_date" name="calendar_selected_date" style="display:none;"><xsl:comment/></textarea>
        </div>
    
        <div style="clear:both;height:40px;"><xsl:comment/></div>

        <div id="daily_summary_contents">
            <xsl:call-template name="daily_summary_contents"/>
        </div>
    
        <div id="ajax_respond"><xsl:comment/><!-- ajax response here--></div>
    
        <script type="text/javascript">
            jQuery(document).ready( function(){
                jQuery('#calendar_selected_date').datepick(
                    {   showOn: 'focus',
                        multiSelect: 0,
                        defaultDate: 0,
                        selectDefaultDate: true,
                        numberOfMonths: 1,
                        stepMonths: 1,
                        prevText: '&lt;&lt;',
                        nextText: '&gt;&gt;',
                        dateFormat: 'dd.mm.yy',
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
                        mandatory: true,
                        onSelect: function(thedate) { 
                            var parsedDate = jQuery.datepick.parseDate('dd.mm.yy', thedate);
                            var formattedDate = jQuery.datepick.formatDate('D, MM d, yy', parsedDate);
                            jQuery('#selected_date_label').html(formattedDate);
                            select_daily_summary_date(thedate);
                        } 
                    }
                );
                jQuery('#calendar_selected_date').datepick('setDate', '<xsl:value-of select="selectiondate"/>');

                // pre-populate label on first time through
                var parsedDate = jQuery.datepick.parseDate('dd.mm.yy', '<xsl:value-of select="selectiondate"/>');
                var formattedDate = jQuery.datepick.formatDate('D, MM d, yy', parsedDate);
                jQuery('#selected_date_label').html(formattedDate);
        });
        </script>
    </div>
</xsl:template>

</xsl:stylesheet>