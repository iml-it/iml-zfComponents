<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
    xmlns:date="http://exslt.org/dates-and-times"
    extension-element-prefixes="date">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:decimal-format decimal-separator="." grouping-separator="," />
    <xsl:include href="xsl/phphelper.xsl" />
    <xsl:param name="checkstyle.hide.warnings" select="'false'"/>

    <xsl:template match="/">
        <xsl:variable name="total.error.count" select="count(checkstyle/file/error[@severity='error'])" />
        <xsl:variable name="total.warning.count" select="count(checkstyle/file/error[@severity='warning'])" />
        <html>
            <head>
                <title>Unit Test Results</title>
        <style type="text/css">
        body {
            font-family: verdana,arial,helvetica;
            color:#000000;
            font-size: 12px;
        }
        table.details {
            margin-bottom: 10px;
        }
        table tr td, table tr th {
            font-family: verdana,arial,helvetica;
            font-size: 12px;
        }
        table.details tr th{
            font-family: verdana,arial,helvetica;
            font-weight: bold;
            text-align:left;
            background:#a6caf0;
        }
        table.details tr td{
            background:#eeeee0;
        }

        p {
            line-height:1.5em;
            margin-top:0.5em; margin-bottom:1.0em;
            font-size: 12px;
        }
        h1 {
            margin: 0px 0px 5px;
            font-family: verdana,arial,helvetica;
        }
        h2 {
            margin-top: 1em; margin-bottom: 0.5em;
            font-family: verdana,arial,helvetica;
        }
        h3 {
            margin-bottom: 0.5em;
            font-family: verdana,arial,helvetica;
        }
        h4 {
            margin-bottom: 0.5em;
            font-family: verdana,arial,helvetica;
        }
        h5 {
            margin-bottom: 0.5em;
            font-family: verdana,arial,helvetica;
        }
        h6 {
            margin-bottom: 0.5em;
            font-family: verdana,arial,helvetica;
        }
        .error {
            font-weight:bold; color:red;
        }
        .warning {
            font-weight:bold; color:purple;
        }
        .small {
           font-size: 9px;
        }
        a {
          color: #003399;
        }
        a:hover {
          color: #888888;
        }
        </style>
            </head>
            <body>
                <a name="top"></a>
                <xsl:call-template name="pageHeader"/>
                <hr size="1"/>

                <!-- Summary part -->
                <h2>Summary</h2>
                <xsl:call-template name="summary"/>
                <hr size="1" />

                <!-- Details by file -->
                <h2>Details</h2>
                <xsl:apply-templates />

                <xsl:call-template name="pageFooter"/>
            </body>
        </html>
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

        <table class="details">
            <tr>
                <th colspan="2">
                    <xsl:value-of select="$phpclass" />
                    (Errors: <xsl:value-of select="$total.error.count"/>
                    / Warnings: <xsl:value-of select="$total.warning.count"/>)
                </th>
            </tr>
            <xsl:choose>
                <xsl:when test="$checkstyle.hide.warnings = 'true' and $total.error.count = 0">
                    <tr>
                        <td colspan="2">
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

    <xsl:template name="summary">
        <table>
            <tr>
                <td>Files:</td><td><xsl:value-of select="count(/checkstyle/file[error])"/></td>
            </tr>
            <tr>
                <td>Errors:</td><td><xsl:value-of select="count(/checkstyle/file/error[@severity='error'])"/></td>
            </tr>
            <tr>
                <td>Warnings:</td><td><xsl:value-of select="count(/checkstyle/file/error[@severity='warning'])"/></td>
            </tr>
        </table>
    </xsl:template>

    <!-- Page HEADER -->
    <xsl:template name="pageHeader">
        <h1>Code Sniffer Results</h1>
        <table width="100%">
        <tr>
            <td align="left"></td>
            <td align="right">Designed for use with <a href='http://pear.php.net/package/PHP_CodeSniffer'>PHP_CodeSniffer</a> and <a href='http://phing.info/'>Phing</a>.</td>
        </tr>
        </table>
    </xsl:template>

    <!-- Page Footer -->
    <xsl:template name="pageFooter">
        <table width="100%">
          <tr><td><hr noshade="yes" size="1"/></td></tr>
          <tr><td class="small">Report generated at <xsl:value-of select="date:date-time()"/></td></tr>
        </table>
    </xsl:template>

</xsl:stylesheet>
