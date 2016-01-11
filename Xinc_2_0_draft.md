# Introduction #

Version 2.0 of Xinc will mainly focus on an architectural change.
Xinc will be using a **Plugin-Architecture**, which will allow for faster evolution and broader community-support.

# Build-Process #

The build-process will be defined by the following Slots:
  * **PRE\_PROCESS**: for example: Place to put modification-sets
  * **PROCESS**: for example: Place to put builders
  * **POST\_PROCESS**: for example: Place to put publishers

# Plugin-Architecture #

Plugins will be able to:
  * define tasks (similar to taskdef in ant)
  * define subtasks as children of a kind of "task-group"
  * define Listeners
  * plug into one of the above defined slots
  * stop the build-process
  * continue the build-process