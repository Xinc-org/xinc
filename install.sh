#!/bin/sh
################################################################################
# Xinc - Continuous Integration for PHP
#
# Install Script.                                         
# 
# package Xinc
# author David Ellis
# author Gavin Foster
# author Arno Schneider
# version 2.0
# copyright 2007 David Ellis, One Degree Square
# license  http://www.gnu.org/copyleft/lgpl.html GNU/LGPL, see license.php
# 	This file is part of Xinc.
# 	Xinc is free software; you can redistribute it and/or modify
# 	it under the terms of the GNU Lesser General Public License as published by
# 	the Free Software Foundation; either version 2.1 of the License, or
# 	(at your option) any later version.
# 
# 	Xinc is distributed in the hope that it will be useful,
# 	but WITHOUT ANY WARRANTY; without even the implied warranty of
# 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# 	GNU Lesser General Public License for more details.
# 
# 	You should have received a copy of the GNU Lesser General Public License
# 	along with Xinc, write to the Free Software
# 	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
################################################################################

INTERACTIVE=true

if [ "$1" = "--non-interactive" ]; then
	INTERACTIVE=false
	echo "Installing with default values"
fi

DEFAULT_INCLUDE=/usr/share/php
echo "Directory to install the Xinc PHP files: [/usr/share/php]"
if [ "$INTERACTIVE" = true ]; then read INCLUDE; fi
if [ "$INCLUDE" = "" ]; then
    INCLUDE=$DEFAULT_INCLUDE
fi
if [ ! -d $INCLUDE ]
then
    mkdir $INCLUDE
fi

if [ ! -d $INCLUDE/data/Xinc ]
then
   mkdir $INCLUDE/data/Xinc -p
fi

#if [ `which php` = "" ]; then
#	declare PHP_BIN = "/usr/bin/php"
	echo "Path to the PHP binary: [/usr/bin/php]"
if [ "$INTERACTIVE" = true ]; then read PHP_BIN; fi
	if [ "$PHP_BIN" = "" ]; then
	   PHP_BIN=/usr/bin/php
	fi
#else
#	PHP_BIN=`which php`
#	echo "Using Path to the PHP binary: $PHP_BIN\n"
#fi


echo "Directory to install the Xinc run script: [/bin]"
if [ "$INTERACTIVE" = true ]; then read BIN; fi
if [ "$BIN" = "" ]; then
    BIN=/bin
fi
if [ ! -d $BIN ]
then
    mkdir $BIN
fi

echo "Directory to keep the Xinc config files: [/etc/xinc]"
if [ "$INTERACTIVE" = true ]; then read ETC; fi

if [ "$ETC" = "" ]; then
    ETC=/etc/xinc
fi
if [ ! -d $ETC ]
then
    mkdir $ETC
    mkdir $ETC/conf.d
else 
    if [ ! -d $ETC/conf.d ]; then mkdir $ETC/conf.d; fi
fi

# copy Xinc config-files to Config-Directory
if [ ! -f $ETC/system.xml ]
then
    cp -R etc/xinc/system.xml $ETC/
else
	echo "Do you want to overwrite$ETC/system.xml? [N / y]"
	if [ "$INTERACTIVE" = true ]; then read OVERWRITE_CONFIG; fi
	if [ "$OVERWRITE_CONFIG" = "y" ]; then
		cp -Rf etc/xinc/system.xml $ETC/
	fi
fi


echo "Directory to keep the Xinc Projects and Status information: [/var/xinc]"
if [ "$INTERACTIVE" = true ]; then read XINCDIR; fi
if [ "$XINCDIR" = "" ]; then
    XINCDIR=/var/xinc
fi
DATADIR="$XINCDIR/projects"
STATUSDIR="$XINCDIR/status"
if [ ! -d $XINCDIR ]
then
    mkdir $XINCDIR -p
    mkdir $DATADIR -p
    mkdir $STATUSDIR -p
fi


echo "Directory to keep the Xinc log files: [/var/log]"
if [ "$INTERACTIVE" = true ]; then read LOG; fi
if [ "$LOG" = "" ]; then
    LOG=/var/log
fi
if [ ! -d $LOG/xinc ]
then
    mkdir $LOG/xinc -p
fi

echo "Directory to install the Xinc start/stop daemon: [/etc/init.d]"
if [ "$INTERACTIVE" = true ]; then read INIT; fi
if [ "$INIT" = "" ]; then
    INIT=/etc/init.d
