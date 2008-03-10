<?php

require_once 'Zend/Auth/Adapter/Interface.php';

class Iml_Auth_Adapter_Shibboleth implements Zend_Auth_Adapter_Interface
{
    protected $identityField = 'SHIB_SWISS_EDUPERSON_ID';
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