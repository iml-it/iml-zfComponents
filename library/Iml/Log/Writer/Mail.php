<?php

/**
 * IML Zend Framework Components
 *
 * LICENSE
 *
 * This work is licensed under the Creative Commons Attribution-Share Alike 2.5
 * Switzerland License. To view a copy of this license, visit
 * http://creativecommons.org/licenses/by-sa/2.5/ch/ or send a letter to Creative
 * Commons, 171 Second Street, Suite 300, San Francisco, California, 94105, USA.
 *
 * @category   Iml
 * @package    Iml_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Zend_Log_Writer_Abstract
 */
require_once 'Zend/Log/Writer/Abstract.php';

/**
 * Zend_Log_Formatter_Simple
 */
require_once 'Zend/Log/Formatter/Simple.php';

/**
 * Mail based log writer
 *
 * This log writer implements the funcitonality to log to mails. This
 * way developers and maintainers can be notified about severe errors in
 * a production application.
 *
 * @category   Iml
 * @package    Iml_Log
 * @subpackage Writer
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Log_Writer_Mail extends Zend_Log_Writer_Abstract
{
    /**
     * Holds a Zend_Mail object to write to.
     *
     * @var Zend_Mail
     */
    protected $_mail;

    /**
     * Class Constructor
     *
     * @param  Zend_Mail  Mail object
     * @return void
     */
    public function __construct(Zend_Mail $mail)
    {
        $this->_mail = $mail;
        $this->_formatter = new Zend_Log_Formatter_Simple();
    }

    /**
     * Destroy the Zend_Mail object
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_mail = null;
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     */
    protected function _write($event)
    {
        if ($this->_mail == null) {
            throw new Zend_Log_Exception('No mail object available to log to');
        }
        $line = $this->_formatter->format($event);

        $this->_mail->setBodyText($line);
        $this->_mail->send();
    }

}
