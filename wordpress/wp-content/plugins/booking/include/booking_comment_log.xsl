<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="comment">
    <p><xsl:value-of select="createdDate"/> [<xsl:value-of select="createdBy"/>]: <xsl:value-of select="value"/></p>
</xsl:template>

</xsl:stylesheet>