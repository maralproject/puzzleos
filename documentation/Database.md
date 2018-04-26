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

5. `Database::updateRowAdvanced($table, $row_input, $findByCol, $findByVal)` *$table*: **string** table name, *$row_input*: **DatabaseRowInput** row(s) to be updated, *$findByCol*: **string** column name which contains *$findByVal*: **string** value

   Update database row

   Return value: **bool**

6. `Database::newRowAdvanced($table, $row_input)` *$table*: **string** table name, *$row_input*: **DatabaseRowInput**

   Write a new record

   Return value: **bool**

7. `Database::readAll($table, $options, ...$param)` *$table*: **string** table name, *$options*: **string** additional query, *$param*: **array string**

   Readl all records in a table

   Return value: **array**

## DatabaseRowInput (database.php)

An object for row input.

1. `DatabaseRowInput->setField($column_name, $value)` *$column_name*: **string** column name, *$value*: **string** column value

   Set field value

   Return value: **this**

2. `DatabaseRowInput->getStructure()`

   Get row structure

   Return value: **array** row structure

3. `DatabaseRowInput->clearStructure()`

   Clear row structure

   Return value: **this**

   ​



## DatabaseTableBuilder (database.php)

Database and table structure builder object. Use this in **.table.php* file (see [BuatAplikasi](BuatAplikasi.md)).

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

   Add new column to current table. This will automatically select recently added column internally.

   Return value: **this**

5. `DatabaseTableBuilder->selectColumn($name)` *$name*: **string** column name

   Select a column

   Return value: **this**

6. `DatabaseTableBuilder->setAsPrimaryKey()`

   Set a selected column as primary key

   Return value: **this**

7. `DatabaseTableBuilder->removePrimaryKey()`

   Remove primary key property from selected column

   Return value: **this**

8. `DatabaseTableBuilder->allowNull($bool)` *$bool*: **bool** (1=allow null)

   Allow null value for selected column

   Return value: **this**

9. `DatabaseTableBuilder->defaultValue($str)` *$str*: **string** default colum value

   Set default value for selected column

   Return value: **this**

10. `DatabaseTableBuilder->setType($type)` *$type*: **string** column type

   Set type for selected column

   Return value: **this**