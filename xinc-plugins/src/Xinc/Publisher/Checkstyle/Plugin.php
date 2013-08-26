<?php
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Publisher
 * @subpackage Checkstyle
 * @author     Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright  2013 Alexander Opitz, Leipzig
 * @license    http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *             This file is part of Xinc.
 *             Xinc is free software; you can redistribute it and/or modify
 *             it under the terms of the GNU Lesser General Public License as
 *             published by the Free Software Foundation; either version 2.1 of
 *             the License, or (at your option) any later version.
 *
 *             Xinc is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Lesser General Public License for more details.
 *
 *             You should have received a copy of the GNU Lesser General Public
 *             License along with Xinc, write to the Free Software Foundation,
 *             Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * @link       http://code.google.com/p/xinc/
 */

require_once 'Xinc/Plugin/Abstract.php';
require_once 'Xinc/Publisher/Checkstyle/Task.php';
require_once 'Xinc/Publisher/Checkstyle/Widget.php';

class Xinc_Publisher_Checkstyle_Plugin extends Xinc_Plugin_Abstract
{
    /**
     * Returns the defined tasks of the plugin
     *
     * @return Xinc_Plugin_Task[]
     */
    public function getTaskDefinitions()
    {
        return array(new Xinc_Publisher_Checkstyle_Task($this));
    }

    public function getGuiWidgets()
    {
        return array(new Xinc_Publisher_Checkstyle_Widget($this));
    }

    /**
     * Generates statistsics and saves them into the build.
     *
     * @param Xinc_Build_Interface $build      The actuall build.
     * @param string               $sourceFile The xml file to read stats from.
     *
     * @return boolean True if registering was ok, false if it failed.
     */
    public function generateStats(Xinc_Build_Interface $build, $file)
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
                    $build->getStatistics()->set('checkstyle.' . $strName, $mValue);
                }
            }
        } catch (Exception $e) {
            Xinc_Logger::getInstance()->error(
                'Could not parse checkstyle xml: ' . $e->getMessage() . "\n" . 'Trace: ' . $e->getTraceAsString()
            );
            return false;
        }

        return true;
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
        $warningCount = 0;
        $errorCount = 0;

        $xml = new SimpleXMLElement($strContent);
        $xmlFiles = $xml->xpath('/checkstyle/file');

        foreach ($xmlFiles as $xmlFile) {
            $attributes = $xmlFile->attributes();
            $filename = $attributes['name'];

            $xmlErrors = $xmlFile->xpath('./error[@severity="error"]');
            $xmlWarnings = $xmlFile->xpath('./error[@severity="warning"]');
            $warningCount += count($xmlWarnings);
            $errorCount += count($xmlErrors);
        }

        $arStats = array(
            'numberOfFiles' => count($xmlFiles),
            'numberOfWarnings' => $warningCount,
            'numberOfErrors' => $errorCount,
        );

        return $arStats;
    }
}
