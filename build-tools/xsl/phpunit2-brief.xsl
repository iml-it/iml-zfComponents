<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
                xmlns:lxslt="http://xml.apache.org/xslt">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:decimal-format decimal-separator="." grouping-separator="," />

    <xsl:variable name="testsuite.list" select="//testsuite"/>
    <xsl:variable name="testsuite.error.count" select="count($testsuite.list/error)"/>
    <xsl:variable name="testcase.list" select="$testsuite.list/testcase"/>
    <xsl:variable name="testcase.error.list" select="$testcase.list/error"/>
    <xsl:variable name="testcase.failure.list" select="$testcase.list/failure"/>
    <xsl:variable name="totalErrorsAndFailures" select="count($testcase.error.list) + count($testcase.failure.list) + $testsuite.error.count"/>

    <xsl:template match="/" mode="unittests">
        <div class="phpunit">
            <h2>PHPUnit Summary</h2>
            <table class="result" align="center">
                <thead>
                    <tr>
                        <th colspan="4">
                            Unit Tests: (<xsl:value-of select="count($testcase.list)"/>)
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <xsl:choose>
                        <xsl:when test="count($testsuite.list) = 0">
                            <tr>
                                <td colspan="2">No Tests Run</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="error">This project doesn't have any tests</td>
                            </tr>
                        </xsl:when>
                        <xsl:when test="$totalErrorsAndFailures = 0">
                            <tr>
                                <td colspan="2">All Tests Passed</td>
                            </tr>
                        </xsl:when>
                    </xsl:choose>
                    <xsl:apply-templates select="$testcase.error.list" mode="unittests"/>
                    <xsl:apply-templates select="$testcase.failure.list" mode="unittests"/>
                    <tr>
                        <td colspan="2">&#160;</td>
                    </tr>
                    <xsl:if test="$totalErrorsAndFailures > 0">
                    <tr>
                        <th colspan="4">
                            Unit Test Error Details: (<xsl:value-of select="$totalErrorsAndFailures"/>)
                        </th>
                    </tr>

                    <!-- (PENDING) Why doesn't this work if set up as variables up top? -->
                    <xsl:call-template name="testdetail">
                        <xsl:with-param name="detailnodes" select="//testsuite/testcase[.//error]"/>
                    </xsl:call-template>

                    <xsl:call-template name="testdetail">
                        <xsl:with-param name="detailnodes" select="//testsuite/testcase[.//failure]"/>
                    </xsl:call-template>
                    </xsl:if>
                </tbody>
            </table>
        </div>
    </xsl:template>

    <!-- UnitTest Errors -->
    <xsl:template match="error" mode="unittests">
        <tr>
            <xsl:if test="position() mod 2 = 1">
                <xsl:attribute name="class">oddrow</xsl:attribute>
            </xsl:if>
            <td class="error" width="50">error</td>
            <td width="300">
                <xsl:value-of select="../@name"/>
            </td>
            <td class="unittests-data" width="400">
                <xsl:value-of select="..//..//@name"/>
            </td>
        </tr>
    </xsl:template>

    <!-- UnitTest Failures -->
    <xsl:template match="failure" mode="unittests">
        <tr>
            <xsl:if test="($testsuite.error.count + position()) mod 2 = 1">
                <xsl:attribute name="class">oddrow</xsl:attribute>
            </xsl:if>
            <td class="failure" width="50">failure</td>
            <td width="300">
                <xsl:value-of select="../@name"/>
            </td>
            <td class="unittests-data" width="400">
                <xsl:value-of select="..//..//@name"/>
            </td>
        </tr>
    </xsl:template>

    <!-- UnitTest Errors And Failures Detail Template -->
    <xsl:template name="testdetail">
        <xsl:param name="detailnodes"/>

        <xsl:for-each select="$detailnodes">
            <tr>
                <td colspan="2">
                    <table width="100%" border="0" cellspacing="0">

                        <tr class="unittests-title">
                            <td width="50">Test:&#160;</td>
                            <td>
                                <xsl:value-of select="@name"/>
                            </td>
                        </tr>
                        <tr class="unittests-data">
                            <td>Class:&#160;</td>
                            <td>
                                <xsl:value-of select="..//@name"/>
                            </td>
                        </tr>

                        <xsl:if test="error">
                            <xsl:call-template name="test-data">
                                <xsl:with-param name="word" select="error"/>
                                <xsl:with-param name="type" select="'error'"/>
                            </xsl:call-template>
                        </xsl:if>

                        <xsl:if test="failure">
                            <xsl:call-template name="test-data">
                                <xsl:with-param name="word" select="failure"/>
                                <xsl:with-param name="type" select="'failure'"/>
                            </xsl:call-template>
                        </xsl:if>

                    </table>
                </td>
            </tr>
        </xsl:for-each>
    </xsl:template>

    <xsl:template name="test-data">
        <xsl:param name="word"/>
        <xsl:param name="type"/>
        <tr>
            <td />
            <td>
                <xsl:call-template name="stack-trace">
                    <xsl:with-param name="word" select="$word"/>
                    <xsl:with-param name="type" select="$type"/>
                </xsl:call-template>
            </td>
        </tr>
    </xsl:template>

    <xsl:template name="stack-trace">
        <xsl:param name="word"/>
        <xsl:param name="type"/>
        <table width="100%" border="1" cellspacing="0" cellpadding="2">
            <tr>
                <td>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <xsl:call-template name="br-replace">
                            <xsl:with-param name="word" select="$word"/>
                            <xsl:with-param name="type" select="$type"/>
                            <xsl:with-param name="count" select="0"/>
                        </xsl:call-template>
                    </table>
                </td>
            </tr>
        </table>
    </xsl:template>

    <xsl:template name="br-replace">
        <xsl:param name="word"/>
        <xsl:param name="type"/>
        <xsl:param name="count"/>
        <!-- </xsl:text> on next line on purpose to get newline -->
        <xsl:variable name="stackstart"><xsl:text>  at</xsl:text></xsl:variable>
        <xsl:variable name="cr"><xsl:text> </xsl:text></xsl:variable>
        <xsl:choose>
            <xsl:when test="contains($word,$cr)">
                <tr>
                    <xsl:attribute name="class">unittests-<xsl:value-of select="$type"/></xsl:attribute>
                    <xsl:if test="$count mod 2 != 0">
                        <xsl:attribute name="bgcolor">#EEEEEE</xsl:attribute>
                    </xsl:if>
                    <xsl:if test="$count != 0 and starts-with($word,$stackstart)">
                        <td width="30"/>
                        <td>
                            <xsl:value-of select="substring-before($word,$cr)"/>&#160;
                        </td>
                    </xsl:if>
                    <xsl:if test="$count != 0 and not(starts-with($word,$stackstart))">
                        <td colspan="2">
                            <xsl:value-of select="substring-before($word,$cr)"/>&#160;
                        </td>
                    </xsl:if>
                    <xsl:if test="$count = 0">
                        <td colspan="2">
                            <xsl:value-of select="substring-before($word,$cr)"/>&#160;
                        </td>
                    </xsl:if>
                </tr>
                <xsl:call-template name="br-replace">
                    <xsl:with-param name="word" select="substring-after($word,$cr)"/>
                    <xsl:with-param name="type" select="$type"/>
                    <xsl:with-param name="count" select="$count + 1"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <tr>
                    <xsl:attribute name="class">unittests-<xsl:value-of select="$type"/></xsl:attribute>
                    <xsl:if test="$count mod 2 != 0">
                        <xsl:attribute name="bgcolor">#EEEEEE</xsl:attribute>
                    </xsl:if>
                    <td width="30"/>
                    <td>
                        <xsl:value-of select="$word"/>
                    </td>
                </tr>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <xsl:template match="/">
        <xsl:apply-templates select="." mode="unittests"/>
    </xsl:template>
</xsl:stylesheet>
