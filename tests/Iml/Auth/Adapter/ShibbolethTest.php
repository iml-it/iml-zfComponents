<?php

/**
 * IML Zend Framework Components
 *
 * LICENSE
 *
 * This work is licensed under the Creative Commons Attribution-Share Alike 2.5
 * Switzerland License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-sa/2.5/ch/ or send a letter to
 * Creative Commons, 171 Second Street, Suite 300, San Francisco, California,
 * 94105, USA.
 *
 * @category   Iml
 * @package    Iml_Auth
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2007 Institute for Medical Education,
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Test helper
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR
             . 'TestHelper.php';

/**
 * Iml_Auth_Adapter_Shibboleth
 */
require_once 'Iml/Auth/Adapter/Shibboleth.php';

/**
 * Zend_Auth
 */
require_once 'Zend/Auth.php';

/**
 * Zend_Auth_Exception
 */
require_once 'Zend/Auth/Exception.php';


/**
 * @category   Iml
 * @package    Iml_Auth
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2007 Institute for Medical Education,
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Auth_Adapter_ShibbolethTest extends PHPUnit_Framework_TestCase
{
    /**
     * Name of the identity field.
     *
     * @var string
     */
    protected $_identityField = null;

    /**
     * A key map mapping shib variables to app variables
     */
    protected $_keyMap = array();

    /**
     * Setup fixtures for the tests. This includes fake variables
     * in $_SERVER some other variables.
     */
    protected function setUp()
    {
        // emulate shibdaemon + apache setting up environment variables
        $_SERVER['HTTP_SHIB_SWISSEP_UNIQUEID']   = 'demouser@unibe.ch';
        $_SERVER['HTTP_SHIB_INETORGPERSON_MAIL'] = 'demouser@test.unibe.ch';
        $_SERVER['HTTP_SHIB_INETORGPERSON_GIVENNAME'] = 'demo';
        $_SERVER['HTTP_SHIB_PERSON_SURNAME']     = 'User';
        $_SERVER['HTTP_SHIB_EP_AFFILIATION']     = 'staff';
        
        // the identity field name to use for the tests
        $this->_identityField = 'HTTP_SHIB_SWISSEP_UNIQUEID';
        
        // a key map to use for the tests
        $this->_keyMap = array(
            'HTTP_SHIB_SWISSEP_UNIQUEID'        => 'id',
            'HTTP_SHIB_INETORGPERSON_MAIL'      => 'email',
            'HTTP_SHIB_INETORGPERSON_GIVENNAME' => 'firstname',
            'HTTP_SHIB_PERSON_SURNAME'          => 'name',
        );
        
    }

    /**
     * Destroy fixtures after test.
     *
     */
    protected function tearDown()
    {
        unset($_SERVER['HTTP_SHIB_SWISSEP_UNIQUEID']);
        unset($_SERVER['HTTP_SHIB_INETORGPERSON_MAIL']);
        unset($_SERVER['HTTP_SHIB_INETORGPERSON_GIVENNAME']);
        unset($_SERVER['HTTP_SHIB_PERSON_SURNAME']);
    }
    
    /**
     * Test setting parameters in constructor.
     */
    public function testSettingParameterInConstructor()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth($this->_identityField,
                                                $this->_keyMap
                                                );
        $this->assertAttributeContains($this->_identityField, 
                                       '_identityField',
                                       $auth
                                       );
        $this->assertAttributeContains('email', '_keyMap', $auth);
    }

    /**
     * Test if authenticate throws if no name for the identiyfield was given.
     */
    public function testNoIdentiyFieldThrows()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth();
        try {
            $auth->authenticate();
            $this->fail();
        } catch (Exception $e) {
            $this->assertType('Zend_Auth_Exception', $e);
            $this->assertRegExp('/identity field/i', $e->getMessage());
        }
    }
    
    /**
     * Test a failed authentication due to no shib session available.
     */
    public function testNoShibSession()
    {
        // unset identity field in $_SERVER
        unset($_SERVER[$this->_identityField]);
        
        // build auth adapter
        $auth = new Iml_Auth_Adapter_Shibboleth();
        $auth->setIdentityField($this->_identityField);
        $result = $auth->authenticate();
        $this->assertFalse($result->isValid());
        $this->assertEquals(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                            $result->getCode()
                            );
        $messages = $result->getMessages();
        $this->assertContains('shibboleth session', $messages[0]);
    }
    
    /**
     * Test if setting/clearing and asking for key map works
     */   
    public function testSettingClearingKeyMap()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth();
        $auth->setKeyMap($this->_keyMap);
        $this->assertTrue($auth->hasKeyMap(), 
                          'Adapter should have a key map.'
                          );
        $auth->clearKeyMap();
        $this->assertFalse($auth->hasKeyMap(), 
                           'Adapter should not have a key map.'
                           );
    }
    
    /**
     * Test if setKeyMap() throws if no array was provided.
     */
    public function testSettingKeyMapWithWrongType()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth();
        try {
            $auth->setKeyMap('a string');
            $this->fail();
        } catch (Exception $e) {
            $this->assertType('Zend_Auth_Exception', $e);
            $this->assertRegExp('/array of key\/value/i', $e->getMessage());
        }
    }
    
    /**
     * Test if a direct authentication works.
     */
    public function testDirectAuthentication()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth();
        $auth->setIdentityField($this->_identityField);
        $result = $auth->authenticate();
        $this->assertTrue($result instanceof Zend_Auth_Result);
        $this->assertTrue($result->isValid());
        $identity = $result->getIdentity();
        $this->assertType('array', $identity);
        $this->assertArrayHasKey($this->_identityField, $identity);
        $this->assertEquals('demouser@unibe.ch', 
                            $identity[$this->_identityField]);
    }
    
    /**
     * Test if a direct authentication works using a keymap.
     */
    public function testDirectAuthenticationWithKeyMap()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth();
        $auth->setIdentityField($this->_identityField);
        $auth->setKeyMap($this->_keyMap);
        $result = $auth->authenticate();
        $this->assertTrue($result instanceof Zend_Auth_Result);
        $this->assertTrue($result->isValid());
        $identity = $result->getIdentity();
        $this->assertType('array', $identity);
        $this->assertArrayHasKey($this->_keyMap[$this->_identityField], 
                                 $identity
                                 );
        $this->assertEquals('demouser@unibe.ch', 
                            $identity[$this->_keyMap[$this->_identityField]]
                            );
    }
    
    /**
     * Test if a indirect authentication works
     */
    public function testIndirectAuthentication()
    {
        $auth_adapter = new Iml_Auth_Adapter_Shibboleth();
        $auth_adapter->setIdentityField($this->_identityField);
        $auth = Zend_Auth::getInstance();
        $auth->authenticate($auth_adapter);
        $this->assertTrue($auth->hasIdentity());
        $identity = $auth->getIdentity();
        $this->assertType('array', $identity);
        $this->assertArrayHasKey($this->_identityField, 
                                 $identity
                                 );
        $this->assertEquals('demouser@unibe.ch', 
                            $identity[$this->_identityField]
                            );
    }

    /**
     * Test if adapter only sets attributes in identity which are
     * specified in the key map.
     */
    public function testIdentityContentWhenUsingKeyMap()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth();
        $auth->setIdentityField($this->_identityField);
        $auth->setKeyMap($this->_keyMap);
        $result = $auth->authenticate();
        $identity = $result->getIdentity();
        $this->assertEquals(count($this->_keyMap), count($identity));
    }
}
