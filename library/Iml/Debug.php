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
 * @package    Iml_Debug
 * @subpackage Writer
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

/**
 * Zend_Debug
 */
require_once 'Zend/Debug.php';

/**
 * Zend_Log_Formatter_Simple
 */
require_once 'Zend/Log/Formatter/Simple.php';

/**
 * @category   Iml
 * @package    Iml_Debug
 * @subpackage Writer
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
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
        // format label
        $output = ($label===null) ? '' : trim($label) . ': ';
        $output = (self::getSapi() == 'cli') ? strip_tags($output) : $output;

        // use a Zend_Log_Formatter_Simple to do the job
        if (self::getSapi() != 'cli') {
            $format = '<tr><td>%timestamp%</td><td>%priorityName% (%priority%)</td><td>%message%</td></tr>';
        } else {
            $format = '%timestamp% %priorityName% (%priority%): %message%';
        }
        $formatter = new Zend_Log_Formatter_Simple($format);

        // format with the created formatter
        foreach ($events as $event) {
            $output .= $formatter->format($event) . PHP_EOL;
        }
        $output = (self::getSapi() == 'cli') ? $output : '<table>' . PHP_EOL . $output . '</table>' . PHP_EOL;

        if ($echo) {
            echo($output);
        }
        return $output;
    }

}
