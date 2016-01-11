## Installation ##

Make sure you have a version of [Phing](http://phing.info) installed and a recent installation of [PHPUnit](http://www.phpunit.de).

Download [Xinc 2.0-preview](http://xinc.googlecode.com/files/xinc-2.0-pre.tar.gz), uncompress the file, and then follow the relevant installation instructions below.

### Automated installation ###

```
cd xinc
sudo ./install.sh
```

**if you want to explore a simple example setup of a Xinc project please answer the question "Do you want to install the example SimpleProject?" with "y"**

if you experience any problems check the log-dir/xinc.log (default /var/log/xinc.log) for errors
  * in doubt run xinc manually in the foreground

If you would like to use the web reporting component, follow the instructions given at the end of the installation to include the virtualhost file in your apache-configuration.

### Manual installation (if you have root access) ###

  1. Copy the content of classes/ to a directory on the include path.
  1. Copy the bin/xinc script to e.g. /usr/local/bin/ (replace @PHP\_BIN@ with the path to the PHP binary).
  1. Copy the examples/init.d/xinc script to /etc/init.d/ (replace @ETC@ and @LOG@ with the relevant paths).
  1. Create the /etc/xinc directory, and chown to root.
  1. Xinc can then be run via the init.d script.
  1. If you would like to use the web reporting component, create a virtualhost and include  this in the apache-configuration:
```
Listen @PORT@
<VirtualHost 127.0.0.1:@PORT@>

DocumentRoot "@WEB_DIR@"
DirectoryIndex index.php
php_value auto_prepend_file "@INCLUDE@/Xinc/webloader.php"
<Directory "@WEB_DIR@">
	AllowOverride All
    Allow from All
</Directory>
</VirtualHost>

```

  1. (@PORT@) Port Xinc-Web-Application should be running on
  1. (@WEB\_DIR@) Folder where the index.php and .htaccess file from xinc/web are copied to
  1. (@INCLUDE@) Path where the Xinc class files are
  1. modify the @WEB\_DIR@/index.php to point to the right config-files
  1. You may want to substitute the IP-Address
  1. **Enable mod-rewrite**
  1. restart apache

### Manual installation (if you do not have root access) ###

  1. Copy the content of classes/ to a directory on the include path.
  1. Copy the bin/xinc script somewhere accessible (replace @PHP\_BIN@ with the path to the PHP binary).
  1. Decide where the config file, log file and status files are to go.
  1. Xinc can then be run via the bin/xinc script, by specifying the resource locations on the command line. See the Usage section below for more info.
  1. If you would like to use the web reporting component, create a virtualhost and include this in the apache-configuration:
```
Listen @PORT@
<VirtualHost 127.0.0.1:@PORT@>

DocumentRoot "@WEB_DIR@"
DirectoryIndex index.php
php_value auto_prepend_file "@INCLUDE@/Xinc/webloader.php"
<Directory "@WEB_DIR@">
	AllowOverride All
    Allow from All
</Directory>
</VirtualHost>

```

  1. (@PORT@) Port Xinc-Web-Application should be running on
  1. (@WEB\_DIR@) Folder where the index.php and .htaccess file from xinc/web are copied to
  1. (@INCLUDE@) Path where the Xinc class files are
  1. modify the @WEB\_DIR@/index.php to point to the right config-files
  1. You may want to substitute the IP-Address
  1. **Enable mod-rewrite**
  1. restart apache


### Repository access ###

You can check out the latest code from the subversion repository:

```
svn checkout http://xinc.googlecode.com/svn/trunk/ xinc
```

## Configuration of projects ##

Annotated example configuration file (config.xml):

```
01: <?xml version="1.0"?>
02: <xinc>
03:     <project name="SimpleProject">
04:     	<schedule interval="240"/>
05:         <modificationset>
06:             <buildalways/>
07:         </modificationset>
08:         <builders>
09:         	<phingBuilder buildfile="@EXAMPLE_DIR@/SimpleProject/build.xml" target="build"/>
10:         </builders>
11:         <publishers>
12:             <onfailure>
13: 	            <email to="root" 
14: 	                   subject="Simple Project Name build failed"
15: 	                   message="The build failed."/>
16:             </onfailure>
17:             <onsuccess>
18:             	<phingPublisher buildfile="@EXAMPLE_DIR@/SimpleProject/publish.xml" target="build"/>
19:             </onsuccess>
20:             <onrecovery>
21:             	<email to="root" 
22: 	                   subject="Simple Project Name build was recovered"
23: 	                   message="The build passed after having failed before."/>
24:             </onrecovery>
25:         </publishers>
26:     </project>
27: </xinc>
```

  1. (line 03) project @name Name of project.
  1. (line 04) schedule @interval The interval in which the build should happen
  1. (line 05) modificationset Plugin-Task, contains modificationsets which determine the need for a new build
  1. (line 06) buildalways Plugin-Task, acts as a modificationset and results in building everytime the build is scheduled
  1. (line 08) builders Plugin-Task, hosts different builder subtasks, that will be executed if a modification-set detects a need for a build
  1. (line 09) phingBuilder Phing-Plugin, builds the project using a @buildfile and calling the @target
  1. (line 11) publishers Plugin-Task, hosts different publishers that will be executed after the builders
  1. (line 12) onfailure Publisher-Task that acts only on failure of the builder-process and executes all subtasks
  1. (line 13) email Email-Publisher In this case its used to inform a person of a failed build
  1. (line 17) onsuccess Publisher-Task that acts only on success of the builder-process and executes all subtasks
  1. (line 18) phingPublisher Phing-Plugin, publishes the build-result using a @buildfile and calling a @target
  1. (line 20) onrecovery Publisher-Task that acts only if the current build was successful and the previous build failed, to inform about a recovered build



---

## Configuration of plugins ##

Annotated example configuration file (plugins.xml):
```
01: <?xml version="1.0"?>
02: <plugins>
03: 	<plugin filename="Xinc/Plugin/Repos/ModificationSet.php" classname="Xinc_Plugin_Repos_ModificationSet"/>
04: 	<plugin filename="Xinc/Plugin/Repos/ModificationSet/Svn.php" classname="Xinc_Plugin_Repos_ModificationSet_Svn"/>
05: 	<plugin filename="Xinc/Plugin/Repos/ModificationSet/BuildAlways.php" classname="Xinc_Plugin_Repos_ModificationSet_BuildAlways"/>
06: 	<plugin filename="Xinc/Plugin/Repos/Schedule.php" classname="Xinc_Plugin_Repos_Schedule"/>
07: 	<plugin filename="Xinc/Plugin/Repos/Builder.php" classname="Xinc_Plugin_Repos_Builder"/>
08: 	<plugin filename="Xinc/Plugin/Repos/Phing.php" classname="Xinc_Plugin_Repos_Phing"/>
09: 	<plugin filename="Xinc/Plugin/Repos/Publisher.php" classname="Xinc_Plugin_Repos_Publisher"/>
10: 	<plugin filename="Xinc/Plugin/Repos/Publisher/OnSuccess.php" classname="Xinc_Plugin_Repos_Publisher_OnSuccess"/>
11: 	<plugin filename="Xinc/Plugin/Repos/Publisher/OnRecovery.php" classname="Xinc_Plugin_Repos_Publisher_OnRecovery"/>
12: 	<plugin filename="Xinc/Plugin/Repos/Publisher/OnFailure.php" classname="Xinc_Plugin_Repos_Publisher_OnFailure"/>
13: 	<plugin filename="Xinc/Plugin/Repos/Publisher/Email.php" classname="Xinc_Plugin_Repos_Publisher_Email"/>
14: 	<plugin filename="Xinc/Plugin/Repos/Gui/Dashboard.php" classname="Xinc_Plugin_Repos_Gui_Dashboard"/>
15: 	<plugin filename="Xinc/Plugin/Repos/Gui/Homepage.php" classname="Xinc_Plugin_Repos_Gui_Homepage"/>
16: </plugins>

```
  1. (line 02) plugins Contains all registered plugins.
  1. (line 03) plugin Registers a plugin-class @classname from the file @filename
  1. (line 03-15) see [Plugins](Xinc_2_0_Plugins.md) for a description of registered base-plugins

---

## Usage of the Xinc Server ##

### Via the init.d script ###
```
/etc/init.d/xinc [start/stop]
```

### Direct ###
```
xinc [-f /path/to/config.xml] [-p /path/to/plugins.xml] [-l /path/to/logfile.xml] [-s /path/to/statusdir]
```
The defaults are:

  * _config file:_ config.xml
  * _plugins file:_ plugins.xml
  * _log file:_ ./log/xinc.xml
  * _status directory:_ ./log/xinc

## Usage of the Xinc Web-Application ##

  * Visit http://127.0.0.1:8080/ (_or your customized address_)
  * You will see the alpha version of the dashboard-Widget
    * The dashboard gives you an overview of all the configured projects and its build-status
      * By clicking on the name of the project you are going into the dashboard detail-view, which will give you a build-history plus log-messages for each build.