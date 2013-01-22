<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">
    <h3>Reset Sample Test Data</h3>
    This will *DELETE* all current data and recreate sample test data...
    <form class="booking_form" method="post" action="">
        <div style="float:left; text-align:left; width:450px;">
            <div id="ajax_respond">
                <div id="submitting"><xsl:comment/></div>
            </div>
            <div style="float:left; text-align:left; width:450px;">
                <p><input type="button" value="Generate" onclick="generate_test_data(this.form);" /></p>
            </div>
        </div>
    </form>
</xsl:template>

</xsl:stylesheet>