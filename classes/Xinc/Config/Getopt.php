<?php
declare(encoding = 'utf-8');
/**
 * Xinc - Continuous Integration.
 * Parser for xinc command-line options.
 *
 * PHP version 5
 *
 * @category  Development
 * @package   Xinc.Config
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author    Jamie Talbot <username@example.org>
 * @copyright 2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
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

require_once 'Xinc/Config/Exception/Getopt.php';

/**
 * Command-line options parsing class.
 *
 * @package   Xinc.Config
 * @author    Andrei Zmievski <andrei@php.net>
 * @author    Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @author    Jamie Talbot
 * @copyright 2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Xinc_Config_Getopt
{
    /**
     * Enter description here...
     *
     * @param array $args
     * @param unknown_type $shortOptions
     * @param unknown_type $longOptions
     * @return unknown
     * @throws Xinc_Config_Exception_Getopt
     */
    public static function getopt(array $args, $shortOptions, $longOptions = null)
    {
        if (empty($args)) {
            return array(array(), array());
        }
        $opts = array();
        $nonOpts = array();

        if ($longOptions) {
            sort($longOptions);
        }

        if (isset($args[0]{0}) && $args[0]{0} != '-') {
            array_shift($args);
        }

        reset($args);

        while (list($i, $arg) = each($args)) {
            if ($arg == '--') {
                $nonOpts = array_merge($nonOpts, array_slice($args, $i + 1));
                break;
            }

            if ($arg{0} != '-' || (strlen($arg) > 1 && $arg{1} == '-' && !$longOptions)) {
                // This argument is a project file, but there could be other
                // options following, so just take the next one and continue.
            		$nonOpts = array_merge($nonOpts, array_slice($args, $i, 1));
            } else if (strlen($arg) > 1 && $arg{1} == '-') {
                self::parseLongOption(substr($arg, 2), $longOptions, $opts, $args);
            } else {
                self::parseShortOption(substr($arg, 1), $shortOptions, $opts, $args);
            }
        }

        return array($opts, $nonOpts);
    }

    /**
     * Parses short options from the command line.
     *
     * @param string $arg The argument to parse.
     * @param string $short_options Short options to match.
     * @param array $opts The options accumulator.
     * @param array $args The arguments passed to the option.
     * @throws Xinc_Config_Exception_Getopt If the option is unrecognised, or missing a required value.
     */
    protected static function parseShortOption($arg, $shortOptions, &$opts, &$args)
    {
        for ($i = 0; $i < strlen($arg); $i++) {
            $opt = $arg{$i};
            $optArg = null;

            if (($spec = strstr($shortOptions, $opt)) === false || $arg{$i} == ':') {
                throw new Xinc_Config_Exception_Getopt("unrecognized option -- $opt");
            }

            if (strlen($spec) > 1 && $spec{1} == ':') {
                if (strlen($spec) > 2 && $spec{2} == ':') {
                    if ($i + 1 < strlen($arg)) {
                        $opts[] = array($opt, substr($arg, $i + 1));
                        break;
                    }
                } else {
                    if ($i + 1 < strlen($arg)) {
                        $opts[] = array($opt, substr($arg, $i + 1));
                        break;
                    } else if (list(, $optArg) = each($args)) {
                    } else {
                        throw new Xinc_Config_Exception_Getopt("option requires an argument -- $opt");
                    }
                }
            }

            $opts[] = array($opt, $optArg);
        }
    }

    /**
     * Parses long options from the command line.
     *
     * @param string $arg The argument to parse.
     * @param array $long_options Long options to match.
     * @param array $opts The options accumulator.
     * @param array $args The arguments passed to the option.
     * @throws Xinc_Config_Exception_Getopt If the option is unrecognised, or missing a required value, or ambiguous.
     */
    protected static function parseLongOption($arg, $longOptions, &$opts, &$args)
    {
        @list($opt, $optArg) = explode('=', $arg);
        $optLen = strlen($opt);

        for ($i = 0; $i < count($longOptions); $i++) {
            $longOpt  = $longOptions[$i];
            $optStart = substr($longOpt, 0, $optLen);

            if ($optStart != $opt) continue;

            $optRest = substr($longOpt, $optLen);

            if ($optRest != '' && $opt{0} != '=' &&
            $i + 1 < count($longOptions) &&
            $opt == substr($longOptions[$i+1], 0, $optLen)) {
                throw new Xinc_Config_Exception_Getopt("option --$opt is ambiguous");
            }

            if (substr($longOpt, -1) == '=') {
                if (substr($longOpt, -2) != '==') {
                    if (!strlen($optArg) && !(list(, $optArg) = each($args))) {
                        throw new Xinc_Config_Exception_Getopt("option --$opt requires an argument");
                    }
                }
            } else if ($optArg) {
                throw new Xinc_Config_Exception_Getopt("option --$opt doesn't allow an argument");
            }

            $opts[] = array('--' . $opt, $optArg);
            return;
        }

        throw new Xinc_Config_Exception_Getopt("unrecognized option --$opt");
    }
}
