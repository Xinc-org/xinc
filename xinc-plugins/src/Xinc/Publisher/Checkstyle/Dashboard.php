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

require_once 'Xinc/Build/Interface.php';
require_once 'Xinc/Plugin/Repos/Gui/Dashboard/Detail/Extension.php';
require_once 'Xinc/Publisher/Checkstyle/Widget.php';

class Xinc_Publisher_Checkstyle_Dashboard extends Xinc_Plugin_Repos_Gui_Dashboard_Detail_Extension
{
    /**
     * @var Xinc_Publisher_Checkstyle_Widget
     */
    private $widget;

    /**
     * @var Xinc_Build_Interface
     */
    private $build;

    public function setWidget(Xinc_Publisher_Checkstyle_Widget $widget)
    {
        $this->widget = $widget;
    }

    /**
     * Returns title of the widget.
     *
     * @return string Title of widget.
     */
    public function getTitle()
    {
        return 'Checkstyle Summary';
    }

    /**
     * Returns the content of this widget.
     *
     * @return mixed String of HTML or false if it like not to be viewed.
     */
    public function getContent(Xinc_Build_Interface $build)
    {
        return $this->widget->getTestResults($build);
    }
}
