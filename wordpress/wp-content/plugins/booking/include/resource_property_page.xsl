<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">
    <div class="resource_prop_container">
        <div style="margin-top:10px; height:1px; clear:both;"><xsl:comment/></div>
        <h3>Resource Properties for <xsl:value-of select="resourceName"/></h3>
        <form method="post" action="" name="post_resource_properties">
            <xsl:apply-templates select="properties/property"/>
            <xsl:if test="saved = 'true'">
                <p style="color:green">Update successful.</p>
            </xsl:if>
            <p><input class="button-secondary" type="submit" value="Save"/></p>
        </form>
    
        <xsl:if test="saved = 'true'">
            <script language="javascript">
                window.location.href="<xsl:value-of select="resources_url"/>";
            </script>
        </xsl:if>
    </div>
</xsl:template>

<xsl:template match="property">
    <p>
        <div style="margin-left:30px">
            <input type="checkbox" name="resource_property[]" value="{id}">
                <xsl:if test="@selected = 'true'"><xsl:attribute name="checked">checked</xsl:attribute></xsl:if>
                &#160;<xsl:value-of select="value"/>
            </input>
        </div>
    </p>
</xsl:template>

</xsl:stylesheet>