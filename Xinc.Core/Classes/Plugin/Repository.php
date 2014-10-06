<?php
/**
 * Xinc - Continuous Integration.
 * Repository to manage all registered Plugins
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Core
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
 * @link      http://code.google.com/p/xinc/
 */

namespace Xinc\Core\Plugin;

class Repository extends \Xinc\Core\Singleton
{
    /**
     * @var Xinc_Plugin_Task_Interface[]
     */
    private $definedTasks = array();

    /**
     * @var Xinc_Plugin_Interface[]
     */
    private $plugins = array();

    /**
     * Holding a reference from the task to
     * the slot they are working in
     *
     * @var array
     */
    private $slotReference = array();

    /**
     * Load the plugin config, which is handled by composer xinc package installer.
     */
    public function loadPluginConfig()
    {
        // @TODO Only load active packages.
        $packagePersistence = new \Xinc\Packager\Persistence();
        $packages = $packagePersistence->getPackagesAsClass();

        // @var $package \Xinc\Packager\Models\Package
        foreach ($packages as $package) {
            $this->registerPackage($package);
        }
    }

    /**
     * Loads plugins, tca, ... from a package.
     *
     * @param \Xinc\Packager\Models\Package $package The package to register.
     * @return void
     */
    public function registerPackage(\Xinc\Packager\Models\Package $package)
    {
        $configPath = $this->getConfigurationPath($package);
        $this->registerTca($configPath);
        $this->registerSignals($configPath);
        $this->registerSlots($configPath);
        $this->registerPlugins($configPath);
    }

    public function getConfigurationPath(\Xinc\Packager\Models\Package $package)
    {
        // @TODO get it working from other pathes
        return 'Packages/' . $package->getPathPackage() . 'Configuration/';
    }

    /**
     * TCA Configuration
     * Not yet Supported
     *
     * @TODO
     * @return void
     */
    public function registerTca($configPath)
    {
        $files = glob($configPath . 'TCA/*.php');
    }

    /**
     * Signals Configuration
     *
     * @param string $configPath path to the configuration of the package wich includes the Signals directory.
     * @return void
     */
    public function registerSignals($configPath)
    {
        $files = glob($configPath . 'Signals/*.php');
    }

    /**
     * Slot Configuration
     *
     * @param string $configPath path to the configuration of the package wich includes the Signals directory.
     * @return void
     */
    public function registerSlots($configPath)
    {
        $files = glob($configPath . 'Slots/*.php');
    }

    /**
     * Register more Configuration data
     *
     * @param string $configPath path to the configuration of the package wich includes the Signals directory.
     * @return void
     */
    public function registerPlugins($configPath)
    {
        $files = glob($configPath . 'Plugin.php');
        foreach ($files as $file) {
            if (is_readable($file)) {
                include $file;
            }
        }
    }

    /**
     * Enter description here...
     *
     * @param Xinc_Plugin_Interface $plugin
     *
     * @return boolean
     * @throws Xinc_Plugin_Task_Exception
     */
    public function registerPlugin(Xinc_Plugin_Interface $plugin)
    {
        $tasks = $plugin->getTaskDefinitions();

        foreach ($tasks as $task) {
        }

        $widgets = $plugin->getGuiWidgets();
        foreach ($widgets as $widget) {
            Xinc_Gui_Widget_Repository::getInstance()->registerWidget($widget);
        }
        $apiModules = $plugin->getApiModules();
        foreach ($apiModules as $apiMod) {
            Xinc_Api_Module_Repository::getInstance()->registerModule($apiMod);
        }
        $this->plugins[] = $plugin;
    }

    /**
     * Returns Plugin Iterator
     *
     * @return Xinc_Iterator
     */
    public function getPlugins()
    {
        return new Xinc_Plugin_Iterator($this->plugins);
    }
}
