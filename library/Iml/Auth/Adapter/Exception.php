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
 * @subpackage Adapter
 * @copyright  Copyright (c) 2007 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Iml_Auth_Exception
 */
require_once 'Zend/Auth/Adapter/Exception.php';

/**
 * Iml Auth Adapter Exception
 * 
 * This exception type should be thrown by all authentication adapters.
 * 
 * @category   Iml
 * @package    Iml_Auth
 * @subpackage Adapter
 * @copyright  Copyright (c) 2007 Institute for Medical Education, 
 *             University of Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Auth_Adapter_Exception extends Zend_Auth_Adapter_Exception
{
}
