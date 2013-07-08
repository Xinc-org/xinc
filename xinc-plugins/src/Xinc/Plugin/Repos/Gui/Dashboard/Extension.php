<?php
/**
 * Xinc - Continuous Integration.
 * Extension to the Dashboard Widget
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Dashboard
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

require_once 'Xinc/Build/Interface.php';
require_once 'Xinc/Gui/Widget/Extension/Interface.php';

abstract class Xinc_Plugin_Repos_Gui_Dashboard_Extension implements Xinc_Gui_Widget_Extension_Interface
{
    public abstract function getTitle();

    public abstract function getContent(Xinc_Build_Interface $build);

    public final function generate(Xinc_Build_Interface $build, $templateFile)
    {
        $templateContent = file_get_contents($templateFile);
        $id = strtolower(str_replace(' ', '-', $this->getTitle()));
        $content = $this->getContent($build);
        if ($content === false) {
            return false;
        }
        $result = str_replace(array('{id}', '{content}', '{title}'), 
                              array($id, $content, $this->getTitle()),
                              $templateContent);
        return $result;
    }
}