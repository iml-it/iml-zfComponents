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
 * @package    Iml_Shibboleth
 * @subpackage Gmt
 * @copyright  Copyright (c) 2007 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * Iml_Shibboleth_Gmt_Exception
 */
require_once 'Iml/Shibboleth/Gmt/Exception.php';

/**
 * Shibboleth GMT Query Class
 * 
 * This class handles remote queries to a Switch Group Management Tool by
 * using http requests.
 * 
 * @category   Iml
 * @package    Iml_Shibboleth
 * @subpackage Gmt
 * @copyright  Copyright (c) 2007 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Shibboleth_Gmt
{
    /**
     * Instance of Zend_Http_Client to use for queries
     * to the remote GMT.
     *
     * @var Zend_Http_Client|boolean
     */
    protected $_httpClient = false;
    
    /**
     * Shared symmetric key used for encryption.
     *
     * @var string
     */
    protected $_sharedKey = '';
    
    /**
     * Wether to use encryption.
     *
     * @var boolean
     */
    protected $_encrypt = false;

    /**
     * Class constructor. Builds a Zend_Http_Client object and sets
     * the shared key for encryption.
     *
     * @param string $remoteUri
     * @param string $sharedKey
     */
    public function __construct($remoteUri, $sharedKey = '')
    {
        $httpClient = new Zend_Http_Client();
        $httpClient->setUri($remoteUri);
        $httpClient->setConfig(
            array('maxredirects' => 0, 
                  'timeout' => 5, 
                  'keepalive' => true,
            )
        );
        $this->_httpClient = $httpClient;   
        if (!empty($sharedKey)) {
            $this->_sharedKey = $sharedKey;
            $this->_encrypt = true;
        }
    }
    
    /**
     * Checks if user is in a particular group.
     *
     * @param string $user uniqueid
     * @param string $group group
     * @return boolean
     * @throws Iml_Shibboleth_Gmt_Exception if error encountered
     */
    public function isInGroup($user, $group)
    {
        $parameters = array('user' => $user, 'group' => $group);
        $response = $this->_request($parameters);

        $result = $this->_parseXml($response->getBody());
        return (bool) strval($result->bool);
    }

    /**
     * Get URL to the members list of a particular groups in the
     * Group Management Tool.
     *
     * @param string $group group name
     * @return string HTTP URL
     * @throws Iml_Shibboleth_Gmt_Exception if error encountered
     */
    public function getGroupModifyUrl($group)
    {
        $parameters = array('group' => $group);
        $response = $this->_request($parameters);
        
        $result = $this->_parseXml($response->getBody());
        return $result->url;
    }

    /**
     * Get the WebApp URL of a particular group stored in the
     * group infos in GMT.
     *
     * @param string $group group name
     * @return string HTTP URL
     * @throws Iml_Shibboleth_Gmt_Exception if error encountered
     */
    public function getWebappUrl($group)
    {
        $parameters = array('group' => $group, 'action' => 'getWebappURL');
        $response = $this->_request($parameters);

        $result = $this->_parseXml($response->getbody());
        return $result->url;
    }

    /**
     * Get all groups a particular user belongs to.
     *
     * @param string $user uniqueid
     * @return array group names
     * @throws Iml_Shibboleth_Gmt_Exception if error encountered
     */
    public function getUserGroups($user)
    {
        $parameters = array('user' => $user);
        $response = $this->_request($parameters);
        
        $result = $this->_parseXml($response->getBody());
        $groups = array();
        foreach ($result->groups->group as $group) {
            $groups[] = strval($group);
        }
        return $groups;
    }

    /**
     * Returns role of a user in a group. If the group argument is not given
     * the users most privileged role in all groups is returned.
     *
     * @param string $user uniqueid
     * @param integer $group group id
     * @return string role id
     * @throws Iml_Shibboleth_Gmt_Excepton if error encountered
     */
    public function getUserRole($user, $group = '')
    {
        $parameters = array('user' => $user, 'action' => 'getUserRole');
        if (!empty($group)) {
            $parameters['group'] = $group;
        }
        $response = $this->_request($parameters);

        $result = $this->_parseXml($response->getBody());
        return (string) $result->role->id;
    }

    /**
     * Get user ata stored in the GMT.
     *
     * @param string $user uniqueid
     * @return array user data
     * @throws Iml_Shibboleth_Gmt_Excepton if error encountered
     */
    public function getUserData($user)
    {
        $parameters = array('user' => $user, 'action' => 'getUserData');
        $response = $this->_request($parameters);

        $result = $this->_parseXml($response->getBody());
        $userData = array();
        $userData['givenName'] = (string) $result->user->givenName;
        $userData['surname']   = (string) $result->user->surname;
        $userData['mail']      = (string) $result->user->mail;
        return $userData;
        
    }

    /**
     * Returns all known groups excluding the system groups.
     *
     * @return array groups names
     * @throws Iml_Shibboleth_Gmt_Excepton if error encountered 
     */
    public function getAllGroups()
    {
        $parameters = array('action' => 'getAllGroups');
        $response = $this->_request($parameters);
        
        $result = $this->_parseXml($response->getBody());
        $groups = array();
        foreach ($result->groups->group as $group) {
            $groups[] = strval($group);
        }
        return $groups;
    }

    /**
     * Returns an associative array with all valid roles a user can have
     * and the names of these roles.
     *
     * @return array with role id as key and the role namesas value
     * @throws Iml_Shibboleth_Gmt_Excepton if error encountered 
     */
    public function getAllRoles()
    {
        $parameters = array('action' => 'getAllRoles');
        $response = $this->_request($parameters);
        
        $result = $this->_parseXml($response->getBody());
        $roles = array();
        foreach ($result->roles->role as $role) {
            $roles[strval($role->id)] = strval($role->name);
        }
        return $roles;
    }

    /**
     * Returns an array with all registered users and their global role.
     * If you want users from a particular group use {@link getGroupMembers}
     * instead.
     *
     * @return array with all users with uniqueid as key the role id as value
     * @throws Iml_Shibboleth_Gmt_Exception if error encountered
     */
    public function getAllUsers()
    {
        $parameters = array('action' => 'getAllUsers');
        $response = $this->_request($parameters);
        
        $result = $this->_parseXml($response->getBody());
        $users = array();
        foreach ($result->users->user as $user) {
            $users[strval($user->id)] = strval($user->role);
        }
        return $users;
    }

    /**
     * Returns all users of a group with the given role in that group.
     *
     * @param string $group groupname
     * @return array of users with uniqueid as key and their role id as value
     * @throws Iml_Shibboleth_Gmt_Exception if error encountered
     */
    public function getGroupMembers($group)
    {
        $parameters = array('group' => $group, 'action' => 'getGroupMembers');
        $response = $this->_request($parameters);
        
        $result = $this->_parseXml($response->getBody());
        $users = array();
        foreach ($result->users->user as $user) {
        	$users[strval($user->id)] = strval($user->role);
        }
        return $users;
        
    }

    /**
     * Returns all users of a group with the given role.
     *
     * @param string $group groups name
     * @param string $role role id
     * @return array of uniqueids
     * @throws Iml_Shibboleth_Gmt_Exception if error encountered
     */
    public function getRoleMembers($group, $role)
    {
        $parameters = array('group' => $group, 
                            'role' => $role, 
                            'action' => 'getRoleMembers',
        );
        $response = $this->_request($parameters);
        
        $result = $this->_parseXml($response->getBody());
        $users = array();
        foreach ($result->users->user as $user) {
            $users[] = strval($user->id);
        }
        return $users;
    }

    /**
     * Builds the request and executes it. Throws an exception if http return 
     * code isn't 200. 
     *
     * @param array $parameters
     * @return Zend_Http_Response $response
     */
    protected function _request($parameters)
    {
        // reset parameters from previous request
        $this->_httpClient->resetParameters();
        
        // GMT expects dashes subtitutes by underscores in group names
        if (array_key_exists('group', $parameters)) {
            $parameters['group'] = str_replace('-', '_', $parameters['group']);
        }
        
        // Encrypt parameter values if sharedKey is sest
        if ($this->_encrypt) {
            $parameters = $this->_encryptParameters($parameters);
        }
        
        // GMT expects parameter values base64 encoded
        foreach ($parameters as $key => $value) {
        	$this->_httpClient->setParameterPost($key, base64_encode($value));
        }
        
        // send the request
        $response =  $this->_httpClient->request(Zend_Http_Client::POST);
        if ($response->getStatus() != 200) {
            throw new Iml_Shibboleth_Gmt_Exception(sprintf('GMT request failed (%s: %s)', 
                                                           $response->getStatus(), 
                                                           $response->getMessage()));
        }
        return $response;        
    }

    /**
     * Encrypts given parameters using the shared secret provided.
     *
     * @param array $parameters request parameters
     * @return array ecnrypted request parameters
     */
    protected function _encryptParameters($parameters)
    {
        $sessionKey = md5(uniqid(rand(), 1));
        $sessionKey.= md5(uniqid(rand(), 1));

        foreach ($parameters as $key => $value) {
            $parameters[$key] = $this->_cryptRc4($sessionKey, $value);
        }
        $parameters['encryption'] = $this->_cryptRc4($this->_sharedKey, $sessionKey);
        return $parameters;
    }

    /**
     * Decrypts an encrypted response.
     *
     * @param SimpleXMLElement $response encrypted respnse
     * @return SimpleXMLElement representing the unencrypted result
     */
    protected function _decryptResponse($response)
    {
        $encryptedSessionKey = strval($response->encrypted->sessionkey);
        $sessionKey = $this->_cryptRc4($this->_sharedKey, base64_decode($encryptedSessionKey));
        $encryptedResult = strval($response->encrypted->result);
        $result = $this->_cryptRc4($sessionKey, base64_decode($encryptedResult));
        $response = simplexml_load_string('<response><result>' . $result . '</result></response>');
        return $response;
    }

    /**
     * Encrypts or decrypts $value using shared secret key $sharedKey with
     * RC4 algorithm.
     *
     * @param string $secretKey private shared key 
     * @param string $value to be encrypted/decrypted
     */
    protected function _cryptRc4($secretKey, $text)
    {
        $key = array('');
        $box = array('');
        $cipher = '';
        
        $secretKeyLength = strlen($secretKey);
        $textLength = strlen($text);
        
        for ($i = 0; $i<256; $i++) {
            $key[$i] = ord($secretKey[$i % $secretKeyLength]);
            $box[$i] = $i;
        }
        
        for ($j = $i = 0; $i<256; $i++) {
            $j = ($j + $box[$i] + $key[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        
        for ($a = $j = $i = 0; $i < $textLength; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $k = $box[($box[$a] + $box[$j]) % 256];
            $cipher.= chr(ord($text[$i]) ^ $k );
        }
        
        return $cipher;
    }

    /**
     * Parses xml response to an array.
     *
     * @param string $xml xml response
     * @return SimpleXMLElement $response SimpleXml Object 
     */
    protected function _parseXml($xml)
    {
        $response = simplexml_load_string($xml);

        // test to wellformdness of xml
        if (!$response) {
            throw new Iml_Shibboleth_Gmt_Exception('GMT error: Respose not wellformed xml data');
        }

        // Check if response is encrypted. If yes decrypt it.
        if ($this->_encrypt && $response->encrypted) {
            $response = $this->_decryptResponse($response);
        }

        // Check if respnse represents a query error
        if ($response->error) {
            throw new Iml_Shibboleth_Gmt_Exception(sprintf('GMT error: %s', $response->error));
        }

        return $response->result;
    }
}
