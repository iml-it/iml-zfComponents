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
//require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR
//             . 'TestHelper.php';

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
     *
     * @var array
     */
    protected $_keyMap = array();

    /**
     * Default Shibboleth attribute prefix
     *
     * @var string
     */
    protected $_defaultAttributePrefix = 'Shib-';

    /**
     * Setup fixtures for the tests. This includes fake variables
     * in $_SERVER some other variables.
     */
    protected function setUp()
    {
        require_once 'Zend/Session.php';
        Zend_Session::$_unitTestEnabled = true;
        // emulate shibdaemon + apache setting up environment variables
        $_SERVER['Shib-SwissEP-UniqueID']   = 'demouser@unibe.ch';
        $_SERVER['Shib-InetOrgPerson-mail'] = 'demouser@test.unibe.ch';
        $_SERVER['Shib-InetOrgPerson-givenName'] = 'demo';
        $_SERVER['Shib-Person-surname']     = 'User';
        $_SERVER['Shib-EP-Affiliation']     = 'staff';
        
        // the identity field name to use for the tests
        $this->_identityField = 'Shib-SwissEP-UniqueID';
        
        // a key map to use for the tests
        $this->_keyMap = array(
            'Shib-SwissEP-UniqueID'        => 'id',
            'Shib-InetOrgPerson-mail'      => 'email',
            'Shib-InetOrgPerson-givenName' => 'firstname',
            'Shib-Person-surname'          => 'name',
        );
        
    }

    /**
     * Destroy fixtures after test.
     *
     */
    protected function tearDown()
    {
        unset($_SERVER['Shib-SwissEP-UniqueID']);
        unset($_SERVER['Shib-InetOrgPerson-mail']);
        unset($_SERVER['Shib-InetOrgPerson-givenName']);
        unset($_SERVER['Shib-Person-surname']);
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
                                       $auth);
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
                            $result->getCode());
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
                          'Adapter should have a key map.');
        $auth->clearKeyMap();
        $this->assertFalse($auth->hasKeyMap(), 
                           'Adapter should not have a key map.');
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
                                 $identity);
        $this->assertEquals('demouser@unibe.ch', 
                            $identity[$this->_keyMap[$this->_identityField]]);
    }
    
    /**
     * Test if a indirect authentication works
     */
    public function testIndirectAuthentication()
    {
        $authAdapter = new Iml_Auth_Adapter_Shibboleth();
        $authAdapter->setIdentityField($this->_identityField);
        $auth = Zend_Auth::getInstance();
        $auth->authenticate($authAdapter);
        $this->assertTrue($auth->hasIdentity());
        $identity = $auth->getIdentity();
        $this->assertType('array', $identity);
        $this->assertArrayHasKey($this->_identityField, 
                                 $identity);
        $this->assertEquals('demouser@unibe.ch', 
                            $identity[$this->_identityField]);
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

    /**
     * test if default attribute prefix is set within auth adapter
     */
    public function testDefaultAttributePrefix()
    {
        $auth = new Iml_Auth_Adapter_Shibboleth();
        $this->assertEquals($this->_defaultAttributePrefix, $auth->getShibbolethAttributePrefix());
    }

    /**
     * test if default attribute prefix can be overwritten
     */
    public function testOverrideDefaultAttributePrefix()
    {
        $prefix = 'OTHER-';
        $identityField = 'OTHER-SwissEP-UniqueID';
        $keyMap = array(
            'OTHER-SwissEP-UniqueID'        => 'id',
            'OTHER-InetOrgPerson-mail'      => 'email',
            'OTHER-InetOrgPerson-givenName' => 'firstname',
            'OTHER-Person-surname'          => 'name',
        );
        $this->tearDown();
        foreach ($keyMap as $key => $value) {
            $_SERVER[$key] = $value;
        }
        $auth = new Iml_Auth_Adapter_Shibboleth();
        $auth->setShibbolethAttributePrefix('OTHER-');
        $auth->setIdentityField($identityField);
        $auth->setKeyMap($keyMap);
        $this->assertEquals($prefix, $auth->getShibbolethAttributePrefix());
        $result = $auth->authenticate();
        $this->assertTrue($result instanceof Zend_Auth_Result);
        $this->assertTrue($result->isValid());
    }

}
