# Introduction #

This document will describe the usage of Xinc for [continuous integration](http://en.wikipedia.org/wiki/Continuous_integration) development. As it manages tasks, you can also use it for cron replacement.

# Table of content #



# Installation #

## Requirements Xinc Common ##

  * PHP 5.2.x
  * PHP-XSL
  * PHP-XML

## Requirements Xinc Server ##

  * Pear >= 1.9.4
  * Phing > 2.4.0
  * Xinc Common = 2.3

## Requirements Xinc Web ##

  * Pear >= 1.9.4
  * Xinc Common = 2.3
  * Apache or compatible with rewrite module + php5 support

## Requirements Xinc Plugins ##

  * Pear >= 1.9.4
  * Xinc Common = 2.3

### Optional (Depending on Plugin) ###

  * Subversion >=1.2
  * Pear: VersionControl\_SVN >= 0.5.2
  * Pear: VersionControl\_Git > 0.4.4
  * Pear: Mail > 1.2.0
  * Pear: PHPUnit > 3.5.0
  * Pear: PhpDocumentor > 1.4.0
  * Pear: `PHP_CodeSniffer` > 1.3.0
  * Pear: Archive\_Tar > 1.3.0
  * Pecl: Xdebug > 2.0.0

## Deploy using pear-channel ##

The Xinc 2.3 releases can be optained via pear channel server. To get the latest alpha version of Xinc via pear:

```
pear channel-discover pear.elektrischeslicht.de
pear install xinc/Xinc-alpha
pear install xinc/XincContrib-alpha
pear install xinc/XincServer-alpha
pear install xinc/XincWeb-alpha
pear install xinc/XincPlugins-alpha
```

Or if you want to resolve all dependencies use

```
pear install --alldeps xinc/XincContrib-alpha
pear install --alldeps xinc/XincServer-alpha
pear install --alldeps xinc/XincClient-alpha
pear install --alldeps xinc/XincPlugins-alpha
```

## Finalizing installation ##

### Xinc Server ###

You need to copy from
```
- PEAR_DATA/XincServer/etc/xinc
- PEAR_DATA/XincServer/etc/init.d/xinc
```
to the appropriated places.

### Xinc Web ###

Webpath, .htaccess, ...

# Configuration #

For configuration see the Documentation [Website](http://elektrischeslicht.de/xinc/book/).