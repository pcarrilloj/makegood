<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2011 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: 2.16.0
 * @since      File available since Release 2.1.0
 */

/**
 * The base class for test collectors.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: 2.16.0
 * @since      Class available since Release 2.1.0
 */
abstract class Stagehand_TestRunner_Collector
{
    protected $superTypes;
    protected $filePattern;
    protected $config;
    protected $suite;

    /**
     * Initializes some properties of an instance.
     *
     * @param Stagehand_TestRunner_Config $config
     */
    public function __construct(Stagehand_TestRunner_Config $config)
    {
        $this->config = $config;
        $this->suite = $this->createTestSuite('The test suite generated by Stagehand_TestRunner');
    }

    /**
     * Collects tests.
     *
     * @return mixed
     * @throws Stagehand_TestRunner_Exception
     */
    public function collect()
    {
        foreach ($this->config->testingResources as $testingResource) {
            $absoluteTargetPath = realpath($testingResource);
            if ($absoluteTargetPath === false) {
                throw new Stagehand_TestRunner_Exception(
                    'The directory or file [ ' .
                    $testingResource .
                    ' ] is not found'
                                                         );
            }

            if (is_dir($absoluteTargetPath)) {
                $directoryScanner = new Stagehand_DirectoryScanner(array($this, 'collectTestCases'));
                $directoryScanner->setRecursivelyScans($this->config->recursivelyScans);
                $directoryScanner->scan($absoluteTargetPath);
            } else {
                $this->collectTestCasesFromFile($absoluteTargetPath);
            }
        }

        return $this->suite;
    }

    /**
     * Collects all test cases included in the specified directory.
     *
     * @param string $element
     */
    public function collectTestCases($element)
    {
        if (is_dir($element)) {
            return;
        }

        $this->collectTestCasesFromFile($element);
    }

    /**
     * @param string $testCase
     * @since Method available since Release 2.10.0
     */
    abstract public function collectTestCase($testCase);

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**
     * Creates the test suite object.
     *
     * @param string $name
     * @return mixed
     */
    abstract protected function createTestSuite($name);

    /**
     * Collects all test cases included in the given file.
     *
     * @param string $file
     */
    protected function collectTestCasesFromFile($file)
    {
        if (!$this->shouldTreatFileAsTest(basename($file))) return;

        foreach ($this->findNewClasses($file) as $newClass) {
            if ($this->shouldTreatClassAsTest($newClass)) {
                $this->collectTestCase($newClass);
            }
        }
    }

    /**
     * @param string $file
     * @return boolean
     * @since Method available since Release 2.14.0
     */
    protected function shouldTreatFileAsTest($file)
    {
        if (!is_null($this->config->testFilePattern)) {
            $filePattern = $this->config->testFilePattern;
        } elseif (!is_null($this->config->testFileSuffix)) {
            $filePattern = $this->config->testFileSuffix . '\.php$';
        } else {
            $filePattern = $this->filePattern;
        }

        return (boolean)preg_match('/' . str_replace('/', '\/', $filePattern) . '/', $file);
    }

    /**
     * @param string $class
     * @return boolean
     * @since Method available since Release 2.14.0
     */
    protected function shouldTreatClassAsTest($class)
    {
        return $this->validateSuperType($class);
    }

    /**
     * @param string $class
     * @return boolean
     * @since Method available since Release 2.14.0
     */
    protected function validateSuperType($class)
    {
        foreach ($this->superTypes as $superType) {
            if ($class == $superType) {
                return false;
            }

            if (is_subclass_of($class, $superType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $file
     * @return boolean
     * @since Method available since Release 2.14.0
     */
    protected function findNewClasses($file)
    {
        $currentClasses = get_declared_classes();
        if (!include_once($file)) return array();
        return array_values(array_diff(get_declared_classes(), $currentClasses));
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
