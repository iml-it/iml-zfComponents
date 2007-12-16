<?php

/**
 * IML Zend Framework Components
 *
 * LICENSE
 *
 * This work is licensed under the Creative Commons Attribution-Share Alike 2.5
 * Switzerland License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-sa/2.5/ch/ or send a letter to Creative
 * Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
 *
 * @category   Iml
 * @package    Iml_Debug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Test helper
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';

/**
 * Iml_Debug
 */
require_once 'Iml/Debug.php';

/**
 * Iml_Debug_Exception
 */
require_once 'Iml/Debug/Exception.php';

/**
 * Zend_Log_Formatter_Simple
 */
require_once 'Zend/Log/Formatter/Simple.php';

/**
 * @category   Iml
 * @package    Iml_Debug
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_DebugTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test event array
     *
     * @var array
     */
    protected $_events = array();

    /**
     * Expected value for cli output.
     */
    protected $_expected_cli = null;

    /**
     * Expected value for other cases.
     */
    protected $_expected = null;


    /**
     * Setup fixtures for the tests. Most tests use
     * an array with a test event and the respective
     * expected output.
     */
    protected function setUp()
    {
        $this->_events[] = array(
                            'timestamp' => '2007-1-1T12:00:0+01:00',
                            'message' => 'Test log message',
                            'priority' => 7,
                            'priorityName' => 'DEBUG',
                            );

        $this->_expected_cli = $this->_events[0]['timestamp'] . ' '
                             . $this->_events[0]['priorityName'] . ' (' . $this->_events[0]['priority'] . '): '
                             . $this->_events[0]['message'] . PHP_EOL;

        $this->_expected     = '<table>' . PHP_EOL
                             . '<tr><td>' . $this->_events[0]['timestamp'] . '</td>'
                             . '<td>' . $this->_events[0]['priorityName'] . ' (' . $this->_events[0]['priority'] . ')</td>'
                             . '<td>' . $this->_events[0]['message'] . '</td>'
                             . '</tr>' . PHP_EOL . '</table>' . PHP_EOL;
    }

    /**
     * Destroy fixtures after test.
     *
     */
    protected function tearDown()
    {
        $this->_events = array();
    }

    /**
     * Test if dumpLogEvents() throws an exception if it's
     * getting a wrong parameter type on call.
     */
    public function testWrongParamThrows()
    {
        Iml_Debug::setSapi('cli');
        try {
            $result = Iml_Debug::dumpLogEvents('a string', null, false);
            $this->fail();
        } catch (Exception $e) {
            $this->assertType('Iml_Debug_Exception', $e);
            $this->assertRegExp('/expected for argument/i', $e->getMessage());
        }
    }

    /**
     * Test case for the debug output in cli mode
     */
    public function testDebugDumpCli()
    {
        Iml_Debug::setSapi('cli');
        $result = Iml_Debug::dumpLogEvents($this->_events, null, false);
        $this->assertEquals($this->_expected_cli, $result);
    }

    /**
     * Test case for the debug output in other modes
     */
    public function testDebugDumpOther()
    {
        Iml_Debug::setSapi('cgi');
        $result = Iml_Debug::dumpLogEvents($this->_events, null, false);
        $this->assertEquals($this->_expected, $result);
    }

    /**
     * Test cli mode with directly echoing the output
     */
    public function testDebugDumpCliEcho()
    {
        Iml_Debug::setSapi('cli');

        ob_start();
        $result1 = Iml_Debug::dumpLogEvents($this->_events, null, true);
        $result2 = ob_get_contents();
        ob_end_clean();

        $this->assertContains($this->_expected_cli, $result1);
        $this->assertEquals($result1, $result2);
    }

    /**
     * Test cli mode with a label
     */
    public function testDebugDumpCliLabel()
    {
        Iml_Debug::setSapi('cli');
        $label = '<h1>A LABEL</h1>';
        $result = Iml_Debug::dumpLogEvents($this->_events, $label, false);
        $expected = strip_tags($label) . ': ' . $this->_expected_cli;
        $this->assertEquals($expected, $result);
    }

    /**
     * Test other modes with a label
     */
    public function testDebugDumpOtherLabel()
    {
        Iml_Debug::setSapi('cgi');
        $label = '<h1>A LABEL</h1>';
        $result = Iml_Debug::dumpLogEvents($this->_events, $label, false);
        $expected = strip_tags($label) . ': ' . PHP_EOL .$this->_expected;
        $this->assertEquals($expected, $result);
    }

}
