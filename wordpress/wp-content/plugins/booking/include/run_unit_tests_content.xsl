<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->
<xsl:template match="view">
    <div style="width:100%;margin:15px auto;font-style:italic">
        <ul>
            <xsl:apply-templates select="unitTest"/><xsl:comment/>
        </ul>
    </div>
    <div id="submitting"><xsl:comment/></div>
</xsl:template>

<xsl:template match="unitTest">
    <li><xsl:value-of select="."/></li>
</xsl:template>

</xsl:stylesheet>