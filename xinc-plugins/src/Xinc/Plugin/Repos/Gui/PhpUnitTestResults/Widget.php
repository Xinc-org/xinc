<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.PhpUnitTestResults
 * @author    Arno Schneider <username@example.org>
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
 * @link      http://xincplus.sourceforge.net
 */

require_once 'Xinc/Gui/Widget/Interface.php';
require_once 'Xinc/Build/Iterator.php';
require_once 'Xinc/Project.php';
require_once 'Xinc/Build.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';
require_once 'Xinc/Plugin/Repos/Gui/PhpUnitTestResults/Extension/Dashboard.php';
require_once 'Xinc/Data/Repository.php';

class Xinc_Plugin_Repos_Gui_PhpUnitTestResults_Widget implements Xinc_Gui_Widget_Interface
{
    protected $_plugin;

    private $_extensions = array();

    public $projects = array();

    public $builds;

    public function __construct(Xinc_Plugin_Interface $plugin)
    {
        $this->_plugin = $plugin;
    }

    public function mime_content_type2($fileName)
    {
        $contentType = null;
        /**if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME); // return mime type ala mimetype extension
            if(!$finfo) return;
            $contentType = finfo_file($finfo, $fileName);
            finfo_close($finfo);
        } else*/
        if (function_exists('mime_content_type')) {
            $contentType = mime_content_type($fileName);
        }

        return $contentType;
    }

    public function handleEvent($eventId)
    {
        $query = $_SERVER['REQUEST_URI'];

        $projectName = isset($_REQUEST['project'])?$_REQUEST['project']:null;
        $buildTime = isset($_REQUEST['buildtime'])?$_REQUEST['buildtime']:null;
        if (empty($projectName) || empty($buildTime)) {
            die('Could not find phpunit test results');
        }

        $project = new Xinc_Project();
        $project->setName($projectName);
        try {
            $build = Xinc_Build::unserialize(
                $project,
                $buildTime,
                Xinc_Gui_Handler::getInstance()->getStatusDir()
            );
            $buildLabel = $build->getLabel();
            $timezone = $build->getConfigDirective('timezone');
            if ($timezone !== null) {
                Xinc_Timezone::set($timezone);
            } else {
                $xincTimezone = Xinc_Gui_Handler::getInstance()->getConfigDirective('timezone');
                if ($xincTimezone !== null) {
                    Xinc_Timezone::set($xincTimezone);
                } else {
                    Xinc_Timezone::set(Xinc_Gui_Handler::getInstance()->getSystemTimezone());
                }
            }

            $sourceFile = $build->getInternalProperties()->get('phpunit.file');

            if ($sourceFile != null && file_exists($sourceFile) && class_exists('XSLTProcessor')) {
                $xslFile = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'phpunit' . DIRECTORY_SEPARATOR . 'details.xsl';
                try {
                    $outputFileName = Xinc_Ini::getInstance()->get('tmp_dir', 'xinc') . DIRECTORY_SEPARATOR . 'phpunit_details_' . $projectName . '_' . $buildTime;
                } catch (Exception $e) {
                    Xinc_Logger::getInstance()->error('Cannot get xinc.ini configuration');
                    $outputFileName = 'phpunit_details_' . $projectName . '_' . $buildTime;
                }
                if (file_exists($outputFileName)) {
                    $details = file_get_contents($outputFileName);
                } else {
                    $details = $this->_transformResults($sourceFile, $xslFile, $outputFileName);
                }
                //$click = 'openMenuTab(\'phpunit-'.$projectName.'-'.$buildTimestamp.'\',\'PHPUnit - '.$projectName.'\',\''.$url.'\',null,false,false,\'auto\');';
                $title='PHPUnit Test Results';
                $buildTimeString = date('Y-m-d H:i:s', $build->getBuildTime()) . '-' . Xinc_Timezone::get();
                $content = str_replace(array('{title}','{details}','{projectName}','{buildLabel}','{buildTime}'),
                                        array($title,$details, $projectName, $buildLabel, $buildTimeString), $details);
            } else {
                $content = false;
            }
            Xinc_Timezone::reset();
            echo $content;
        } catch (Exception $e) {
            echo "Could not find phpunit test results";
        }
    }

