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
 * @package UnitTests
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
$include_path = array(
        $imlZfcCoreLibrary,
        $imlZfcCoreTests,
        get_include_path()
        );
set_include_path(implode(PATH_SEPARATOR, $path));

/*
 * Load the user-defined test configuration file, if it exists; otherwise, load
 * the default configuration.
 */
if (is_readable($imlZfcCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php')) {
    require_once $imlZfcCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php';
} else {
    require_once $imlZfcCoreTests . DIRECTORY_SEPARATOR . 'TestConfiguration.php.dist';
}

/*
 * Add Zend Framework library/ directory to the PHPUnit code coverage
 * whitelist. This has the effect that only production code source files appear
 * in the code coverage report and that all production code source files, even
 * those that are not covered by a test yet, are processed.
 */
if (TESTS_GENERATE_REPORT === true &&
    version_compare(PHPUnit_Runner_Version::id(), '3.1.6', '>=')) {
    PHPUnit_Util_Filter::addDirectoryToWhitelist($imlZfcCoreLibrary);
}

/*
 * Unset global variables that are no longer needed.
 */
unset($imlZfcRoot, $imlZfcCoreLibrary, $imlZfcCoreTests, $path);
