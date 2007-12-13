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
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2007 Institute for Medical Education, University of Bern (http://www.iml.unibe.ch)
 * @license    http://creativecommons.org/licenses/by-sa/2.5/ch/     CC-By-Sa
 * @version    $Id$
 */

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Framework/IncompleteTestError.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Runner/Version.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

/*
 * Set error reporting to the level to which Zend Framework code must comply.
 */
error_reporting( E_ALL | E_STRICT );

/*
 * Determine the root, library, and tests directories of the iml zf
 * components distribution.
 */
$imlZfcRoot        = dirname(dirname(__FILE__));
$imlZfcCoreLibrary = $imlZfcRoot . DIRECTORY_SEPARATOR . 'library';
$imlZfcCoreTests   = $imlZfcRoot . DIRECTORY_SEPARATOR . 'tests';

/*
 * Prepend the iml-zfc library/ and tests/ directories to the
 * include_path. This allows the tests to run out of the box and helps prevent
 * loading other copies of the framework code and tests that would supersede
 * this copy.
 */
$path = array(
    $imlZfcCoreLibrary,
    $imlZfcCoreTests,
    get_include_path()
    );
set_include_path(implode(PATH_SEPARATOR, $path));

/*
 * Unset global variables that are no longer needed.
 */
unset($imlZfcRoot, $imlZfcCoreLibrary, $imlZfcCoreTests, $path);
