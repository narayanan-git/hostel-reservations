<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

<!--
//*****************************************************************************
// Distributed under the GNU General Public Licence
//*****************************************************************************
-->

<xsl:template match="/allocations">

<table width="100%" cellspacing="0" cellpadding="0" border="0">

    <tbody>
        <tr valign="top">
            <td width="200">
                <div class="roomTypeCol">
                    <table class="roomtype" width="200" cellspacing="0" cellpadding="3" border="0">
                        <tbody>
                            <tr>
                                <td>Name</td>
                            </tr>
                            <xsl:apply-templates select="allocation" mode="allocation_name"/>
                        </tbody>
                    </table>
                </div>
            </td>
            <td width="200">
                <div class="roomTypeCol">
                    <table class="roomtype" width="200" cellspacing="0" cellpadding="3" border="0">
                        <tbody>
                            <tr>
                                <td>Gender</td>
                            </tr>
                            <xsl:apply-templates select="allocation" mode="allocation_gender"/>
                        </tbody>
                    </table>
                </div>
            </td>
            <td width="200">
                <div class="roomTypeCol">
                    <table class="roomtype" width="200" cellspacing="0" cellpadding="3" border="0">
                        <tbody>
                            <tr>
                                <td>Room Type</td>
                            </tr>
                            <xsl:apply-templates select="allocation" mode="allocation_resource"/>
                        </tbody>
                    </table>
                </div>
            </td>
            <td width="470" valign="top">
                <table class="availability" width="100%" cellspacing="0" cellpadding="3" border="0">
                    <caption><xsl:value-of select="dateheaders/header"/></caption>
                    <thead>
                        <tr>
                            <xsl:apply-templates select="dateheaders/datecol" mode="availability_date_header"/>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:apply-templates select="allocation/dates" mode="allocation_dates"/>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
<!--
    <caption>sample allocation table</caption>
    <thead>
      <tr>
        <th>Name</th>
        <th>Gender</th>
        <th>Bed</th>
        <th>Dates</th>
      </tr>
    </thead>
    <tbody>
        <xsl:apply-templates select="//allocation" />
    </tbody>
-->
</table>
  
</xsl:template>

<!-- xsl:template match="allocation">

  <tr>
    <xsl:attribute name="class">
      <xsl:choose>
        <xsl:when test="position() mod 2">odd</xsl:when>
        <xsl:otherwise>even</xsl:otherwise>
      </xsl:choose>
    </xsl:attribute>

    <td><xsl:value-of select="name"/></td>
    <td><xsl:value-of select="gender"/></td>
    <td><xsl:value-of select="resource"/></td>
  </tr>

</xsl:template -->


<!-- adds table row entry for allocation name attribute -->
<xsl:template mode="allocation_name" match="allocation">
    <tr>
        <xsl:call-template name="row_class">
            <xsl:with-param name="posn"><xsl:value-of select="position()"/></xsl:with-param>
        </xsl:call-template>
        <td><xsl:value-of select="name"/></td>
    </tr>
</xsl:template>

<!-- adds table row entry for allocation gender attribute -->
<xsl:template mode="allocation_gender" match="allocation">
    <tr>
        <xsl:call-template name="row_class">
            <xsl:with-param name="posn"><xsl:value-of select="position()"/></xsl:with-param>
        </xsl:call-template>
        <td><xsl:value-of select="gender"/></td>
    </tr>
</xsl:template>

<!-- adds table row entry for allocation resource attribute -->
<xsl:template mode="allocation_resource" match="allocation">
    <tr>
        <xsl:call-template name="row_class">
            <xsl:with-param name="posn"><xsl:value-of select="position()"/></xsl:with-param>
        </xsl:call-template>
        <td><xsl:value-of select="resource"/></td>
    </tr>
</xsl:template>

<!-- adds "class" attribute to a table row depending on position -->
<xsl:template name="row_class" match="text()">
    <xsl:param name="posn" select="'0'" />
    <xsl:attribute name="class">
        <xsl:choose>
            <xsl:when test="$posn mod 2">odd</xsl:when>
            <xsl:otherwise>even</xsl:otherwise>
        </xsl:choose>
    </xsl:attribute>
</xsl:template>

<!-- adds header entries for the availability table -->
<xsl:template mode="availability_date_header" match="datecol">
    <th><xsl:value-of select="date"/><span><xsl:value-of select="day"/></span></th>
</xsl:template>

<!-- adds row for each allocation in the availability table -->
<xsl:template mode="allocation_dates" match="dates">
    <tr>
        <xsl:apply-templates select="date" mode="allocation_date"/>
    </tr>
</xsl:template>

<!-- adds table entries for each allocation in the availability table -->
<xsl:template mode="allocation_date" match="date">
    <td><xsl:attribute name="class">
            <xsl:choose>
                <xsl:when test="@state = 'inactive'">inactive</xsl:when>
                <xsl:when test="@state = 'pending'">pending</xsl:when>
                <xsl:otherwise><xsl:value-of select="@state"/></xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>
        <xsl:value-of select="@payment"/>
    </td>
</xsl:template>

</xsl:stylesheet>