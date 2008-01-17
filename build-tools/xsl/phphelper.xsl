<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:decimal-format decimal-separator="." grouping-separator="," />

    <xsl:template name="phpname">
        <xsl:param name="filename"/>
        <xsl:variable name="file" select="translate($filename, '\','/')" />
        <xsl:choose>
            <xsl:when test="contains($file, '/tests/') = true()">
                <xsl:value-of select="substring-after($file, '/tests/')"/>
            </xsl:when>
            <xsl:when test="contains($file, '/library/') = true()">
                <xsl:value-of select="substring-after($file, '/library/')"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$file" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
