<?php

/**
 * IML ZendFramework Components
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
 * @subpackage Adapters
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of
 *             Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Zend_Auth_Adapter_Interface
 */
require_once 'Zend/Auth/Adapter/Interface.php';

/**
 * @category   Iml
 * @package    Iml_Auth
 * @subpackage Adapters
 * @copyright  Copyright (c) 2007 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Auth_Adapter_Shibboleth implements Zend_Auth_Adapter_Interface
{
    /**
     * $_identityField - key in $_SERVER
     * Denotes the identity field.
     *
     * @var string
     */
    protected $_identityField = null;
    
    /**
     * $_keyMap - Keymap hash for translating keys
     * to variable names
     * 
     * @var array
     */
    protected $_keyMap = null;
    
    /**
     * $_hasKeyMap - Set to true if a keymap is in place
     *
     * @var boolean
     */
    protected $_hasKeyMap = false;
    
    /**
     * $_identity - Identity array
     *
     * @var array
     */
    protected $_identity = array();

    /**
     * Class constructor
     *
     * @param string $identityField key in $_SERVER to use for identity
     * @param array $keyMap Key map of envvar names to appvar names
     * @return void
     */
    public function __construct($identityField = null, $keyMap = null)
    {
        if (null !== $identityField) {
            $this->setIdentityField($identityField);
        }

        if (is_array($keyMap)) {
            $this->setKeyMap($keyMap);
        }
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be
     *         performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        if (null === $this->_identityField) {
            throw new Zend_Auth_Exception('No identity field set. ' . 
                                          'Use setIdentityField()'
                                          );
        }
        if (isset($_SERVER[$this->_identityField])) {
            $this->_setupIdentity();
            $result = array(
                'code'     => Zend_Auth_Result::SUCCESS,
                'identity' => $this->_identity,
                'messages' => array(),
            );
        } else {
            $result = array(
                'code'     => Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND,
                'identity' => $this->_identity,
                'messages' => array('There was no active shibboleth session' .
                                      ' to authenticate against.'),
            );
        }
        return new Zend_Auth_Result($result['code'], 
                                    $result['identity'], 
                                    $result['messages']
                   );
    }

    /**
     * setIdentityFiled() - set the key in $_SERVER to be used as identity
     *
     * @param string $identityField
     */
    public function setIdentityField($identityField)
    {
        $this->_identityField = $identityField;
    }

    /**
     * setKeyMap() - Setup a key map
     *
     * @throws Zend_Auth_Adapter_Exception If $keyMap is not of type array 
     * @param array $keyMap
     * @return void
     */
    public function setKeyMap($keyMap)
    {
        if (is_array($keyMap)) {
            $this->_keyMap = $keyMap;
            $this->_hasKeyMap = true;
        } else {
            throw new Zend_Auth_Exception(
                                'An array of key/value pairs has to be ' .
                                'provided. "' . getType($keyMap) . '" given.'
                                );
        }
    }

    /**
     * clearKeyMap() - Clear out a previously set keymap.
     *
     * @return void
     */
    public function clearKeyMap()
    {
        $this->_keyMap = null;
        $this->_hasKeyMap = false;
    }

    /**
     * hasKeyMap() - Returns true if a keymap has been setup.
     *
     * @return boolean
     */
    public function hasKeyMap()
    {
        return $this->_hasKeyMap;
    }

    /**
     * _setupIdentity() - Builds the identity from values in $_SERVER set
     * by the shibboleth daemon; honors a key mapping if one is set. 
     *
     * @return void
     */
    protected function _setupIdentity()
    {
        foreach ($_SERVER as $key => $value) {
            if (false !== strpos($key, 'HTTP_SHIB')) {
                if ($this->hasKeyMap()) {
                    if (array_key_exists($key, $this->_keyMap)) {
                        $key = $this->_keyMap[$key];
                        $this->_identity[$key] = $value;
                    }
                } else {
                    $this->_identity[$key] = $value;
                }
            }
        }
    }
}