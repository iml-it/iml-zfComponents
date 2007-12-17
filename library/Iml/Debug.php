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
 * Iml_Debug_Exception
 */
require_once 'Iml/Debug/Exception.php';

/**
 * Zend_Debug
 */
require_once 'Zend/Debug.php';

/**
 * Zend_Log_Formatter_Simple
 */
require_once 'Zend/Log/Formatter/Simple.php';

/**
 * Mulitpurpose Debugging Class
 *
 * In addition to the Zend_Debug base class this class provides some
 * helpful functions to use while debugging.
 *
 * @category   Iml
 * @package    Iml_Debug
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of
 *             Bern (http://www.iml.unibe.ch)
 * @author     Michael Rolli <michael.rolli@iml.unibe.ch>
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 */
class Iml_Debug extends Zend_Debug
{
    /**
     * Debug helper function specially for log events collected i.e. by a mock
     * logger. Output is a log table in reverse order (newest at top) that
     * gives a good colored overview of the log messages.
     *
     * @param  array  $events   The variable to dump.
     * @param  string $label OPTIONAL Label to prepend to output.
     * @param  bool   $echo  OPTIONAL Echo output if true.
     * @return string
     */
    public static function dumpLogEvents($events, $label=null, $echo=true)
    {
        if (!is_array($events)) {
            throw new Iml_Debug_Exception('Array expected for argument 1, ' . gettype($events) . ' given');
        }
        // format label
        $output = ($label===null) ? '' : trim(strip_tags($label)) . ': ';
        $output = ($label !== null && self::getSapi() != 'cli') ? $output . PHP_EOL : $output;

        // use a Zend_Log_Formatter_Simple to do the job
        if (self::getSapi() != 'cli') {
            $format = '<tr><td>%timestamp%</td><td>%priorityName% (%priority%)</td><td>%message%</td></tr>';
        } else {
            $format = '%timestamp% %priorityName% (%priority%): %message%';
        }
        $formatter = new Zend_Log_Formatter_Simple($format);

        // reverse order
        $events = array_reverse($events);
        // format with the created formatter
        $formatted = '';
        foreach ($events as $event) {
            $formatted .= $formatter->format($event) . PHP_EOL;
        }
        $output .= (self::getSapi() == 'cli') ? $formatted : '<table>' . PHP_EOL . $formatted . '</table>' . PHP_EOL;

        if ($echo) {
            echo($output);
        }
        return $output;
    }

}
