# Introduction #

This document will describe the usage of Xinc for [continuous integration](http://en.wikipedia.org/wiki/Continuous_integration) development. As it manages tasks, you can also use it for cron replacement.

# Table of content #



# Installation #

## Requirements ##

  * Pear > 1.4.0
  * Phing > 2.4.0
  * pear needs to be in the includepath
  * For Web Frontend:
    * Apache or compatible with rewrite module + php5 support

### Optional ###

  * Subversion >=1.2
  * Pear: VersionControl\_SVN
  * Pear: VersionControl\_Git > 0.4.4
  * Pear: Mail > 1.2.0
  * Pear: PHPUnit > 3.5.0
  * Pear: PhpDocumentor > 1.4.0
  * Pear: `PHP_CodeSniffer` > 1.3.0
  * Pear: Archive\_Tar > 1.3.0
  * Pear: PackageFileManager2 > 1.0.2
  * Pecl: Xdebug > 2.0.0

## Deploy using pear-channel ##

The Xinc 2.2 releases can be optained via pear channel server. To get the latest alpha version of Xinc via pear:

```
pear channel-discover pear.elektrischeslicht.de
pear channel-discover components.ez.no
pear install xinc/Xinc
```

Or if you want to resolve all dependencies use

```
pear install --alldeps xinc/Xinc
```

## Finalizing installation through postinstall scripts ##

Finally you need to run

```
pear run-scripts xinc/Xinc
```

which will execute the pear post-installation script.

# Configuration #

For configuration see the Documentation [Website](http://elektrischeslicht.de/xinc/book/).