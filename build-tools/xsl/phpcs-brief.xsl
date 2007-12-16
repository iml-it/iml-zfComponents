<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:output method="html"/>
    <xsl:param name="checkstyle.hide.warnings" select="'false'"/>

    <xsl:template match="/">
        <xsl:variable name="total.error.count" select="count(checkstyle/file/error[@severity='error'])" />
        <xsl:variable name="total.warning.count" select="count(checkstyle/file/error[@severity='warning'])" />

        <div class="codesniffer">
            <xsl:call-template name="checkstyle-summary" />
        </div>
    </xsl:template>

    <xsl:template name="checkstyle-summary">
        <h2>PHP CodeSniffer Summary</h2>
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
