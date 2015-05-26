<?php

   namespace Alo;

   if(!defined('GEN_START')) {
      http_response_code(404);
      die();
   }

   use Alo\Exception\CronException as CE;
   use Log;

   /**
    * The crontab editor class. You must call the commit() method to save your changes.
    *
    * @author Art <a.molcanovas@gmail.com>
    */
   class Cron {

      /**
       * Defines the day of the week as Sunday
       *
       * @var int
       */
      const WEEKDAY_SUN = 0;

      /**
       * Defines the day of the week as Monday
       *
       * @var int
       */
      const WEEKDAY_MON = 1;

      /**
       * Defines the day of the week as Tuesday
       *
       * @var int
       */
      const WEEKDAY_TUE = 2;

      /**
       * Defines the day of the week as Wednesday
       *
       * @var int
       */
      const WEEKDAY_WED = 3;

      /**
       * Defines the day of the week as Thursday
       *
       * @var int
       */
      const WEEKDAY_THU = 4;

      /**
       * Defines the day of the week as Friday
       *
       * @var int
       */
      const WEEKDAY_FRI = 5;

      /**
       * Defines the day of the week as Saturday
       *
       * @var int
       */
      const WEEKDAY_SAT = 6;

      /**
       * A pre-setting to run the cronjob yearly at 1 Jan, 00:00
       *
       * @var string
       */
      const CONST_YEARLY = '0 0 1 1 *';

      /**
       * A pre-setting to run the cronjob monthly at 00:00
       *
       * @var string
       */
      const CONST_MONTHLY = '0 0 1 * *';

      /**
       * A pre-setting to run the cronjob weekly on Sunday, 00:00
       *
       * @var string
       */
      const CONST_WEEKLY = '0 0 * * 0';

      /**
       * A pre-setting to run the cronjob daily at 00:00
       *
       * @var string
       */
      const CONST_DAILY = '0 0 * * *';

      /**
       * A pre-setting to run the cronjob hourly at 00 minutes
       *
       * @var string
       */
      const CONST_HOURLY = '0 * * * *';

      /**
       * A pre-setting to run the cronjob on server startup
       *
       * @var string
       */
      const CONST_REBOOT = '@reboot';

      /**
       * Defines the month as January
       *
       * @var int
       */
      const MONTH_JAN = 1;

      /**
       * Defines the month as February
       *
       * @var int
       */
      const MONTH_FEB = 2;

      /**
       * Defines the month as March
       *
       * @var int
       */
      const MONTH_MAR = 3;

      /**
       * Defines the month as April
       *
       * @var int
       */
      const MONTH_APR = 4;

      /**
       * Defines the month as May
       *
       * @var int
       */
      const MONTH_MAY = 5;

      /**
       * Defines the month as June
       *
       * @var int
       */
      const MONTH_JUN = 6;

      /**
       * Defines the month as July
       *
       * @var int
       */
      const MONTH_JUL = 7;

      /**
       * Defines the month as August
       *
       * @var int
       */
      const MONTH_AUG = 8;

      /**
       * Defines the month as September
       *
       * @var int
       */
      const MONTH_SEP = 9;

      /**
       * Defines the month as October
       *
       * @var int
       */
      const MONTH_OCT = 10;

      /**
       * Defines the month as November
       *
       * @var int
       */
      const MONTH_NOV = 11;

      /**
       * Defines the month as December
       *
       * @var int
       */
      const MONTH_DEC = 12;

      /**
       * Array of valid CRON constants
       *
       * @var array
       */
      protected static $validConstants = [
         '@yearly',
         '@annually',
         '@monthly',
         '@weekly',
         '@daily',
         '@hourly',
         '@reboot'
      ];

      /**
       * The current crontab data
       *
       * @var array
       */
      protected $crontab;

      /**
       * Whether changes should be autocommited automatically
       *
       * @var bool
       */
      protected $autocommit;

      /**
       * Instantiates the crontab handler
       *
       * @author Art <a.molcanovas@gmail.com>
       * @throws CE When the machine is running Windows
       */
      function __construct() {
         if(\server_is_windows()) {
            throw new CE('Windows does not support cron!', CE::E_WINDOWS);
         } else {
            $this->autocommit = false;
            $this->reloadCrontab();
         }
      }

      /**
       * (re)loads the cron job array
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Cron
       */
      function reloadCrontab() {
         $this->crontab = shell_exec('crontab -l');

         if($this->crontab) {
            $this->crontab = trim($this->crontab);
            $this->crontab = explode("\n", $this->crontab);

            //Make sure it's really empty
            $last_index = count($this->crontab) - 1;

            while($last_index >= 0 && !$this->crontab[$last_index]) {
               unset($this->crontab[$last_index]);
               $last_index--;
            }
         } else {
            $this->crontab = [];
         }

         Log::debug('(Re)loaded crontab contents');

         return $this;
      }

      /**
       * Edits the cron job at index $index. Can be substituded with the full
       * CRON expression (schedule + command) to perform a search -
       * use with caution!
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int        $index        The job index in the crontab array
       * @param string     $command      The command to run
       * @param int|string $minute_const The minute parameter
       * @param int|string $hour         The hour parameter
       * @param int|string $day          The day of the month parameter
       * @param int|string $month        The month parameter
       * @param int|string $weekday      The day of the week parameter
       *
       * @throws CE When the minute expression is invalid
       * @throws CE When the parameters are invalid
       * @throws CE When one or more parameters are non-scalar
       * @return Cron
       */
      function editCronJob($index,
                           $command,
                           $minute_const = '*',
                           $hour = '*',
                           $day = '*',
                           $month = '*',
                           $weekday = '*') {
         if(!is_numeric($index)) {
            $search = array_search($index, $this->crontab);

            if($search !== false) {
               $index = $search;
            }
         }

         return $this->editCrontab($index, $command, $minute_const, $hour, $day, $month, $weekday);
      }

      /**
       * Performs modifiation on the crontab file
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int        $index        The job index in the crontab array
       * @param string     $command      The command to run
       * @param int|string $minute_const The minute parameter
       * @param int|string $hour         The hour parameter
       * @param int|string $day          The day of the month parameter
       * @param int|string $month        The month parameter
       * @param int|string $weekday      The day of the week parameter
       *
       * @return Cron
       * @throws CE When the minute expression is invalid
       * @throws CE When the parameters are invalid
       * @throws CE When one or more parameters are non-scalar
       */
      protected function editCrontab($index,
                                     $command,
                                     $minute_const = '*',
                                     $hour = '*',
                                     $day = '*',
                                     $month = '*',
                                     $weekday = '*') {
         $schedule = '';

         if(!is_scalar($command) ||
            !is_scalar($minute_const) ||
            !is_scalar($hour) ||
            !is_scalar($day) ||
            !is_scalar($month) ||
            !is_scalar($weekday)
         ) {
            throw new CE('All cron attributes must be scalar!', CE::E_ARGS_NONSCALAR);
         } elseif(preg_match('/^(\@[a-z]+|[0-9 \*]{9})$/i', $minute_const)) {
            $atSign = stripos($minute_const, '@');

            if($atSign === false || ($atSign === 0 && in_array($minute_const, self::$validConstants))) {
               $schedule = $minute_const;
            } else {
               throw new CE('Invalid schedule minute expression: ' . $minute_const, CE::E_INVALID_MIN);
            }
         } elseif(!self::formatOK($minute_const, $hour, $day, $month, $weekday)) {
            throw new CE('Invalid schedule parameters: ' . json_encode([
                                                                          'minute/constant' => $minute_const,
                                                                          'hour'            => $hour,
                                                                          'day'             => $day,
                                                                          'month'           => $month,
                                                                          'weekday'         => $weekday,
                                                                          'cmd'             => $command,
                                                                          'index'           => $index
                                                                       ]), CE::E_INVALID_EXPR);
         } else {
            $add = $minute_const . ' ' . $hour . ' ' . $day . ' ' . $month . ' ' . $weekday . ' ' . $command;

            if($index === null) {
               $this->crontab[] = $add;
               Log::debug('Appended crontab with ' . $add);
            } else {
               $this->crontab[$index] = $add;
               Log::debug('Edited crontab index ' . $index . ' with ' . $add);
            }

            if($this->autocommit) {
               $this->commit();
            }
         }

         return $this;
      }

      /**
       * Checks whether all the fields are formatted properly
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int|string $min     The minute parameter
       * @param int|string $hour    The hour parameter
       * @param int|string $day     The day of the month parameter
       * @param int|string $month   The month parameter
       * @param int|string $weekday The day of the week parameter
       *
       * @return boolean
       */
      protected static function formatOK($min, $hour, $day, $month, $weekday) {
         $pat_min_h_mth = '/^(\*|[0-9]{1,2}|\*\/[0-9]{1,2}|[0-9,]+|[0-9\-]+)$/';
         $pat_day       = '/^(\*|[0-9]{1,2}|[0-9]{1,2}(L|W)|\*\/[0-9]{1,2}|[0-9,]+|[0-9\-]+)$/';
         $pat_weekday   = '/^(\*|[0-9]{1,2}|[0-9]{1,2}L|\*\/[0-9]{1,2}|[0-9,]+|[0-9\-]+)$/';

         return
            preg_match($pat_min_h_mth, $min) &&
            preg_match($pat_min_h_mth, $hour) &&
            preg_match($pat_min_h_mth, $month) &&
            preg_match($pat_day, $day) &&
            preg_match($pat_weekday, $weekday);
      }

      /**
       * Saves any changes made
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Cron
       */
      function commit() {
         $commit = trim(implode("\n", $this->crontab)) . "\n";
         file_put_contents("/tmp/crontab.txt", $commit);
         echo shell_exec('crontab /tmp/crontab.txt');

         Log::debug('Saved crontab changes');

         return $this;
      }

      /**
       * Appends the crontab file
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param string     $command      The command to run
       * @param int|string $minute_const The minute parameter
       * @param int|string $hour         The hour parameter
       * @param int|string $day          The day of the month parameter
       * @param int|string $month        The month parameter
       * @param int|string $weekday      The day of the week parameter
       *
       * @throws CE When the minute expression is invalid
       * @throws CE When the parameters are invalid
       * @throws CE When one or more parameters are non-scalar
       * @return Cron
       */
      function appendCrontab($command, $minute_const = '*', $hour = '*', $day = '*', $month = '*', $weekday = '*') {
         return $this->editCrontab(null, $command, $minute_const, $hour, $day, $month, $weekday);
      }

      /**
       * Removes the cron job @ index $index
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int $index The cron job's index in the array
       *
       * @return Cron
       */
      function deleteJob($index) {
         unset($this->crontab[$index]);
         Log::debug('Deleted crontab entry @ index ' . $index);

         if($this->autocommit) {
            $this->commit();
         }

         return $this;
      }

      /**
       * Clears the crontab
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return Cron
       */
      function clearCrontab() {
         $this->crontab = [];

         if($this->autocommit) {
            $this->commit();
         }

         return $this;
      }

      /**
       * If no parameter is passed or the parameter isn't TRUE/FALSE, returns the current autocommit setting, otherwise
       * sets it. Use with caution!
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param bool|null $set The desired setting if changing
       *
       * @return Cron|bool $this if not changing the autocommit value or the value otherwise
       */
      function autocommit($set = null) {
         if(is_bool($set)) {
            $this->autocommit = $set;

            return $this;
         } else {
            return $this->autocommit;
         }
      }

      /**
       * Returns crontab entry at index $i
       *
       * @author Art <a.molcanovas@gmail.com>
       *
       * @param int $i The index
       *
       * @return null|string
       */
      function getAtIndex($i) {
         return \get($this->crontab[$i]);
      }

      /**
       * Returns the crontab array
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return array
       */
      function getCrontab() {
         return $this->crontab;
      }

      /**
       * Returns a string representation of the object data
       *
       * @author Art <a.molcanovas@gmail.com>
       * @return string
       */
      function __toString() {
         return \lite_debug($this);
      }

   }
