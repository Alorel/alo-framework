<?php
   namespace Alo\Windows;

   use Alo\Exception\OSException;

   if(!defined('GEN_START')) {
      http_response_code(404);
   } elseif(!defined('PHPUNIT_RUNNING') && !server_is_windows()) {
      throw new OSException('The service manager is only supported on Windows.');
   } else {

      /**
       * Windows service handler
       *
       * @author Art <a.molcanovas@gmail.com>
       */
      abstract class Service {

         /**
          * Checks if a service exists
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name Service name
          *
          * @return bool
          */
         static function exists($name) {
            return trim(shell_exec(DIR_SYS . 'bin' . DIRECTORY_SEPARATOR . 'serviceexists.bat ' . $name)) == 'OK';
         }

         /**
          * Deletes a service
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name Service name
          *
          * @return string shell_exec() output
          */
         static function delete($name) {
            return self::stop($name) . PHP_EOL . shell_exec('sc delete ' . $name);
         }

         /**
          * Stops a service
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name Service name
          *
          * @return string shell_exec() output
          */
         static function stop($name) {
            return shell_exec('sc stop ' . $name);
         }

         /**
          * Starts a service
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string $name Service name
          *
          * @return string shell_exec() output
          */
         static function start($name) {
            return shell_exec('sc start ' . $name);
         }

         /**
          * Installes a service from an executable
          *
          * @author Art <a.molcanovas@gmail.com>
          *
          * @param string      $service_name The name of the service
          * @param string      $exe_path     Path to the executable
          * @param null|string $display_name Optionally, a custom display name for the service
          *
          * @return string shell_exec() output
          */
         static function installExe($service_name, $exe_path, $display_name = null) {
            $cmd = 'sc create ' . $service_name . ' binPath= "' . $exe_path . '"';

            if($display_name) {
               $cmd .= ' DisplayName= "' . $display_name . '"';
            }

            return shell_exec($cmd);
         }
      }
   }