    public function getPaths()
    {
        return array('/phpunit/results/', '/phpunit/results');
    }

    public function getTestResults(Xinc_Build_Interface $build)
    {
        require_once 'PEAR/Config.php';
        $statusDir = Xinc_Gui_Handler::getInstance()->getStatusDir();
        $projectName = $build->getProject()->getName();
        $buildTimestamp = $build->getBuildTime();
        $buildLabel = $build->getLabel();

        $templateFile = Xinc_Data_Repository::getInstance()->get(
            'templates' . DIRECTORY_SEPARATOR . 'dashboard' . DIRECTORY_SEPARATOR
            . 'detail' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR
            .'phpunit-summary.phtml'
        );

        $template = file_get_contents($templateFile);
        $url = '/phpunit/results/?project='.$projectName . '&buildtime=' . $buildTimestamp .'&f=results.html';

        $sourceFile = $build->getInternalProperties()->get('phpunit.file');

        if ($sourceFile != null && file_exists($sourceFile) && class_exists('XSLTProcessor')) {
            $xslFile = PEAR_Config::singleton()->get('data_dir') . DIRECTORY_SEPARATOR . 'Xinc' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'phpunit' . DIRECTORY_SEPARATOR . 'summary.xsl';
            try {
                $outputFileName = Xinc_Ini::getInstance()->get('tmp_dir', 'xinc') . DIRECTORY_SEPARATOR . 'phpunit_summary_' . $projectName . '_' . $buildTimestamp;
            } catch (Exception $e) {
                Xinc_Logger::getInstance()->error('Cannot get xinc.ini configuration');
                $outputFileName = 'phpunit_summary_' . $projectName . '_' . $buildTimestamp;
            }
            if (file_exists($outputFileName)) {
                $summary = file_get_contents($outputFileName);
            } else {
                $summary = $this->_transformResults($sourceFile, $xslFile, $outputFileName);
            }
            //$click = 'openMenuTab(\'phpunit-'.$projectName.'-'.$buildTimestamp.'\',\'PHPUnit - '.$projectName.'\',\''.$url.'\',null,false,false,\'auto\');';
            $detailsLink='<a href="'.$url.'">Details</a>';
            $content = str_replace(array('{detailsLink}','{summary}'),
                                    array($detailsLink,$summary), $template);
        } else {
            $content = false;
        }
        return $content;
    }

    private function _fixPackages(DOMDocument $document)
    {
        $testsuites = $document->getElementsByTagName('testsuite');

        foreach ($testsuites as $testsuite) {
            if (!$testsuite->hasAttribute('package')) {
                $testsuite->setAttribute('package', 'default');
            }
        }
    }

    private function _transformResults($xmlFile, $xslFile, $outputFileName)
    {
        $xml = new DOMDocument;
        $xml->load($xmlFile);
        $this->_fixPackages($xml);
        $xsl = new DOMDocument;

        $xsl->load($xslFile);
        // Configure the transformer
        $proc = new XSLTProcessor;
        $proc->importStyleSheet($xsl); // attach the xsl rules

        $return=$proc->transformToXml($xml);
        if ($return) {
            file_put_contents($outputFileName, $return);
        } else {
            $return = '<span style="color:red"><b>ERROR - could not transform</b></span>';
        }
        return $return;
    }

    public function init()
    {
        $detailWidget = Xinc_Gui_Widget_Repository::getInstance()
            ->getWidgetByClassName('Xinc_Plugin_Repos_Gui_Dashboard_Detail');

        $extension = new Xinc_Plugin_Repos_Gui_PhpUnitTestResults_Extension_Dashboard();
        $extension->setWidget($this);

        $detailWidget->registerExtension('BUILD_DETAILS', $extension);
    }

    public function registerExtension($extensionPoint, $extension)
    {
        $this->_extensions[$extensionPoint] = $extension;
    }

    public function getExtensionPoints()
    {
        return array();
    }

    public function hasExceptionHandler()
    {
        return false;
    }

    public function handleException(Exception $e)
    {
    }
}