<?php
/**
 * PUT DESCRIPTION HERE
 * 
 * @package Xinc.Plugin
 * @author Arno Schneider
 * @version 2.0
 * @copyright 2007 David Ellis, One Degree Square
 * @license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
 *    This file is part of Xinc.
 *    Xinc is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU Lesser General Public License as published
 *    by the Free Software Foundation; either version 2.1 of the License, or    
 *    (at your option) any later version.
 *
 *    Xinc is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Lesser General Public License for more details.
 *
 *    You should have received a copy of the GNU Lesser General Public License
 *    along with Xinc, write to the Free Software
 *    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
require_once 'Xinc/Plugin/Base.php';
require_once 'Xinc/Plugin/Repos/Builder/Phing/Task.php';

require_once 'Xinc/Plugin/Repos/Publisher/Phing/Task.php';
require_once 'Xinc/Plugin/Repos/Phing/Listener.php';
require_once 'phing/Phing.php';
class Xinc_Plugin_Repos_Phing  extends Xinc_Plugin_Base
{
    
    public function __construct()
    {
        ini_set('track_errors', 1);
        
        /* set classpath */
        if (getenv('PHP_CLASSPATH')) {
            define('PHP_CLASSPATH', getenv('PHP_CLASSPATH') . PATH_SEPARATOR . get_include_path());
            ini_set('include_path', PHP_CLASSPATH);
        } else if (!defined('PHP_CLASSPATH')) {
            define('PHP_CLASSPATH', get_include_path());
        }
        /* Setup Phing environment */
        Phing::startup();
        
        /* 
          find phing home directory 
           -- if Phing is installed from PEAR this will probably be null,
           which is fine (I think).  Nothing uses phing.home right now.
        */
        Phing::setProperty('phing.home', getenv('PHING_HOME'));
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
             Xinc_Logger::getInstance()->error('Could not include'
                                              . ' necessary files. '
                                              . 'You may need to adopt your '
                                              . 'classpath to include Phing');
             return false;
        }
        return true;
    }
    public function getTaskDefinitions()
    {
        return array(new Xinc_Plugin_Repos_Builder_Phing_Task($this),
                     new Xinc_Plugin_Repos_Publisher_Phing_Task($this));
    }
    public function build(Xinc_Build_Interface &$build, $buildfile,$target)
    {
        //$phing = new Phing();
        $logLevel = Xinc_Logger::getInstance()->getLogLevel();
        $arguments = array();
        
        switch ($logLevel) {
            case Xinc_Logger::LOG_LEVEL_VERBOSE :
                $arguments[] = '-verbose';
                break;
        }
        
        //$arguments[] = '-quiet';
        //$arguments[] = '-listener';
        //$arguments[] = 'Xinc.Plugin.Repos.Phing.Listener';
        $arguments[] = '-logger';
        $arguments[] = 'phing.listener.DefaultLogger';
        $arguments[] = '-buildfile';
        $arguments[] = $buildfile;
        $arguments[] = $target;
        $arguments[] = '-Dxinc.buildlabel=' . $build->getLabel();
        foreach ($build->getProperties()->getAllProperties() as $name => $value) {
            $arguments[] = '-Dxinc.' . $name . '=' . $value;
        }
        exec('phing ' . implode(' ', $arguments) . ' 2>&1', $output, $returnValue);
        
        foreach ($output as $line) {
            Xinc_Logger::getInstance()->info($line);
        }
        
        switch ($returnValue) {
            case 0:
            case 1:
                if (strstr(implode('', $output), "BUILD FINISHED")) {
                    return true;
                } else {
                    $build->setStatus(Xinc_Build_Interface::FAILED);
                    return false;
                }
                break;
                
            case -1:
            case -2:
                $build->setStatus(Xinc_Build_Interface::FAILED);
                return false;
                break;
        }
        //$phing->execute($arguments);
        //Phing::setDefinedProperty('xinc.buildlabel', $build->getLabel());
        //try {
        //    $phing->runBuild();
        //    return true;
        //}
        //catch(Exception $e){
        //$build->setStatus(Xinc_Build_Interface::FAILED);
        //return false;
        //}
    }
}