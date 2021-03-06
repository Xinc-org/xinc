<?php
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
                       "name":"%s",
                       "url":"%s",
                       "text":"%s",
                       "iconCls":"%s",
                       "singleClickExpand":%s,
                       "leaf": %s,
                       "children":[%s]}';

    private $id;

    private $strName;

    private $strUrl;

    private $strText;

    private $bSingleClickExpand;

    private $bLeaf;

    private $arChildren;

    public function __construct(
        $id, $strName, $strUrl, $strText, $strIconClass = '', $bSingleClickExpand=true,
        $bLeaf=true, $arChildren=array()
    ) {
        $this->id = $id;
        $this->strName = $strName;
        $this->strUrl = $strUrl;
        $this->strText = $strText;
        $this->strIconClass = $strIconClass;
        $this->bSingleClickExpand = $bSingleClickExpand;
        $this->bLeaf = $bLeaf;
        $this->arChildren = $arChildren;
    }

    public function generate()
    {
        $result = call_user_func_array(
            'sprintf',
            array(
                self::TEMPLATE,
                $this->id,
                $this->strName,
                $this->strUrl,
                $this->strText,
                $this->strIconClass,
                $this->bSingleClickExpand ? 'true':'false',
                $this->bLeaf ? 'true':'false',
                $this->_generateChildren()
            )
        );

        return $result;
    }

    protected function _generateChildren()
    {
        return implode(',', $this->arChildren);
    }

    public function getExtensionPoint()
    {
        return 'MAIN_MENU_ITEMS';
    }
}