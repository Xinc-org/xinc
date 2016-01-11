# Xinc 2.0 Example #

Check the new [Xinc 2.0 preview](http://xinc.googlecode.com/files/xinc-2.0-pre.tar.gz) release and the included example project.

# Xinc 1.0 Example #

_Prerequisites: Phing, PHPUnit, Xinc._

For the purpose of this example, we'll use our own [simple Subversion project](SimpleSubversionProject.md).

We'll assume the example project files are checked out to /var/projects/project.  We can now configure Xinc to continuously integrate this project.

All we need to do is create a Xinc config file (config.xml e.g. /etc/xinc/config.xml) with the following content:

```
<?xml version="1.0"?>
<projects>
    <project name="Simple Project Name" interval="10">
        <modificationsets>
            <svn directory="/var/projects/project"/>
        </modificationsets>
        <builder type="phing" buildfile="/var/projects/project/build.xml" target="build"/>
        <publishers>
            <email to="myemail@example.com" 
                   subject="Simple Project Name build failed"
                   message="The build failed."
                   publishonfailure="true"/>
        </publishers>
    </project>
</projects>
```

This configuration file tells Xinc which directory our project can be found in, and that the ''build'' target of build.xml should be executed with Phing whenever a commit to the project is detected.  The file also instructs Xinc to send an email if the build should fail.

Finally we start Xinc:

```
/etc/init.d/xinc start &
```

Now Xinc is running it will periodically interrogate the /var/projects/project directory for changes to the Subversion project.  When these occur it will act as specified by the config file.

You can simulate a commit that breaks the build by checking out a copy of the project, commenting the 'return $this->output;' line in Page.php to prevent output being returned, and committing this code while Xinc is running.

When Xinc discovers that the build does not complete successfully it will use the publisher(s) specified in the config.xml file to report this.  In the example above we have told Xinc to report build failures using the email publisher.

We haven't used the Phing publisher in this example.  The Phing publisher can be used to run a Phing target on the success or failure of the project build.  See the Configuration section of the documentation for more info.
