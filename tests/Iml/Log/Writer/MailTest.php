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
 * @package    Iml_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'TestHelper.php';

/**
 * Iml_Log_Writer_Mail
 */
require_once 'Iml/Log/Writer/Mail.php';

/**
 * Zend_Mail
 */
require_once 'Zend/Mail.php';

/**
 * Zend_Mail_Transport_Abstract
 */
require_once 'Zend/Mail/Transport/Abstract.php';

/**
 * Mock mail transport class for testing purposes
 *
 * @category   Iml
 * @package    Iml_Mail
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Iml_Mail_Transport_Mock extends Zend_Mail_Transport_Abstract
{
    /**
     * @var Zend_Mail
     */
    public $mail       = null;
    public $returnPath = null;
    public $subject    = null;
    public $from       = null;
    public $called     = false;

    public function _sendMail()
    {
        $this->mail       = $this->_mail;
        $this->subject    = $this->_mail->getSubject();
        $this->from       = $this->_mail->getFrom();
        $this->returnPath = $this->_mail->getReturnPath();
        $this->called     = true;
    }
}

/**
 * @category   Iml
 * @package    Iml_Log
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Log_Writer_MailTest extends PHPUnit_Framework_TestCase
{
    /**
     * fixture for the mail transport mock object
     *
     * @var null|Iml_Mail_Transport_Mock
     */
    protected $_mock = null;

    /**
     * fixture for the mail object used in the tests
     *
     * @var null|Zend_Mail
     */
    protected $_mail = null;

    /**
     * Setup fixtures for the tests. Most tests use
     * an instance of Zend_Mail with a mock transport
     * to not actually send a mail. Properties of the
     * mail sent can be fetched from the mock transport.
     */
    protected function setUp()
    {
        $this->_mail = new Zend_Mail();
        $this->_mail->setFrom('testmail@example.com', 'test Mail User');
        $this->_mail->setSubject('Test Subject');
        $this->_mail->addTo('recipient1@example.com');

        $this->_mock = new Iml_Mail_Transport_Mock();
        $this->_mail->setDefaultTransport($this->_mock);
    }

    /**
     * Destroy fixtures after test.
     *
     */
    protected function tearDown()
    {
        $this->_mock = null;
        $this->_mail = null;
    }

    /**
     * Test case for the contructor which should throw an exception if
     * no valid Zend_Mail object is passed
     */
    public function testConstructorThrowException()
    {
        $notAZendMail = 'a simple string';
        try {
            new Iml_Log_Writer_Mail($notAZendMail);
            $this->fail();
        } catch (Exception $e) {
            $this->assertType('Zend_Log_Exception', $e);
            $this->assertRegExp('/must be an instance of Zend_Mail/i', $e->getMessage());
        }
    }

    /**
     * Test case for the constructor when provided with an instance
     * of Zend_Mail
     */
    public function testContructorOnCorrectInstantiation()
    {
        $mail = new Zend_Mail();
        new Iml_Log_Writer_Mail($mail);
    }

    /**
     * Test case for the write functionality; should send an email with the
     * formatted line in it
     */
    public function testWrite()
    {
        $event = array('message' => 'A test log message');

        $logwriter = new Iml_Log_Writer_Mail($this->_mail);
        $logwriter->write($event);

        $this->assertTrue($this->_mock->called);
        $this->assertContains('A test log message', $this->_mock->body);
    }

    /**
     * Test case to assert that write fails when shutdown was
     * called on the writer
     */
    public function testShutdownDestroysMailObject()
    {
        $event = array('message' => 'A test log message');

        $logwriter = new Iml_Log_Writer_Mail($this->_mail);
        $logwriter->shutdown();

        try {
            $logwriter->write($event);
            $this->fail();
        } catch (Exception $e) {
            $this->assertType('Zend_Log_Exception', $e);
            $this->assertRegExp('/No mail object available/i', $e->getMessage());
        }
    }

    /**
     * Test case to assert that a new formatter can be set
     */
    public function testSettingNewFormatter()
    {
        $expected = 'foo';

        $formatter = new Zend_Log_Formatter_Simple($expected);
        $logwriter = new Iml_Log_Writer_Mail($this->_mail);
        $logwriter->setFormatter($formatter);

        $logwriter->write(array('bar' => 'baz'));

        $this->assertContains($expected, $this->_mock->body);
    }
}
