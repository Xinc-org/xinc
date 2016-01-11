# Plugins #


## Schedule ##
  * Class: Xinc\_Plugin\_Repos\_Schedule
  * Process-Slot: INIT\_PROCESS
  * Registered Tasks:
    * 

&lt;schedule/&gt;


  * Attributes:
    * interval: number of seconds between build-attempts
  * Description:
    * determines whether its time to build based on the last build time and the configured interval

## ModificationSet ##
  * Class: Xinc\_Plugin\_Repos\_ModificationSet
  * Process-Slot: PRE\_PROCESS
  * Registered Tasks:
    * 

&lt;modificationset/&gt;


  * Attributes: none
  * Description:
    * Executes the subtasks and passes the build-process on to the PROCESS Slot if a modification has been detected

### Svn ###
  * Class: Xinc\_Plugin\_Repos\_ModificationSet\_Svn
    * Process-Slot: PRE\_PROCESS
  * parent task: 

&lt;modificationset/&gt;


  * Registered Tasks:
    * 

&lt;svn/&gt;


  * Attributes:
    * directory: svn working copy
  * Description:
    * Checks if the @directory revision is up2date

### BuildAlways ###
  * Class: Xinc\_Plugin\_Repos\_ModificationSet\_BuildAlways
  * Process-Slot: PRE\_PROCESS
  * parent task: 

&lt;modificationset/&gt;


  * Registered Tasks:
    * 

&lt;buildalways/&gt;


  * Attributes: none
  * Description:
    * Always triggers a build

## Builder ##
  * Class: Xinc\_Plugin\_Repos\_Builder
  * Process-Slot: PROCESS
  * Registered Tasks:
    * 

&lt;builders/&gt;


  * Attributes: none
  * Description:
    * Executes the subtask builders and passes the build-process on to the POST\_PROCESS Slot after the build

## Phing ##
  * Class: Xinc\_Plugin\_Repos\_Phing
  * Process-Slot: PROCESS
  * Registered Tasks:
    * 

&lt;phingBuilder/&gt;


    * 

&lt;phingPublisher/&gt;


  * Attributes:
    * buildfile
    * target
  * Description:
    * Executes the phing-script @buildfile and calls target @target

## Publisher ##
  * Class: Xinc\_Plugin\_Repos\_Publisher
  * Process-Slot: POST\_PROCESS
  * Registered Tasks:
    * 

&lt;publisher/&gt;


  * Attributes: none
  * Description:
    * Executes the subtask publishers

### OnSuccess ###
  * Class: Xinc\_Plugin\_Repos\_Publisher\_OnSuccess
  * Process-Slot: POST\_PROCESS
  * parent task: 

&lt;publisher/&gt;


  * Registered Tasks:
    * 

&lt;onsuccess/&gt;


  * Attributes: none
  * Description:
    * contained publishers are called on a successful build only

### OnFailure ###
  * Class: Xinc\_Plugin\_Repos\_Publisher\_OnFailure
  * Process-Slot: POST\_PROCESS
  * parent task: 

&lt;publisher/&gt;


  * Registered Tasks:
    * 

&lt;onfailure/&gt;


  * Attributes: none
  * Description:
    * contained publishers are called on a failed build only

### OnRecovery ###
  * Class: Xinc\_Plugin\_Repos\_Publisher\_OnRecovery
  * Process-Slot: POST\_PROCESS
  * parent task: 

&lt;publisher/&gt;


  * Registered Tasks:
    * 

&lt;onrecovery/&gt;


  * Attributes: none
  * Description:
    * contained publishers are called on a recovered build only. A recovered build is the first successful build after a sequence of at least 1 failed build or more.


### Email ###
  * Class: Xinc\_Plugin\_Repos\_Publisher\_Email
  * Process-Slot: POST\_PROCESS
  * parent task: 

&lt;publisher/&gt;

 (

&lt;onsuccess/&gt;

,

&lt;onfailure/&gt;

,

&lt;onrecovery/&gt;

)
  * Registered Tasks:
    * 

&lt;email/&gt;


  * Attributes:
    * to
    * subject
    * message
  * Description:
    * Sends an email

### Homepage ###
  * Class: Xinc\_Plugin\_Repos\_Gui\_Homepage
  * Process-Slot: GUI
  * registered paths: /
  * Description:
    * A simple menu system which displays all Widgets

### Dashboard ###
  * Class: Xinc\_Plugin\_Repos\_Gui\_Dashboard
  * Process-Slot: GUI
  * registered paths: /dashboard , /dashboard/detail
  * Description:
    * Displaying build-results