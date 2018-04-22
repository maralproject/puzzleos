# Cron

## CronJob (cron.php)

Register and run a cron job. 

1. `CronJob::register($key, $trigger, $F)` *$key*: **string** cron job key, *$trigger*: **CronTrigger** trigger, *$F*: **function** cron job script.

   Register a cron job. You **must** register it in application's services file (see [BuatAplikasi](BuatAplikasi.md)).

   A cron job key is isolated to the current registering application.

   Example:

   ```php
   $trigger=new CronTrigger; //more about CronTrigger below
   ...
       
       
   CronJob::register("cron_key", $trigger, function(){
       //actions
   })
   ```

## CronTrigger (cron.php)

A trigger object for CronJob class. Add a trigger for CronJob.

If a cron job is already executed at one of the specified triggers, it will NOT be executed.

In this documentation, let `$trigger=new CronTrigger;`

1. `CronTrigger->interval($seconds)` *$seconds*: **integer** number of seconds between CronJob executions.

   This trigger can NOT be combined with all other triggers.

   You can use T_MINUTE, T_HOUR, T_DAY definition for *$seconds*.

   Minimum interval is 15 minutes.

   Example: 

   ```php
   $trigger->interval(5*T_HOUR); //Execute a cron job every 5 hours
   ```

   Return value: **void**

2. `CronTrigger->hour($hour)` *$hour*: **integer** 24-hour format

   Add an hour trigger.

   Example:

   ```php
   $trigger->hour(15); //Execute a cron job every 3 P.M
   ```

   Return value: **void**

3. `CronTrigger->day($day)` *$day*: **integer** day number (0 is Sunday, 6 is Saturday)

   Add a day trigger.

   Example:

   ```php
   $trigger->day(5); //Execute a cron job every Friday
   ```

   Return value: **void**

4. `CronTrigger->date($date)` *$date*: **integer** date

   Add a date trigger. `CronTrigger->date(31)` will not activate on February.

   Example:

   ```php
   $trigger->date(13); //Execute a cron job on 13th every month
   ```

   Return value: **void**

5. `CronTrigger->month($month)` *$month*: **integer** month

   Add a month trigger.

   Example:

   ```php
   $trigger->month(11); //Execute a cron job every November
   ```

   Return value: **void**

6. `CronTrigger->year($year)` *$year*: **integer** 4-digit year

   Add a year trigger. Note that this trigger will activate once.

   Example:

   ```php
   $trigger->year(2018); //Execute a cron job on 2018
   ```

   Return value: **void**

**NOTE**: You can combine triggers. For example:

```php
$trigger=new CronTrigger;
$trigger->day(5)->date(13); //Execute a cron job every Friday 13th
```

