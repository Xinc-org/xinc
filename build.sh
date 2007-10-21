#!/bin/sh

rm -r trunk
svn export https://xinc.googlecode.com/svn/trunk trunk --username gavinleefoster

rm -r xinc
mkdir xinc

cp -r trunk/bin xinc/
cp -r trunk/classes xinc/
cp -r trunk/examples xinc/
cp -r trunk/install.sh xinc/
cp -r trunk/README.txt xinc/
cp -r trunk/resources xinc/
cp -r trunk/web xinc/

tar czvf xinc-1.0.tar.gz xinc/

rm -rf trunk
rm -rf xinc
