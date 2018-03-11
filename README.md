## Installation

- Download the [Latest Release](https://github.com/staxDB/humhub-modules-tasks/releases/latest) and upload contents to **/protected/modules/task**
- or simply clone this repo by `git clone https://github.com/staxDB/humhub-modules-tasks.git task` into **/protected/modules**
then go to `Administration -> Modules` and **Enable** the **Tasks** module.

**_Note:_** You should also enable the module on each Space (settings) you wan't to use it.

> **_Warning:_** this module is a **revised version** of the Tasks module and **uses the same table names!** Therefore, the old task module **MUST be uninstalled** first.


## Description
This task manager is a complete revision of the simple original task module and provides advanced features.

DECIDE  
You decide who is allowed to work on a task and who is not. You also assign responsible persons.

STAY INFORMED  
Keep responsible users up to date when task status gets changed. Before completing a task, responsible persons will be informed to review the task (if review is checked).
Reminders can also be created.

KEEPING AN OVERVIEW  
Add tasks either to the assigned & responsible users calendar or add it to the space calendar.
Also you can use the myTasks-Snippet to keep an overview of tasks, you can work on!

TODO
- add a repeat-function
    - recalculate the time period
    - before a task will be reset, check if it is completed --> if not, notify assigned and responsible users and do not reset the task (skip repeat)!
- add TaskResults (add text or upload files) for users who are assigned / responsible
- log some result-statistics to give out a user-performance overview
- testing (maybe someone can write tests for this module)
