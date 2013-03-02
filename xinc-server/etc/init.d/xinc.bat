@ECHO OFF

REM :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
REM : Xinc - Continuous Integration for PHP.                                         
REM : 
REM : package Xinc
REM : author David Ellis
REM : author Gavin Foster
REM : author Arno Schneider
REM : version 2.0
REM : copyright 2007 David Ellis, One Degree Square
REM : license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
REM : 	This file is part of Xinc.
REM : 	Xinc is free software; you can redistribute it and/or modify
REM : 	it under the terms of the GNU Lesser General Public License as published by
REM : 	the Free Software Foundation; either version 2.1 of the License, or
REM : 	(at your option) any later version.
REM : 
REM : 	Xinc is distributed in the hope that it will be useful,
REM : 	but WITHOUT ANY WARRANTY; without even the implied warranty of
REM : 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
REM : 	GNU Lesser General Public License for more details.
REM : 
REM : 	You should have received a copy of the GNU Lesser General Public License
REM : 	along with Xinc, write to the Free Software
REM : 	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
REM :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

SET XINC_CONFIG="@ETC@\system.xml"
SET XINC_PROJECTS="@ETC@\conf.d\*.xml"
SET XINC_LOG="@LOG@/xinc.log"
SET XINC_STATUS="@STATUSDIR@"
SET XINC_DATADIR="@DATADIR@"

 
SET XINC_DAEMON="@BIN_DIR@\xinc.bat"

: send log output to text-log-file
: logfile is going to be used for build-based logging only
SET XINC_OPTIONS=-f %XINC_CONFIG% -p %XINC_DATADIR% -s %XINC_STATUS% -w %XINC_DATADIR% -l %XINC_LOG% %XINC_PROJECTS%

%XINC_DAEMON% %XINC_OPTIONS%
