# News #

This is the Xinc 1.0 documentation, which is deprecated. So please switch to Xinc 2.0 or the 2.1 beta.

## Installation ##

[Download Xinc 1.0](http://xinc.googlecode.com/files/xinc-1.0.tar.gz), uncompress the file, and then follow the relevant installation instructions below.

### Automated installation ###

```
cd xinc
sudo ./install.sh
```

If you would like to use the web reporting component, copy web/index.php to your web server directory.

### Manual installation (if you have root access) ###

  1. Copy the content of classes/ to a directory on the include path.
  1. Copy the bin/xinc script to e.g. /usr/local/bin/ (replace @PHP\_BIN@ with the path to the PHP binary).
  1. Copy the examples/init.d/xinc script to /etc/init.d/ (replace @ETC@ and @LOG@ with the relevant paths).
  1. Create the /etc/xinc directory, and chown to root.
  1. Xinc can then be run via the init.d script.
  1. If you would like to use the web reporting component, copy web/index.php to your web server directory.

### Manual installation (if you do not have root access) ###

  1. Copy the content of classes/ to a directory on the include path.
  1. Copy the bin/xinc script somewhere accessible (replace @PHP\_BIN@ with the path to the PHP binary).
  1. Decide where the config file, log file and status files are to go.
  1. Xinc can then be run via the bin/xinc script, by specifying the resource locations on the command line. See the Usage section below for more info.
  1. If you would like to use the web reporting component, copy web/index.php to your web server directory.  Edit the default file paths in index.php as necessary.

### Repository access ###

You can check out the latest code from the subversion repository:

```
svn co http://xinc.entrypoint.biz/svn/xinc/trunk xinc
```

## Configuration ##

Annotated example configuration file:

```
01: <?xml version="1.0"?>
02: <projects>
03:    <project name="Project Name" interval="10">
04:        <modificationsets>
05:            <svn directory="/path/to/project/dir" />
06:        </modificationsets>
07:        <builder type="phing" buildfile="/path/to/phing/build.xml" workingdirectory="/path/to/phing/working/dir"
08:                 target="test" />
09:        <publishers>
10:            <phing buildfile="/path/to/phing/build.xml" workingdirectory="/path/to/phing/working/dir" 
11:                   target="success" publishonsuccess="true" />
12:            <phing buildfile="/path/to/phing/build.xml" workingdirectory="/path/to/phing/working/dir"
13:                   target="failed" publishonfailure="true" />
14:            <email to="email@example.com"
15:                   subject="Project Name build failed"
16:                   message="The build failed."
17:                   publishonfailure="true" />
18:        </publishers>
19:    </project>
20: </projects>
```

  1. (line 03) project @name Name of project.
  1. (line 03) project @interval Subversion is polled every Interval number of seconds`*`.
  1. (line 05) svn @directory Subversion project directory to poll for updates.
  1. (line 07) builder @buildfile The main Phing build file for the project.
  1. (line 08) builder @target The Phing build task that builds the project.
  1. (line 07) builder @workingdirectory Optionally specify a working directory for the Phing builder (necessary if the Phing buildfile contains relative paths).
  1. (line 10) This line specifies that the Phing target 'success' should be called in build.xml file if the build is successful.
  1. (line 12) This line specifies that the Phing target 'failure' should be called in build.xml file if the build fails.
  1. (line 14) The email publisher can be used to send emails dependent on the success or failure of the build.

_`*`If the interval for a project is negative, Xinc will enter 'run-once mode': it will check the project a single time, act on any changes detected, and then exit._


---


## Usage ##

### Via the init.d script ###
```
/etc/init.d/xinc [start/stop]
```

### Direct ###
```
xinc [-f /path/to/config.xml] [-l /path/to/logfile.xml] [-s /path/to/statusdir]
```
The defaults are:

  * _config file:_ config.xml
  * _log file:_ ./log/xinc.xml
  * _status directory:_ ./log/xinc