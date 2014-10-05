<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<xsl:template name="write_inline_js">

    <script type="text/javascript">
        jQuery(document).ready( function(){
            jQuery('input.wpdevbk-filters-section-calendar').datepicker(
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
/**********

        TODO: FIX TOOLTIPS 

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
**************/
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