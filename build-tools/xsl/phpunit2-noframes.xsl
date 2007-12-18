<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:decimal-format decimal-separator="." grouping-separator="," />

    <!--
    Root template
    -->
    <xsl:template match="/">
        <script type="text/javascript" language="JavaScript">
            <!--
            Function show/hide given div
            -->
            function toggleDivVisibility(_div) {
                if (_div.style.display=="none") {
                    _div.style.display="block";
                } else {
                    _div.style.display="none";
                }
            }
        </script>

        <!-- Main table -->
        <div class="phpunit">
            <h2>PHPUnit Test Results</h2>
            <table class="result">
                <colgroup>
                    <col width="10%"/>
                    <col width="45%"/>
                    <col width="25%"/>
                    <col width="10%"/>
                    <col width="10%"/>
                </colgroup>
                <thead>
                    <tr>
                        <th colspan="3">Name</th>
                        <th>Status</th>
                        <th nowrap="nowrap">Time(s)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- display test suites -->
                    <xsl:apply-templates select="//testsuite">
                        <xsl:sort select="count(testcase/error)" data-type="number" order="descending"/>
                        <xsl:sort select="count(testcase/failure)" data-type="number" order="descending"/>
                        <xsl:sort select="@package"/>
                        <xsl:sort select="@name"/>
                    </xsl:apply-templates>
                </tbody>
            </table>
        </div>
    </xsl:template>

    <!--
    Test Suite Template
    Construct TestSuite section
    -->
    <xsl:template match="testsuite">
        <tr>
            <xsl:attribute name="class">
                <xsl:choose>
                    <xsl:when test="testcase/error">error</xsl:when>
                    <xsl:when test="testcase/failure">failure</xsl:when>
                </xsl:choose>
            </xsl:attribute>
            <th colspan="5"><xsl:value-of select="concat(@package,'.',@name)"/></th>
        </tr>
        <!-- Display tests -->
        <xsl:apply-templates select="testcase"/>
        <!-- Display details links -->
        <xsl:apply-templates select="current()" mode="details"/>
    </xsl:template>

    <!--
    Testcase template
    Construct testcase section
    -->
    <xsl:template match="testcase">
        <tr>
            <xsl:attribute name="class">
                <xsl:choose>
                    <xsl:when test="error">
                        <xsl:text>error</xsl:text>
                        <xsl:if test="position() mod 2 = 0">
                            <xsl:text> oddrow</xsl:text>
                        </xsl:if>
                    </xsl:when>
                    <xsl:when test="failure">
                        <xsl:text>failure</xsl:text>
                        <xsl:if test="position() mod 2 = 0">
                            <xsl:text> oddrow</xsl:text>
                        </xsl:if>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text>success</xsl:text>
                        <xsl:if test="position() mod 2 = 0">
                            <xsl:text> oddrow</xsl:text>
                        </xsl:if>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            <td colspan="3">
                <xsl:attribute name="class">
                    <xsl:choose>
                        <xsl:when test="error">
                            <xsl:text>error</xsl:text>
                        </xsl:when>
                        <xsl:when test="failure">
                            <xsl:text>failure</xsl:text>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:text>success</xsl:text>
                        </xsl:otherwise>
                    </xsl:choose>
                </xsl:attribute>
                <xsl:value-of select="@name"/>
            </td>
            <td>
                <xsl:choose>
                    <xsl:when test="error">
                        <a href="javascript:void(0)"
                           onClick="toggleDivVisibility(document.getElementById('{concat('error.',../@package,'.',../@name,'.',@name)}'))">Error &#187;</a>
                    </xsl:when>
                    <xsl:when test="failure">
                        <a href="javascript:void(0)"
                           onClick="toggleDivVisibility(document.getElementById('{concat('failure.',../@package,'.',../@name,'.',@name)}'))">Failure &#187;</a>
                    </xsl:when>
                    <xsl:otherwise>Success</xsl:otherwise>
                </xsl:choose>
            </td>
            <xsl:choose>
                <xsl:when test="not(failure|error)">
                    <td>
                        <xsl:value-of select="format-number(@time,'0.000')"/>
                    </td>
                </xsl:when>
                <xsl:otherwise>
                    <td/>
                </xsl:otherwise>
            </xsl:choose>
        </tr>
        <xsl:if test="error">
            <tr>
                <td colspan="3">
                    <span id="{concat('error.',../@package,'.',../@name,'.',@name)}" class="testresults-output-div" style="display: none;">
                        <h3>Error:</h3>
                        <xsl:apply-templates select="error/text()" mode="newline-to-br"/>
                    </span>
                </td>
                <td />
                <td />
            </tr>
        </xsl:if>
        <xsl:if test="failure">
            <tr>
                <td colspan="3">
                    <span id="{concat('failure.',../@package,'.',../@name,'.',@name)}" class="testresults-output" style="display: none;">
                        <h3>Failure:</h3>
                        <xsl:apply-templates select="failure/text()" mode="newline-to-br"/>
                    </span>
                </td>
                <td />
                <td />
            </tr>
        </xsl:if>
    </xsl:template>

    <!--
    Display Properties and Output links
    and construct hidden div's with data
    -->
    <xsl:template match="testsuite" mode="details">
        <tr class="unittests-data">
            <td colspan="2">
                <xsl:if test="count(properties/property)&gt;0">
                    <a class="failure" href="javascript:void(0)" onClick="toggleDivVisibility(document.getElementById('{concat('properties.',@package,'.',@name)}'))">Properties &#187;</a>
                </xsl:if>&#xA0;
            </td>
            <td>
                <xsl:if test="system-out/text()">
                    <a class="failure" href="javascript:void(0)" onClick="toggleDivVisibility(document.getElementById('{concat('system_out.',@package,'.',@name)}'))">System.out &#187;</a>
                </xsl:if>&#xA0;
            </td>
            <td>
                <xsl:if test="system-err/text()">
                    <a class="failure" href="javascript:void(0)" onClick="toggleDivVisibility(document.getElementById('{concat('system_err.',@package,'.',@name)}'))">System.err &#187;</a>
                </xsl:if>&#xA0;
            </td>
            <td>&#xA0;</td>
        </tr>
        <tr>
            <td colspan="5">
                <!-- Construct details div's -->
                <!-- System Error -->
                <xsl:apply-templates select="system-err" mode="system-err-div">
                    <xsl:with-param name="div-id" select="concat('system_err.',@package,'.',@name)"/>
                </xsl:apply-templates>
                <!-- System Output -->
                <xsl:apply-templates select="system-out" mode="system-out-div">
                    <xsl:with-param name="div-id" select="concat('system_out.',@package,'.',@name)"/>
                </xsl:apply-templates>
                <!-- Properties -->
                <xsl:apply-templates select="properties" mode="properties-div">
                    <xsl:with-param name="div-id" select="concat('properties.',@package,'.',@name)"/>
                </xsl:apply-templates>
                &#xA0;
            </td>
        </tr>
    </xsl:template>

    <!--
    Create div with detailed system output
    -->
    <xsl:template match="system-out" mode="system-out-div" >
        <xsl:param name="div-id"/>
        <span id="{$div-id}" class="testresults-output" style="display: none;">
        <span style="font-weight:bold">System out:</span><br/>
            <xsl:apply-templates select="current()" mode="newline-to-br"/>
        </span>
    </xsl:template>

    <!--
    Create div with detailed errors output
    -->
    <xsl:template match="system-err" mode="system-err-div" >
        <xsl:param name="div-id"/>
        <span id="{$div-id}" class="testresults-output" style="display: none;">
        <span style="font-weight:bold">System err:</span><br/>
            <xsl:apply-templates select="current()" mode="newline-to-br"/>
        </span>
    </xsl:template>

    <!--
    Create div with properties
    -->
    <xsl:template match="properties" mode="properties-div" >
        <xsl:param name="div-id"/>
        <div id="{$div-id}" class="testresults-output" style="display: none;">
            <span style="font-weight:bold">Properties:</span><br/>
            <table>
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
                <xsl:for-each select="property">
                    <xsl:sort select="@name"/>
                    <tr>
                        <td><xsl:value-of select="@name"/>&#xA0;</td>
                        <td><xsl:value-of select="@value"/>&#xA0;</td>
                    </tr>
                </xsl:for-each>
            </table>
        </div>
    </xsl:template>

    <!--
    Convert line brakes in given text into <br/>
    -->
    <xsl:template match="text()" mode="newline-to-br">
        <xsl:call-template name="replace">
            <xsl:with-param name="string" select="current()"/>
            <xsl:with-param name="from" select="'&#xa;'"/>
            <xsl:with-param name="to" select="'&lt;br/&gt;'"/>
        </xsl:call-template>
    </xsl:template>

    <!-- reusable replace-string function -->
    <xsl:template name="replace">
        <xsl:param name="string"/>
        <xsl:choose>
            <xsl:when test="contains($string,'&#10;')">
                <xsl:value-of select="substring-before($string,'&#10;')" />
                <br />
                <xsl:call-template name="replace">
                    <xsl:with-param name="string"
                                    select="substring-after($string,'&#10;')"/>
                    </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$string" />
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

</xsl:stylesheet>
