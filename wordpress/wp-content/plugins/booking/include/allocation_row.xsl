<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="/">

<table border="0">
    <caption>sample table</caption>
    <thead>
      <tr>
        <th>Name</th>
        <th>Bed</th>
        <th>Dates</th>
      </tr>
    </thead>
    <tbody>
        <xsl:apply-templates select="//allocation" />
    </tbody>
</table>
  
</xsl:template>

<xsl:template match="allocation">

  <tr>
    <xsl:attribute name="class">
      <xsl:choose>
        <xsl:when test="position() mod 2">odd</xsl:when>
        <xsl:otherwise>even</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>

    <td><xsl:value-of select="name"/></td>
    <td><xsl:value-of select="resource"/></td>
  </tr>

</xsl:template>

</xsl:stylesheet>