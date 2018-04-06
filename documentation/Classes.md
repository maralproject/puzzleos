# Classes

All classes that are available in PuzzleOS

## AppManager (*appFramework.php*)

Manage applications

1. `AppManager::startApp($app)` *$app*: string, rootname

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

3. `AppManager::isInstalled($app)` *$app*: string, rootname

   Get installation status of an app by application rootname.

   Return value: **bool**

4. `AppManager::isDefault($app)` *$app*: string, rootname

   Check if an app is set as default by application rootname.

   Return value: **bool** 

5. `AppManager::isOnGroup($group_id)` *$group_id*: integer, group id

   Check if at least one application is registered to a user group by group id.

   Return value: **bool**

6. `AppManager::chownApp($appname, $newgroup)` *$appname*: string rootname, *$newgroup*: integer group id

   Change application group ownership.

   Return value: **bool**

7. `AppManager::setDefaultByName($name)` *$name*: string rootname

   Set default application by application rootname.

   Return value: **bool**

8. `AppManager::getNameFromDirectory($directory)` *$directory*: string, directory name (not path)

   Find application rootname based on its directory name.

   Return value: **string** rootname

