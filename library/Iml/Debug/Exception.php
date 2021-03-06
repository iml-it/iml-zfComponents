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
 * @package    Iml_Debug
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of
 *             Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Iml_Exception
 */
require_once 'Iml/Exception.php';


/**
 * Exception class for the Iml_Debug package
 *
 * Classes from the Iml_Debug package should throw this
 * exception type.
 *
 * @category   Iml
 * @package    Iml_Debug
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of
 *             Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Debug_Exception extends Iml_Exception
{
}