fi
if [ ! -d $INIT ]
then
    mkdir $INIT
fi

echo "Do you want to install the SimpleProject example? [y / N]"
if [ "$INTERACTIVE" = true ]; then read INSTALL_EXAMPLE; fi
if [ "$INSTALL_EXAMPLE" = "y" ]; then
    echo "Directory to install the Example to: [$DATADIR]"
    if [ "$INTERACTIVE" = true ]; then read EXAMPLE_DIR; fi
    if [ "$EXAMPLE_DIR" = "" ]; then
    	EXAMPLE_DIR=$DATADIR
	fi
	if [ ! -d $EXAMPLE_DIR ]
	then
	    mkdir $EXAMPLE_DIR -p
	fi
	cp -R examples/SimpleProject $EXAMPLE_DIR/
	cp -R examples/empty.xml $ETC/conf.d/
	cat $EXAMPLE_DIR/SimpleProject/build.tpl.xml | sed -e "s#@EXAMPLE_DIR@#$EXAMPLE_DIR#" > $EXAMPLE_DIR/SimpleProject/build.xml
	rm $EXAMPLE_DIR/SimpleProject/build.tpl.xml
	cat $EXAMPLE_DIR/SimpleProject/publish.tpl.xml | sed -e "s#@EXAMPLE_DIR@#$EXAMPLE_DIR#" > $EXAMPLE_DIR/SimpleProject/publish.xml
	rm $EXAMPLE_DIR/SimpleProject/publish.tpl.xml
	cat examples/simpleproject.tpl.xml | sed -e "s#@EXAMPLE_DIR@#$EXAMPLE_DIR#" > $ETC/conf.d/simpleproject.xml
fi

echo "Directory to install the Xinc web-application: [/var/www/xinc]"
if [ "$INTERACTIVE" = true ]; then read WEB_DIR; fi
if [ "$WEB_DIR" = "" ]; then
	WEB_DIR="/var/www/xinc"
fi

if [ ! -d $WEB_DIR ]; then
    mkdir $WEB_DIR -p
fi
# cp web/handler.php $WEB_DIR/
cp web/.htaccess $WEB_DIR/
cp web/* -Rf $WEB_DIR/ -Rf
rm $WEB_DIR/www.tpl.conf
rm $WEB_DIR/handler.php.tpl

echo "IP of Xinc web-application: [127.0.0.1]"
if [ "$INTERACTIVE" = true ]; then read IP; fi
if [ "$IP" = "" ]; then
	IP="127.0.0.1"
fi

echo "Port of Xinc web-application: [8080]"
if [ "$INTERACTIVE" = true ]; then read PORT; fi
if [ "$PORT" = "" ]; then
	PORT="8080"
fi

cat web/www.tpl.conf | sed -e "s#@INCLUDE@#$INCLUDE#" | sed -e "s#@WEB_DIR@#$WEB_DIR#" | sed -e "s#@PORT@#$PORT#" | sed -e "s#@IP@#$IP#" > $ETC/www.conf
cat web/handler.php.tpl | sed -e "s#@STATUSDIR@#$STATUSDIR#" | sed -e "s#@ETC@#$ETC#" > $WEB_DIR/handler.php



# copy Xinc classes to include path
cp classes/Xinc.php $INCLUDE/
cp -R classes/Xinc $INCLUDE/
cp -Rf data/* $INCLUDE/data/Xinc

# copy bin script to bin
cat bin/xinc | sed -e "s#@PHP_BIN@#$PHP_BIN#" | sed -e "s#@BIN_DIR@#/bin#" > $BIN/xinc
chmod ugo+x $BIN/xinc

# copy init.d script to init.d
cat etc/init.d/xinc | sed -e "s#@ETC@#$ETC#" | sed -e "s#@LOG@#$LOG#" | sed -e "s#@STATUSDIR@#$STATUSDIR#" | sed -e "s#@DATADIR@#$DATADIR#" > $INIT/xinc
chmod ugo+x $INIT/xinc

echo 'Xinc installation complete.';
echo "- Please include $ETC/www.conf in your apache virtual hosts."
echo "- Please enable mod-rewrite."
echo "- To add projects to Xinc, copy the project xml to /etc/xinc/conf.d/"
echo "- To start xinc execute: sudo /etc/init.d/xinc start"
