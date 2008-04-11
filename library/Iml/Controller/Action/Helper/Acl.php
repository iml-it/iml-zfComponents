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
 * @package    Iml_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2007 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Zend_Controller_Action_Helper_Abstract
 */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Iml_Controller_Action_Helper_Acl
 * 
 * This action helper performs the action of checking wether a particular
 * identity (user) has the right to access a request action of a given 
 * controller. The check is done in the predispatch phase before the 
 * request is dispatched to the requested action.
 * 
 * Building the acl rules for the action of a controller has to be done by the
 * controller itself (best in init()) to leverage the overhed of huge acl
 * objects. The controller uses the proxy function allow/deny of the action
 * helper to accomplish this task.
 * 
 * Conventions used:
 * Resource = ControllerName
 * Privilege = ActionName
 * 
 * Usage:
 * <code>
 * // in your bootstrap:
 * $acl = new Zend_Acl();
 * ... add roles (and resources) ...
 * $aclActionHelper = new Iml_Controller_Action_Helper_Acl($view, array('acl' => $acl));
 * Zend_Controller_Action_HelperBroker::addHelper($aclActionHelper);
 * 
 * // in the init() of your controller:
 * $allActions = array('action1', 'action2', 'action3');
 * $adminActions = array('action4');
 * $this->getHelper('Acl')->allow('member, $allActions);
 * $this->getHelper('Acl')->allow('admin', $adminActions);
 * // to allow access everything to everybody use:
 * $this->getHelper('Acl')->allow(null);
 * </code>
 * 
 * @category   Iml
 * @package    Iml_Controller
 * @subpackage Action_Helper
 * @copyright  Copyright (c) 2007 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Controller_Action_Helper_Acl extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Instance of Zend_Auth
     *
     * @var Zend_Auth
     */
    protected $_auth;
    
    /**
     * Instance of a Zend_Acl object
     *
     * @var Zend_Acl
     */
    protected $_acl;
    
    /**
     * Current action controller the helper is called from.
     *
     * @var Zend_Controller_Action
     */
    protected $_action;

    /**
     * Name of the current action controller
     *
     * @var string
     */
    protected $_controllerName;

    /**
     * Role label to apply to a user that is not authed
     *
     * @var string
     */
    protected $_defaultRole = 'guest';
    
    /**
     * Throw an exception if access is denied?
     * 
     * @var boolean
     */
    protected $_throwIfDenied = true;

    /**
     * Array of module, controller, action to redirect to
     * when acl check denies and the user was not authed.
     *
     * @var array|null
     */
    protected $_noauth = null;

    /**
     * Array of module, controller, action to redirect to
     * when acl check denies and the user was authed.
     *
     * @var array|null
     */
    protected $_noacl  = null;

    /**
     * Class constructor. Setup the helper which is customizable by an 
     * options array.
     * 
     * Possible keys for the options array:
     * - acl : acl object to query (mandatory)
     * - throwifdenied : should helper throw an exception if acl check denies (optional)
     * - noauth : array (module, controller, action) to redirect to if access denied and
     *            not logged in user (i.e. a login form)
     * - noacl  : array (module, controller, action) to redirect to if access denied and
     *            logged in user (i.e an error action)
     *
     * @param Zend_View_Interface $view
     * @param array $options
     */
    public function __construct(Zend_View_Interface $view = null, array $options = array())
    {
        $this->_auth = Zend_Auth::getInstance();

        if (!isset($options['acl'])) {
            throw new Zend_Controller_Action_Exception('No Acl object provided.');
        } elseif (!$options['acl'] instanceof Zend_Acl) {
            throw new Zend_Controller_Action_Exception('Acl object must extend Zend_Acl.');
        } else {
            $this->_acl  = $options['acl'];
        }

        if (isset($options['throwIfDenied'])) {
            $this->_throwIfDenied = $options['throwIfDenied'];
        }

        if (isset($options['noauth']) && is_array($options['noauth'])) {
            $this->_noauth = $options['noauth'];
        }

        if (isset($options['noacl']) && is_array($options['noacl'])) {
            $this->_noauth = $options['noacl'];
        }
        

        
    }
    
    /**
     * Hook into action controller initialization
     * 
     * @return void
     */
    public function init()
    {
        $this->_action = $this->getActionController();
        
        // add resource for this controller
        $this->_controllerName = $this->_action->getRequest()->getControllerName();
        if (!$this->_acl->has($this->_controllerName)) {
            $this->_acl->add(new Zend_Acl_Resource($this->_controllerName));
        }
    }
    
    /** 
     * Proxy to the underlying Zend_Acl's allow() function. 
     * 
     * We use the controller's name as the resource and the 
     * action name(s) as the privilege(s) 
     * 
     * @param  Zend_Acl_Role_Interface|string|array     $roles 
     * @param  string|array                             $actions 
     * @uses   Zend_Acl::setRule() 
     * @return Iml_Controller_Action_Helper_Acl Provides a fluent interface 
     */ 
    public function allow($roles = null, $actions = null) 
    { 
        $resource = $this->_controllerName; 
        $this->_acl->allow($roles, $resource, $actions); 
        return $this; 
    } 
 
    /** 
     * Proxy to the underlying Zend_Acl's deny() function. 
     * 
     * We use the controller's name as the resource and the 
     * action name(s) as the privilege(s) 
     * 
     * @param  Zend_Acl_Role_Interface|string|array     $roles 
     * @param  string|array                             $actions 
     * @uses   Zend_Acl::setRule() 
     * @return Iml_Controller_Action_Helper_Acl Provides a fluent interface 
     */ 
    public function deny($roles = null, $actions = null) 
    { 
        $resource = $this->_controllerName; 
        $this->_acl->deny($roles, $resource, $actions); 
        return $this; 
    }

    /**
     * Hook into the predispatch phase. Based on the role of a given identity
     * check if user has acces to the controller/action requested. If not
     * take appropriate steps (throw, redirect).
     * 
     * @return void
     * @throws Zend_Controller_Action_Exception|Zend_Acl_Exception
     *
     */
    public function preDispatch()
    {
        if ($this->_auth->hasIdentity()) {
            $identity = $this->_auth->getIdentity();
            if (is_array($identity)) {
                $role = $identity['role'];
            } elseif (is_object($identity)) {
                $role = $identity->role;
            } else {
                throw new Zend_Controller_Action_Exception('Unable to determine role.');
            }
        } else {
            $role = $this->_defaultRole;
        }
        if (!$this->_acl->isAllowed($role, $this->_controllerName, $this->_action->getRequest()->getActionName())) {
            if ($this->_throwIfDenied) {
                throw new Zend_Acl_Exception('Access denied to requested action');
            } else {
                if ($this->_auth->hasIdentity() && null != $this->_noacl) {
                    $module     = $this->_noacl['module'];
                    $controller = $this->_noacl['controller'];
                    $action     = $this->_noacl['action'];
                } elseif (null != $this->_noauth) {
                    $module     = $this->_noauth['module'];
                    $controller = $this->_noauth['controller'];
                    $action     = $this->_noauth['action'];
                } else {
                    throw new Zend_Controller_Action_Exception('Exceptions disabled but no redirect destinations set.');
                }
            $request = $oldRequest = $this->_action->getRequest();
            $request->setModuleName($module)
                    ->setControllerName($controller)
                    ->setActionName($action)
                    ->setParam('oldRequest', $oldRequest)
                    ->setDispatched(false);
            }
        }
    }
}
