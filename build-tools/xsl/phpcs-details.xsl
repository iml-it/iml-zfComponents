<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:decimal-format decimal-separator="." grouping-separator="," />
    <xsl:include href="xsl/phphelper.xsl" />
    <xsl:param name="checkstyle.hide.warnings" select="'false'"/>

    <xsl:template match="/">
        <xsl:variable name="total.error.count" select="count(checkstyle/file/error[@severity='error'])" />
        <xsl:variable name="total.warning.count" select="count(checkstyle/file/error[@severity='warning'])" />

        <div class="codesniffer">
            <xsl:call-template name="checkstyle-summary" />
            <h2>Details</h2>
            <xsl:apply-templates />
        </div>
    </xsl:template>

    <xsl:template match="file">
        <xsl:param name="total.error.count" select="count(error[@severity='error'])" />
        <xsl:param name="total.warning.count" select="count(error[@severity='warning'])" />

        <xsl:variable name="filename" select="translate(@name,'\','/')"/>
        <xsl:variable name="phpclass">
            <xsl:call-template name="phpname">
                <xsl:with-param name="filename" select="$filename"/>
            </xsl:call-template>
        </xsl:variable>

        <table class="result">
            <tr>
                <th class="checkstyle-data" colspan="2">
                    <xsl:value-of select="$phpclass" />
                    (Errors: <xsl:value-of select="$total.error.count"/>
                    / Warnings: <xsl:value-of select="$total.warning.count"/>)
                </th>
            </tr>
            <xsl:choose>
                <xsl:when test="$checkstyle.hide.warnings = 'true' and $total.error.count = 0">
                    <tr>
                        <td class="checkstyle-data" colspan="2">
                            <xsl:value-of select="$total.warning.count"/> warnings
                        </td>
                    </tr>
                </xsl:when>
                <xsl:when test="$checkstyle.hide.warnings = 'true'">
                    <xsl:apply-templates select="error[@severity='error']" />
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="error" />
                </xsl:otherwise>
            </xsl:choose>
        </table>
    </xsl:template>

    <xsl:template match="error">
        <tr>
            <xsl:if test="position() mod 2 = 1">
                <xsl:attribute name="class">oddrow</xsl:attribute>
            </xsl:if>
            <td class="{@severity}">
                <xsl:value-of select="@line" />
            </td>
            <td>
                <xsl:value-of select="@message" />
            </td>
        </tr>
    </xsl:template>

    <xsl:template name="checkstyle-summary">
        <h2>PHP CodeSniffer</h2>
        <dl>
            <dt>Files: </dt>
            <dd><xsl:value-of select="count(/checkstyle/file[error])"/></dd>
            <dt>Errors: </dt>
            <dd><xsl:value-of select="count(/checkstyle/file/error[@severity='error'])"/></dd>
            <dt>Warnings: </dt>
            <dd><xsl:value-of select="count(/checkstyle/file/error[@severity='warning'])"/></dd>
        </dl>
    </xsl:template>

</xsl:stylesheet>
