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
     * @param unknown_type $remoteUri
     * @param unknown_type $sharedKey
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
        $userData['givenName'] = $result->user->givenName;
        $userData['surname']   = $result->user->surname;
        $userData['mail']      = $result->user->mail;
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
     * Build the request and executes it. Throws an exception if http return 
     * code isn't 200. 
     *
     * @param array $parameters
     * @return Zend_Http_Response $response
     */
    protected function _request($parameters)
    {
        if (array_key_exists('group', $parameters)) {
            $parameters['group'] = str_replace('-', '_', $parameters['group']);
        }
        $this->_httpClient->resetParameters();
        foreach ($parameters as $key => $value) {
        	$this->_httpClient->setParameterPost($key, base64_encode($value));
        }
        $response =  $this->_httpClient->request(Zend_Http_Client::POST);
        if ($response->getStatus() != 200) {
            throw new Iml_Shibboleth_Gmt_Exception(sprintf('GMT request failed (%s: %s)', 
                                                           $response->getStatus(), 
                                                           $response->getMessage()));
        }
        return $response;        
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
        if (!$response) {
            throw new Iml_Shibboleth_Gmt_Exception('GMT error: Respose not wellformed xml data');
        } elseif ($response->error) {
            throw new Iml_Shibboleth_Gmt_Exception(sprintf('GMT error: %s', $response->error));
        }
        return $response->result;
    }
}
