<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Extension to the Dashboard Menu Widget
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Plugin.Repos.Gui.Menu.Extension
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

require_once 'Xinc/Gui/Widget/Extension/Interface.php';

class Xinc_Plugin_Repos_Gui_Menu_Extension_Item
    implements Xinc_Gui_Widget_Extension_Interface
{
    const TEMPLATE = '{"id":"%s",
                       "text":"%s",
                       "singleClickExpand":%s,
                       "url":%s, "iconCls":"%s",
                       "title":"%s","scripts":%s,
                       "leaf": %s,
                       "iframe":%s,
                       "height":"%s",
                       "children":[%s]}';

    private $_id;

    private $_text;

    private $_singleClickExpand;

    private $_url;

    private $_iconCls;

    private $_title;

    private $_scripts;

    private $_leaf;

    private $_frame;

    private $_height;

    private $_children;

    public function __construct(
        $id, $text, $singleClickExpand=true, $url=null, $iconCls=null, $title='',
        $scripts=false, $leaf=true, $frame=false, $height='auto', $children=array()
    ) {
        $this->_id = $id;
        $this->_text = $text;
        $this->_singleClickExpand = $singleClickExpand;
        $this->_url = $url;
        $this->_iconCls = $iconCls;
        $this->_title = $title;
        $this->_scripts = $scripts;
        $this->_leaf = $leaf;
        $this->_frame = $frame;
        $this->_height = $height;
        $this->_children = $children;
    }

    public function generate()
    {
        $result = call_user_func_array('sprintf', array(self::TEMPLATE, 
                                       $this->_id, 
                                       $this->_text,
                                       $this->_singleClickExpand ? 'true':'false',
                                       $this->_url != null ? "\"$this->_url\"": "null",
                                       $this->_iconCls,
                                       $this->_title,
                                       $this->_scripts ? 'true':'false',
                                       $this->_leaf ? 'true':'false',
                                       $this->_frame ? 'true':'false',
                                       $this->_height,
                                       $this->_generateChildren()
                                       ));

        return $result;
    }

    protected function _generateChildren()
    {
        return implode(',', $this->_children);
    }

    public function getExtensionPoint()
    {
        return 'MAIN_MENU_ITEMS';
    }
}