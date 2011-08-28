<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Phing plugin to execute the phing command for a certain build file
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos
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

require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Ini.php';
require_once 'Xinc/Plugin/Repos/Builder/Phing/Task.php';
require_once 'Xinc/Plugin/Repos/Publisher/Phing/Task.php';

class Xinc_Plugin_Repos_Phing  extends Xinc_Plugin_Base
{
    public function __construct()
    {
    }

    public function validate()
    {
        $res = @include_once('phing/Phing.php');
        if ($res) {
            if (!class_exists('phing')) {
                Xinc_Logger::getInstance()->error('Required Phing-Class not found');
                return false;
            }
        } else {
            Xinc_Logger::getInstance()->error(
                'Could not include necessary files. '
                . 'You may need to adopt your classpath to include Phing'
            );
             return false;
        }
        return true;
    }

    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Builder_Phing_Task($this),
                     new Xinc_Plugin_Repos_Publisher_Phing_Task($this));
    }

    /**
     * calling phing
     *
     * @param Xinc_Build_Interface $build
     * @param string $buildfile
     * @param string $target
     * @param string $extraParams
     * @param string $workingDir
     *
     * @return boolean
     */
    public function build(
        Xinc_Build_Interface &$build, $buildfile, $target,
        $extraParams = null, $workingDir = null
    ) {
        //$phing = new Phing();
        $logLevel = Xinc_Logger::getInstance()->getLogLevel();
        $arguments = array();
        
        switch ($logLevel) {
            case Xinc_Logger::LOG_LEVEL_VERBOSE :
                $arguments[] = '-verbose';
                break;
        }
        $oldPwd = getcwd();
        /**
         * set workingdir if not set from task,
         * use project dir
         */
        if ($workingDir == null) {
            $workingDir = Xinc::getInstance()->getProjectDir() . DIRECTORY_SEPARATOR . $build->getProject()->getName();
        }
        if (is_dir($workingDir)) {
            Xinc_Logger::getInstance()->debug('Switching to directory: ' . $workingDir);
            chdir($workingDir);
        }

        $logFile = getcwd() . DIRECTORY_SEPARATOR . md5($build->getProject()->getName() . time()) . '.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        $arguments[] = '-logger';
        //$arguments[] = 'phing.listener.DefaultLogger';
        $arguments[] = 'phing.listener.NoBannerLogger';
        $arguments[] = '-logfile';
        $arguments[] = $logFile;
        $arguments[] = '-buildfile';
        $arguments[] = $buildfile;
        if ($target != null) {
            $arguments[] = $target;
        }
        $arguments[] = '-Dxinc.buildlabel=' . $this->_encodeParam($build->getLabel());
        $arguments[] = '-Dprojectname=' . $this->_encodeParam($build->getProject()->getName());
        $arguments[] = '-Dcctimestamp=' . $this->_encodeParam($build->getBuildTime());

        foreach ($build->getProperties()->getAllProperties() as $name => $value) {
            $arguments[] = '-Dxinc.' . $this->_encodeParam($name) . '=' . $this->_encodeParam($value);
        }
        try {
            $phingPath = Xinc_Ini::getInstance()->get('path', 'phing');
        } catch (Exception $e) {
            $phingPath = null;
        }
        if (empty($phingPath)) {
            if (DIRECTORY_SEPARATOR != '/') {
                /**
                 * windows has the phing command inside the bin_dir directory
                 */
                $phingPath = PEAR_Config::singleton()->get('bin_dir') . DIRECTORY_SEPARATOR . 'phing';
            } else {
                $phingPath = 'phing';
            }
        }
        if (DIRECTORY_SEPARATOR == '/') {
            $redirect = "2>&1";
        } else {
            $redirect = "";
        }
        $command = $phingPath . ' ' . implode(' ', $arguments) . ' ' . $extraParams . ' ' . $redirect;
        exec($command, $output, $returnValue);
        chdir($oldPwd);

        $buildSuccess = false;

        if (file_exists($logFile)) {
            $fh = fopen($logFile, 'r');
            if (is_resource($fh)) {
                while ($line = fgets($fh)) {
                    Xinc_Logger::getInstance()->info($line);
                    if (strstr($line, "BUILD FINISHED")) {
                        $buildSuccess = true;
                    }
                }
                fclose($fh);
            }
            unlink($logFile);
        }
        if (count($output)>0) {
            Xinc_Logger::getInstance()->error('Errors on command line:');
            foreach ($output as $line) {
                Xinc_Logger::getInstance()->error($line);
            }
        }

        switch ($returnValue) {
            case 0:
            case 1:
                if ($buildSuccess) {
                    return true;
                } else {
                    $build->error('Phing command '.$command.' exited with status: ' . $returnValue);
                    $build->setStatus(Xinc_Build_Interface::FAILED);
                    return false;
                }
                break;
            case -1:
            case -2:
                $build->error('Phing build script: '.$command.' exited with status: ' . $returnValue);
                $build->setStatus(Xinc_Build_Interface::FAILED);
                return false;
                break;
        }
        return false;
    }

    /**
     * Makes the passed properties command line safe
     *
     * @param string $value
     *
     * @return string
     */
    private function _encodeParam($value)
    {
        return '"'. str_replace('"', '\"', $value) .'"';
    }
}