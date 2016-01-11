# Introduction #

This documentation is "living" ;) It won't be finished until Xinc 2.0 is finally released.
The Plugin-Interface might change slightly in the upcoming weeks.


# Plugin Coding #
## Plugin Class ##
File: MyDomain/Plugin/ModificationSet/Fake.php
```
<?php
....
require_once 'Xinc/Plugin/Base.php';
require_once 'MyDomain/Plugin/ModificationSet/Fake/Task.php';

class MyDomain_Plugin_ModificationSet_Fake extends Xinc_Plugin_Base
{
    public function getTaskDefinitions()
    {
        return array(new MyDomain_Plugin_ModificationSet_Fake_Task($this));
    }

    public function validate()
    {
        // do all necessary checks here to validate that the plugin
        // can work properly
        return true;
    }

}

```

## Task Class ##
File: MyDomain/Plugin/ModificationSet/Fake/Task.php
```
<?php
....
require_once 'Xinc/Plugin/Task/Base.php';

class MyDomain_Plugin_ModificationSet_Fake_Task extends Xinc_Plugin_Task_Base
{
    public function getPluginSlot(){
        /**
         * see Xinc/Plugin/Slot.php for available slots
         */
        return Xinc_Plugin_Slot::PRE_PROCESS;
    }

    public function validate()
    {
        // do all necessary checks here to validate that the plugin
        // can work properly
        return true;
    }
    public function getName(){
         // return the task-element-name you want to use
         return "fake";
    }

    public function process(Xinc_Build_Interface &$build){
          // do whatever you need todo here and set the Build-status on the project
          // see Xinc/Build/Interface.php for available statuses

          // if subtasks have been registered loop over $this->_subtasks and call 
          // process() on them
          $build>setStatus(Xinc_Build_Interface::PASSED);
    }

}

```

## Include your plugin in Xinc ##

  1. Edit /etc/xinc/system.xml in the 

&lt;plugins/&gt;

 section
  1. insert the following line: 

&lt;plugin filename="MyDomain/Plugin/ModificationSet/Fake.php" classname="MyDomain\_Plugin\_ModificationSet\_Fake"/&gt;


  1. Restart xinc: /etc/init.d/xinc restart
  1. check the /var/log/xinc.log for errors
  1. Good luck!