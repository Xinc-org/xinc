<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Publisher
 * @author    Arno Schneider <username@example.org>
 * @author    Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright 2007 Arno Schneider, Barcelona
 * @license   http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *            This file is part of Xinc.
 *            Xinc is free software; you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation; either version 2.1 of
 *            the License, or (at your option) any later version.
 *
 *            Xinc is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public
 *            License along with Xinc, write to the Free Software Foundation,
 *            Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link      http://code.google.com/p/xinc/
 */

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Publisher/PHPUnitTestResults/Task.php';
require_once 'Xinc/Plugin/Repos/Gui/PhpUnitTestResults/Widget.php';

class Xinc_Plugin_Repos_Publisher_PHPUnitTestResults extends Xinc_Plugin_Base
{
    const TESTRESULTS_DIR = 'testresults';
   
    public function validate()
    {
        return true;
    }

    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Publisher_PHPUnitTestResults_Task($this));
    }

    public function getGuiWidgets()
    {
        return array(new Xinc_Plugin_Repos_Gui_PhpUnitTestResults_Widget($this));
    }

    /**
     * Copies a file into a special test results directory for the build and
     * parse the xml file to generate statistics
     *
     * @param Xinc_Build_Interface $build      The actuall build.
     * @param string               $sourceFile The xml file to read out.
     *
     * @return boolean True if registering was ok, false if it failed.
     */
    public function registerResults(Xinc_Build_Interface $build, $sourceFile)
    {
        $statusDir = Xinc::getInstance()->getStatusDir();

        $projectDir = Xinc::getInstance()->getProjectDir();

        $subDir = $build->getStatusSubDir();
        $fullDir = $statusDir . DIRECTORY_SEPARATOR . $subDir
            . DIRECTORY_SEPARATOR . self::TESTRESULTS_DIR;
        $targetFile = $fullDir . DIRECTORY_SEPARATOR . basename($sourceFile);

        /**
         * Verify that the source is in the projectdir
         */
        $relativePath = str_replace($projectDir, '', $sourceFile);
        if ($relativePath == $sourceFile) {
            /**
             * the filename was not within the project path,
             * we need to prevent this file from being copied.
             * 
             * Future: run Xinc in a chroot environment per project
             */
            $build->error(
                'Registering test results: ' . $sourceFile . '->' . $targetFile
                . ' failed.'
            );
            $build->error(
                '-- ' . $sourceFile . ' is not within project dir. Security Problem.'
            );
            return false;
        }

        if (!file_exists($fullDir)) {
            mkdir($fullDir, 0755, true);
        }
        if (is_dir($sourceFile)) {
            if (DIRECTORY_SEPARATOR == '\\') {
                exec('xcopy /E /Y /I ' . $sourceFile . ' ' . $targetFile, $out, $res);
                //chmod($targetFile, 0755);
            } else {
                exec('cp -Rf ' . $sourceFile . ' ' . $targetFile, $out, $res);
            }
            if ($res==0) {
                $status = 'OK';
            } else {
                $status = 'FAILURE';
            }
        } elseif (is_file($sourceFile)) {
            $res = copy($sourceFile, $targetFile);
            if ($res) {
                chmod($targetFile, 0755);
                $status = 'OK';
                /**
                 * register statistics
                 */
                $build->getInternalProperties()->set('phpunit.file', $targetFile);
                $this->generateStats($build, $targetFile);
            } else {
                $status = 'FAILURE';
            }
        } else {
            $build->error('The phpunit file ' . $sourceFile . ' couldn\'t be read.');
            return false;
        }
        $build->info(
            'Registering test results: ' . $sourceFile . '->' . $targetFile
            . ', result: ' . $status
        );
        return $res;
    }
    
    /**
     * Generates statistsics and saves them into the build.
     *
     * @param Xinc_Build_Interface $build      The actuall build.
     * @param string               $sourceFile The xml file to read stats from.
     *
     * @return boolean True if registering was ok, false if it failed.
     */
    private function generateStats(Xinc_Build_Interface $build, $file)
    {
        try {
            /**
             * @var string Contains content of the XML file
             */
            $strContent = file_get_contents($file);

            if ('' !== $strContent) {
                $arStats = $this->parseXml($strContent);

                /**
                 * set the statistics data
                 */
                foreach ($arStats as $strName => $mValue) {
                    $build->getStatistics()->set('phpunit.' . $strName, $mValue);
                }
            }
        } catch (Exception $e) {
            Xinc_Logger::getInstance()->error(
                'Could not parse phpunit xml: ' . $e->getTraceAsString()
            );
        }
    }


    /**
     * Reads the XML file, generates statistsics and saves them into the build.
     *
     * @param string $strContent The XML to parse.
     *
     * @return boolean True if registering was ok, false if it failed.
     */
    private function parseXml($strContent)
    {
        $testSuiteCount = 0;
        $failureCount = 0;
        $errorCount = 0;
        $timeTaken = 0;

        $xml = new SimpleXMLElement();
        $testSuites = $xml->xpath("//testsuite");

        foreach ($testSuites as $name => $suite) {
            $attributes = $suite->attributes();
            $testSuiteCount += $attributes['tests'];
            $failureCount += $attributes['failures'];
            $errorCount += $attributes['errors'];
            $timeTaken += (float)$attributes['time'];
        }



        $arStats = array(
            'numberOfTests' => $testSuiteCount,
            'numberOfFailures' => $failureCount,
            'numberOfErrors' => $errorCount,
            'totalTime' => round($timeTaken, 8)
        );

        return $arStats;
    }
}