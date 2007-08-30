#!/bin/sh

echo "Directory to install the Xinc PHP files: [/usr/share/php]"
read INCLUDE
if [ "$INCLUDE" = "" ]; then
    INCLUDE=/usr/share/php
fi
if [ ! -d $INCLUDE ]
then
    mkdir $INCLUDE
fi

echo "Path to the PHP binary: [/usr/bin/php]"
read PHP_BIN
if [ "$PHP_BIN" = "" ]; then
    PHP_BIN=/usr/bin/php
fi
if [ ! -d $PHP_BIN ]
then
    mkdir $PHP_BIN
fi

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

echo "Directory to keep the Xinc log files: [/var/log]"
read LOG
if [ "$LOG" = "" ]; then
    LOG=/var/log
fi
if [ ! -d $LOG ]
then
    mkdir $LOG
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

# copy Xinc classes to include path
cp classes/Xinc.php $INCLUDE/
cp -R classes/Xinc $INCLUDE/

# copy bin script to bin
cat bin/xinc | sed -e "s#@PHP_BIN@#$PHP_BIN#" > $BIN/xinc
chmod ugo+x $BIN/xinc

# copy init.d script to init.d
cat examples/init.d/xinc | sed -e "s#@ETC@#$ETC#" | sed -e "s#@LOG@#$LOG#" > $INIT/xinc
chmod ugo+x $INIT/xinc

echo 'Xinc installation complete.';