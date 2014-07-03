<?php
/**
 * Xinc - Continuous Integration.
 * Exception, for file IO problems.
 *
 * PHP version 5
 *
 * @category   Development
 * @package    Xinc.Core
 * @subpackage Exception
 * @author     Alexander Opitz <opitz.alexander@gmail.com>
 * @copyright  2012 Alexander Opitz, Leipzig
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

namespace Xinc\Core\Exception;

class IOException extends \Xinc\Core\Exception
{
    const FAILURE_IO = 1;
    const FAILURE_NOT_WRITEABLE = 2;
    const FAILURE_NOT_READABLE = 3;
    const FAILURE_NOT_FOUND = 4;

    /**
     * @var string Path of directory/file/stream that failed.
     */
    private $strResourcePath = null;

    /**
     * @var string Name of directory/file/stream that failed.
     */
    private $strResourceName = null;

    /**
     * Constructor, generates an Exception Message
     *
     * @param string    $strResourceName Name of directory/file/stream that failed.
     * @param string    $strResourcePath Path of directory/file/stream that failed.
     * @param string    $strMessage      Exception message.
     * @param integer   $nCode           Code of failure from this consts.
     * @param Exception $previous        Exception if nested exception.
     */
    public function __construct(
        $strResourceName,
        $strResourcePath = null,
        $strMessage = null,
        $nCode = self::FAILURE_IO,
        Exception $previous = null
    ) {
        $this->strResourceName = $strResourceName;
        $this->strResourcePath = $strResourcePath;
        parent::__construct($this->getErrorMessage($nCode, $strMessage), $nCode, $previous);
    }

    /**
     * Returns the name of the failed resource.
     *
     * @return string The name of the resource that failed.
     */
    public function getResourceName()
    {
        return $this->strResourceName;
    }

    /**
     * Returns the path of the failed resource.
     *
     * @return string The path of the resource that failed.
     */
    public function getResourcePath()
    {
        return $this->strResourcePath;
    }

    /**
     * Builds and returns an error message for this exception.
     *
     * @param integer $nCode      Code of failure from this consts.
     * @param string  $strMessage Exception message.
     *
     * @return string A message for this error.
     */
    protected function getErrorMessage($nCode, $strMessage = null)
    {
        $strReturn = 'Failure: ';
        if (null !== $this->strResourcePath) {
            $strReturn = 'Path: "' . $this->strResourcePath . '" ';
        }
        $strReturn .= 'Name: "' . $this->strResourceName . '"';
        $strReturn .= ' Code: ';
        switch ($nCode) {
            case self::FAILURE_IO:
                $strReturn .= 'General IO Error';
                break;
            case self::FAILURE_NOT_WRITEABLE:
                $strReturn .= 'not writeable';
                break;
            case self::FAILURE_NOT_READABLE:
                $strReturn .= 'not readable';
                break;
            case self::FAILURE_NOT_FOUND:
                $strReturn .= 'not found';
                break;
            default:
                $strReturn .= (string) $nCode;
                break;
        }
        if (null !== $strMessage) {
            $strReturn .= ' with message: "' . $strMessage . '"';
        }
        return $strReturn;
    }
}
