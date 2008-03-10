<?php

/**
 * IML ZendFramework Components
 *
 * LICENSE
 *
 * This work is licensed under the Creative Commons Attribution-Share Alike 2.5
 * Switzerland License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-sa/2.5/ch/ or send a letter to Creative
 * Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
 *
 * @category   Iml
 * @package    Iml_Auth
 * @subpackage Adapters
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
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
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Auth_Adapter_Shibboleth implements Zend_Auth_Adapter_Interface
{
    /**
     * Denotes the key in $_SERVER which holds the
     * unique id of an authenticated user.
     *
     * @var Zend_Mail
     */
    protected $usernameField = 'HTTP_SHIB_SWISSEP_UNIQUEID';

    /**
     * Sets username and password for authentication
     *
     * @return void
     */
    public function __construct($username, $password)
    {
        // TODO: make this class attribute agnostic.
    }

    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $result = array(
            'code' => Zend_Auth_Result::SUCCESS,
            'identity' => $this->_setupIdentity(),
            'messages' => array(),
        );
        return new Zend_Auth_Result($result['code'], $result['identity'], $result['messages']);
    }
    
    protected function _setupIdentity()
    {
        $identity = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 10) == 'HTTP_SHIB_') {
                $identity[$key] = $value;
            }
        }
        return $identity;
    }
}