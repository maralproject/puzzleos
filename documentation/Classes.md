# Classes

All classes that are available in PuzzleOS

## AppManager (*appFramework.php*)

Manage applications

1. `AppManager::startApp($app)` *$app*: **string** rootname

   *NOTE: only allowed to run from index.php once*!

   Start main application by application rootname ($app). You can use this to initiate application at *debug.php*

   Return value: **bool**

2. `AppManager::listAll()`

   List all applications installed in PuzzleOS application directory (/applications)

   Return value: **array**:

   ```php
   $a[$manifest["rootname"]] = [
       "name" 		=> $manifest["rootname"],
       "rootname"	=> $manifest["rootname"],
       "dir" 		=> __ROOTDIR."/applications/" . $dir,
       "dir_name"	=> $dir,
       "title" 	=> $manifest["title"],
       "desc" 		=> $manifest["description"],
       "default" 	=> ($manifest["canBeDefault"] == 0 ? APP_CANNOT_DEFAULT : (ConfigurationMultidomain::$default_application == $manifest["rootname"] ? APP_DEFAULT : APP_NOT_DEFAULT)),
       "level" 	=> $manifest["permission"],
       "permission"=> $group,
       "group" 	=> $group,
       "services" 	=> explode(",",trim($manifest["services"])),
       "menus"		=> explode(",",trim($manifest["menus"])),
       "system" 	=> (Database::read("app_security","system","rootname",$manifest["rootname"]) == "1")
   ]
   ```

   ​

3. `AppManager::isInstalled($app)` *$app*: **string** rootname

   Get installation status of an app by application rootname.

   Return value: **bool**

4. `AppManager::isDefault($app)` *$app*: **string** rootname

   Check if an app is set as default by application rootname.

   Return value: **bool** 

5. `AppManager::isOnGroup($group_id)` *$group_id*: **integer** group id

   Check if at least one application is registered to a user group by group id.

   Return value: **bool**

6. `AppManager::chownApp($appname, $newgroup)` *$appname*: **string** rootname, *$newgroup*: **integer** group id

   Change application group ownership.

   Return value: **bool**

7. `AppManager::setDefaultByName($name)` *$name*: **string** rootname

   Set default application by application rootname.

   Return value: **bool**

8. `AppManager::getNameFromDirectory($directory)` *$directory*: **string** directory name (not path)

   Find application rootname based on its directory name.

   Return value: **string** rootname


## Application (appFramework.php)

Application instance.

Current running application instance can also be accessed by `$appProp`

1. `Application->__get($property)` *$property*: **string** app property

   Valid values for *$property*:

   * "title"	            Return value: **string** application title
   * "desc"                 Return value: **string** application description
   * "appname"        Return value: **string** rootname
   * "path"                 Return value: **string** application path
   * "rootdir"             Return value: **string** root directory
   * "uri", "url"           Return value: **string** URI
   * "isForbidden"    Return value: **bool**

2. `Application->prepare($name)` *$name*: **string** rootname

   *NOTE: use `new Application($rootname)` instead*

   Prepare application information for *$name*. This function will **not** run the application -- use `$appProp->run($name)` instead.

   Return value: **Application** instance for *$name* app.

3. `Application->run($name)` *$name*: **string** rootname

   Run an application in this instance.

   Return value: **bool**

4. `Application->loadView(...$param)` *$param*: **void**. Create *viewSmall.php* in target application directory  to handle the arguments

   Load a view of an application instance. This is controlled by *viewSmall.php* of an application. An example can be found on */applications/menus/viewSmall.php*

   Return value: **void**

5. `Application->loadMainView()` 

   Load the main view of the application.

   Return value: **void**

## Database (database.php)

