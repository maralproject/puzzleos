#Database

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