<table><tr><td><b>Xinc</b>

<blockquote>is a <a href='http://www.martinfowler.com/articles/continuousIntegration.html#EveryCommitShouldBuildTheMainlineOnAnIntegrationMachine'>continuous integration and control server</a> written in PHP 5. It has built-in support for <a href='http://subversion.tigris.org/'>Subversion</a>, <a href='http://git-scm.com/'>git</a>(since 2.2) and <a href='http://phing.info/'>Phing</a> (and therefore <a href='http://www.phpunit.de/'>PHPUnit</a>), and can be easily extended to work with alternative version control or build tools.</td><td width='315'><img src='http://elektrischeslicht.de/xinc/book/images/xinc-logo-big.jpg' /> </td></tr>
<tr><td><b>Continuous Integration and PHP</b></blockquote>

<blockquote>On February 23rd Arno Schneider held a talk on Continuous Integration and PHP in general and Xinc in particular. The talk was hosted at the first <a href='http://www.phpbarcelona.org'>PHP Conference in Barcelona</a>. Here you can see the slides from the presentation:</blockquote>

<a href='http://www.slideshare.net/arnoschn/continuous-integration-and-php'>http://www.slideshare.net/arnoschn/continuous-integration-and-php</a></td><td><wiki:gadget url="http://www.ohloh.net/p/9498/widgets/project_factoids.xml" border="0" width="350" height="170"/></td></tr></table>

# Xinc 2.2 #

Release version 2.2, date: 09.12.2012

The pear channel can be found at http://pear.elektrischeslicht.de

Improvements:

```
- Issue 215: Add support for git (Pear::VersionControl_Git)
- Issue 221: Use Pear::VersionControl_SVN for SVN support.
- Issue 224: Update ExtJS to latest 2.x release (2.2.1)
- Issue 219: Replace deprecated PEAR_PackageFileManager with PEAR_PackageFileManager2
- Issue 171: PHPUnitTestResults Fails when logfile.xml is empty 
- Issue 169: Documentation plugin: content-type text/plain for php doc
- Issue 225: Undefined variable: res...
- Issue 223: If statusDir doesn't exists, xinc claims it isn't writeable
- Issue 226: Documentation Task Escape Issues
- Issue 227: xinc can not create ini file if ini file directory missing
- Issue 229: Installation is not working
- Issue 230: Xinc_Ini::get used but it is not a static method
- Issue 231: Do not use session_is_registered
- And some more code/coding style clean ups
```

Windows users are welcome to test on Windows as I've no Windows box to test on.

## Install ##

You can obtain Xinc 2.2 via [pear channel](http://elektrischeslicht.de/xinc/book/chapters/Setup.html#Setup.PearInstall) or download the latest version from Googlecode for manual installation using pear or install script.

## Documentation ##

The Documentation can be found at http://elektrischeslicht.de/xinc/book/
which is based on and inspired by the Phing documentation format.

For development the documentation can be found on http://elektrischeslicht.de/xinc/doc/

## Screenshots ##

![http://elektrischeslicht.de/xinc/images/xinc_dashboard_2.2.png](http://elektrischeslicht.de/xinc/images/xinc_dashboard_2.2.png)

_Screenshot: Dashboard view of Xinc 2.2 alpha2 version_

![http://elektrischeslicht.de/xinc/images/xinc_detail_2.2.png](http://elektrischeslicht.de/xinc/images/xinc_detail_2.2.png)

_Screenshot: Build Details view of Xinc 2.2 alpha2 version_


# Xinc 2.3 #

Release version 2.3 beta 1, date: 20.10.2013

The pear channel can be found at http://pear.elektrischeslicht.de

Main Improvements:

```
- Issue 188: Xinc should not need document root
- Issue 218: Remove Plotkit and Update ExtJS to 4.1.x
- Issue 238: Split xinc in packages to reduce dependencies.
- Issue 232: CleanUp the PlugIn code.
- Issue 241: Fix PHPUnitTest Publisher
- Issue 242: Check Remote Revision number for given checkout path
- Issue 243: Update XSL files for PHPUnit to a later revision, fix nasted testcases
- Issue 244: Eliminate PEAR PostInstall (to get ready for composer builds)
- Issue 246: Add TriggerTask as CoreTask, move Cron & Scheduler into them, add Sensor. This is a small step to the "force build" task.
- Issue  98: Code Metrics Support
- Issue 174: Allow changing artifact link behavior 
- Issue 250: publisher documentation, target directory not named correctly
- Issue 251: Log Message XML and ESC Sequences
```

The Checkstyle feature:

![http://elektrischeslicht.de/xinc/images/xinc-features1-2.3-alpha.png](http://elektrischeslicht.de/xinc/images/xinc-features1-2.3-alpha.png)
![http://elektrischeslicht.de/xinc/images/xinc-features2-2.3-alpha.png](http://elektrischeslicht.de/xinc/images/xinc-features2-2.3-alpha.png)