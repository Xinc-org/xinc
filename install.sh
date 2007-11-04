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

echo "Directory to install the Xinc PHP files: [/usr/share/php]"
read INCLUDE
if [ "$INCLUDE" = "" ]; then
    INCLUDE=/usr/share/php
fi
if [ ! -d $INCLUDE ]
then
    mkdir $INCLUDE
fi

#if [ `which php` = "" ]; then
#	declare PHP_BIN = "/usr/bin/php"
	echo "Path to the PHP binary: [/usr/bin/php]"
	read PHP_BIN
	if [ "$PHP_BIN" = "" ]; then
	   PHP_BIN=/usr/bin/php
	fi
#else
#	PHP_BIN=`which php`
#	echo "Using Path to the PHP binary: $PHP_BIN\n"
#fi


echo "Directory to install the Xinc run script: [/bin]"
read BIN
if [ "$BIN" = "" ]; then
    BIN=/bin
fi
if [ ! -d $BIN ]
then
    mkdir $BIN
fi

echo "Directory to keep the Xinc config files: [/etc/xinc]"
read ETC
if [ "$ETC" = "" ]; then
    ETC=/etc/xinc
fi
if [ ! -d $ETC ]
then
    mkdir $ETC
fi
# copy Xinc config-files to Config-Directory
if [ ! -f $ETC/config.xml ]
then
    cp -R etc/xinc/config.xml $ETC/
else
	echo "Do you want to overwrite$ETC/config.xml? [N / y]"
	read OVERWRITE_CONFIG
	if [ "$OVERWRITE_CONFIG" = "y" ]; then
		cp -Rf etc/xinc/config.xml $ETC/
	fi
fi
if [ ! -f $ETC/plugins.xml ]
then
    cp -R etc/xinc/plugins.xml $ETC/
else
	echo "Do you want to overwrite $ETC/plugins.xml? [N / y]"
	read OVERWRITE_PLUGIN
	if [ "$OVERWRITE_PLUGIN" = "y" ]; then
		cp -Rf etc/xinc/plugins.xml $ETC/
	fi
fi
echo "Directory to keep the Xinc log files: [/var/log]"
read LOG
if [ "$LOG" = "" ]; then
    LOG=/var/log
fi
if [ ! -d $LOG/xinc ]
then
    mkdir $LOG/xinc -p
fi

echo "Directory to install the Xinc start/stop daemon: [/etc/init.d]"
read INIT
if [ "$INIT" = "" ]; then
    INIT=/etc/init.d
fi
if [ ! -d $INIT ]
then
    mkdir $INIT
fi

echo "Do you want to install the SimpleProject example? [y / N]"
read INSTALL_EXAMPLE
if [ "$INSTALL_EXAMPLE" = "y" ]; then
    echo "Directory to install the Example to: [/etc/xinc/examples]"
    read EXAMPLE_DIR
    if [ "$EXAMPLE_DIR" = "" ]; then
    	EXAMPLE_DIR=/etc/xinc/examples
	fi
	if [ ! -d $EXAMPLE_DIR ]
	then
	    mkdir $EXAMPLE_DIR -p
	fi
	cp -R examples/SimpleProject $EXAMPLE_DIR/
	cat $EXAMPLE_DIR/SimpleProject/build.tpl.xml | sed -e "s#@EXAMPLE_DIR@#$EXAMPLE_DIR#" > $EXAMPLE_DIR/SimpleProject/build.xml
	rm $EXAMPLE_DIR/SimpleProject/build.tpl.xml
	cat $EXAMPLE_DIR/SimpleProject/publish.tpl.xml | sed -e "s#@EXAMPLE_DIR@#$EXAMPLE_DIR#" > $EXAMPLE_DIR/SimpleProject/publish.xml
	rm $EXAMPLE_DIR/SimpleProject/publish.tpl.xml
	cat examples/config.tpl.xml | sed -e "s#@EXAMPLE_DIR@#$EXAMPLE_DIR#" > $ETC/config.xml
fi

echo "Directory to install the Xinc web-application: [/var/www/xinc]"
read WEB_DIR
if [ "$WEB_DIR" = "" ]; then
	WEB_DIR="/var/www/xinc"
fi

if [ ! -d $WEB_DIR ]; then
    mkdir $WEB_DIR -p
fi
cp web/index.php $WEB_DIR/
cp web/.htaccess $WEB_DIR/

echo "Port of Xinc web-application: [8080]"
read PORT
if [ "$PORT" = "" ]; then
	PORT="8080"
fi
cat web/www.tpl.conf | sed -e "s#@INCLUDE@#$INCLUDE#" | sed -e "s#@WEB_DIR@#$WEB_DIR#" | sed -e "s#@PORT@#$PORT#" > $ETC/www.conf



# copy Xinc classes to include path
cp classes/Xinc.php $INCLUDE/
cp -R classes/Xinc $INCLUDE/

# copy bin script to bin
cat bin/xinc | sed -e "s#@PHP_BIN@#$PHP_BIN#" > $BIN/xinc
chmod ugo+x $BIN/xinc

# copy init.d script to init.d
cat etc/init.d/xinc | sed -e "s#@ETC@#$ETC#" | sed -e "s#@LOG@#$LOG#" > $INIT/xinc
chmod ugo+x $INIT/xinc

echo 'Xinc installation complete.';
echo "- Please include $ETC/www.conf in your apache virtual hosts."
echo "- Please enable mod-rewrite."