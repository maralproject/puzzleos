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

Database operation

1. `Database::getLastId($table, $col, $arg, ...$param)` *$table*: **string** table name, *$col*: **string** column name, *$arg*: **string** mysql query, *$param*: **string** mysql query parameters

   Get last ID or from specific column

   Return value: **string** ID

2. `Database::read($table, $column, $findByCol, $findByVal)` *$table*: **string** table name, *$column*: **string** column name to be read, *$findByCol*: **string** column name which contains *$findByVal*: **string** value

   Read a single record from a table

   Return value: **string** column value

3. `Database::readArg($table, $column, $arg, ...$param)` *$table*: **string** table name, *$column*: **string** column name, *$arg*: **string** mysql query, *$param*: **string** mysql query parameters

   Read a single record with custom query

   Return value: **string** column value

4. `Database::newRow($table, ...$array)` *$table*: **string** table name, *$array*: **string** array(field1, field2, field3,...) new row values

   Write a new record. Field will be discarded if column has default value

   Return value: **bool**

5. ​

## DatabaseRowInput (database.php)



## DatabaseTableBuilder (database.php)

Database and table structure builder object. Use this in **.table.php* file.

1. `DatabaseTableBuilder->newInitialRow(...$structure)` *$structure*: **array** table structure and initial record

   Create a structure along with initial record. This won't replace existing records.

   Return value: **void**

2. `DatabaseTableBuilder->newInitialRowAdvanced($structure)` *$structure*: **DatabaseRowInput** table structure

   Create a structure along with initial record. This won't replace existing records.

   Return value: **void**

3. `DatabaseTableBuilder->dropTable()`

   This will drop current table and create a new one.

   Return value: **void**

4. `DatabaseTableBuilder->addColumn($name, $type)` *$name*: **string** column name, *$type*: **string** column data type

   Add new column to current table.

   Return value: **DatabaseTableBuilder** new table object.

5. ​