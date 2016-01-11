# Introduction #

Build properties can be defined via 

&lt;property name="name" value="value"/&gt;

.
They can then be referenced in any attribute inside the projects xml via ${name}.


# Xinc Properties in Phing #

All properties registered in a Xinc project will be handed over to Phing with the prefix "xinc.".

Example:

Xinc:

```
...
<project name="test">
<property name="dir" value="/var/xinc/projects"/>
<property name="property" value="value"/>
....
<builders>
<phingBuilder buildfile="${dir}/build.xml"/>
</builders>
.....
</project>
```

Phing:

```
<project name="Xinc" basedir="." default="build">	
	
	
	<target name="build" depends="update, test">
		<echo message="building in ${xinc.dir} ..."/>
	</target>

....
</project>
```

# Builtin Properties #

|**name**|**phing name**|**description**|
|:-------|:-------------|:--------------|
|build.number|xinc.build.number|Build number, is incremented on each successful build|
|build.label|xinc.build.label|Build number + a prefix|
|build.timestamp|xinc.build.timestamp|Unixtimestamp of build, only available in builders or publishers|
|-       |cctimestamp   |see build.timestamp: compatibility with cruisecontrol|
|project.name|xinc.project.name|Name of the xinc project|
|-       |projectname   |see project.name: compatibility with cruisecontrol|
|workingdir|xinc.workingdir|The working directory of xinc|
|projectdir|xinc.projectdir|Project directory of xinc (i.e. /var/xinc/projects)|
|statusdir|xinc.statusdir|status directory of xinc (i.e. /var/xinc/status)|


