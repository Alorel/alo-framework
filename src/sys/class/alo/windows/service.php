<?php
    namespace Alo\Windows;

    use Alo\Exception\OSException;
    use Alo;

    if (!defined('GEN_START')) {
        http_response_code(404);
    } elseif (!defined('PHPUNIT_RUNNING') && !Alo::serverIsWindows()) {
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
             * @param string      $serviceName The name of the service
             * @param string      $exePath     Path to the executable
             * @param null|string $displayName Optionally, a custom display name for the service
             *
             * @return string shell_exec() output
             */
            static function installExe($serviceName, $exePath, $displayName = null) {
                $cmd = 'sc create ' . $serviceName . ' binPath= "' . $exePath . '"';

                if ($displayName) {
                    $cmd .= ' DisplayName= "' . $displayName . '"';
                }

                return shell_exec($cmd);
            }
        }
    }
